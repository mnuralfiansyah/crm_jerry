<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LaporanPelangganPeriodeService
{
    public function pelanggan_periode(): array
    {
        $idToko = request()->get('id_toko', '');
        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');

        if ($idToko === '' || $startDate === '' || $endDate === '') {
            return [];
        }

        $toko = DB::table('toko')
            ->join('cabang', 'cabang.id', '=', 'toko.id_cabang')
            ->where('toko.id', $idToko)
            ->whereNull('toko.deleted_at')
            ->whereNull('cabang.deleted_at')
            ->select('toko.id as id_toko', 'cabang.id as id_cabang')
            ->first();

        if ($toko === null) {
            return [];
        }

        return DB::table('pelanggan as pp')
            ->join('penjualan as p', 'p.id_pelanggan', '=', 'pp.id')
            ->leftJoin('pelanggan_biodata as pb', 'pp.id', '=', 'pb.id_pelanggan')
            ->leftJoin('karyawan as k', 'k.id', '=', 'pp.id_sales')
            ->leftJoin('kota', 'kota.id', '=', 'pp.id_kota')
            ->leftJoin('provinsi', 'provinsi.id', '=', 'pp.id_provinsi')
            ->whereNull('pp.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('p.status_transaksi', 1)
            ->whereNull('p.id_pesanan_indent')
            ->where('p.id_cabang', $toko->id_cabang)
            ->where('p.id_toko', $toko->id_toko)
            ->select(
                DB::raw("COALESCE(pp.nama, '-') as nama_pelanggan"),
                DB::raw("COALESCE(pp.nomor_hp, '-') as nomor_hp"),
                DB::raw("COALESCE(pb.nomor_hp, '-') as nomor_hp1"),
                DB::raw("COALESCE(pp.alamat, '-') as alamat_pelanggan"),
                DB::raw("COALESCE(pb.alamat, '-') as alamat_pelanggan1"),
                DB::raw("COALESCE(k.nama_karyawan, '-') as nama_sales"),
                DB::raw('COUNT(p.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(p.grand_total), 0) as total_belanja'),
                DB::raw('COALESCE(SUM(p.total_diskon), 0) as total_diskon'),
                DB::raw('COUNT(CASE WHEN YEAR(p.tanggal) = YEAR(CURRENT_DATE) THEN p.id ELSE NULL END) as jumlah_transaksi_tahun_ini'),
                DB::raw('COALESCE(SUM(CASE WHEN YEAR(p.tanggal) = YEAR(CURRENT_DATE) THEN p.grand_total ELSE 0 END), 0) as total_belanja_tahun_ini'),
                DB::raw('COALESCE(SUM(CASE WHEN YEAR(p.tanggal) = YEAR(CURRENT_DATE) THEN p.total_diskon ELSE 0 END), 0) as total_diskon_tahun_ini'),
                DB::raw('DATE(MAX(p.tanggal)) as tanggal_terakhir_belanja'),
                DB::raw("COALESCE(kota.nama, '-') as nama_kota"),
                DB::raw("COALESCE(provinsi.nama, '-') as nama_provinsi"),
                DB::raw("COALESCE(MIN(CASE WHEN p.is_lunas = 0 THEN DATE(p.tanggal) ELSE NULL END), '1970-01-01') as tanggal_nota_belum_lunas_terlama"),
                DB::raw('COALESCE(MAX(pb.lama_piutang), 0) as lama_piutang_pelanggan'),
                DB::raw("DATEDIFF(
                    CURRENT_DATE,
                    COALESCE(
                        MIN(CASE WHEN p.is_lunas = 0 THEN DATE(p.tanggal) ELSE NULL END),
                        CURRENT_DATE
                    )
                ) as umur_nota"),
                DB::raw("COALESCE((MAX(pb.lama_piutang) - DATEDIFF(
                    CURRENT_DATE,
                    COALESCE(
                        MIN(CASE WHEN p.is_lunas = 0 THEN DATE(p.tanggal) ELSE NULL END),
                        CURRENT_DATE
                    )
                )), 0) as selisih_hari"),
                DB::raw('COALESCE(SUM(CASE WHEN p.is_lunas = 0 THEN p.grand_total ELSE 0 END), 0) as total_belanja_belum_lunas'),
                DB::raw('COALESCE((
                    SELECT SUM(pup2.uang) * -1
                    FROM pelanggan_utang_piutang AS pup2
                    WHERE pup2.id_pelanggan = p.id_pelanggan
                        AND pup2.deleted_at IS NULL
                ), 0) as total_sisa_hutang'),
                DB::raw('COALESCE(MAX(pb.limit_piutang), 0) as limit_piutang_pelanggan'),
                DB::raw('COALESCE((MAX(pb.limit_piutang) + COALESCE((
                    SELECT SUM(pup3.uang)
                    FROM pelanggan_utang_piutang AS pup3
                    WHERE pup3.id_pelanggan = p.id_pelanggan
                        AND pup3.deleted_at IS NULL
                ), 0)), 0) as selisih_limit')
            )
            ->selectRaw(
                'COUNT(CASE WHEN p.tanggal BETWEEN ? AND ? THEN p.id ELSE NULL END) as jumlah_transaksi_periode',
                [$startDate, $endDate]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.tanggal BETWEEN ? AND ? THEN p.grand_total ELSE 0 END), 0) as total_belanja_periode',
                [$startDate, $endDate]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.tanggal BETWEEN ? AND ? THEN p.total_diskon ELSE 0 END), 0) as total_diskon_periode',
                [$startDate, $endDate]
            )
            ->selectRaw(
                "COALESCE((
                    SELECT x.kode_penjualan
                    FROM penjualan AS x
                    WHERE x.id_pelanggan = p.id_pelanggan
                        AND x.deleted_at IS NULL
                        AND x.status_transaksi = 1
                        AND x.id_pesanan_indent IS NULL
                        AND x.id_cabang = ?
                        AND x.id_toko = ?
                        AND x.tanggal BETWEEN ? AND ?
                    ORDER BY x.tanggal DESC, x.created_at DESC
                    LIMIT 1
                ), '-') as kode_penjualan_terakhir",
                [$toko->id_cabang, $toko->id_toko, $startDate, $endDate]
            )
            ->selectRaw(
                "COALESCE((
                    SELECT x.kode_penjualan
                    FROM penjualan AS x
                    WHERE x.id_pelanggan = p.id_pelanggan
                        AND x.is_lunas = 0
                        AND x.deleted_at IS NULL
                        AND x.status_transaksi = 1
                        AND x.id_cabang = ?
                        AND x.id_toko = ?
                    ORDER BY x.created_at ASC
                    LIMIT 1
                ), '-') as kode_nota_paling_lama_belum_lunas",
                [$toko->id_cabang, $toko->id_toko]
            )
            ->groupBy(
                'p.id_pelanggan',
                'pp.nama',
                'pp.nomor_hp',
                'pb.nomor_hp',
                'pp.alamat',
                'pb.alamat',
                'k.nama_karyawan',
                'kota.nama',
                'provinsi.nama'
            )
            ->havingRaw(
                'COUNT(CASE WHEN p.tanggal BETWEEN ? AND ? THEN p.id ELSE NULL END) > 0',
                [$startDate, $endDate]
            )
            ->orderByDesc('total_belanja_periode')
            ->get()
            ->map(fn ($pelanggan) => (array) $pelanggan)
            ->all();
    }
}
