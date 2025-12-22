<?php
session_start();
include 'koneksi.php';

if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

$message = '';

// 1. Cek apakah ada ID yang dikirim melalui URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location:data_barang.php");
    exit();
}

$id_barang = $koneksi->real_escape_string($_GET['id']);

// 2. LOGIKA PEMROSESAN UPDATE (Saat form disubmit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $kode_barang = $koneksi->real_escape_string($_POST['kode_barang']);
    $nama_barang = $koneksi->real_escape_string($_POST['nama_barang']);
    $harga_jual = (int)str_replace(['.', 'Rp ', ','], '', $_POST['harga_jual']);
    $stok = (int)$koneksi->real_escape_string($_POST['stok']);
    $satuan = $koneksi->real_escape_string($_POST['satuan']);

    // Query UPDATE
    $query_update = "UPDATE barang SET 
                        kode_barang = '$kode_barang',
                        nama_barang = '$nama_barang',
                        harga_jual = '$harga_jual',
                        stok = '$stok',
                        satuan = '$satuan'
                     WHERE id_barang = '$id_barang'";

    if ($koneksi->query($query_update) === TRUE) {
        $message = "<div class='alert alert-success'>✅ Data barang **$nama_barang** berhasil diperbarui!</div>";
        // Tidak perlu redirect, biarkan di halaman edit
    } else {
        $message = "<div class='alert alert-error'>❌ Error saat update: " . $koneksi->error . "</div>";
    }
}

// 3. AMBIL DATA TERBARU UNTUK DITAMPILKAN DI FORM
$query_select = "SELECT * FROM barang WHERE id_barang = '$id_barang'";
$result = $koneksi->query($query_select);

if ($result && $result->num_rows == 1) {
    $data_barang = $result->fetch_assoc();
} else {
    // Jika ID tidak ditemukan, kembalikan ke halaman data barang
    header("location:data_barang.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Barang</title>
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
            max-width: 800px; /* Lebar untuk form */
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
        
        /* --- Form Styling --- */
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box; 
            font-size: 1em;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: #0099cc;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 153, 204, 0.3);
        }
        
        /* --- Tombol Aksi --- */
        .btn-aksi-group {
            display: flex;
            justify-content: space-between; 
            margin-top: 30px;
        }
        .btn {
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-submit {
            background-color: #f39c12; /* Kuning/Oranye untuk Edit */
            color: white;
            box-shadow: 0 4px #e67e22;
        }
        .btn-submit:hover {
            background-color: #e67e22;
            transform: translateY(1px);
            box-shadow: 0 3px #e67e22;
        }
        .btn-back {
            background-color: #3498db; 
            color: white;
            box-shadow: 0 4px #2980b9;
        }
        .btn-back:hover {
            background-color: #2980b9;
            transform: translateY(1px);
            box-shadow: 0 3px #2980b9;
        }

        /* --- Alert Messages --- */
        .alert {
            padding: 15px;
            margin-top: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 600;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

    <div class="container">

        <h1>✏️ Edit Data Barang</h1>

        <?php 
        // Tampilkan pesan status (sukses/gagal update)
        echo $message; 
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="id_barang">ID Barang</label>
                <input type="text" id="id_barang" value="<?php echo htmlspecialchars($data_barang['id_barang']); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="kode_barang">Kode Barang</label>
                <input type="text" id="kode_barang" name="kode_barang" required 
                       value="<?php echo htmlspecialchars($data_barang['kode_barang']); ?>">
            </div>

            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" required 
                       value="<?php echo htmlspecialchars($data_barang['nama_barang']); ?>">
            </div>

            <div class="form-group">
                <label for="harga_jual">Harga Jual (Masukkan Angka Tanpa Titik/Koma)</label>
                <input type="number" id="harga_jual" name="harga_jual" required min="0" step="100"
                       value="<?php echo htmlspecialchars($data_barang['harga_jual']); ?>">
            </div>

            <div class="form-group">
                <label for="stok">Stok</label>
                <input type="number" id="stok" name="stok" required min="0" 
                       value="<?php echo htmlspecialchars($data_barang['stok']); ?>">
            </div>
            
            <div class="form-group">
                <label for="satuan">Satuan (Contoh: pcs, botol, kg)</label>
                <input type="text" id="satuan" name="satuan" required 
                       value="<?php echo htmlspecialchars($data_barang['satuan']); ?>">
            </div>

            <div class="btn-aksi-group">
                <a href="data_barang.php" class="btn btn-back">⬅️ Kembali ke Data Barang</a>
                <button type="submit" class="btn btn-submit">💾 Simpan Perubahan</button>
            </div>
        </form>

    </div>

</body>
</html>