<?php
session_start();
include 'koneksi.php';

if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

// 1. Cek apakah ID transaksi dikirim melalui URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location:riwayat_transaksi.php?status=gagal_hapus&detail=" . urlencode("ID Transaksi tidak ditemukan."));
    exit();
}

$id_transaksi = $koneksi->real_escape_string($_GET['id']);
$berhasil = false;
$error_detail = "";

// ===============================================
// MULAI TRANSAKSI MYSQL (PENTING UNTUK INTEGRITAS DATA)
// ===============================================
$koneksi->begin_transaction();

try {
    // 2. Ambil detail barang yang terjual (untuk mengembalikan stok)
    $query_detail = "SELECT id_barang, jumlah_beli FROM detail_transaksi WHERE id_transaksi = '$id_transaksi'";
    $result_detail = $koneksi->query($query_detail);

    if ($result_detail->num_rows > 0) {
        
        // 3. Loop dan KEMBALIKAN STOK BARANG
        while ($detail = $result_detail->fetch_assoc()) {
            $id_barang = $detail['id_barang'];
            $jumlah_kembali = $detail['jumlah_beli'];
            
            $query_update_stok = "UPDATE barang SET stok = stok + $jumlah_kembali WHERE id_barang = '$id_barang'";
            if (!$koneksi->query($query_update_stok)) {
                throw new Exception("Gagal mengembalikan stok barang ID: $id_barang. " . $koneksi->error);
            }
        }
    }
    
    // 4. Hapus Detail Transaksi (Harus sebelum Transaksi utama)
    $query_delete_detail = "DELETE FROM detail_transaksi WHERE id_transaksi = '$id_transaksi'";
    if (!$koneksi->query($query_delete_detail)) {
        throw new Exception("Gagal menghapus detail transaksi. " . $koneksi->error);
    }
    
    // 5. Hapus Transaksi Utama
    $query_delete_transaksi = "DELETE FROM transaksi WHERE id_transaksi = '$id_transaksi'";
    if (!$koneksi->query($query_delete_transaksi)) {
        throw new Exception("Gagal menghapus transaksi utama. " . $koneksi->error);
    }

    // 6. Jika semua berhasil, COMMIT (simpan perubahan)
    $koneksi->commit();
    $berhasil = true;

} catch (Exception $e) {
    // Jika ada langkah yang gagal, ROLLBACK (batalkan semua perubahan)
    $koneksi->rollback();
    $error_detail = $e->getMessage();
    $berhasil = false;
}

$koneksi->close();

// 7. Redirect dengan status
if ($berhasil) {
    header("location:riwayat_transaksi.php?status=sukses_hapus");
} else {
    header("location:riwayat_transaksi.php?status=gagal_hapus&detail=" . urlencode($error_detail));
}
exit();
?>