# Dokumentasi Payload API CRM

Base URL:

```text
http://127.0.0.1:8000/api
```

Semua endpoint memakai `GET`, jadi request body tidak ada. Filter dikirim lewat query parameter.

## Cabang

URL:

```text
GET /api/cabang
GET /api/branches
```

Query parameters:

| Parameter | Wajib |
| --- | --- |
| Tidak ada | - |

Response:

```json
[
  {
    "id_cabang": "cabang-1",
    "id_toko": "toko-1",
    "nama_toko": "Toko A",
    "nama_cabang": "Cabang A (Toko A)"
  }
]
```

## Sales

URL:

```text
GET /api/sales
GET /api/salespeople
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `id_cabang` | Tidak | `cabang-1` |
| `id_toko` | Tidak | `toko-1` |

Response:

```json
[
  {
    "id_sales": "sales-1",
    "nama_sales": "Budi",
    "nama_jabatan": "Sales"
  }
]
```

## Stok

URL:

```text
GET /api/stok
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `id_toko` | Ya | `toko-1` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "kode_barang": "BRG001",
        "kode_barcode": "8990001",
        "nama_barang": "Barang A",
        "nama_jenis_barang": "Jenis A",
        "nama_kategori_barang": "Kategori A",
        "nama_merk_barang": "Merk A",
        "nama_vendor": "Vendor A",
        "harga_beli": "10000",
        "harga_jual_umum": "15000",
        "harga_jual_barcode": "16000",
        "harga_jual_ritel": "17000",
        "qty": "20"
      }
    ]
  }
}
```

## Pelanggan Periode

URL:

```text
GET /api/pelanggan-periode
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `id_toko` | Ya | `toko-1` |
| `start_date` | Ya | `2026-01-01` |
| `end_date` | Ya | `2026-05-31` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "nama_pelanggan": "Pelanggan A",
        "nomor_hp": "08123456789",
        "nomor_hp1": "08123456789",
        "alamat_pelanggan": "Alamat A",
        "alamat_pelanggan1": "Alamat Biodata A",
        "nama_sales": "Budi",
        "jumlah_transaksi": 10,
        "total_belanja": "5000000",
        "total_diskon": "100000",
        "jumlah_transaksi_tahun_ini": 8,
        "total_belanja_tahun_ini": "4000000",
        "total_diskon_tahun_ini": "80000",
        "jumlah_transaksi_periode": 5,
        "total_belanja_periode": "2500000",
        "total_diskon_periode": "50000",
        "tanggal_terakhir_belanja": "2026-05-20",
        "kode_penjualan_terakhir": "PJ-001",
        "nama_kota": "Makassar",
        "nama_provinsi": "Sulawesi Selatan",
        "tanggal_nota_belum_lunas_terlama": "2026-01-10",
        "lama_piutang_pelanggan": 30,
        "umur_nota": 12,
        "selisih_hari": 18,
        "kode_nota_paling_lama_belum_lunas": "PJ-0001",
        "total_belanja_belum_lunas": "1000000",
        "total_sisa_hutang": "500000",
        "limit_piutang_pelanggan": "2000000",
        "selisih_limit": "1500000"
      }
    ]
  }
}
```

## Hutang Toko

URL:

```text
GET /api/hutang-toko
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `id_toko` | Tidak | `toko-1` |
| `id_tujuan_toko` | Tidak | `toko-2` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "id_terima_barang": "tb-1",
        "nomor_pembelian": "TB-001",
        "tanggal_pembelian": "18-07-26",
        "pengirim": "Toko Pengirim",
        "toko_penerima": "Toko Penerima",
        "grand_total": "1000000",
        "sisa_hutang": "500000",
        "jenis_terima_barang": "Antar Cabang"
      }
    ],
    "pengirim": [
      {
        "id_pengirim": "toko-2",
        "pengirim": "Toko Pengirim",
        "jenis_terima_barang": "Antar Cabang",
        "jumlah_transaksi": 2,
        "total_grand_total": "2000000",
        "total_sisa_hutang": "1000000"
      }
    ],
    "toko": [
      {
        "id_toko": "toko-1",
        "nama_toko": "Toko Penerima",
        "jumlah_transaksi": 2,
        "total_grand_total": "2000000",
        "total_sisa_hutang": "1000000"
      }
    ]
  }
}
```

## Terima Piutang

URL:

```text
GET /api/receivables
GET /api/terima_piutang_list
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `start_date` | Ya | `2026-07-01` |
| `end_date` | Ya | `2026-07-31` |
| `id_cabang` | Tidak | `cabang-1` |
| `id_toko` | Tidak | `toko-1` |
| `id_sales` | Tidak | `sales-1` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "id_terima_piutang": "tp-1",
        "nomor_terima_piutang": "TP-001",
        "tanggal_terima_piutang": "18-07-26",
        "jumlah_bayar": "1000000",
        "jumlah_bayar_tunai": "400000",
        "jumlah_bayar_bank": "600000",
        "catatan": "Catatan"
      }
    ],
    "sales": [
      {
        "id_sales": "sales-1",
        "nama_sales": "Budi",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "cabang": [
      {
        "id_cabang": "cabang-1",
        "nama_cabang": "Cabang A",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "toko": [
      {
        "id_toko": "toko-1",
        "nama_toko": "Toko A",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "pelanggan": [
      {
        "id_pelanggan": "pelanggan-1",
        "nama_pelanggan": "Pelanggan A",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ]
  }
}
```

## Pendapatan

URL:

```text
GET /api/pendapatan
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `start_date` | Ya | `2026-07-01` |
| `end_date` | Ya | `2026-07-31` |
| `id_cabang` | Tidak | `cabang-1` |
| `id_toko` | Tidak | `toko-1` |
| `id_sales` | Tidak | `sales-1` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "id_pelanggan_riwayat_bayar": "prb-1",
        "tanggal_pelanggan_riwayat_bayar": "18-07-26",
        "uang": "1000000",
        "jumlah_bayar_tunai": "400000",
        "jumlah_bayar_bank": "600000"
      }
    ],
    "sales": [
      {
        "id_sales": "sales-1",
        "nama_sales": "Budi",
        "jumlah_transaksi": 3,
        "total_uang": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "cabang": [
      {
        "id_cabang": "cabang-1",
        "nama_cabang": "Cabang A",
        "jumlah_transaksi": 3,
        "total_uang": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "toko": [
      {
        "id_toko": "toko-1",
        "nama_toko": "Toko A",
        "jumlah_transaksi": 3,
        "total_uang": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "pelanggan": [
      {
        "id_pelanggan": "pelanggan-1",
        "nama_pelanggan": "Pelanggan A",
        "jumlah_transaksi": 3,
        "total_uang": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ]
  }
}
```

## Penjualan

URL:

```text
GET /api/penjualan
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `start_date` | Ya | `2026-07-01` |
| `end_date` | Ya | `2026-07-31` |
| `id_cabang` | Tidak | `cabang-1` |
| `id_toko` | Tidak | `toko-1` |
| `id_sales` | Tidak | `sales-1` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "id_penjualan": "pj-1",
        "kode_penjualan": "PJ-001",
        "tanggal_penjualan": "18-07-26",
        "jumlah_bayar": "1000000",
        "jumlah_bayar_tunai": "400000",
        "jumlah_bayar_bank": "600000",
        "catatan": "Catatan"
      }
    ],
    "sales": [
      {
        "id_sales": "sales-1",
        "nama_sales": "Budi",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "cabang": [
      {
        "id_cabang": "cabang-1",
        "nama_cabang": "Cabang A",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "toko": [
      {
        "id_toko": "toko-1",
        "nama_toko": "Toko A",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ],
    "pelanggan": [
      {
        "id_pelanggan": "pelanggan-1",
        "nama_pelanggan": "Pelanggan A",
        "jumlah_transaksi": 3,
        "total_jumlah_bayar": "3000000",
        "total_bayar_tunai": "1000000",
        "total_bayar_bank": "2000000"
      }
    ]
  }
}
```

## Retur

URL:

```text
GET /api/retur
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `start_date` | Ya | `2026-07-01` |
| `end_date` | Ya | `2026-07-31` |
| `id_cabang` | Tidak | `cabang-1` |
| `id_toko` | Tidak | `toko-1` |
| `id_sales` | Tidak | `sales-1` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "id_retur_penjualan": "rp-1",
        "nomor_retur_penjualan": "RP-001",
        "tanggal_retur_penjualan": "18-07-26",
        "jumlah_retur": "500000",
        "catatan": "Catatan"
      }
    ],
    "sales": [
      {
        "id_sales": "sales-1",
        "nama_sales": "Budi",
        "jumlah_transaksi": 2,
        "total_jumlah_retur": "1000000"
      }
    ],
    "cabang": [
      {
        "id_cabang": "cabang-1",
        "nama_cabang": "Cabang A",
        "jumlah_transaksi": 2,
        "total_jumlah_retur": "1000000"
      }
    ],
    "toko": [
      {
        "id_toko": "toko-1",
        "nama_toko": "Toko A",
        "jumlah_transaksi": 2,
        "total_jumlah_retur": "1000000"
      }
    ],
    "pelanggan": [
      {
        "id_pelanggan": "pelanggan-1",
        "nama_pelanggan": "Pelanggan A",
        "jumlah_transaksi": 2,
        "total_jumlah_retur": "1000000"
      }
    ]
  }
}
```

## Indent

URL:

```text
GET /api/indent
```

Query parameters:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `start_date` | Ya | `2026-07-01` |
| `end_date` | Ya | `2026-07-31` |
| `id_cabang` | Tidak | `cabang-1` |
| `id_toko` | Tidak | `toko-1` |
| `id_sales` | Tidak | `sales-1` |

Response:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "id_pesanan_indent": "pi-1",
        "nomor_pesanan_indent": "PI-001",
        "tanggal_pesanan_indent": "18-07-26",
        "grand_total": "2000000",
        "jumlah_bayar": "1000000",
        "jumlah_bayar_tunai": "400000",
        "jumlah_bayar_bank": "600000",
        "jumlah_cashback": "0",
        "catatan": "Catatan"
      }
    ],
    "sales": [
      {
        "id_sales": "sales-1",
        "nama_sales": "Budi",
        "jumlah_transaksi": 2,
        "total_grand_total": "4000000",
        "total_jumlah_bayar": "2000000",
        "total_bayar_tunai": "800000",
        "total_bayar_bank": "1200000",
        "total_cashback": "0"
      }
    ],
    "cabang": [
      {
        "id_cabang": "cabang-1",
        "nama_cabang": "Cabang A",
        "jumlah_transaksi": 2,
        "total_grand_total": "4000000",
        "total_jumlah_bayar": "2000000",
        "total_bayar_tunai": "800000",
        "total_bayar_bank": "1200000",
        "total_cashback": "0"
      }
    ],
    "toko": [
      {
        "id_toko": "toko-1",
        "nama_toko": "Toko A",
        "jumlah_transaksi": 2,
        "total_grand_total": "4000000",
        "total_jumlah_bayar": "2000000",
        "total_bayar_tunai": "800000",
        "total_bayar_bank": "1200000",
        "total_cashback": "0"
      }
    ],
    "pelanggan": [
      {
        "id_pelanggan": "pelanggan-1",
        "nama_pelanggan": "Pelanggan A",
        "jumlah_transaksi": 2,
        "total_grand_total": "4000000",
        "total_jumlah_bayar": "2000000",
        "total_bayar_tunai": "800000",
        "total_bayar_bank": "1200000",
        "total_cashback": "0"
      }
    ]
  }
}
```
