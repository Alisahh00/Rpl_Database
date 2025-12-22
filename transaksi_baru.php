<?php 
session_start();
include 'koneksi.php';

if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$message = ''; // Variabel pesan diganti agar seragam dengan alert CSS

// -----------------
// LOGIKA TAMBAH KE KERANJANG
// -----------------
if (isset($_POST['tambah_keranjang'])) {
    $id_barang_input = $koneksi->real_escape_string($_POST['id_barang']);
    $jumlah_input = (int)$_POST['jumlah'];

    $query_barang = "SELECT id_barang, kode_barang, nama_barang, harga_jual, stok, satuan FROM barang WHERE id_barang='$id_barang_input'";
    $result_barang = $koneksi->query($query_barang);

    if ($result_barang && $result_barang->num_rows > 0) {
        $barang = $result_barang->fetch_assoc();
        
        // Hitung total jumlah jika item sudah ada
        $jumlah_di_keranjang = isset($_SESSION['keranjang'][$barang['id_barang']]) ? $_SESSION['keranjang'][$barang['id_barang']]['jumlah_beli'] : 0;
        $total_setelah_tambah = $jumlah_di_keranjang + $jumlah_input;
        
        if ($jumlah_input <= 0) {
            $message = "<div class='alert alert-error'>❌ Jumlah beli harus lebih dari 0!</div>";
        } elseif ($total_setelah_tambah > $barang['stok']) {
            $message = "<div class='alert alert-error'>❌ Stok tidak cukup! Total permintaan melebihi stok tersedia: " . $barang['stok'] . " " . htmlspecialchars($barang['satuan']) . "</div>";
        } else {
            $item_key = $barang['id_barang'];
            $subtotal_input = $barang['harga_jual'] * $jumlah_input;
            
            if (isset($_SESSION['keranjang'][$item_key])) {
                $_SESSION['keranjang'][$item_key]['jumlah_beli'] += $jumlah_input;
                $_SESSION['keranjang'][$item_key]['subtotal'] += $subtotal_input;
                 $message = "<div class='alert alert-success'>✅ Jumlah **" . htmlspecialchars($barang['nama_barang']) . "** diperbarui.</div>";
            } else {
                $_SESSION['keranjang'][$item_key] = [
                    'id_barang' => $barang['id_barang'],
                    'kode_barang' => $barang['kode_barang'],
                    'nama_barang' => $barang['nama_barang'],
                    'harga_saat_transaksi' => $barang['harga_jual'],
                    'jumlah_beli' => $jumlah_input,
                    'subtotal' => $subtotal_input,
                    'satuan' => $barang['satuan'] // Tambahkan satuan
                ];
                 $message = "<div class='alert alert-success'>✅ **" . htmlspecialchars($barang['nama_barang']) . "** berhasil ditambahkan!</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-error'>❌ Barang tidak ditemukan atau ID tidak valid!</div>";
    }
}

// -----------------
// LOGIKA HAPUS DARI KERANJANG
// -----------------
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_item') {
    $id_hapus = $koneksi->real_escape_string($_GET['id']);
    if (isset($_SESSION['keranjang'][$id_hapus])) {
        $nama_hapus = htmlspecialchars($_SESSION['keranjang'][$id_hapus]['nama_barang']);
        unset($_SESSION['keranjang'][$id_hapus]);
        $message = "<div class='alert alert-info'>ℹ️ Item **$nama_hapus** berhasil dihapus dari keranjang.</div>";
    }
    // Cegah resubmission
    header("location:transaksi_baru.php");
    exit();
}

// -----------------
// HITUNG TOTAL BELANJA
// -----------------
$total_belanja = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total_belanja += $item['subtotal'];
}

$nama_kasir_login = $_SESSION['nama_kasir'];

// Ambil data barang untuk dropdown
$query_list = "SELECT id_barang, nama_barang, harga_jual, stok, satuan FROM barang WHERE stok > 0 ORDER BY nama_barang ASC";
$result_list = $koneksi->query($query_list);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi Penjualan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
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
        
        /* --- HEADER KASIR & NAVIGATION --- */
        .header-kasir {
            text-align: right;
            margin-bottom: 20px;
            font-weight: 600;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .header-kasir a {
            color: #3498db;
            text-decoration: none;
            margin-left: 15px;
            font-weight: 700;
        }
        
        /* --- LAYOUT GRID 2 KOLOM --- */
        .grid-container {
            display: grid;
            grid-template-columns: 1fr 2fr; 
            gap: 30px;
            margin-top: 20px;
        }
        .panel {
            padding: 20px;
            background-color: #fcfcfc;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .panel h2 {
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
            margin-top: 0;
            font-size: 1.5em;
        }
        
        /* --- Form Input Item --- */
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #34495e; margin-top: 15px; }
        select, input[type="number"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }
        .btn-success { 
            background-color: #2ecc71; 
            color: white; 
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            transition: background-color 0.2s;
            margin-top: 15px;
        }
        .btn-success:hover { background-color: #27ae60; }
        .btn-danger { 
            background-color: #e74c3c; 
            color: white; 
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8em;
            text-decoration: none; /* Untuk tag <a> */
            display: inline-block;
        }
        
        /* --- Keranjang Belanja Styling --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        th {
            background-color: #34495e;
            color: white;
            font-size: 0.9em;
        }
        tr:nth-child(even) { background-color: #f4f4f4; }
        
        .total-box { 
            background-color: #f7e1b5; /* Warna Kuning Lembut */
            border: 2px solid #f39c12; /* Border Oranye */
            padding: 15px; 
            text-align: right; 
            font-size: 1.5em; 
            font-weight: bold; 
            margin-top: 20px;
            border-radius: 6px;
        }
        
        /* --- Alert Messages (Seragam) --- */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 600;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background-color: #d9edf7; color: #31708f; border: 1px solid #bce8f1; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header-kasir">
            Kasir: **<?php echo htmlspecialchars($nama_kasir_login); ?>** | <a href="index.php">⬅️ Kembali ke Dashboard</a>
        </div>

        <h1>💰 Input Transaksi Baru</h1>
        
        <?php echo $message; ?>

        <div class="grid-container">
            
            <div class="panel">
                <h2>Tambah Item</h2>
                <form method="post" action="transaksi_baru.php">
                    <input type="hidden" name="tambah_keranjang" value="1">

                    <label for="id_barang">Pilih Barang:</label>
                    <select id="id_barang" name="id_barang" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php
                        if ($result_list && $result_list->num_rows > 0) {
                            while ($brg = $result_list->fetch_assoc()) {
                                $stok_info = $brg['stok'] > 0 ? "Stok: " . $brg['stok'] . " " . htmlspecialchars($brg['satuan']) : "STOK HABIS";
                                echo "<option value=\"" . $brg['id_barang'] . "\">" . 
                                     htmlspecialchars($brg['nama_barang']) . 
                                     " (Rp " . number_format($brg['harga_jual'], 0, ',', '.') . 
                                     " | " . $stok_info . ")" . 
                                     "</option>";
                            }
                        }
                        ?>
                    </select>
                    
                    <label for="jumlah">Jumlah Beli:</label>
                    <input type="number" id="jumlah" name="jumlah" value="1" min="1" required>
                    
                    <button type="submit" class="btn-success">Tambah ke Keranjang</button>
                </form>
            </div>

            <div class="panel">
                <h2>Keranjang Belanja</h2>

                <?php if (empty($_SESSION['keranjang'])): ?>
                    <p style="text-align: center; color: #7f8c8d; padding: 50px 0;">Keranjang masih kosong.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th style="text-align: right;">Harga</th>
                                <th style="text-align: center;">Jml</th>
                                <th style="text-align: right;">Subtotal</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['keranjang'] as $id => $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                    <td style="text-align: right;">Rp <?php echo number_format($item['harga_saat_transaksi'], 0, ',', '.'); ?></td>
                                    <td style="text-align: center;"><?php echo $item['jumlah_beli']; ?></td>
                                    <td style="text-align: right;">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                    <td style="text-align: center;">
                                        <a class="btn-danger" href="transaksi_baru.php?aksi=hapus_item&id=<?php echo $id; ?>">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="total-box">
                        TOTAL BELANJA: Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?>
                    </div>

                    <form method="post" action="proses_transaksi.php" style="margin-top: 20px;">
                        <h3>Proses Pembayaran</h3>
                        <input type="hidden" name="total_bayar_hidden" value="<?php echo $total_belanja; ?>">
                        
                        <label for="bayar">Uang yang Dibayarkan (Minimal Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?>):</label>
                        <input type="number" id="bayar" name="bayar" min="<?php echo $total_belanja; ?>" required style="width: 100%;">
                        
                        <button type="submit" class="btn-success" style="width: 100%;" name="proses_bayar">Selesaikan Transaksi</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'sukses') {
            const faktur = urlParams.get('faktur');
            const kembalian = urlParams.get('kembalian');
            
            Swal.fire({
                icon: 'success',
                title: 'Transaksi Berhasil!',
                html: `
                    <p><strong>Faktur:</strong> ${faktur}</p>
                    <p><strong>Kembalian:</strong> Rp ${kembalian.toLocaleString('id-ID')}</p>
                    <p>Keranjang belanja telah dikosongkan. Siap untuk transaksi berikutnya!</p>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            });
            
            window.history.replaceState({}, document.title, "transaksi_baru.php");

        } else if (status === 'gagal') {
            const detail = urlParams.get('detail');
            const decodedDetail = decodeURIComponent(detail);

            Swal.fire({
                icon: 'error',
                title: 'Transaksi Gagal!',
                html: `
                    <p>Terjadi kesalahan saat menyimpan data.</p>
                    <p style="font-size: 0.9em; color: #dc3545;"><strong>Detail Error:</strong> ${decodedDetail}</p>
                    <p>Silakan periksa kembali keranjang atau struktur database Anda.</p>
                `,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#dc3545'
            });
            
            window.history.replaceState({}, document.title, "transaksi_baru.php");
        }
    });
    </script>

</body>
</html>
<?php $koneksi->close(); ?>