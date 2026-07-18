<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SalesService
{
    function sales(){
        $query =  DB::table('karyawan')
        ->join('jabatan','jabatan.id','karyawan.id_jabatan')
        ->whereNull('karyawan.deleted_at')
        ->where('jabatan.nama_jabatan','LIKE','%sales%')
        ->whereNull('jabatan.deleted_at');
        // ->select('');


        $id_toko = request()->get('id_toko','');
        $id_cabang = request()->get('id_cabang','');

        if($id_toko != ''){
            $query->where('karyawan.id_toko',$id_toko);
        }

        if($id_cabang != ''){
            $query->where('karyawan.id_cabang',$id_cabang);
        }

        return $query->select(
            'karyawan.id as id_sales',
            'karyawan.nama_karyawan as nama_sales',
            'jabatan.nama_jabatan'
        )
        ->get()
        ->map(fn ($cabang) => (array) $cabang)
        ->all();





    }
}
