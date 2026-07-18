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
        $idToko = request()->get('id_toko', '');

        if ($idToko === '') {
            return [];
        }

        $cabang = DB::table('cabang')
            ->join('toko', 'toko.id_cabang', '=', 'cabang.id')
            ->where('toko.id', $idToko)
            ->whereNull('cabang.deleted_at')
            ->whereNull('toko.deleted_at')
            ->select('cabang.id as id_cabang')
            ->first();

        if ($cabang === null) {
            return [];
        }

        $idCabang = $cabang->id_cabang;

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
}
