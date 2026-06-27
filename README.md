# 🌍 Smart Travel App (Weather-Adaptive)

Smart Travel adalah aplikasi manajemen rencana perjalanan (itinerary) berbasis web yang dilengkapi dengan fitur cerdas **Weather-Adaptive Agent**. Aplikasi ini dikembangkan sebagai Tugas Besar Pemrograman Fullstack.

## ✨ Fitur Utama

- **🗺️ Manajemen Jadwal Perjalanan:** Tambahkan, edit, dan hapus rencana perjalanan dengan mudah.
- **🤖 Weather-Adaptive Agent:** Sistem cerdas yang mendeteksi prediksi hujan berdasarkan tanggal rencana kunjungan. Jika terdeteksi hujan pada jadwal destinasi *outdoor* (luar ruangan), sistem otomatis mencari alternatif destinasi *indoor* di kota yang sama dan mengalihkan jadwal secara cerdas.
- **Real-Time Notification:** Menggunakan Laravel Reverb, ketika jadwal dialihkan otomatis oleh agent, sistem mengirimkan notifikasi secara real-time ke pengguna.
- **Cuaca Visual:** Menampilkan ikon cuaca dan peringatan pada *dashboard* berdasarkan data OpenWeatherMap (atau data simulasi).
- **Profil Pengguna:** Manajemen profil dengan fitur upload foto profil (avatar).

## 💻 Tech Stack

- **Framework:** Laravel 12
- **Frontend:** Laravel Livewire 3 (Volt) & Alpine.js
- **Styling:** Tailwind CSS (dikustomisasi dengan desain UI premium & interaktif)
- **Database:** MySQL
- **WebSockets:** Laravel Reverb untuk event *real-time*
- **External API:** OpenWeatherMap API (Opsional)

## 🚀 Cara Menjalankan Project (Local Development)

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi di perangkat Anda:

1. **Clone repository ini**
   ```bash
   git clone https://github.com/marvdav01/TB_Pemrograman-Fullstack.git
   cd TB_Pemrograman-Fullstack/smart-travel
   ```

2. **Install dependency PHP & Node.js**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi file Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Buka file `.env` dan atur koneksi database (MySQL). Anda dapat membuat database baru misalnya `db_weather-adaptive`.*

4. **Jalankan Migrasi dan Seeder (Untuk mendapatkan data contoh)**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Buat Symlink Storage (Penting untuk foto profil)**
   ```bash
   php artisan storage:link
   ```

6. **Kompilasi aset frontend dan jalankan server lokal**
   Buka 2 terminal terpisah:
   
   **Terminal 1:**
   ```bash
   npm run dev
   ```
   **Terminal 2:**
   ```bash
   php artisan serve
   ```

7. **Buka aplikasi di browser**
   Akses `http://localhost:8000`. Anda bisa login menggunakan akun contoh:
   - Email: `test@example.com`
   - Password: `password`

## 🌦️ Menjalankan Weather Agent

Sistem *Agent* dapat dijalankan secara manual (untuk simulasi) melalui menu **Agent Panel** di aplikasi.
Pilih tanggal yang ingin diproses, dan *Agent* akan mengecek cuaca pada tanggal tersebut lalu memindahkan semua jadwal outdoor ke indoor jika terdeteksi hujan!

## 📝 Lisensi

Aplikasi ini dikembangkan untuk keperluan akademik (Tugas Besar). Hak Cipta dilindungi.
