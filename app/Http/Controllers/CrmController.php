<?php

namespace App\Http\Controllers;

use App\Services\CabangService;
use App\Services\HutangTokoService;
use App\Services\IndentService;
use App\Services\LaporanPelangganPeriodeService;
use App\Services\LaporanPendapatanService;
use App\Services\LaporanPenjualanService;
use App\Services\PiutangPelangganService;
use App\Services\ReturService;
use App\Services\SalesService;
use App\Services\StokService;
use App\Services\TerimaPiutangService;

class CrmController extends Controller
{

    public function __construct(
        protected CabangService $cabangService,
        protected HutangTokoService $hutangTokoService,
        protected SalesService $salesService,
        protected TerimaPiutangService $terimaPiutangService,
        protected ReturService $returService,
        protected LaporanPelangganPeriodeService $laporanPelangganPeriodeService,
        protected StokService $stokService,
        protected LaporanPendapatanService $laporanPendapatanService,
        protected IndentService $indentService,
        protected LaporanPenjualanService $laporanPenjualanService,
        protected PiutangPelangganService $piutangPelangganService,
    ) {}

    public function cabang(): array
    {
        return $this->cabangService->cabang();
    }

    public function sales(): array
    {
        return $this->salesService->sales();
    }

    public function stok()
    {
        return $this->sendSuccessResponse([
            'list' => $this->stokService->stok()
        ]);
    }

    public function pelanggan_periode()
    {
        return $this->sendSuccessResponse([
            'list' => $this->laporanPelangganPeriodeService->pelanggan_periode()
        ]);
    }

    public function hutang_toko()
    {
        return $this->sendSuccessResponse([
            'list' => $this->hutangTokoService->hutang_toko(),
            'pengirim' => $this->hutangTokoService->groupByPengirim(),
            'toko' => $this->hutangTokoService->groupByToko()
        ]);
    }

    public function piutang_pelanggan()
    {
        return $this->sendSuccessResponse([
            'summary' => $this->piutangPelangganService->summary(),
            'list' => $this->piutangPelangganService->piutang_pelanggan(),
            'pelanggan' => $this->piutangPelangganService->groupByPelanggan(),
            'aging' => $this->piutangPelangganService->aging()
        ]);
    }

    public function terima_piutang()
    {
        return $this->sendSuccessResponse([
            'list' => $this->terimaPiutangService->terima_piutang(),
            'sales' => $this->terimaPiutangService->groupBySales(),
            'cabang' => $this->terimaPiutangService->groupByCabang(),
            'toko' => $this->terimaPiutangService->groupByToko(),
             'pelanggan' => $this->terimaPiutangService->groupByPelanggan()
        ]);
    }

    public function pendapatan()
    {
        return $this->sendSuccessResponse([
            'list' => $this->laporanPendapatanService->pendapatan(),
            'sales' => $this->laporanPendapatanService->groupBySales(),
            'cabang' => $this->laporanPendapatanService->groupByCabang(),
            'toko' => $this->laporanPendapatanService->groupByToko(),
            'pelanggan' => $this->laporanPendapatanService->groupByPelanggan()
        ]);
    }

    public function penjualan()
    {
        return $this->sendSuccessResponse([
            'list' => $this->laporanPenjualanService->penjualan(),
            'sales' => $this->laporanPenjualanService->groupBySales(),
            'cabang' => $this->laporanPenjualanService->groupByCabang(),
            'toko' => $this->laporanPenjualanService->groupByToko(),
            'pelanggan' => $this->laporanPenjualanService->groupByPelanggan()
        ]);
    }

    public function retur()
    {
        return $this->sendSuccessResponse([
            'list' => $this->returService->retur(),
            'sales' => $this->returService->groupBySales(),
            'cabang' => $this->returService->groupByCabang(),
            'toko' => $this->returService->groupByToko(),
            'pelanggan' => $this->returService->groupByPelanggan()
        ]);
    }

     public function indent()
    {
        return $this->sendSuccessResponse([
            'list' => $this->indentService->indent(),
            'sales' => $this->indentService->groupBySales(),
            'cabang' => $this->indentService->groupByCabang(),
            'toko' => $this->indentService->groupByToko(),
            'pelanggan' => $this->indentService->groupByPelanggan()
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
