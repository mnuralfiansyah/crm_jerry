<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomerDashboardService
{
    public function summary(): array
    {
        $summary = $this->customerQuery()
            ->select(
                DB::raw('COUNT(pelanggan.id) as total_pelanggan'),
                DB::raw('SUM(CASE WHEN pelanggan.is_aktif = 1 THEN 1 ELSE 0 END) as pelanggan_aktif'),
                DB::raw('SUM(CASE WHEN pelanggan.is_aktif = 0 THEN 1 ELSE 0 END) as pelanggan_nonaktif'),
                DB::raw('COALESCE(SUM(pb.piutang_saat_ini), 0) as total_piutang_saat_ini'),
                DB::raw('COALESCE(SUM(pb.limit_piutang), 0) as total_limit_piutang')
            )
            ->first();

        return (array) $summary;
    }

    public function customers(): array
    {
        return $this->customerQuery()
            ->select(
                'pelanggan.id as id_pelanggan',
                'pelanggan.kode_pelanggan',
                'pelanggan.nama as nama_pelanggan',
                'pelanggan.nomor_hp',
                'pelanggan.alamat',
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',
                'cabang.id as id_cabang',
                'cabang.nama_cabang',
                'toko.id as id_toko',
                'toko.nama_toko',
                'pb.lama_piutang',
                'pb.limit_piutang',
                'pb.piutang_saat_ini',
                DB::raw('(
                    SELECT COUNT(p.id)
                    FROM penjualan AS p
                    WHERE p.deleted_at IS NULL
                        AND p.status_transaksi = 1
                        AND p.id_pelanggan = pelanggan.id
                ) as jumlah_transaksi'),
                DB::raw('(
                    SELECT COALESCE(SUM(p.grand_total), 0)
                    FROM penjualan AS p
                    WHERE p.deleted_at IS NULL
                        AND p.status_transaksi = 1
                        AND p.id_pelanggan = pelanggan.id
                ) as total_belanja'),
                DB::raw('(
                    SELECT DATE(MAX(p.tanggal))
                    FROM penjualan AS p
                    WHERE p.deleted_at IS NULL
                        AND p.status_transaksi = 1
                        AND p.id_pelanggan = pelanggan.id
                ) as tanggal_terakhir_belanja')
            )
            ->orderBy('pelanggan.nama')
            ->get()
            ->map(fn ($customer) => (array) $customer)
            ->all();
    }

    public function inactive(): array
    {
        $query = $this->customerQuery()
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('penjualan as p')
                    ->whereColumn('p.id_pelanggan', 'pelanggan.id')
                    ->whereNull('p.deleted_at')
                    ->where('p.status_transaksi', 1);

                $this->applyDateFilters($subQuery, 'p.tanggal');
            });

        return $query
            ->select(
                'pelanggan.id as id_pelanggan',
                'pelanggan.kode_pelanggan',
                'pelanggan.nama as nama_pelanggan',
                'pelanggan.nomor_hp',
                'pelanggan.alamat',
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',
                'cabang.id as id_cabang',
                'cabang.nama_cabang',
                'toko.id as id_toko',
                'toko.nama_toko',
                DB::raw('(
                    SELECT DATE(MAX(p.tanggal))
                    FROM penjualan AS p
                    WHERE p.deleted_at IS NULL
                        AND p.status_transaksi = 1
                        AND p.id_pelanggan = pelanggan.id
                ) as tanggal_terakhir_belanja')
            )
            ->orderBy('pelanggan.nama')
            ->get()
            ->map(fn ($customer) => (array) $customer)
            ->all();
    }

    public function salesHistory(string $idPelanggan): array
    {
        $query = DB::table('penjualan')
            ->join('cabang', 'cabang.id', '=', 'penjualan.id_cabang')
            ->join('toko', 'toko.id', '=', 'penjualan.id_toko')
            ->leftJoin('karyawan as sales', 'sales.id', '=', 'penjualan.id_sales')
            ->whereNull('penjualan.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at')
            ->where('penjualan.status_transaksi', 1)
            ->where('penjualan.id_pelanggan', $idPelanggan);

        $this->applyDateFilters($query, 'penjualan.tanggal');

        return $query
            ->select(
                'penjualan.id as id_penjualan',
                'penjualan.kode_penjualan',
                DB::raw("DATE_FORMAT(penjualan.tanggal, '%d-%m-%y') as tanggal_penjualan"),
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',
                'cabang.id as id_cabang',
                'cabang.nama_cabang',
                'toko.id as id_toko',
                'toko.nama_toko',
                'penjualan.total_qty',
                'penjualan.total_harga',
                'penjualan.total_diskon',
                'penjualan.grand_total',
                'penjualan.jumlah_bayar',
                'penjualan.sisa_hutang',
                'penjualan.is_lunas'
            )
            ->orderByDesc('penjualan.tanggal')
            ->orderByDesc('penjualan.created_at')
            ->get()
            ->map(fn ($history) => (array) $history)
            ->all();
    }

    public function transactions(string $idPelanggan): array
    {
        $query = DB::table('detail_penjualan as dp')
            ->join('penjualan as p', 'p.id', '=', 'dp.id_penjualan')
            ->join('barang as b', 'b.id', '=', 'dp.id_barang')
            ->leftJoin('barang_varian as bv', 'bv.id', '=', 'dp.id_barang_varian')
            ->whereNull('dp.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('b.deleted_at')
            ->where('p.status_transaksi', 1)
            ->where('p.id_pelanggan', $idPelanggan);

        $this->applyDateFilters($query, 'p.tanggal');

        return $query
            ->select(
                'p.id as id_penjualan',
                'p.kode_penjualan',
                DB::raw("DATE_FORMAT(p.tanggal, '%d-%m-%y') as tanggal_penjualan"),
                'b.id as id_barang',
                'b.kode_barang',
                DB::raw("CASE WHEN b.varian_barang = 'iya' THEN bv.barcode ELSE b.kode_barang END as kode_barcode"),
                'b.nama_barang',
                'dp.qty',
                'dp.harga_beli',
                'dp.harga_jual',
                'dp.diskon',
                'dp.sub_total'
            )
            ->orderByDesc('p.tanggal')
            ->orderBy('b.nama_barang')
            ->get()
            ->map(fn ($transaction) => (array) $transaction)
            ->all();
    }

    private function customerQuery(): Builder
    {
        $query = DB::table('pelanggan')
            ->leftJoin('pelanggan_biodata as pb', 'pb.id_pelanggan', '=', 'pelanggan.id')
            ->leftJoin('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->leftJoin('cabang', 'cabang.id', '=', 'pelanggan.id_cabang')
            ->leftJoin('toko', 'toko.id', '=', 'pelanggan.id_toko')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('pb.deleted_at')
            ->whereNull('sales.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');
        $search = request()->get('search', '');

        if ($idCabang !== '') {
            $query->where('pelanggan.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('pelanggan.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('pelanggan.nama', 'like', "%{$search}%")
                    ->orWhere('pelanggan.kode_pelanggan', 'like', "%{$search}%")
                    ->orWhere('pelanggan.nomor_hp', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function applyDateFilters(Builder $query, string $dateColumn): void
    {
        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        if ($startDate !== '') {
            $query->whereDate($dateColumn, '>=', $startDate);
        }

        if ($endDate !== '') {
            $query->whereDate($dateColumn, '<=', $endDate);
        }
    }
}
