<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class HutangTokoService
{
    public function summary(): array
    {
        $summary = $this->baseQuery()
            ->select(
                DB::raw('COUNT(tb.id) as jumlah_nota'),
                DB::raw('COALESCE(SUM(tb.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(cup_total.sisa_hutang), 0) as total_sisa_hutang'),
                DB::raw('MAX(DATEDIFF(CURRENT_DATE, DATE(tb.tanggal))) as umur_tertua')
            )
            ->first();

        return $summary === null ? [] : (array) $summary;
    }

    public function hutang_toko(): array
    {
        $query = $this->baseQuery();

        return $query
            ->select(
                'tb.id as id_terima_barang',
                'tb.nomor as nomor_pembelian',
                DB::raw("DATE_FORMAT(tb.tanggal, '%d-%m-%y') as tanggal_pembelian"),
                DB::raw("CASE WHEN tb.jenis_terima_barang = 'Antar Cabang' THEN t.nama_toko ELSE v.nama_vendor END as pengirim"),
                'p.nama_toko as toko_penerima',
                'tb.grand_total',
                DB::raw('COALESCE(cup_total.sisa_hutang, 0) as sisa_hutang'),
                'tb.jenis_terima_barang'
            )
            ->get()
            ->map(fn ($hutang) => (array) $hutang)
            ->all();
    }

    public function groupByPengirim(): array
    {
        $query = $this->baseQuery();

        return $query
            ->select(
                DB::raw("CASE WHEN tb.jenis_terima_barang = 'Antar Cabang' THEN t.id ELSE v.id END as id_pengirim"),
                DB::raw("CASE WHEN tb.jenis_terima_barang = 'Antar Cabang' THEN t.nama_toko ELSE v.nama_vendor END as pengirim"),
                'tb.jenis_terima_barang',
                DB::raw('COUNT(tb.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(tb.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(cup_total.sisa_hutang), 0) as total_sisa_hutang')
            )
            ->groupBy(
                DB::raw("CASE WHEN tb.jenis_terima_barang = 'Antar Cabang' THEN t.id ELSE v.id END"),
                DB::raw("CASE WHEN tb.jenis_terima_barang = 'Antar Cabang' THEN t.nama_toko ELSE v.nama_vendor END"),
                'tb.jenis_terima_barang'
            )
            ->orderBy('pengirim')
            ->get()
            ->map(fn ($pengirim) => (array) $pengirim)
            ->all();
    }

    public function groupByToko(): array
    {
        $query = $this->baseQuery();

        return $query
            ->select(
                'p.id as id_toko',
                'p.nama_toko',
                DB::raw('COUNT(tb.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(tb.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(cup_total.sisa_hutang), 0) as total_sisa_hutang')
            )
            ->groupBy(
                'p.id',
                'p.nama_toko'
            )
            ->orderBy('p.nama_toko')
            ->get()
            ->map(fn ($toko) => (array) $toko)
            ->all();
    }

    public function aging(): array
    {
        $umurExpression = 'DATEDIFF(CURRENT_DATE, DATE(tb.tanggal))';
        $bucketExpression = "CASE
            WHEN {$umurExpression} <= 30 THEN '0-30'
            WHEN {$umurExpression} <= 60 THEN '31-60'
            WHEN {$umurExpression} <= 90 THEN '61-90'
            ELSE '>90'
        END";

        return $this->baseQuery()
            ->select(
                DB::raw("{$bucketExpression} as umur"),
                DB::raw('COUNT(tb.id) as jumlah_nota'),
                DB::raw('COALESCE(SUM(tb.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(cup_total.sisa_hutang), 0) as total_sisa_hutang'),
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
        $sisaHutang = DB::table('cabang_utang_piutang')
            ->select(
                'id_terima_barang',
                DB::raw('SUM(uang) as sisa_hutang')
            )
            ->whereNull('deleted_at')
            ->groupBy('id_terima_barang');

        $query = DB::table('terima_barang as tb')
            ->leftJoinSub($sisaHutang, 'cup_total', function ($join) {
                $join->on('cup_total.id_terima_barang', '=', 'tb.id');
            })
            ->leftJoin('vendor as v', 'v.id', '=', 'tb.id_vendor')
            ->leftJoin('toko as t', 't.id', '=', 'tb.id_tujuan_toko')
            ->leftJoin('toko as p', 'p.id', '=', 'tb.id_toko')
            ->whereNull('tb.deleted_at')
            ->where('tb.is_konfirmasi', 1)
            ->where('tb.is_lunas', 0);

        $idToko = request()->get('id_toko', '');
        $idTujuanToko = request()->get('id_tujuan_toko', '');

        if ($idToko !== '') {
            $query->where('tb.id_toko', $idToko);
        }

        if ($idTujuanToko !== '') {
            $query->where('tb.id_tujuan_toko', $idTujuanToko);
        }

        return $query;
    }
}
