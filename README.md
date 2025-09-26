<p align="center">
  <img src="https://img.icons8.com/fluency/96/qr-code.png" width="120" alt="SekQRen Logo"/>
</p>

<h1 align="center">SekQRen</h1>
<p align="center"><b>Sistem Elektronik Kehadiran QR Code dengan Integrasi IoT</b></p>

<p align="center">
  <a href="https://github.com/username/sekqren"><img src="https://img.shields.io/badge/Status-Development-yellow" alt="Project Status"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-green" alt="License"></a>
</p>

---

## 📌 Tentang SekQRen
**SekQRen** adalah sistem absensi pintar berbasis **QR Code** yang terintegrasi dengan **IoT (Internet of Things)**.  
Proyek ini dikembangkan untuk menggantikan metode absensi manual yang sering lambat, rawan kecurangan (*titip absen*), dan sulit dalam pengelolaan laporan.  

Dengan SekQRen, siswa cukup memindai QR Code unik yang sudah terdaftar, data absensi langsung tercatat di server, dan hasilnya bisa dipantau secara **real-time** oleh guru, admin sekolah, maupun orang tua.

---

## 🚀 Fitur Utama
- 🔑 **Login multi-role** → Admin, Guru, Siswa, dan Orang Tua.
- 📲 **Absensi berbasis QR Code** → cepat, mudah, dan unik untuk setiap pengguna.
- ☁️ **Integrasi IoT** → data absensi disimpan otomatis ke server cloud.
- 🔔 **Notifikasi Real-time** → orang tua mendapat laporan kehadiran anak secara instan.
- 📊 **Dashboard Monitoring** → menampilkan statistik kehadiran siswa/guru.
- 📝 **Laporan Kehadiran** → tersedia dalam format harian, mingguan, dan bulanan (Excel/PDF).

---

## 🛠️ Teknologi yang Digunakan
- **Frontend**: Laravel Blade / Vue.js (opsional)
- **Backend**: Laravel Framework
- **Database**: MySQL / MariaDB
- **IoT Device**: Raspberry Pi / ESP32 sebagai pemindai QR
- **Cloud Integration**: API RESTful untuk sinkronisasi data
- **Deployment**: Ubuntu Server (Proxmox/VM)

---

## 🧩 Arsitektur Sistem
**Model Input – Process – Output (IPO):**

- **Input**: QR Code unik siswa/guru, perangkat pemindai, data jadwal/kelas.
- **Process**: Validasi identitas → pencatatan absensi → sinkronisasi ke server cloud.
- **Output**: Dashboard kehadiran, notifikasi real-time, laporan kehadiran.

---

## 📸 Contoh Use Case Diagram
*(opsional: tambahkan gambar diagram use case di folder `/docs/` lalu tautkan di sini)*  
```mermaid
usecaseDiagram
actor "Siswa" as S
actor "Guru" as G
actor "Orang Tua" as O
actor "Admin" as A

S --> (Scan QR Code)
G --> (Pantau Kehadiran)
O --> (Terima Notifikasi)
A --> (Kelola Data Pengguna)
