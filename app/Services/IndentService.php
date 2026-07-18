<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class IndentService
{
    public function indent()
    {
        $query = DB::table('pesanan_indent')
            ->join('pelanggan', 'pelanggan.id', 'pesanan_indent.id_pelanggan')
            ->join('cabang', 'cabang.id', 'pesanan_indent.id_cabang')
            ->join('toko', 'toko.id', 'pesanan_indent.id_toko')
            ->join('karyawan as sales', 'sales.id', 'pelanggan.id_sales')
            ->whereNull('pesanan_indent.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('pesanan_indent.is_konfirmasi', 1);

        $start_date = request()->get('start_date', '');
        $end_date = request()->get('end_date', '');

        $id_cabang = request()->get('id_cabang', '');
        $id_toko = request()->get('id_toko', '');
        $id_sales = request()->get('id_sales', '');

        if ($start_date == '' or $end_date == '') {
            return [];
        }

        $query->whereDate('pesanan_indent.created_at', '>=', $start_date);
        $query->whereDate('pesanan_indent.created_at', '<=', $end_date);

        if ($id_cabang != '') {
            $query->where('pesanan_indent.id_cabang', $id_cabang);
        }

        if ($id_toko != '') {
            $query->where('pesanan_indent.id_toko', $id_toko);
        }

        if ($id_sales != '') {
            $query->where('pelanggan.id_sales', $id_sales);
        }

        return $query->select(
            'pesanan_indent.id as id_pesanan_indent',
            'pesanan_indent.nomor_pesanan_indent',
            DB::raw("DATE_FORMAT(pesanan_indent.tanggal, '%d-%m-%y') as tanggal_pesanan_indent"),
            'pesanan_indent.grand_total',
            'pesanan_indent.jumlah_bayar',
            'pesanan_indent.jumlah_bayar_tunai',
            'pesanan_indent.jumlah_bayar_bank',
            'pesanan_indent.jumlah_cashback',
            'pesanan_indent.catatan',
        )
            ->get()
            ->map(fn($indent) => (array) $indent)
            ->all();
    }

    public function groupByCabang()
    {
        $query = DB::table('pesanan_indent')
            ->join('pelanggan', 'pelanggan.id', '=', 'pesanan_indent.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'pesanan_indent.id_cabang')
            ->join('toko', 'toko.id', '=', 'pesanan_indent.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('pesanan_indent.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('pesanan_indent.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query->whereDate('pesanan_indent.created_at', '>=', $startDate)
            ->whereDate('pesanan_indent.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('pesanan_indent.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('pesanan_indent.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'cabang.id as id_cabang',
                'cabang.nama_cabang',
                DB::raw('COUNT(pesanan_indent.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(pesanan_indent.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_tunai), 0) as total_bayar_tunai'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_bank), 0) as total_bayar_bank'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_cashback), 0) as total_cashback')
            )
            ->groupBy(
                'cabang.id',
                'cabang.nama_cabang'
            )
            ->orderBy('cabang.nama_cabang')
            ->get()
            ->map(fn($cabang) => (array) $cabang)
            ->all();
    }

    public function groupBySales()
    {
        $query = DB::table('pesanan_indent')
            ->join('pelanggan', 'pelanggan.id', '=', 'pesanan_indent.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'pesanan_indent.id_cabang')
            ->join('toko', 'toko.id', '=', 'pesanan_indent.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('pesanan_indent.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('pesanan_indent.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query
            ->whereDate('pesanan_indent.created_at', '>=', $startDate)
            ->whereDate('pesanan_indent.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('pesanan_indent.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('pesanan_indent.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',
                DB::raw('COUNT(pesanan_indent.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(pesanan_indent.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_tunai), 0) as total_bayar_tunai'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_bank), 0) as total_bayar_bank'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_cashback), 0) as total_cashback')
            )
            ->groupBy(
                'sales.id',
                'sales.nama_karyawan'
            )
            ->orderBy('sales.nama_karyawan')
            ->get()
            ->map(fn($sales) => (array) $sales)
            ->all();
    }

    public function groupByToko()
    {
        $query = DB::table('pesanan_indent')
            ->join('pelanggan', 'pelanggan.id', '=', 'pesanan_indent.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'pesanan_indent.id_cabang')
            ->join('toko', 'toko.id', '=', 'pesanan_indent.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('pesanan_indent.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('pesanan_indent.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query
            ->whereDate('pesanan_indent.created_at', '>=', $startDate)
            ->whereDate('pesanan_indent.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('pesanan_indent.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('pesanan_indent.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'toko.id as id_toko',
                'toko.nama_toko',
                DB::raw('COUNT(pesanan_indent.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(pesanan_indent.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_tunai), 0) as total_bayar_tunai'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_bank), 0) as total_bayar_bank'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_cashback), 0) as total_cashback')
            )
            ->groupBy(
                'toko.id',
                'toko.nama_toko'
            )
            ->orderBy('toko.nama_toko')
            ->get()
            ->map(fn($toko) => (array) $toko)
            ->all();
    }

    public function groupByPelanggan()
    {
        $query = DB::table('pesanan_indent')
            ->join('pelanggan', 'pelanggan.id', '=', 'pesanan_indent.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'pesanan_indent.id_cabang')
            ->join('toko', 'toko.id', '=', 'pesanan_indent.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('pesanan_indent.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('pesanan_indent.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query
            ->whereDate('pesanan_indent.created_at', '>=', $startDate)
            ->whereDate('pesanan_indent.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('pesanan_indent.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('pesanan_indent.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'pelanggan.id as id_pelanggan',
                'pelanggan.nama as nama_pelanggan',
                DB::raw('COUNT(pesanan_indent.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(pesanan_indent.grand_total), 0) as total_grand_total'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_tunai), 0) as total_bayar_tunai'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_bayar_bank), 0) as total_bayar_bank'),
                DB::raw('COALESCE(SUM(pesanan_indent.jumlah_cashback), 0) as total_cashback')
            )
            ->groupBy(
                'pelanggan.id',
                'pelanggan.nama'
            )
            ->orderBy('pelanggan.nama')
            ->get()
            ->map(fn($pelanggan) => (array) $pelanggan)
            ->all();
    }
}
