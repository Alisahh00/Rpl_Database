<?php
// Konfigurasi Database
$server = "localhost"; // Ganti jika database berada di server lain
$username = "root";    // Ganti dengan username database Anda
$password = "";        // Ganti dengan password database Anda
$database = "aplikasi_kasir_sederhana"; // Ganti dengan nama database yang Anda gunakan

// Membuat koneksi
$koneksi = new mysqli($server, $username, $password, $database);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    // Jika koneksi gagal, tampilkan pesan error dan hentikan eksekusi
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Opsional: Atur karakter set ke UTF8 untuk mendukung karakter khusus
$koneksi->set_charset("utf8");

// Pesan ini hanya untuk debugging saat pertama kali dijalankan
// echo "Koneksi berhasil!"; 
// Anda dapat menghapus baris di atas setelah dipastikan berhasil.

?>