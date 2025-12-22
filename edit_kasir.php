<?php
session_start();
include 'koneksi.php';

if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

$id_kasir = '';
$data = ['nama_kasir' => '', 'username' => ''];
$message = '';

// 1. Ambil data kasir yang akan diedit (Berdasarkan ID dari URL)
if (isset($_GET['id']) && !isset($_POST['id_kasir'])) { // Hanya ambil dari GET saat pertama kali loading
    $id_kasir = $koneksi->real_escape_string($_GET['id']);
} elseif (isset($_POST['id_kasir'])) { // Gunakan ID dari POST jika sedang update
    $id_kasir = $koneksi->real_escape_string($_POST['id_kasir']);
}

if ($id_kasir) {
    // Ambil data sebelum pemrosesan atau refresh setelah update
    $query_select = "SELECT id_kasir, nama_kasir, username FROM kasir WHERE id_kasir='$id_kasir'";
    $result_select = $koneksi->query($query_select);
    
    if ($result_select && $result_select->num_rows > 0) {
        $data = $result_select->fetch_assoc();
    } else {
        $message = "<div class='alert alert-error'>❌ Data kasir tidak ditemukan!</div>";
        $id_kasir = ''; // Reset ID agar form tidak ditampilkan
    }
}


// 2. Proses Update (saat form disubmit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_kasir'])) {
    $id_update = $koneksi->real_escape_string($_POST['id_kasir']);
    $nama_kasir = $koneksi->real_escape_string($_POST['nama_kasir']);
    $username = $koneksi->real_escape_string($_POST['username']);
    $password_baru = $_POST['password_baru']; 

    $sql_update_fields = "nama_kasir='$nama_kasir', username='$username'";
    
    // Tambahkan password jika diisi
    if (!empty($password_baru)) {
        // MENGGUNAKAN PASSWORD_HASH UNTUK KEAMANAN
        $password_hash_baru = password_hash($password_baru, PASSWORD_DEFAULT); 
        $sql_update_fields .= ", password_hash='$password_hash_baru'";
    }
    
    $sql_update = "UPDATE kasir SET $sql_update_fields WHERE id_kasir='$id_update'";

    if ($koneksi->query($sql_update) === TRUE) {
        $message = "<div class='alert alert-success'>✅ Data kasir **$nama_kasir** berhasil diubah!</div>";
    } else {
        // Cek jika error karena username duplikat (misalnya error code 1062)
        if ($koneksi->errno == 1062) {
             $message = "<div class='alert alert-error'>❌ Error: Username **$username** sudah digunakan oleh kasir lain.</div>";
        } else {
             $message = "<div class='alert alert-error'>❌ Error saat mengupdate data: " . $koneksi->error . "</div>";
        }
    }
    
    // Setelah update, refresh data $data untuk form
    $query_select_refresh = "SELECT id_kasir, nama_kasir, username FROM kasir WHERE id_kasir='$id_update'";
    $result_refresh = $koneksi->query($query_select_refresh);
    if ($result_refresh->num_rows > 0) { $data = $result_refresh->fetch_assoc(); }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Kasir</title>
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
        .btn-submit { 
            background-color: #9b59b6; /* Ungu untuk Kasir */
            color: white;
            box-shadow: 0 4px #8e44ad;
        }
        .btn-submit:hover { 
            background-color: #8e44ad; 
            transform: translateY(1px); 
            box-shadow: 0 3px #8e44ad; 
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
        .alert { padding: 15px; margin-top: 20px; margin-bottom: 20px; border-radius: 6px; font-weight: 600; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Gaya tambahan untuk section password */
        .password-section {
            padding-top: 15px;
            margin-top: 15px;
            border-top: 1px dashed #ccc;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>✏️ Edit Data Kasir</h1>

        <?php echo $message; ?>

        <?php if ($id_kasir != ''): ?>
        <form method="post" action="edit_kasir.php">
            <input type="hidden" name="id_kasir" value="<?php echo htmlspecialchars($id_kasir); ?>">

            <div class="form-group">
                <label for="nama_kasir">Nama Lengkap:</label>
                <input type="text" id="nama_kasir" name="nama_kasir" value="<?php echo htmlspecialchars($data['nama_kasir']); ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" required>
            </div>
            
            <div class="password-section">
                <h3 style="color: #e67e22; margin-top: 0; font-size: 1.2em;">Ubah Password</h3>
                <p style="font-size: 0.9em; color: #7f8c8d;">Kosongkan kolom di bawah jika Anda tidak ingin mengganti password saat ini.</p>
                
                <div class="form-group">
                    <label for="password_baru">Password Baru:</label>
                    <input type="password" id="password_baru" name="password_baru">
                </div>
            </div>

            <div class="btn-aksi-group">
                <a href="data_kasir.php" class="btn btn-back">⬅️ Kembali ke Data Kasir</a>
                <button type="submit" class="btn btn-submit">💾 Simpan Perubahan</button>
            </div>
        </form>
        <?php endif; ?>

    </div>

</body>
</html>
<?php 
// Menutup koneksi
if (isset($koneksi) && $koneksi) {
    $koneksi->close(); 
}
?>