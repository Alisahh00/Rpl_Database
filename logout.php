<?php 
// 1. Mulai sesi
session_start();

// 2. Hancurkan semua data sesi yang ada
session_destroy();

// 3. Arahkan pengguna kembali ke halaman login.php
// Pesan 'logout' bisa digunakan di login.php untuk menampilkan notifikasi "Anda berhasil logout."
header("location:login.php?pesan=logout");

// 4. Hentikan eksekusi skrip untuk memastikan redirect berhasil
exit();
?>