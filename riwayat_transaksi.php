<?php 
session_start();
if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}
include 'koneksi.php';

// --- LOGIKA PENANGANAN STATUS HAPUS ---
$status_message = '';
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status == 'sukses_hapus') {
        $status_message = "<div class='alert alert-success'>✅ Transaksi berhasil dihapus dan stok barang telah dikembalikan!</div>";
    } elseif ($status == 'gagal_hapus') {
        $detail = isset($_GET['detail']) ? htmlspecialchars(urldecode($_GET['detail'])) : 'Kesalahan tak terduga.';
        $status_message = "<div class='alert alert-error'>❌ Gagal menghapus transaksi. Detail: $detail</div>";
    }
    // Bersihkan URL dari parameter status
    echo '<script>window.history.replaceState({}, document.title, "riwayat_transaksi.php");</script>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Penjualan</title>
    <style>
        /* --- Gaya Dasar & Layout (Seragam) --- */
        body { 
            font-family: 'Open Sans', Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: #e9ebee; 
            color: #333;
        }
        .container { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 30px; 
            background-color: white; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        h1 { 
            color: #2c3e50;
            text-align: center; 
            margin-bottom: 30px; 
            font-size: 2.5em;
            font-weight: 700;
        }

        /* --- Navigasi & Aksi Menu --- */
        .aksi-menu {
            display: flex;
            justify-content: flex-start;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .aksi-menu a {
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .btn-dashboard { background-color: #3498db; color: white; }
        .btn-dashboard:hover { background-color: #2980b9; }
        .btn-tambah { background-color: #2ecc71; color: white; }
        .btn-tambah:hover { background-color: #27ae60; }

        /* --- Tabel Styling --- */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td { 
            padding: 15px; 
            text-align: left; 
            border: 1px solid #ecf0f1; 
        }
        th { 
            background-color: #34495e; 
            color: white; 
            font-weight: 700;
            font-size: 0.9em;
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f0f0f0; }

        /* --- Tombol Aksi dalam Tabel --- */
        .btn-detail { 
            background-color: #17a2b8; 
            color: white; 
            padding: 8px 15px; 
            border-radius: 4px; 
            text-decoration: none; 
            font-size: 0.9em;
            font-weight: 600;
            transition: background-color 0.2s;
            display: inline-block;
        }
        .btn-detail:hover { background-color: #138496; }
        
        .btn-danger { 
            background-color: #e74c3c; /* Merah */
            color: white; 
            padding: 8px 15px; 
            border-radius: 4px; 
            text-decoration: none; 
            font-size: 0.9em;
            font-weight: 600;
            transition: background-color 0.2s;
            cursor: pointer;
            display: inline-block;
        }
        .btn-danger:hover { background-color: #c0392b; }

        /* --- Kolom Nominal (Rata Kanan) --- */
        .text-right { text-align: right; }
        .total-nominal { font-weight: bold; color: #2ecc71; }
        .kembalian-nominal { color: #3498db; }
        .faktur-column { font-weight: bold; color: #e67e22; }

        /* --- Alert Messages --- */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 600;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">
        
        <h1>📋 Riwayat Transaksi Penjualan</h1>

        <?php echo $status_message; ?>

        <div class="aksi-menu">
            <a href="index.php" class="btn-dashboard">⬅️ Kembali ke Dashboard</a>
            <a href="transaksi_baru.php" class="btn-tambah">➕ Transaksi Baru</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th class="text-right">Total Bayar</th>
                    <th class="text-right">Uang Dibayar</th>
                    <th class="text-right">Kembalian</th>
                    <th>Detail</th>
                    <th>Hapus</th> </tr>
            </thead>
            <tbody>
                <?php
                $query_transaksi = "SELECT t.id_transaksi, t.no_faktur, t.tanggal_transaksi, t.waktu_transaksi, t.total_bayar, t.bayar, t.kembalian, k.nama_kasir
                                    FROM transaksi t
                                    JOIN kasir k ON t.id_kasir = k.id_kasir
                                    ORDER BY t.tanggal_transaksi DESC, t.waktu_transaksi DESC";
                
                $result_transaksi = $koneksi->query($query_transaksi);

                if ($result_transaksi->num_rows > 0) {
                    while($row = $result_transaksi->fetch_assoc()) {
                        $tanggal_waktu_db = $row["tanggal_transaksi"] . " " . $row["waktu_transaksi"];

                        echo "<tr>";
                        echo "<td class='faktur-column'>" . htmlspecialchars($row["no_faktur"]) . "</td>";
                        echo "<td>" . date("d-m-Y H:i:s", strtotime($tanggal_waktu_db)) . "</td>"; 
                        echo "<td>" . htmlspecialchars($row["nama_kasir"]) . "</td>";
                        echo "<td class='text-right total-nominal'>Rp " . number_format($row["total_bayar"], 0, ',', '.') . "</td>";
                        echo "<td class='text-right'>Rp " . number_format($row["bayar"], 0, ',', '.') . "</td>";
                        echo "<td class='text-right kembalian-nominal'>Rp " . number_format($row["kembalian"], 0, ',', '.') . "</td>";
                        
                        // Kolom Detail
                        echo "<td>";
                        echo "<a class='btn-detail' href='transaksi_sukses.php?id=" . $row["id_transaksi"] . "'>Detail</a>";
                        echo "</td>";
                        
                        // Kolom Hapus
                        echo "<td style='text-align: center;'>";
                        echo "<a class='btn-danger' href='#' onclick=\"konfirmasiHapus(" . $row['id_transaksi'] . ", '" . htmlspecialchars($row['no_faktur']) . "')\">Hapus</a>";
                        echo "</td>";
                        
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' style='text-align: center; padding: 30px; color: #7f8c8d;'>Belum ada riwayat transaksi yang tersimpan.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>

<script>
function konfirmasiHapus(id, faktur) {
    if (confirm('⚠️ PERINGATAN! Anda akan menghapus transaksi Faktur ' + faktur + '. Stok barang akan dikembalikan ke gudang. Lanjutkan?')) {
        window.location.href = 'hapus_transaksi.php?id=' + id;
    }
}
</script>

</body>
</html>
<?php $koneksi->close(); ?>