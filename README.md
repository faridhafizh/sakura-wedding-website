# Website Undangan Pernikahan Haruka & Yuto 🌸

Selamat datang di repositori kode untuk website undangan pernikahan interaktif Haruka & Yuto! Website ini dirancang untuk memberikan informasi detail mengenai perayaan cinta kami dan memudahkan tamu untuk melakukan RSVP.

![Contoh Tampilan Website Pernikahan](URL_SCREENSHOT_WEBSITE_ANDA_DISINI)
*(Ganti URL_SCREENSHOT_WEBSITE_ANDA_DISINI dengan URL ke gambar screenshot website Anda. Anda bisa mengunggah screenshot ke tab "Issues" di repo ini lalu salin URL gambarnya, atau gunakan layanan image hosting.)*

## ✨ Fitur Utama

*   **Desain Elegan Bertema Sakura:** UI/UX yang lembut dan menenangkan dengan sentuhan bunga sakura.
*   **Informasi Pernikahan Lengkap:** Detail tanggal, waktu, lokasi, dan peta interaktif.
*   **Hitung Mundur (Countdown):** Menghitung waktu menuju hari bahagia.
*   **Galeri Foto:** Slideshow momen-momen berharga Haruka & Yuto.
*   **Formulir RSVP Interaktif:** Tamu dapat dengan mudah mengonfirmasi kehadiran.
*   **Pemutar Musik Latar:** Musik romantis untuk menemani pengunjung.
*   **Animasi Halus:** Efek visual yang menarik menggunakan AnimeJS.
*   **Desain Responsif:** Tampilan optimal di berbagai perangkat (desktop, tablet, mobile).

## 🛠️ Teknologi yang Digunakan

*   **Frontend:**
    *   HTML5
    *   CSS3 (dengan Tailwind CSS untuk styling cepat dan kustomisasi)
    *   JavaScript (ES6+)
    *   [Anime.js](https://animejs.com/) untuk animasi
    *   [Google Fonts](https://fonts.google.com/) (Playfair Display & Montserrat)
*   **Backend:**
    *   PHP untuk pemrosesan formulir RSVP dan interaksi database.
*   **Database:**
    *   MySQL (atau MariaDB) untuk menyimpan data RSVP.
*   **Lainnya:**
    *   Git & GitHub untuk version control dan hosting kode.

## 🚀 Persiapan & Instalasi Lokal (Untuk Pengembangan)

Jika Anda ingin menjalankan atau mengembangkan proyek ini secara lokal:

1.  **Prasyarat:**
    *   Web server lokal (misalnya XAMPP, MAMP, WAMP, atau server PHP bawaan).
    *   PHP (versi 7.4+ direkomendasikan).
    *   MySQL atau MariaDB.
    *   Git.

2.  **Clone Repositori:**
    ```bash
    git clone https://github.com/NAMA_PENGGUNA_ANDA/NAMA_REPOSITORI_ANDA.git
    cd NAMA_REPOSITORI_ANDA
    ```

3.  **Setup Database:**
    *   Buat database baru di phpMyAdmin (misalnya, `wedding_db`).
    *   Impor file `nama_file_sql_anda.sql` (jika Anda membuatnya terpisah) atau jalankan skema SQL yang ada di dalam `index.php` (jika digabungkan) atau yang ada di dokumentasi/file SQL terpisah ke database yang baru dibuat.
    *   Konfigurasi detail koneksi database di dalam file `index.php` (atau file konfigurasi PHP terpisah jika ada):
        ```php
        $dbHost = 'localhost';
        $dbUser = 'username_db_anda';
        $dbPass = 'password_db_anda';
        $dbName = 'wedding_db';
        ```

4.  **Jalankan Proyek:**
    *   Tempatkan folder proyek di direktori `htdocs` (XAMPP), `www` (WAMP), atau direktori web server lokal Anda.
    *   Buka browser dan akses `http://localhost/NAMA_FOLDER_PROYEK_ANDA/` (misalnya `http://localhost/sakura-wedding-website/`).

## 📝 Struktur File (Contoh)
/sakura-wedding-website
│
├── index.php # File utama (HTML, CSS, JS, dan logika PHP)
├── wedding-music.mp3 # Contoh file musik (ganti dengan milik Anda)
├── sakura.jpg # Contoh gambar hero (ganti dengan milik Anda)
├── gallery1.jpg # Contoh gambar galeri (ganti dengan milik Anda)
├── ... (aset gambar lainnya)
└── README.md # File yang sedang Anda baca

## 🔧 Konfigurasi Penting dalam `index.php`

Pastikan Anda memperbarui bagian berikut di dalam file `index.php`:

1.  **Detail Koneksi Database:** Seperti yang disebutkan di bagian Setup Database.
2.  **Tanggal Pernikahan (JavaScript):**
    ```javascript
    const weddingDate = new Date('December 31, 2024 14:00:00').getTime(); // GANTI TANGGAL INI!
    ```
3.  **Tanggal Pernikahan (HTML Tampilan):** Sesuaikan juga tanggal yang ditampilkan di bagian detail pernikahan.
4.  **Path Aset:** Pastikan path ke gambar (`<img> src`) dan file musik (`<audio> src`) sudah benar.

## 🌐 Hosting & Deployment

Proyek ini dirancang untuk di-host di server yang mendukung PHP dan MySQL. Beberapa opsi hosting gratis atau berbayar:

*   **Shared Hosting:** Banyak penyedia menawarkan paket murah dengan dukungan PHP & MySQL.
*   **VPS (Virtual Private Server):** Memberikan kontrol lebih, tetapi membutuhkan pengetahuan teknis lebih.
*   **Platform Gratis (dengan batasan):** Beberapa platform seperti 000webhost atau InfinityFree mungkin bisa digunakan untuk tujuan demo (perhatikan batasan dan performa).
*   **GitHub Pages (Hanya untuk Statis):** Jika Anda menghilangkan bagian PHP dan database (RSVP akan memerlukan layanan pihak ketiga seperti Google Forms), Anda bisa hosting bagian frontend statisnya di GitHub Pages.

## 🤝 Kontribusi (Jika Relevan)

Jika ini adalah proyek komunitas atau Anda terbuka untuk kontribusi:
Saat ini, proyek ini dikelola secara pribadi. Namun, saran dan masukan selalu diterima! Silakan buat *Issue* jika Anda menemukan bug atau memiliki ide perbaikan.

---

**Terima kasih telah mengunjungi!** ❤️
Haruka & Yuto
