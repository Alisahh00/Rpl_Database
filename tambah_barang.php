<?php
session_start();
include 'koneksi.php';

if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

$message = '';

// --- LOGIKA PEMROSESAN FORMULIR ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan bersihkan (sanitize)
    $kode_barang = $koneksi->real_escape_string($_POST['kode_barang']);
    $nama_barang = $koneksi->real_escape_string($_POST['nama_barang']);
    // Hapus format mata uang atau pemisah ribuan dari harga_jual sebelum disimpan
    $harga_jual = (int)str_replace(['.', 'Rp ', ','], '', $_POST['harga_jual']);
    $stok = (int)$koneksi->real_escape_string($_POST['stok']);
    $satuan = $koneksi->real_escape_string($_POST['satuan']);

    // Validasi sederhana
    if (empty($kode_barang) || empty($nama_barang) || empty($harga_jual) || empty($stok) || empty($satuan)) {
        $message = "<div class='alert alert-error'>❌ Gagal: Semua kolom wajib diisi!</div>";
    } else {
        // Query INSERT
        $query = "INSERT INTO barang (kode_barang, nama_barang, harga_jual, stok, satuan) 
                  VALUES ('$kode_barang', '$nama_barang', '$harga_jual', '$stok', '$satuan')";

        if ($koneksi->query($query) === TRUE) {
            // Setelah berhasil, kosongkan input agar form siap diisi lagi
            $message = "<div class='alert alert-success'>✅ Data barang **$nama_barang** berhasil ditambahkan!</div>";
            unset($_POST); // Clear POST data
        } else {
            $message = "<div class='alert alert-error'>❌ Error: " . $koneksi->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Barang</title>
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
            justify-content: space-between; /* Untuk memisahkan tombol Kembali dan Simpan */
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
            background-color: #2ecc71; 
            color: white;
            box-shadow: 0 4px #27ae60;
        }
        .btn-submit:hover {
            background-color: #27ae60;
            transform: translateY(1px);
            box-shadow: 0 3px #27ae60;
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

        <h1>➕ Tambah Data Barang Baru</h1>

        <?php 
        // Tampilkan pesan status (sukses/gagal)
        echo $message; 
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="kode_barang">Kode Barang</label>
                <input type="text" id="kode_barang" name="kode_barang" required 
                       value="<?php echo isset($_POST['kode_barang']) ? htmlspecialchars($_POST['kode_barang']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" required 
                       value="<?php echo isset($_POST['nama_barang']) ? htmlspecialchars($_POST['nama_barang']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="harga_jual">Harga Jual (Masukkan Angka Tanpa Titik/Koma)</label>
                <input type="number" id="harga_jual" name="harga_jual" required min="0" step="100"
                       value="<?php echo isset($_POST['harga_jual']) ? htmlspecialchars($_POST['harga_jual']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="stok">Stok Awal</label>
                <input type="number" id="stok" name="stok" required min="0" 
                       value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="satuan">Satuan (Contoh: pcs, botol, kg)</label>
                <input type="text" id="satuan" name="satuan" required 
                       value="<?php echo isset($_POST['satuan']) ? htmlspecialchars($_POST['satuan']) : ''; ?>">
            </div>

            <div class="btn-aksi-group">
                <a href="data_barang.php" class="btn btn-back">⬅️ Kembali ke Data Barang</a>
                <button type="submit" class="btn btn-submit">💾 Simpan Data Barang</button>
            </div>
        </form>

    </div>

</body>
</html>