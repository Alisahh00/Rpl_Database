<?php
session_start();
include 'koneksi.php';

// Pastikan hanya pengguna yang login yang bisa mengakses
if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

$pesan_redirect = "gagal_hapus"; // Default pesan

// 1. Cek apakah ID dikirim melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_kasir = $koneksi->real_escape_string($_GET['id']);
    
    // Query DELETE
    $query = "DELETE FROM kasir WHERE id_kasir = '$id_kasir'";

    if ($koneksi->query($query) === TRUE) {
        // Data berhasil dihapus
        $pesan_redirect = "sukses_hapus";
    } else {
        // Gagal hapus
        $pesan_redirect = "gagal_hapus";
    }
} else {
    // Tidak ada ID yang dikirim
    $pesan_redirect = "tidak_ada_id";
}

// Menutup koneksi
if (isset($koneksi) && $koneksi) {
    $koneksi->close();
}

// Arahkan kembali ke halaman data kasir dengan membawa status
header("location:data_kasir.php?status=$pesan_redirect");
exit();
?>