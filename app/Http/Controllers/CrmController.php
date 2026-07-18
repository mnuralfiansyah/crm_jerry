<?php

namespace App\Http\Controllers;

use App\Services\CabangService;
use App\Services\IndentService;
use App\Services\LaporanPelangganPeriodeService;
use App\Services\LaporanPendapatanService;
use App\Services\LaporanPenjualanService;
use App\Services\ReturService;
use App\Services\SalesService;
use App\Services\StokService;
use App\Services\TerimaPiutangService;

class CrmController extends Controller
{

    public function __construct(
        protected CabangService $cabangService,
        protected SalesService $salesService,
        protected TerimaPiutangService $terimaPiutangService,
        protected ReturService $returService,
        protected LaporanPelangganPeriodeService $laporanPelangganPeriodeService,
        protected StokService $stokService,
        protected LaporanPendapatanService $laporanPendapatanService,
        protected IndentService $indentService,
        protected LaporanPenjualanService $laporanPenjualanService,
    ) {}

    public function cabang(): array
    {
        return $this->cabangService->cabang();
    }

    public function sales(): array
    {
        return $this->salesService->sales();
    }

    public function terima_piutang()
    {
        return $this->sendSuccessResponse([
            'list' => $this->terimaPiutangService->groupBySales(),
            'sales' => $this->terimaPiutangService->groupBySales(),
            'cabang' => $this->terimaPiutangService->groupByCabang(),
            'toko' => $this->terimaPiutangService->groupByToko(),
             'pelanggan' => $this->terimaPiutangService->groupByPelanggan()
        ]);
    }


    function sendSuccessResponse($data)
    {
        return response()->json([
            'success' => true,
            'message' => 'Berhasil',
            'data' => (object) $data,
        ], 200);
    }
}
