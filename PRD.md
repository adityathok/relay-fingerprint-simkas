# Product Requirement Document (PRD)
## Centralized Fingerprint Relay System (Shared Hosting & Cron Job Architecture)

---

## 1. Ringkasan Proyek (Overview)
Sistem ini adalah server *relay* terpusat (*Central Hub*) berbasis Laravel yang dirancang khusus untuk berjalan di lingkungan **Shared Hosting**. Sistem ini bertugas menjembatani mesin *fingerprint* fisik di berbagai lokasi klien dengan aplikasi utama Laravel yang berada di hosting masing-masing klien menggunakan metode **Webhook + Cron Job per menit**.

### Masalah & Batasan Lingkungan Shared Hosting:
1.  **Tidak Ada Daemon Queue**: Shared hosting tidak mengizinkan perintah `php artisan queue:work` berjalan terus-menerus di latar belakang.
2.  **Keterbatasan Sumber Daya (CPU/RAM)**: Bombardir data massal dari banyak mesin secara bersamaan dapat memicu *Resource Limit Exceeded* (Error 508).
3.  **Desentralisasi Klien**: Data dari mesin yang masuk ke server pusat harus dipilah secara otomatis dan diteruskan ke URL hosting klien yang tepat dengan aman.

---

## 2. Arsitektur & Alur Data (Cron-Based Architecture)

[Mesin Fingerprint Klien]││ HTTP POST (Raw Data + Serial Number Mesin)▼[Server Pusat: API Ingestion Endpoint]││ 1. Validasi Serial Number (SN) Mesin│ 2. Simpan mentah ke tabel fingerprint_raw_logs (Status: pending)│ 3. Return HTTP 200 OK ke Mesin (Proses instan, cegah timeout mesin)▼[cPanel Cron Job (Setiap 1 Menit)] -> Menjalankan php artisan schedule:run│▼[Laravel Command: attendance:dispatch]││ 1. Ambil maksimal 30 data berstatus 'pending'│ 2. Cari webhook_url & api_token klien berdasarkan SN mesin│ 3. Kirim data via HTTP POST Webhook (Secara bertahap/chunk)▼┌─────┴─────────────────────────────┐▼                                   ▼[Aplikasi Klien A]          [Aplikasi Klien B](Terima JSON -> Simpan DB)  (Terima JSON -> Simpan DB)

---

## 3. Struktur Database Esensial (Server Pusat)

### 3.1 Tabel `clients`
```php
Schema::create('clients', function (Blueprint \(table) {\)table->id();
    \(table->string('client_name');\)table->string('webhook_url'); // Contoh: https://klien-a.com
    \(table->string('api_token');   // Token unik untuk autentikasi ke hosting klien\)table->timestamps();
});
```

### 3.2 Tabel `devices`
```php
Schema::create('devices', function (Blueprint \(table) {\)table->id();
    \(table->foreignId('client_id')->constrained('clients')->onDelete('cascade');\)table->string('device_sn')->unique(); // Serial Number Mesin dari vendor
    \(table->string('device_name');\)table->timestamps();
});
```

### 3.3 Tabel `fingerprint_raw_logs` (Sebagai Pengganti Antrean/Queue)
```php
Schema::create('fingerprint_raw_logs', function (Blueprint \$table) {
    \(table->id();\)table->string('device_sn');
    \(table->longText('raw_payload'); // Data JSON mentah kiriman dari mesin\)table->enum('status', ['pending', 'success', 'failed'])->default('pending');
    \(table->integer('retry_count')->default(0);\)table->timestamps();
});
```

---

## 4. Kebutuhan Fungsional (Functional Requirements)

### 4.1 API Ingestion (Server Pusat)
*   **Endpoint**: `/api/v1/central-relay/push`
*   **Method**: `POST`
*   **Logika Utama**:
    1. Menerima payload. Mengekstrak parameter identitas mesin (misal: `SN` atau `SerialNumber`).
    2. Validasi apakah `device_sn` terdaftar di tabel `devices`. Jika tidak terdaftar, log diabaikan atau disimpan sebagai *unknown device*.
    3. Simpan data mentah ke tabel `fingerprint_raw_logs` dengan `status = 'pending'`.
    4. Langsung kembalikan respons `HTTP 200 OK` (atau format sukses sesuai standar vendor mesin) agar koneksi HTTP mesin segera terputus.

### 4.2 Laravel Command Dispatcher (Server Pusat)
*   **Nama Perintah**: `php artisan attendance:dispatch`
*   **Jadwal Eksekusi**: Setiap 1 menit (via `app/Console/Kernel.php` atau `routes/console.php`).
*   **Logika Utama**:
    1. Mengambil maksimal **30 data** dari `fingerprint_raw_logs` yang berstatus `pending` secara *Oldest First* (FIFO).
    2. Melakukan perulangan (*looping*):
        * Ambil `webhook_url` dan `api_token` milik klien terkait melalui relasi tabel `devices -> clients`.
        * Kirim payload data absensi menggunakan `Http::withToken($api_token)->post($webhook_url, $data)`.
        * Jika respons dari hosting klien bernilai `200` (Sukses), ubah status log di server pusat menjadi `success`.
        * Jika gagal / *timeout*, naikkan `retry_count`. Jika `retry_count` > 5, ubah status menjadi `failed`.

### 4.3 Webhook Receiver (Sisi Aplikasi Klien)
*   **Endpoint**: `/api/v1/webhook/attendance`
*   **Method**: `POST`
*   **Keamanan**: Wajib menggunakan Middleware untuk memvalidasi `Bearer Token` yang dikirim dari server pusat.
*   **Logika Utama**: Menerima data JSON yang sudah rapi, memproses pencatatan absensi ke tabel `attendances` lokal klien, dan mengembalikan respons `200`.

---

## 5. Kebutuhan Non-Fungsional & Keamanan (Non-Functional & Security)

*   **Keamanan Data**: Pengiriman dari server pusat ke hosting klien wajib menggunakan HTTPS dan diproteksi dengan *Secret Bearer Token* unik per klien guna menghindari manipulasi data absen dari pihak luar.
*   **Pembersihan Otomatis (Housekeeping)**: Server pusat wajib menghapus log berstatus `success` yang sudah berusia lebih dari 7 hari secara berkala (menggunakan cron job terpisah) agar kuota penyimpanan *shared hosting* tidak penuh.
*   **Tanpa Overlapping**: Ekstensi fungsi cron wajib menggunakan `.withoutOverlapping()` agar proses menit sebelumnya yang belum selesai tidak ditumpuk oleh proses menit berikutnya.

---

## 6. Rencana Pengujian (Testing Plan)

1.  **Uji Tembak Lokal**: Menggunakan Postman atau skrip cURL untuk mengirim data tiruan berulang kali ke server pusat, lalu memastikan data tersimpan ke tabel `fingerprint_raw_logs` dengan status `pending`.
2.  **Uji Cron Manual**: Menjalankan perintah `php artisan attendance:dispatch` secara manual melalui terminal/SSH hosting (jika ada) atau hit via rute web sementara untuk melihat apakah data berhasil diteruskan ke aplikasi klien.
3.  **Uji Hambatan**: Memastikan bahwa ketika aplikasi klien sengaja dimatikan (*down*), server pusat tidak kehilangan data dan status log berubah menjadi *retry* / tetap *pending*.

---
