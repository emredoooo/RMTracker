# SIMRS RM Tracker (Standalone Web App)

Sistem pelacakan berkas fisik Rekam Medis (RM) berbasis web menggunakan pendekatan *Mobile-First* dan *Chain of Custody*. Aplikasi ini dirancang untuk membaca QR Code pada sampul berkas fisik guna memonitor pergerakan berkas dari Ruang RM ke berbagai instalasi/ruangan (seperti IGD, Rawat Inap, dll) secara *real-time*.

## Keunggulan Proyek Ini
1. **Desain Mobile-First & Intuitif**: Antarmuka dibangun dengan Bootstrap 5 yang disesuaikan secara khusus untuk *smartphone* (Bottom Navigation, tombol *scan* mengambang) sehingga sangat natural dan cepat digunakan oleh perawat/petugas di lapangan.
2. **Keamanan Data (Single Database Lokal)**: Beroperasi 100% pada *database* lokal (`rmtracker_db`), sehingga tidak membebani atau berisiko merusak struktur database SIMRS Khanza yang sensitif.
3. **Chain of Custody Logik**: Alur pelacakan jelas (*Check-Out*, *Transfer*, *Check-In*). Sistem otomatis mencatat perpindahan tanggung jawab (*holder*) secara historis dan *immutable* (tidak bisa dihapus selain oleh superadmin dari database).
4. **Fitur Pengaman Scan (Confirmation)**: Mencegah *human error* (tidak sengaja *scan* ganda) dengan menampilkan detail riwayat dan *popup* konfirmasi sebelum aksi diproses.
5. **Ringan & Native**: Dibangun dengan PHP Native (PDO) dan Javascript Vanilla tanpa *overhead framework backend* yang berat, sehingga sangat cocok dijalankan di *server* rumah sakit skala menengah ke bawah.

## Kelemahan (Limitasi) Saat Ini
1. **Tidak Ada Sinkronisasi Data Pasien Langsung**: Karena *database* dibuat mandiri (berpisah dari SIMRS Khanza), sistem saat ini hanya mengenali angka "No RM", tanpa bisa menampilkan nama pasien secara otomatis. 
2. **Kebergantungan pada HTTPS (Kamera)**: Library *Scanner QR* (html5-qrcode) mensyaratkan akses lewat protokol yang aman (HTTPS) atau `localhost` murni agar *browser* (Chrome/Safari) mengizinkan akses ke kamera *smartphone*. Jika diakses lewat IP LAN HTTP biasa (misal: `http://192.168.1.10/`), kamera mungkin diblokir oleh browser.
3. **Tidak Tersedia Recovery Lupa Password**: Saat ini penambahan dan pengaturan peran/password *user* hanya dapat dilakukan manual dari *dashboard* Superadmin.

## Potensi Pengembangan (Future Development)
1. **Integrasi API Read-Only ke SIMRS Khanza**: Membangun *endpoint* khusus (atau koneksi DB read-only) agar saat No RM di-scan, sistem bisa langsung menampilkan **Nama Pasien** dan **Diagnosa Singkat/Tujuan Poliklinik**.
2. **PWA (Progressive Web App)**: Mengubah sistem ini menjadi PWA agar bisa di-*install* langsung ke layar utama (*homescreen*) *smartphone* Android/iOS perawat tanpa perlu membuka browser secara manual.
3. **Sistem Notifikasi Telegram/WhatsApp**: Menghubungkan *trigger* Overdue (> 24 Jam) ke Bot Telegram agar sistem dapat otomatis "meneror" atau mengingatkan perawat/petugas yang bersangkutan untuk mengembalikan berkas.
4. **Dashboard Analitik**: Menambahkan grafik (*Chart.js*) untuk melihat performa pengembalian berkas, ruangan mana yang paling lama menyimpan berkas, dsb.

---

### Panduan Instalasi (Development)
1. Pindahkan folder `RMTracker` ke dalam `htdocs` XAMPP.
2. Buka MySQL/MariaDB (phpMyAdmin) atau via Terminal.
3. Jalankan sintaks SQL yang ada di dalam file `database/schema.sql` untuk membuat tabel-tabel pendukung.
4. Sesuaikan `config/database.php` jika diperlukan (default: localhost, root, tanpa password).
5. Akses `http://localhost/em/RMTracker/login.php` dan gunakan akun bawaan `admin` (password: `admin123`).
