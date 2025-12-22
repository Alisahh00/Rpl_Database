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
    // Ambil data dari form dan bersihkan
    $nama_kasir = $koneksi->real_escape_string($_POST['nama_kasir']);
    $username = $koneksi->real_escape_string($_POST['username']);
    $password_raw = $_POST['password']; 
    
    // PENTING: Menggunakan password_hash untuk keamanan
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

    // Query INSERT
    // PASTIKAN NAMA KOLOM DISINI SESUAI DENGAN DB ANDA (yaitu: password_hash)
    $query = "INSERT INTO kasir (nama_kasir, username, password_hash) 
              VALUES ('$nama_kasir', '$username', '$password_hashed')";

    if ($koneksi->query($query) === TRUE) {
        $message = "<div class='alert alert-success'>✅ Data Kasir **$nama_kasir** berhasil ditambahkan!</div>";
        // Bersihkan input setelah sukses
        unset($_POST); 
    } else {
        // Error jika username sudah ada (misal kode error MySQL 1062)
        if ($koneksi->errno == 1062) {
             $message = "<div class='alert alert-error'>❌ Error: Username **$username** sudah digunakan.</div>";
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
    <title>Tambah Data Kasir</title>
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
            max-width: 800px; 
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
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #34495e; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; 
            font-size: 1em; transition: border-color 0.2s;
        }
        input:focus { border-color: #0099cc; outline: none; box-shadow: 0 0 5px rgba(0, 153, 204, 0.3); }
        
        /* --- Tombol Aksi --- */
        .btn-aksi-group { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn {
            padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; 
            transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-submit { background-color: #9b59b6; color: white; box-shadow: 0 4px #8e44ad; }
        .btn-submit:hover { background-color: #8e44ad; transform: translateY(1px); box-shadow: 0 3px #8e44ad; }
        .btn-back { background-color: #3498db; color: white; box-shadow: 0 4px #2980b9; }
        .btn-back:hover { background-color: #2980b9; transform: translateY(1px); box-shadow: 0 3px #2980b9; }

        /* --- Alert Messages --- */
        .alert { padding: 15px; margin-top: 20px; margin-bottom: 20px; border-radius: 6px; font-weight: 600; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">

        <h1>➕ Tambah Data Kasir Baru</h1>

        <?php 
        echo $message; 
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nama_kasir">Nama Lengkap Kasir</label>
                <input type="text" id="nama_kasir" name="nama_kasir" required 
                       value="<?php echo isset($_POST['nama_kasir']) ? htmlspecialchars($_POST['nama_kasir']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="username">Username (Digunakan untuk Login)</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="btn-aksi-group">
                <a href="data_kasir.php" class="btn btn-back">⬅️ Kembali ke Data Kasir</a>
                <button type="submit" class="btn btn-submit">💾 Simpan Kasir Baru</button>
            </div>
        </form>

    </div>

</body>
</html>
<?php 
// Menutup koneksi
if (isset($koneksi) && $koneksi) {
    $koneksi->close(); 
}
?>