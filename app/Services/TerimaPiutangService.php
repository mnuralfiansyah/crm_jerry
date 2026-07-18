<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TerimaPiutangService
{

    public function terima_piutang()
    {
        $query =  DB::table('terima_piutang')
            ->join('pelanggan', 'pelanggan.id', 'terima_piutang.id_pelanggan')
            ->join('cabang', 'cabang.id', 'terima_piutang.id_cabang')
            ->join('toko', 'toko.id', 'terima_piutang.id_toko')
            ->join('karyawan as sales', 'sales.id', 'pelanggan.id_sales')
            ->whereNull('terima_piutang.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('terima_piutang.is_konfirmasi', 1);


        $start_date = request()->get('start_date', '');
        $end_date = request()->get('end_date', '');

        $id_cabang = request()->get('id_cabang', '');
        $id_toko = request()->get('id_toko', '');
        $id_sales = request()->get('id_sales', '');
        // $end_date = request()->get('end_date','');





        if ($start_date == '' or $end_date == '') {
            return [];
        }

        $query->whereDate('terima_piutang.created_at', '>=', $start_date);
        $query->whereDate('terima_piutang.created_at', '<=', $end_date);


        if ($id_cabang != '') {
            $query->where('terima_piutang.id_cabang', $id_cabang);
        }

        if ($id_toko != '') {
            $query->where('terima_piutang.id_toko', $id_toko);
        }

        if ($id_sales != '') {
            $query->where('pelanggan.id_sales', $id_sales);
        }



        // return $query->toRawSql();
        return $query->select(
            'terima_piutang.id as id_terima_piutang',
            'terima_piutang.nomor_terima_piutang',
            DB::raw("DATE_FORMAT(terima_piutang.tanggal, '%d-%m-%y') as tanggal_terima_piutang"),
            'terima_piutang.jumlah_bayar',
            'terima_piutang.jumlah_bayar_tunai',
            'terima_piutang.jumlah_bayar_bank',
            'terima_piutang.catatan',
        )
            ->get()
            ->map(fn($cabang) => (array) $cabang)
            ->all();
    }

    public function groupByCabang()
    {

        $query = DB::table('terima_piutang')
            ->join('pelanggan', 'pelanggan.id', '=', 'terima_piutang.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'terima_piutang.id_cabang')
            ->join('toko', 'toko.id', '=', 'terima_piutang.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('terima_piutang.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('terima_piutang.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query->whereDate('terima_piutang.created_at', '>=', $startDate)
            ->whereDate('terima_piutang.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('terima_piutang.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('terima_piutang.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'cabang.id as id_cabang',
                'cabang.nama_cabang',
                DB::raw('COUNT(terima_piutang.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(terima_piutang.jumlah_bayar), 0) as total_jumlah_bayar'),
                DB::raw('COALESCE(SUM(terima_piutang.jumlah_bayar_tunai), 0) as total_bayar_tunai'),
                DB::raw('COALESCE(SUM(terima_piutang.jumlah_bayar_bank), 0) as total_bayar_bank')
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
        $query = DB::table('terima_piutang')
            ->join('pelanggan', 'pelanggan.id', '=', 'terima_piutang.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'terima_piutang.id_cabang')
            ->join('toko', 'toko.id', '=', 'terima_piutang.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('terima_piutang.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('terima_piutang.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query
            ->whereDate('terima_piutang.created_at', '>=', $startDate)
            ->whereDate('terima_piutang.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('terima_piutang.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('terima_piutang.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'sales.id as id_sales',
                'sales.nama_karyawan as nama_sales',

                DB::raw('COUNT(terima_piutang.id) as jumlah_transaksi'),

                DB::raw(
                    'COALESCE(SUM(terima_piutang.jumlah_bayar), 0)
                as total_jumlah_bayar'
                ),

                DB::raw(
                    'COALESCE(SUM(terima_piutang.jumlah_bayar_tunai), 0)
                as total_bayar_tunai'
                ),

                DB::raw(
                    'COALESCE(SUM(terima_piutang.jumlah_bayar_bank), 0)
                as total_bayar_bank'
                )
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
        $query = DB::table('terima_piutang')
            ->join('pelanggan', 'pelanggan.id', '=', 'terima_piutang.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'terima_piutang.id_cabang')
            ->join('toko', 'toko.id', '=', 'terima_piutang.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('terima_piutang.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('terima_piutang.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query
            ->whereDate('terima_piutang.created_at', '>=', $startDate)
            ->whereDate('terima_piutang.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('terima_piutang.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('terima_piutang.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'toko.id as id_toko',
                'toko.nama_toko',

                DB::raw('COUNT(terima_piutang.id) as jumlah_transaksi'),

                DB::raw(
                    'COALESCE(SUM(terima_piutang.jumlah_bayar), 0)
                as total_jumlah_bayar'
                ),

                DB::raw(
                    'COALESCE(SUM(terima_piutang.jumlah_bayar_tunai), 0)
                as total_bayar_tunai'
                ),

                DB::raw(
                    'COALESCE(SUM(terima_piutang.jumlah_bayar_bank), 0)
                as total_bayar_bank'
                )
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
        $query = DB::table('terima_piutang')
            ->join('pelanggan', 'pelanggan.id', '=', 'terima_piutang.id_pelanggan')
            ->join('cabang', 'cabang.id', '=', 'terima_piutang.id_cabang')
            ->join('toko', 'toko.id', '=', 'terima_piutang.id_toko')
            ->join('karyawan as sales', 'sales.id', '=', 'pelanggan.id_sales')
            ->whereNull('terima_piutang.deleted_at')
            ->whereNull('pelanggan.deleted_at')
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('terima_piutang.is_konfirmasi', 1);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        $idCabang = request()->get('id_cabang', '');
        $idToko = request()->get('id_toko', '');
        $idSales = request()->get('id_sales', '');

        if ($startDate === '' || $endDate === '') {
            return [];
        }

        $query
            ->whereDate('terima_piutang.created_at', '>=', $startDate)
            ->whereDate('terima_piutang.created_at', '<=', $endDate);

        if ($idCabang !== '') {
            $query->where('terima_piutang.id_cabang', $idCabang);
        }

        if ($idToko !== '') {
            $query->where('terima_piutang.id_toko', $idToko);
        }

        if ($idSales !== '') {
            $query->where('pelanggan.id_sales', $idSales);
        }

        return $query
            ->select(
                'pelanggan.id as id_pelanggan',
                'pelanggan.nama as nama_pelanggan',

                DB::raw('COUNT(terima_piutang.id) as jumlah_transaksi'),

                DB::raw('COALESCE(SUM(terima_piutang.jumlah_bayar), 0) as total_jumlah_bayar'),

                DB::raw('COALESCE(SUM(terima_piutang.jumlah_bayar_tunai), 0) as total_bayar_tunai'),

                DB::raw('COALESCE(SUM(terima_piutang.jumlah_bayar_bank), 0) as total_bayar_bank')
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
