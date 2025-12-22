<?php
// Mulai sesi PHP
session_start();

// Panggil file koneksi
include 'koneksi.php';

// Tangkap data dari form login
$username = $_POST['username'];
$password = $_POST['password'];

// Melindungi dari SQL Injection (disarankan menggunakan prepared statement, tapi ini versi cepat)
$username = $koneksi->real_escape_string($username);
$password = $koneksi->real_escape_string($password);

// Query untuk mengambil data kasir berdasarkan username
$sql = "SELECT * FROM kasir WHERE username='$username'";
$result = $koneksi->query($sql);

// Cek apakah data kasir ditemukan
if ($result->num_rows == 1) {
    $data_kasir = $result->fetch_assoc();
    
    // VERIFIKASI PASSWORD
    // Catatan: Karena password di DB Anda (seperti 'hash_password_budi') adalah string dummy/plaintext,
    // kita akan membandingkannya secara langsung.
    // Dalam aplikasi nyata, gunakan if (password_verify($password, $data_kasir['password_hash']))
    
    if ($password == $data_kasir['password_hash']) { 
        
        // Login Berhasil!
        // Daftarkan data ke session
        $_SESSION['username'] = $username;
        $_SESSION['nama_kasir'] = $data_kasir['nama_kasir'];
        $_SESSION['id_kasir'] = $data_kasir['id_kasir'];
        $_SESSION['status'] = "login";
        
        // Redirect ke halaman dashboard (index.php)
        header("location:index.php");
    } else {
        // Password salah
        header("location:login.php?pesan=gagal");
    }
} else {
    // Username tidak ditemukan
    header("location:login.php?pesan=gagal");
}

$koneksi->close();
?>