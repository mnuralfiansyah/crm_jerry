<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StokService
{
    private const KATEGORI_HARGA_JUAL_UMUM = 'a0d47ab4-d52f-491c-9c0b-6b11c8bfca58';
    private const KATEGORI_HARGA_JUAL_BARCODE = '41c310f8-6447-483c-b648-2721aa40930e';
    private const KATEGORI_HARGA_JUAL_RITEL = 'b28c5fbf-b2a1-4038-a527-f72c6310bc76';

    public function stok(): array
    {
        $toko = $this->resolveToko();

        if ($toko === null) {
            return [];
        }

        $idCabang = $toko->id_cabang;
        $idToko = $toko->id_toko;

        return DB::table('barang as b')
            ->leftJoin('barang_varian as bv', 'bv.id_barang', '=', 'b.id')
            ->leftJoin('jenis_barang as jb', 'jb.id', '=', 'b.id_jenis_barang')
            ->leftJoin('kategori_barang as kb', 'kb.id', '=', 'jb.id_kategori_barang')
            ->leftJoin('merk_barang as mb', 'mb.id', '=', 'b.id_merk_barang')
            ->leftJoin('vendor as v', 'v.id', '=', 'b.id_vendor')
            ->whereNull('b.deleted_at')
            ->whereIn('b.id', function ($query) use ($idCabang) {
                $query->select('bc.id_barang')
                    ->from('barang_cabang as bc')
                    ->where('bc.id_cabang', $idCabang);
            })
            ->where('b.bahan_baku', 'tidak')
            ->select(
                'b.kode_barang',
                DB::raw("CASE WHEN b.varian_barang = 'iya' THEN bv.barcode ELSE b.kode_barang END as kode_barcode"),
                'b.nama_barang',
                'jb.nama_jenis_barang',
                'kb.nama_kategori_barang',
                'mb.nama_merk_barang',
                'v.nama_vendor'
            )
            ->selectRaw(
                'COALESCE((
                    SELECT h.harga_beli
                    FROM harga_beli_per_cabang AS h
                    WHERE h.deleted_at IS NULL
                        AND h.id_cabang = ?
                        AND h.id_toko = ?
                        AND h.id_barang = b.id
                    LIMIT 1
                ), 0) AS harga_beli',
                [$idCabang, $idToko]
            )
            ->selectRaw(
                'COALESCE((
                    SELECT hj.harga_jual
                    FROM barang_kategori_harga_jual AS hj
                    WHERE hj.deleted_at IS NULL
                        AND hj.id_barang = b.id
                        AND hj.id_cabang = ?
                        AND hj.id_kategori_hj = ?
                    LIMIT 1
                ), 0) AS harga_jual_umum',
                [$idCabang, self::KATEGORI_HARGA_JUAL_UMUM]
            )
            ->selectRaw(
                'COALESCE((
                    SELECT hj.harga_jual
                    FROM barang_kategori_harga_jual AS hj
                    WHERE hj.deleted_at IS NULL
                        AND hj.id_barang = b.id
                        AND hj.id_cabang = ?
                        AND hj.id_kategori_hj = ?
                    LIMIT 1
                ), 0) AS harga_jual_barcode',
                [$idCabang, self::KATEGORI_HARGA_JUAL_BARCODE]
            )
            ->selectRaw(
                'COALESCE((
                    SELECT hj.harga_jual
                    FROM barang_kategori_harga_jual AS hj
                    WHERE hj.deleted_at IS NULL
                        AND hj.id_barang = b.id
                        AND hj.id_cabang = ?
                        AND hj.id_kategori_hj = ?
                    LIMIT 1
                ), 0) AS harga_jual_ritel',
                [$idCabang, self::KATEGORI_HARGA_JUAL_RITEL]
            )
            ->selectRaw(
                "COALESCE(
                    CASE
                        WHEN b.varian_barang = 'iya' THEN (
                            SELECT SUM(sb.qty)
                            FROM stok_barang AS sb
                            WHERE sb.id_cabang = ?
                                AND sb.id_toko = ?
                                AND sb.id_barang = b.id
                                AND sb.id_barang_varian = bv.id
                                AND sb.deleted_at IS NULL
                        )
                        ELSE (
                            SELECT SUM(sb.qty)
                            FROM stok_barang AS sb
                            WHERE sb.id_cabang = ?
                                AND sb.id_toko = ?
                                AND sb.id_barang = b.id
                                AND sb.id_barang_varian IS NULL
                                AND sb.deleted_at IS NULL
                        )
                    END,
                0) AS qty",
                [$idCabang, $idToko, $idCabang, $idToko]
            )
            ->orderBy('b.kode_barang')
            ->get()
            ->map(fn ($stok) => (array) $stok)
            ->all();
    }

    public function summary(): array
    {
        $toko = $this->resolveToko();

        if ($toko === null) {
            return [];
        }

        $summary = DB::table('stok_barang as sb')
            ->whereNull('sb.deleted_at')
            ->where('sb.id_cabang', $toko->id_cabang)
            ->where('sb.id_toko', $toko->id_toko)
            ->select(
                DB::raw('COUNT(DISTINCT sb.id_barang) as jumlah_barang'),
                DB::raw('COUNT(DISTINCT COALESCE(sb.id_barang_varian, sb.id_barang)) as jumlah_sku'),
                DB::raw('COALESCE(SUM(sb.qty), 0) as total_qty'),
                DB::raw('COALESCE(SUM(sb.qty * sb.harga_beli), 0) as total_nilai_stok')
            )
            ->first();

        return $summary === null ? [] : (array) $summary;
    }

    public function movements(): array
    {
        $toko = $this->resolveToko();

        if ($toko === null) {
            return [];
        }

        $query = DB::table('stok_barang as sb')
            ->join('barang as b', 'b.id', '=', 'sb.id_barang')
            ->leftJoin('barang_varian as bv', 'bv.id', '=', 'sb.id_barang_varian')
            ->whereNull('sb.deleted_at')
            ->whereNull('b.deleted_at')
            ->where('sb.id_cabang', $toko->id_cabang)
            ->where('sb.id_toko', $toko->id_toko);

        $startDate = request()->get('start_date', '');
        $endDate = request()->get('end_date', '');
        $idBarang = request()->get('id_barang', '');

        if ($startDate !== '') {
            $query->whereRaw('DATE(COALESCE(sb.date_created_at, sb.created_at)) >= ?', [$startDate]);
        }

        if ($endDate !== '') {
            $query->whereRaw('DATE(COALESCE(sb.date_created_at, sb.created_at)) <= ?', [$endDate]);
        }

        if ($idBarang !== '') {
            $query->where('sb.id_barang', $idBarang);
        }

        return $query
            ->select(
                'sb.id as id_stok_barang',
                DB::raw("DATE_FORMAT(COALESCE(sb.date_created_at, sb.created_at), '%d-%m-%y') as tanggal"),
                'b.id as id_barang',
                'bv.id as id_barang_varian',
                'b.kode_barang',
                DB::raw("CASE WHEN b.varian_barang = 'iya' THEN bv.barcode ELSE b.kode_barang END as kode_barcode"),
                'b.nama_barang',
                'sb.qty',
                'sb.sisa_qty',
                'sb.harga_beli',
                'sb.harga_jual',
                'sb.transaksi_id',
                'sb.transaksi_model',
                'sb.transaksi_parent_id',
                'sb.transaksi_parent_model'
            )
            ->orderByRaw('COALESCE(sb.date_created_at, sb.created_at) DESC')
            ->orderByDesc('sb.id')
            ->get()
            ->map(fn ($movement) => (array) $movement)
            ->all();
    }

    private function resolveToko()
    {
        $idToko = request()->get('id_toko', '');

        if ($idToko === '') {
            return null;
        }

        return DB::table('cabang')
            ->join('toko', 'toko.id_cabang', '=', 'cabang.id')
            ->where('toko.id', $idToko)
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at')
            ->select(
                'cabang.id as id_cabang',
                'toko.id as id_toko'
            )
            ->first();
    }
}
