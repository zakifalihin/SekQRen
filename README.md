# School Attendance System (Laravel + Flutter)

Sistem absensi sekolah modern berbasis **Laravel** sebagai backend & web admin, serta **Flutter** sebagai aplikasi mobile untuk guru. Sistem ini mendukung absensi real-time, laporan lengkap, dan manajemen data sekolah (guru, siswa, kelas, jadwal, dan mata pelajaran).

---

## 1. Fitur Utama

### Web Admin (Laravel)
- CRUD Guru
- CRUD Siswa + Import Excel
- CRUD Kelas & Jadwal
- CRUD Mata Pelajaran
- Dashboard Absensi Real-time
- Laporan Absensi Harian, Mingguan, Bulanan
- Export Laporan (Excel, PDF, CSV)
- Manajemen Role User
- Autentikasi (Laravel Sanctum)
- Notifikasi Email (Queue)
- Cron Job (Backup otomatis)

### Mobile App (Flutter)
- Login Guru menggunakan token Sanctum
- Check-in dan Check-out absensi
- Melihat riwayat absensi
- Notifikasi (via push)
- Penyimpanan session di local storage

---

## 2. Teknologi yang Digunakan

### Backend (Laravel)
- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Laravel Sanctum
- Laravel Excel
- Redis (opsional)
- Bootstrap 5

### Mobile (Flutter)
- Flutter 3.x
- Dart
- Dio (HTTP client)
- SharedPreferences

---

## 3. Struktur Proyek

```
root/
 ├── backend-laravel/      → Backend + Web Admin
 └── mobile-flutter/       → Aplikasi Mobile Flutter
```

---

## 4. Instalasi Backend (Laravel)

### 4.1 Clone Repository
```bash
git clone https://github.com/zakifalihin/SekQRen.git
cd backend-laravel
```

### 4.2 Install Dependencies
```bash
composer install
```

### 4.3 Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

Atur database di `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3308
DB_DATABASE=absensi_sekolah
DB_USERNAME=root
DB_PASSWORD=
```

### 4.4 Migrasi Database
```bash
php artisan migrate
```

Atau import file SQL:

```bash
mysql -u root -p absensi_sekolah < absensi_sekolah.sql
```

### 4.5 Jalankan Server
```bash
php artisan serve --host=0.0.0.0
```

Akses:
```
http://localhost:8000
```

---

## 5. Instalasi Mobile App (Flutter)

### 5.1 Masuk ke direktori Flutter
```bash
cd mobile-flutter
```

### 5.2 Install Dependencies
```bash
flutter pub get
```

### 5.3 Konfigurasi Base URL API
Edit file:
```
lib/config/api_config.dart
```

Isi:
```dart
static const baseUrl = "http://192.168.1.100:8000/api";
```

### 5.4 Jalankan Aplikasi
```bash
flutter run
```

---

## 6. Dokumentasi API

### Autentikasi

#### POST /api/login
```json
{
  "nip": "1302023001",
  "password": "password123"
}
```

#### POST /api/logout
Header:
```
Authorization: Bearer <token>
```

---

### Absensi

#### POST /api/attendance/checkin
```json
{
  "latitude": -6.2088,
  "longitude": 106.8456,
  "location_name": "SMA Negeri 1 Jakarta"
}
```

#### GET /api/attendance/history
Query:
```
?page=1&limit=10&month=2024-01
```

---

## 7. Roadmap

- [x] CRUD Guru & Siswa
- [x] Autentikasi Guru (Sanctum)
- [x] Absensi Check-in/out
- [x] Laporan Absensi
- [ ] Push Notification (FCM)
- [ ] Validasi GPS Radius
- [ ] Absensi Menggunakan Face Recognition
- [ ] Dashboard Orang Tua

---

## 8. Kontribusi
Pull request sangat diterima.  
Ikuti standar PSR-12 untuk PHP dan formatting Dart.

---

## 9. Lisensi
Proyek ini menggunakan **MIT License**.

