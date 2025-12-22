<?php 
session_start();

// Cek apakah session status adalah "login"
if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit(); 
}

// Tambahkan inisialisasi variabel
$nama_kasir_login = isset($_SESSION['nama_kasir']) ? $_SESSION['nama_kasir'] : 'Pengguna';

// Memanggil file koneksi (POSISI ASLI)
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir Sederhana</title>
    <style>
        /* --- Gaya Dasar & Layout --- */
        body { 
            font-family: 'Open Sans', Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: #e9ebee; /* Latar belakang sangat lembut */
            color: #333;
        }
        .container { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 30px; 
            background-color: white; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); /* Bayangan yang lebih dalam */
            border-radius: 12px;
        }
        h1 { 
            color: #2c3e50; /* Warna gelap elegan */
            text-align: center; 
            margin-bottom: 30px; 
            font-size: 2.5em;
            font-weight: 700;
        }
        h2 { 
            color: #2c3e50; 
            border-left: 5px solid #0099cc; /* Garis vertikal di kiri judul */
            padding-left: 15px; 
            margin-top: 50px;
            margin-bottom: 20px;
            font-size: 1.8em;
            font-weight: 600;
        }
        
        /* --- Info Kasir & Logout --- */
        .info-kasir { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px; 
            background-color: #f7f9fb; /* Warna hampir putih */
            border-left: 5px solid #1abc9c; /* Border hijau tosca */
            border-radius: 8px; 
            margin-bottom: 30px; 
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        .info-kasir p { margin: 0; font-size: 1.1em; }
        .info-kasir strong { color: #1abc9c; font-weight: 700; }
        .info-kasir a { 
            color: #e74c3c; /* Merah untuk Logout */
            text-decoration: none; 
            font-weight: 600; 
        }
        .info-kasir a:hover { text-decoration: underline; }

        /* --- Navigasi Menu (GRID/Card Menu) --- */
        .nav-menu { 
            margin-bottom: 40px; 
            padding: 20px 0;
            border-top: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: center;
        }
        .nav-menu a { 
            text-decoration: none; 
            color: #2c3e50; 
            font-weight: 600; 
            padding: 25px 15px; 
            border-radius: 10px;
            display: block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            background-color: white;
            border: 1px solid #eee;
        }
        .nav-menu a:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        /* Warna highlight ikon/teks menu */
        a[href="data_barang.php"] span { color: #3498db; }
        a[href="data_kasir.php"] span { color: #9b59b6; }
        a[href="transaksi_baru.php"] span { color: #2ecc71; font-size: 1.5em; }
        a[href="riwayat_transaksi.php"] span { color: #f39c12; }
        
        /* Menambahkan ikon */
        .nav-menu a span {
            display: block;
            font-size: 2em;
            margin-bottom: 8px;
        }

        /* --- Gaya Tabel Data Sekilas --- */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
            border-radius: 8px;
            overflow: hidden; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #ecf0f1; 
        }
        th { 
            background-color: #34495e; /* Header sangat gelap */
            color: white; 
            font-weight: 600;
            text-transform: uppercase;
        }
        tr:nth-child(even) { background-color: #fcfcfc; }
        tr:hover { background-color: #f0f4f7; transition: background-color 0.2s; }
        
        /* Status koneksi */
        .koneksi-status { margin-bottom: 20px; text-align: center; font-style: italic; font-size: 0.9em; color: #7f8c8d; }
    </style>
</head>
<body>

    <div class="container">

        <h1>Kasir Sederhana</h1>

        <div class="info-kasir">
            <p>
                Halo, <?php echo htmlspecialchars($nama_kasir_login); ?>
            </p>
            <p>
                <a href="logout.php">🚪 Keluar (Logout)</a>
            </p>
        </div>

        <div class="nav-menu">
            <div class="menu-grid">
                <a href="data_barang.php">
                    <span>📦</span>
                    Kelola Data Barang
                </a>
                <a href="data_kasir.php">
                    <span>👥</span>
                    Kelola Data Kasir
                </a>
                <a href="transaksi_baru.php">
                    <span>🛒</span>
                    Mulai Transaksi Baru
                </a>
                <a href="riwayat_transaksi.php">
                    <span>📊</span>
                    Riwayat Transaksi
                </a>
            </div>
        </div>

        <?php
        // Pengecekan koneksi
        if (isset($koneksi) && $koneksi) {
            echo "<p class='koneksi-status' style='color: #27ae60;'>✅ Koneksi ke database berhasil!</p>";
        } else {
             echo "<p class='koneksi-status' style='color: #e74c3c;'>❌ Koneksi ke database GAGAL! Periksa file koneksi.php</p>";
        }
        ?>
        
        <h2>Data Barang (Sekilas)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($koneksi) && $koneksi) {
                    $query_barang = "SELECT * FROM barang ORDER BY id_barang DESC LIMIT 5";
                    $result_barang = $koneksi->query($query_barang);
    
                    if ($result_barang && $result_barang->num_rows > 0) {
                        while($row = $result_barang->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id_barang"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["kode_barang"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["nama_barang"]) . "</td>";
                            echo "<td>Rp " . number_format($row["harga_jual"], 0, ',', '.') . "</td>";
                            echo "<td>" . htmlspecialchars($row["stok"]) . " " . htmlspecialchars($row["satuan"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Tidak ada data barang. Silakan tambahkan item baru.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='color: #e74c3c;'>Database tidak terhubung. Data tidak dapat ditampilkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Data Kasir (Sekilas)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kasir</th>
                    <th>Username</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($koneksi) && $koneksi) {
                    $query_kasir = "SELECT id_kasir, nama_kasir, username FROM kasir ORDER BY id_kasir DESC LIMIT 5";
                    $result_kasir = $koneksi->query($query_kasir);
    
                    if ($result_kasir && $result_kasir->num_rows > 0) {
                        while($row = $result_kasir->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id_kasir"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["nama_kasir"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Tidak ada data kasir. Silakan tambahkan kasir baru.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='color: #e74c3c;'>Database tidak terhubung. Data tidak dapat ditampilkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div> <?php
    // Menutup koneksi
    if (isset($koneksi) && $koneksi) {
        $koneksi->close();
    }
    ?>

</body>
</html>