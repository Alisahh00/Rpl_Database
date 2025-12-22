<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}
include 'koneksi.php';

if (!isset($_POST['proses_bayar']) || empty($_SESSION['keranjang'])) {
    header("location:transaksi_baru.php?pesan=keranjang_kosong");
    exit();
}

// ------------------------------------
// 1. PENGAMBILAN DATA & VALIDASI
// ------------------------------------
$total_belanja = (float)$_POST['total_bayar_hidden'];
$bayar = (float)$_POST['bayar'];
$id_kasir = $_SESSION['id_kasir'];
$kembalian = $bayar - $total_belanja;

if ($kembalian < 0) {
    header("location:transaksi_baru.php?pesan=bayar_kurang");
    exit();
}

// DIPERBARUI: Pisahkan menjadi tanggal dan waktu
$tanggal_saja = date("Y-m-d"); 
$waktu_saja = date("H:i:s");

// ----------------------------------------------------
// 2. TRANSAKSI DATABASE
// ----------------------------------------------------
$koneksi->begin_transaction();
$sukses = true; 

try {
    // A. GENERATE NO FAKTUR UNIK
    $tgl = date("Ymd");
    $jam = date("His");
    $no_faktur = "TRX" . $tgl . $jam . rand(100, 999); 

    // B. INSERT KE TABEL TRANSAKSI (HEADER)
    // DIPERBARUI: Menyertakan 'waktu_transaksi' dan 7 placeholder
    $sql_transaksi = "INSERT INTO transaksi (no_faktur, tanggal_transaksi, waktu_transaksi, total_bayar, bayar, kembalian, id_kasir) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $koneksi->prepare($sql_transaksi);
    
    if ($stmt === FALSE) {
        $sukses = false;
        throw new Exception("SQL PREPARE GAGAL di transaksi header: " . $koneksi->error); 
    }
    
    // DIPERBARUI: 7 parameter: 'sssdddi'
    $stmt->bind_param("sssdddi", 
        $no_faktur, 
        $tanggal_saja, // string
        $waktu_saja,   // string
        $total_belanja, 
        $bayar, 
        $kembalian, 
        $id_kasir
    );
    
    if (!$stmt->execute()) {
        $sukses = false;
        throw new Exception("Gagal menyimpan transaksi header: " . $stmt->error);
    }

    $id_transaksi_baru = $koneksi->insert_id;
    $stmt->close();
    
    // C. LOOP DETAIL TRANSAKSI & UPDATE STOK
    $sql_detail = "INSERT INTO detail_transaksi (id_transaksi, id_barang, harga_saat_transaksi, jumlah_beli, subtotal) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt_detail = $koneksi->prepare($sql_detail);
    
    $sql_stok = "UPDATE barang SET stok = stok - ? WHERE id_barang = ?";
    $stmt_stok = $koneksi->prepare($sql_stok);
    
    if ($stmt_detail === FALSE || $stmt_stok === FALSE) {
         throw new Exception("SQL PREPARE GAGAL pada detail atau stok: " . $koneksi->error); 
    }
    
    foreach ($_SESSION['keranjang'] as $item) {
        // C1. Insert Detail Transaksi
        $stmt_detail->bind_param("iiddd", 
            $id_transaksi_baru, 
            $item['id_barang'], 
            $item['harga_saat_transaksi'], 
            $item['jumlah_beli'], 
            $item['subtotal']
        );
        if (!$stmt_detail->execute()) {
            throw new Exception("Gagal menyimpan detail transaksi: " . $stmt_detail->error);
        }

        // C2. Kurangi Stok Barang
        $stmt_stok->bind_param("ii", $item['jumlah_beli'], $item['id_barang']);
        if (!$stmt_stok->execute()) {
            throw new Exception("Gagal mengurangi stok: " . $stmt_stok->error);
        }
    }
    
    // 3. COMMIT jika semua query berhasil dijalankan
    $koneksi->commit();

    // 4. Kosongkan keranjang 
    unset($_SESSION['keranjang']);

    // 5. Redirect ke halaman transaksi baru dengan status sukses untuk menampilkan POP-UP
    header("location:transaksi_baru.php?status=sukses&faktur=" . $no_faktur);
    
} catch (Exception $e) {
    // 6. ROLLBACK jika ada error
    $koneksi->rollback();
    error_log($e->getMessage()); 
    
    // Redirect kembali ke halaman transaksi dengan status gagal
    header("location:transaksi_baru.php?status=gagal&detail=" . urlencode("Gagal menyimpan data. Error: " . $e->getMessage()));
}

$koneksi->close();
exit();
?>