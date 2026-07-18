<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function summary(): array
    {
        $penjualan = $this->penjualanQuery()
            ->select(
                DB::raw('COUNT(penjualan.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(penjualan.grand_total), 0) as total_omzet'),
                DB::raw('COALESCE(SUM(penjualan.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(penjualan.jumlah_bayar_tunai), 0) as total_bayar_tunai'),
                DB::raw('COALESCE(SUM(penjualan.jumlah_bayar_bank), 0) as total_bayar_bank'),
                DB::raw('COALESCE(SUM(penjualan.total_diskon), 0) as total_diskon'),
                DB::raw('COALESCE(SUM(penjualan.total_qty), 0) as total_qty')
            )
            ->first();

        $retur = $this->returQuery()
            ->select(
                DB::raw('COUNT(retur_penjualan.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(retur_penjualan.jumlah_retur), 0) as total_retur')
            )
            ->first();

        $piutang = $this->piutangQuery()
            ->select(
                DB::raw('COUNT(penjualan.id) as jumlah_nota'),
                DB::raw('COUNT(DISTINCT pelanggan.id) as jumlah_pelanggan'),
                DB::raw('COALESCE(SUM(piutang_total.sisa_piutang), 0) as total_sisa_piutang')
            )
            ->first();

        $hutang = $this->hutangQuery()
            ->select(
                DB::raw('COUNT(tb.id) as jumlah_nota'),
                DB::raw('COALESCE(SUM(tb.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(cup_total.sisa_hutang), 0) as total_sisa_hutang')
            )
            ->first();

        $pelanggan = $this->pelangganSummary();

        return [
            'penjualan' => (array) $penjualan,
            'retur' => (array) $retur,
            'piutang' => (array) $piutang,
            'hutang' => (array) $hutang,
            'pelanggan' => $pelanggan,
        ];
    }

    public function trend(): array
    {
        $period = request()->get('period', 'daily');
        $dateExpression = match ($period) {
            'weekly' => "DATE_FORMAT(penjualan.tanggal, '%x-W%v')",
            'monthly' => "DATE_FORMAT(penjualan.tanggal, '%Y-%m')",
            default => 'DATE(penjualan.tanggal)',
        };

        return $this->penjualanQuery()
            ->select(
                DB::raw("{$dateExpression} as periode"),
                DB::raw('COUNT(penjualan.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(penjualan.grand_total), 0) as total_omzet'),
                DB::raw('COALESCE(SUM(penjualan.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(penjualan.total_qty), 0) as total_qty')
            )
            ->groupBy(DB::raw($dateExpression))
            ->orderBy('periode')
            ->get()
            ->map(fn ($trend) => (array) $trend)
            ->all();
    }

    public function salesPerformance(): array
    {
        return $this->penjualanQuery()
            ->select(
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',
                DB::raw('COUNT(penjualan.id) as jumlah_transaksi'),
                DB::raw('COUNT(DISTINCT penjualan.id_pelanggan) as jumlah_pelanggan'),
                DB::raw('COALESCE(SUM(penjualan.grand_total), 0) as total_omzet'),
                DB::raw('COALESCE(SUM(penjualan.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(penjualan.total_qty), 0) as total_qty'),
                DB::raw('COALESCE(AVG(penjualan.grand_total), 0) as rata_rata_transaksi')
            )
            ->groupBy(
                'sales.id',
                'sales.nama_karyawan'
            )
            ->orderByDesc('total_omzet')
            ->get()
            ->map(fn ($sales) => (array) $sales)
            ->all();
    }

    private function penjualanQuery(): Builder
    {
        $query = DB::table('penjualan')
            ->join('pelanggan', 'pelanggan.id', '=', 'penjualan.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'penjualan.id_cabang')
            ->join('toko', 'toko.id', '=', 'penjualan.id_toko')
            ->leftJoin('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('penjualan.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('penjualan.status_transaksi', 1);

        return $this->applyFilters($query, 'penjualan', 'pelanggan.id_sales', 'penjualan.tanggal');
    }

    private function returQuery(): Builder
    {
        $query = DB::table('retur_penjualan')
            ->join('pelanggan', 'pelanggan.id', '=', 'retur_penjualan.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'retur_penjualan.id_cabang')
            ->join('toko', 'toko.id', '=', 'retur_penjualan.id_toko')
            ->leftJoin('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('retur_penjualan.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('retur_penjualan.is_konfirmasi', 1);

        return $this->applyFilters($query, 'retur_penjualan', 'pelanggan.id_sales', 'retur_penjualan.tanggal');
    }

    private function piutangQuery(): Builder
    {
        $sisaPiutang = DB::table('pelanggan_utang_piutang')
            ->select('id_penjualan', DB::raw('(SUM(uang) * -1) as sisa_piutang'))
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
            ->whereNull('penjualan.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at')
            ->where('penjualan.status_transaksi', 1)
            ->where('penjualan.is_lunas', 0);

        return $this->applyFilters($query, 'penjualan', 'pelanggan.id_sales', 'penjualan.tanggal');
    }

    private function hutangQuery(): Builder
    {
        $sisaHutang = DB::table('cabang_utang_piutang')
            ->select('id_terima_barang', DB::raw('SUM(uang) as sisa_hutang'))
            ->whereNull('deleted_at')
            ->whereNotNull('id_terima_barang')
            ->groupBy('id_terima_barang');

        $query = DB::table('terima_barang as tb')
            ->leftJoinSub($sisaHutang, 'cup_total', function ($join) {
                $join->on('cup_total.id_terima_barang', '=', 'tb.id');
            })
            ->whereNull('tb.deleted_at')
            ->where('tb.is_konfirmasi', 1)
            ->where('tb.is_lunas', 0);

        return $this->applyFilters($query, 'tb', null, 'tb.tanggal');
    }

    private function pelangganSummary(): array
    {
        $query = DB::table('pelanggan')
            ->whereNull('pelanggan.deleted_at');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($idCabang !== '') {
            $query->where('pelanggan.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('pelanggan.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        $summary = $query
            ->select(
                DB::raw('COUNT(pelanggan.id) as total_pelanggan'),
                DB::raw('SUM(CASE WHEN pelanggan.is_aktif = 1 THEN 1 ELSE 0 END) as pelanggan_aktif'),
                DB::raw('SUM(CASE WHEN pelanggan.is_aktif = 0 THEN 1 ELSE 0 END) as pelanggan_nonaktif')
            )
            ->first();

        return (array) $summary;
    }

    private function applyFilters(Builder $query, string $table, ?string $salesColumn, string $dateColumn): Builder
    {
        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');
        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate !== '') {
            $query->whereDate($dateColumn, '>=', $startDate);
        }

        if ($endDate !== '') {
            $query->whereDate($dateColumn, '<=', $endDate);
        }

        if ($idCabang !== '') {
            $query->where("{$table}.id_cabang", $idCabang);
        }

        if ($idToko !== '') {
            $query->where("{$table}.id_toko", $idToko);
        }

        if ($salesColumn !== null && $idSales !== '') {
            $query->where($salesColumn, $idSales);
        }

        return $query;
    }
}
