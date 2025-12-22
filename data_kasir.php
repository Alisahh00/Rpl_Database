<?php
session_start();
include 'koneksi.php';
// --- KODE BARU INI DITEMPATKAN DI BAGIAN ATAS data_kasir.php ---
$status_message = '';
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status == 'sukses_hapus') {
        $status_message = "<div class='alert alert-success'>✅ Data kasir berhasil dihapus!</div>";
    } elseif ($status == 'gagal_hapus') {
        $status_message = "<div class='alert alert-error'>❌ Gagal menghapus data kasir. Silakan coba lagi.</div>";
    } elseif ($status == 'tidak_ada_id') {
        $status_message = "<div class='alert alert-error'>❌ Gagal: ID kasir tidak valid atau tidak ditemukan.</div>";
    }
}
// --- AKHIR KODE BARU ---
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
    <title>Manajemen Data Kasir</title>
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
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px rgba(0,0,0,0.1);
        }
        .aksi-menu a:hover {
            transform: translateY(1px);
            box-shadow: 0 3px rgba(0,0,0,0.1);
        }

        .btn-dashboard {
            background-color: #3498db; /* Biru */
            color: white;
        }
        .btn-dashboard:hover {
            background-color: #2980b9;
        }
        .btn-tambah {
            background-color: #9b59b6; /* Ungu untuk kasir */
            color: white;
        }
        .btn-tambah:hover {
            background-color: #8e44ad;
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
            transition: background-color 0.2s;
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

        <div class="container">

    <h1>👥 Manajemen Data Kasir</h1>
    
    <?php echo $status_message; ?>
        
        <div class="aksi-menu">
            <a href="index.php" class="btn-dashboard">⬅️ Kembali ke Dashboard</a>
            <a href="tambah_kasir.php" class="btn-tambah">➕ Tambah Kasir Baru</a>
        </div>

        <?php
        // Ambil data kasir dari database
        // NOTE: Jangan tampilkan password di sini
        $query = "SELECT id_kasir, nama_kasir, username FROM kasir ORDER BY id_kasir DESC";
        $result = $koneksi->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kasir</th>
                    <th>Username</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id_kasir"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama_kasir"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                        echo "<td style='text-align: center;'>";
                        echo "<a href='edit_kasir.php?id=" . $row["id_kasir"] . "' class='btn-aksi btn-edit'>Edit</a>";
                        // Anda perlu membuat file hapus_kasir.php
                        echo "<a href='hapus_kasir.php?id=" . $row["id_kasir"] . "' class='btn-aksi btn-hapus' onclick=\"return confirm('Yakin ingin menghapus data kasir: " . htmlspecialchars($row["nama_kasir"]) . "?')\">Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center; color: #7f8c8d; padding: 30px;'>Belum ada data kasir. Silakan tambahkan kasir baru.</td></tr>";
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