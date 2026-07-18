<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CabangService
{
    public function cabang(): array
    {
        return DB::table('cabang')
        ->join('toko','toko.id_cabang','cabang.id')
        ->whereNull('cabang.deleted_at')
        ->whereNull('toko.deleted_at')
        ->select(
            'cabang.id as id_cabang',
            'toko.id as id_toko',
            'toko.nama_toko',
            DB::raw('concat(cabang.nama_cabang," (",toko.nama_toko,")") as nama_cabang' )
        )
        ->get()
        ->map(fn ($cabang) => (array) $cabang)
        ->all();
    }
}
