# Dokumentasi Payload API CRM

Base URL:

```text
http://127.0.0.1:8000/api
```

## GET /cabang

URL:

```text
GET /api/cabang
GET /api/branches
```

Query:

```text
Tidak ada
```

Filter:

| Field | Filter |
| --- | --- |
| `cabang.deleted_at` | `IS NULL` |
| `toko.deleted_at` | `IS NULL` |

Response payload:

```json
[
  {
    "id_cabang": 1,
    "id_toko": 2,
    "nama_toko": "Toko A",
    "nama_cabang": "Cabang Utama (Toko A)"
  }
]
```

## GET /sales

URL:

```text
GET /api/sales
GET /api/salespeople
```

Query:

| Parameter | Wajib | Contoh |
| --- | --- | --- |
| `id_cabang` | Tidak | `1` |
| `id_toko` | Tidak | `2` |

Contoh URL:

```text
GET /api/sales?id_cabang=1&id_toko=2
```

Filter:

| Field | Filter |
| --- | --- |
| `karyawan.deleted_at` | `IS NULL` |
| `jabatan.deleted_at` | `IS NULL` |
| `jabatan.nama_jabatan` | `LIKE '%sales%'` |
| `karyawan.id_cabang` | `id_cabang` jika dikirim |
| `karyawan.id_toko` | `id_toko` jika dikirim |

Response payload:

```json
[
  {
    "id_sales": 10,
    "nama_sales": "Budi",
    "nama_jabatan": "Sales"
  }
]
```

## GET /receivables

URL:

```text
GET /api/receivables
GET /api/terima_piutang_list
```

Query:

| Parameter | Wajib | Format | Contoh |
| --- | --- | --- | --- |
| `start_date` | Ya | `YYYY-MM-DD` | `2026-07-01` |
| `end_date` | Ya | `YYYY-MM-DD` | `2026-07-31` |
| `id_cabang` | Tidak | Angka | `1` |
| `id_toko` | Tidak | Angka | `2` |
| `id_sales` | Tidak | Angka | `10` |

Contoh URL:

```text
GET /api/receivables?start_date=2026-07-01&end_date=2026-07-31&id_cabang=1&id_toko=2&id_sales=10
```

Filter:

| Field | Filter |
| --- | --- |
| `terima_piutang.deleted_at` | `IS NULL` |
| `pelanggan.deleted_at` | `IS NULL` |
| `toko.deleted_at` | `IS NULL` |
| `cabang.deleted_at` | `IS NULL` |
| `sales.deleted_at` | `IS NULL` |
| `terima_piutang.is_konfirmasi` | `= 1` |
| `terima_piutang.created_at` | `>= start_date` |
| `terima_piutang.created_at` | `<= end_date` |
| `terima_piutang.id_cabang` | `id_cabang` jika dikirim |
| `terima_piutang.id_toko` | `id_toko` jika dikirim |
| `pelanggan.id_sales` | `id_sales` jika dikirim |

Response payload:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [
      {
        "id_sales": 10,
        "nama_sales": "Budi",
        "jumlah_transaksi": 5,
        "total_jumlah_bayar": "1500000",
        "total_bayar_tunai": "500000",
        "total_bayar_bank": "1000000"
      }
    ],
    "sales": [
      {
        "id_sales": 10,
        "nama_sales": "Budi",
        "jumlah_transaksi": 5,
        "total_jumlah_bayar": "1500000",
        "total_bayar_tunai": "500000",
        "total_bayar_bank": "1000000"
      }
    ],
    "cabang": [
      {
        "id_cabang": 1,
        "nama_cabang": "Cabang Utama",
        "jumlah_transaksi": 5,
        "total_jumlah_bayar": "1500000",
        "total_bayar_tunai": "500000",
        "total_bayar_bank": "1000000"
      }
    ],
    "toko": [
      {
        "id_toko": 2,
        "nama_toko": "Toko A",
        "jumlah_transaksi": 5,
        "total_jumlah_bayar": "1500000",
        "total_bayar_tunai": "500000",
        "total_bayar_bank": "1000000"
      }
    ],
    "pelanggan": [
      {
        "id_pelanggan": 25,
        "nama_pelanggan": "Pelanggan A",
        "jumlah_transaksi": 2,
        "total_jumlah_bayar": "700000",
        "total_bayar_tunai": "200000",
        "total_bayar_bank": "500000"
      }
    ]
  }
}
```

Payload jika `start_date` atau `end_date` kosong:

```json
{
  "success": true,
  "message": "Berhasil",
  "data": {
    "list": [],
    "sales": [],
    "cabang": [],
    "toko": [],
    "pelanggan": []
  }
}
```
