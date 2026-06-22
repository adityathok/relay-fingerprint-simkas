# Dokumentasi Route iClock (ZKTeco)

Dokumentasi endpoint yang digunakan oleh mesin fingerprint berbasis protokol iClock untuk berkomunikasi dengan server Laravel.

---

# 1. CData

Endpoint utama untuk mengirim data dari mesin ke server.

## Route

```http
POST /iclock/cdata
GET  /iclock/cdata
```

## Query Parameters

| Parameter | Keterangan |
|------------|------------|
| SN | Serial Number mesin |
| table | Jenis data yang dikirim |
| Stamp | Nomor urut sinkronisasi data |

## Contoh Request

```http
POST /iclock/cdata?SN=FP001&table=ATTLOG&Stamp=9999
```

## Contoh Data Absensi (ATTLOG)

```text
12345	2026-06-22 07:01:12	0	1	0
12346	2026-06-22 07:05:21	0	1	0
```

Keterangan kolom:

| Kolom | Keterangan |
|---------|-----------|
| 12345 | PIN/User ID |
| 2026-06-22 07:01:12 | Waktu scan |
| 0 | State |
| 1 | Verify Mode |
| 0 | Work Code |

## Jenis Table Umum

| Table | Fungsi |
|---------|---------|
| ATTLOG | Log absensi |
| OPERLOG | Log operasi mesin |
| USERINFO | Data pengguna |
| BIOPHOTO | Foto pengguna |
| templatev10 | Template sidik jari |
| templatev11 | Template sidik jari versi baru |

---

# 2. GetRequest

Digunakan mesin untuk meminta perintah dari server.

## Route

```http
GET /iclock/getrequest
```

## Query Parameters

| Parameter | Keterangan |
|------------|------------|
| SN | Serial Number mesin |

## Contoh Request

```http
GET /iclock/getrequest?SN=FP001
```

## Response Jika Tidak Ada Perintah

```text
OK
```

## Response Jika Ada Perintah

```text
C:1:CHECK
```

atau

```text
C:2:REBOOT
```

atau

```text
C:3:DATA UPDATE USERINFO
```

## Fungsi

- Sinkronisasi user
- Restart mesin
- Refresh data
- Ambil log tertentu
- Menjalankan command custom

---

# 3. DeviceCMD

Digunakan untuk mengambil daftar command yang menunggu dieksekusi.

> Tidak semua firmware menggunakan endpoint ini.

## Route

```http
GET /iclock/devicecmd
```

## Query Parameters

| Parameter | Keterangan |
|------------|------------|
| SN | Serial Number mesin |

## Contoh Request

```http
GET /iclock/devicecmd?SN=FP001
```

## Contoh Response

```text
ID:1
COMMAND:REBOOT
```

atau

```text
ID:2
COMMAND:DATA UPDATE USERINFO
```

## Fungsi

- Mengambil command dari server.
- Biasanya digunakan sebagai alternatif `getrequest`.

---

# 4. Registry / Connect

Digunakan saat mesin pertama kali terhubung atau melakukan sinkronisasi konfigurasi.

## Route

```http
GET /iclock/cdata
```

## Query Parameters

| Parameter | Keterangan |
|------------|------------|
| SN | Serial Number mesin |
| options | Informasi yang diminta mesin |

## Contoh Request

```http
GET /iclock/cdata?SN=FP001&options=all
```

atau

```http
GET /iclock/cdata?SN=FP001
```

## Fungsi

- Registrasi mesin baru.
- Menyimpan serial number mesin.
- Memperbarui status online mesin.
- Mengambil konfigurasi server.

## Contoh Data yang Dapat Disimpan

```json
{
    "serial_number": "FP001",
    "last_seen_at": "2026-06-22 07:00:00",
    "ip_address": "192.168.1.100"
}
```

---

# Alur Komunikasi Umum

```text
Mesin Fingerprint
        │
        ├── GET /iclock/cdata
        │       (registrasi / handshake)
        │
        ├── POST /iclock/cdata?table=ATTLOG
        │       (kirim log absensi)
        │
        ├── GET /iclock/getrequest
        │       (cek command)
        │
        └── GET /iclock/devicecmd
                (opsional, tergantung firmware)
```