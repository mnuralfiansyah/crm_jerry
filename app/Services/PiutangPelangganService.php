<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PiutangPelangganService
{
    public function summary(): array
    {
        $summary = $this->baseQuery()
            ->select(
                DB::raw('COUNT(penjualan.id) as jumlah_nota'),
                DB::raw('COUNT(DISTINCT pelanggan.id) as jumlah_pelanggan'),
                DB::raw('COALESCE(SUM(penjualan.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(penjualan.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(piutang_total.sisa_piutang), 0) as total_sisa_piutang'),
                DB::raw('MAX(DATEDIFF(CURRENT_DATE, DATE(penjualan.tanggal))) as umur_tertua')
            )
            ->first();

        if ($summary === null) {
            return [];
        }

        return (array) $summary;
    }

    public function piutang_pelanggan(): array
    {
        return $this->baseQuery()
            ->select(
                'penjualan.id as id_penjualan',
                'penjualan.kode_penjualan',
                DB::raw("DATE_FORMAT(penjualan.tanggal, '%d-%m-%y') as tanggal_penjualan"),
                DB::raw('DATE(penjualan.tanggal) as tanggal'),
                DB::raw('DATEDIFF(CURRENT_DATE, DATE(penjualan.tanggal)) as umur'),
                'pelanggan.id as id_pelanggan',
                'pelanggan.nama as nama_pelanggan',
                'pelanggan.nomor_hp',
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',
                'cabang.id as id_cabang',
                'cabang.nama_cabang',
                'toko.id as id_toko',
                'toko.nama_toko',
                'penjualan.grand_total',
                'penjualan.jumlah_bayar',
                'penjualan.jumlah_bayar_tunai',
                'penjualan.jumlah_bayar_bank',
                DB::raw('COALESCE(piutang_total.sisa_piutang, 0) as sisa_piutang'),
                'pb.lama_piutang',
                'pb.limit_piutang',
                'penjualan.catatan'
            )
            ->orderBy('penjualan.tanggal')
            ->orderBy('penjualan.created_at')
            ->get()
            ->map(fn ($piutang) => (array) $piutang)
            ->all();
    }

    public function groupByPelanggan(): array
    {
        return $this->baseQuery()
            ->select(
                'pelanggan.id as id_pelanggan',
                'pelanggan.nama as nama_pelanggan',
                'pelanggan.nomor_hp',
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',
                DB::raw('COUNT(penjualan.id) as jumlah_nota'),
                DB::raw('COALESCE(SUM(penjualan.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(penjualan.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(piutang_total.sisa_piutang), 0) as total_sisa_piutang'),
                DB::raw('DATE(MIN(penjualan.tanggal)) as tanggal_piutang_tertua'),
                DB::raw('DATE(MAX(penjualan.tanggal)) as tanggal_piutang_terakhir'),
                DB::raw('MAX(DATEDIFF(CURRENT_DATE, DATE(penjualan.tanggal))) as umur_tertua'),
                DB::raw('COALESCE(MAX(pb.lama_piutang), 0) as lama_piutang'),
                DB::raw('COALESCE(MAX(pb.limit_piutang), 0) as limit_piutang')
            )
            ->groupBy(
                'pelanggan.id',
                'pelanggan.nama',
                'pelanggan.nomor_hp',
                'sales.id',
                'sales.nama_karyawan'
            )
            ->orderByDesc('total_sisa_piutang')
            ->get()
            ->map(fn ($pelanggan) => (array) $pelanggan)
            ->all();
    }

    public function aging(): array
    {
        $umurExpression = 'DATEDIFF(CURRENT_DATE, DATE(penjualan.tanggal))';
        $bucketExpression = "CASE
            WHEN {$umurExpression} <= 30 THEN '0-30'
            WHEN {$umurExpression} <= 60 THEN '31-60'
            WHEN {$umurExpression} <= 90 THEN '61-90'
            ELSE '>90'
        END";

        return $this->baseQuery()
            ->select(
                DB::raw("{$bucketExpression} as umur"),
                DB::raw('COUNT(penjualan.id) as jumlah_nota'),
                DB::raw('COUNT(DISTINCT pelanggan.id) as jumlah_pelanggan'),
                DB::raw('COALESCE(SUM(piutang_total.sisa_piutang), 0) as total_sisa_piutang'),
                DB::raw("MIN({$umurExpression}) as min_umur"),
                DB::raw("MAX({$umurExpression}) as max_umur")
            )
            ->groupBy(DB::raw($bucketExpression))
            ->orderByRaw("MIN({$umurExpression})")
            ->get()
            ->map(fn ($aging) => (array) $aging)
            ->all();
    }

    private function baseQuery()
    {
        $sisaPiutang = DB::table('pelanggan_utang_piutang')
            ->select(
                'id_penjualan',
                DB::raw('(SUM(uang) * -1) as sisa_piutang')
            )
            ->whereNull('deleted_at')
            ->whereNotNull('id_penjualan')
            ->groupBy('id_penjualan');

        $query = DB::table('penjualan')
            ->leftJoinSub($sisaPiutang, 'piutang_total', function ($join) {
                $join->on('piutang_total.id_penjualan', '=', 'penjualan.id');
            })
            ->join('pelanggan', 'pelanggan.id', '=', 'penjualan.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'penjualan.id_cabang')
            ->join('toko', 'toko.id', '=', 'penjualan.id_toko')
            ->leftJoin('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->leftJoin('pelanggan_biodata as pb', 'pb.id_pelanggan', '=', 'pelanggan.id')
            ->whereNull('penjualan.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('penjualan.status_transaksi', 1)
            ->where('penjualan.is_lunas', 0);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');
        $idPelanggan = request()->get('id_pelanggan', '');

        if ($startDate !== '') {
            $query->whereDate('penjualan.tanggal', '>=', $startDate);
        }

        if ($endDate !== '') {
            $query->whereDate('penjualan.tanggal', '<=', $endDate);
        }

        if ($idCabang !== '') {
            $query->where('penjualan.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('penjualan.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        if ($idPelanggan !== '') {
            $query->where('penjualan.id_pelanggan', $idPelanggan);
        }

        return $query;
    }
}
