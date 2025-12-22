<?php
session_start();
include 'koneksi.php';

if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Barang</title>
    <style>
        /* --- Gaya Dasar & Layout (Disamakan dengan Dashboard) --- */
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
        
        /* --- Navigasi dan Aksi --- */
        .aksi-menu {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        .aksi-menu a {
            text-decoration: none;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 6px;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-dashboard {
            background-color: #3498db; /* Biru */
            color: white;
        }
        .btn-dashboard:hover {
            background-color: #2980b9;
        }
        .btn-tambah {
            background-color: #2ecc71; /* Hijau */
            color: white;
        }
        .btn-tambah:hover {
            background-color: #27ae60;
        }

        /* --- Gaya Tabel --- */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
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
            background-color: #34495e;
            color: white; 
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        tr:nth-child(even) { background-color: #fcfcfc; }
        tr:hover { background-color: #f0f4f7; transition: background-color 0.2s; }
        
        /* Tombol Aksi dalam Tabel */
        .btn-aksi {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin-right: 5px;
            display: inline-block;
        }
        .btn-edit {
            background-color: #f39c12; /* Kuning */
            color: white;
        }
        .btn-edit:hover {
            background-color: #e67e22;
        }
        .btn-hapus {
            background-color: #e74c3c; /* Merah */
            color: white;
        }
        .btn-hapus:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

    <div class="container">

        <h1>Manajemen Data Barang</h1>
        
        <div class="aksi-menu">
            <a href="index.php" class="btn-dashboard">⬅️ Kembali ke Dashboard</a>
            <a href="tambah_barang.php" class="btn-tambah">➕ Tambah Barang Baru</a>
        </div>

        <?php
        // Ambil data barang dari database
        $query = "SELECT * FROM barang ORDER BY id_barang DESC";
        $result = $koneksi->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id_barang"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["kode_barang"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama_barang"]) . "</td>";
                        // Format mata uang
                        echo "<td>Rp " . number_format($row["harga_jual"], 0, ',', '.') . "</td>";
                        echo "<td>" . htmlspecialchars($row["stok"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["satuan"]) . "</td>";
                        echo "<td style='text-align: center;'>";
                        echo "<a href='edit_barang.php?id=" . $row["id_barang"] . "' class='btn-aksi btn-edit'>Edit</a>";
                        echo "<a href='hapus_barang.php?id=" . $row["id_barang"] . "' class='btn-aksi btn-hapus' onclick=\"return confirm('Yakin ingin menghapus data barang: " . htmlspecialchars($row["nama_barang"]) . "?')\">Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; color: #7f8c8d; padding: 30px;'>Belum ada data barang. Silakan tambahkan item baru.</td></tr>";
                }
                
                // Menutup koneksi
                if (isset($koneksi) && $koneksi) {
                    $koneksi->close();
                }
                ?>
            </tbody>
        </table>

    </div> 

</body>
</html>