<?php 
session_start();
if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}
include 'koneksi.php';

// Cek apakah ID Transaksi dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location:riwayat_transaksi.php?pesan=id_invalid");
    exit();
}

$id_transaksi = $koneksi->real_escape_string($_GET['id']);
$data_transaksi = null;
$detail_item = [];

// 1. Ambil Data Header Transaksi
$query_header = "SELECT t.id_transaksi, t.no_faktur, t.tanggal_transaksi, t.waktu_transaksi, 
                         t.total_bayar, t.bayar, t.kembalian, k.nama_kasir
                  FROM transaksi t
                  JOIN kasir k ON t.id_kasir = k.id_kasir
                  WHERE t.id_transaksi = '$id_transaksi'";

$result_header = $koneksi->query($query_header);

if ($result_header && $result_header->num_rows > 0) {
    $data_transaksi = $result_header->fetch_assoc();
    
    // 2. Ambil Detail Item Transaksi
    $query_detail = "SELECT d.jumlah_beli, d.harga_saat_transaksi, d.subtotal, b.nama_barang, b.satuan
                      FROM detail_transaksi d
                      JOIN barang b ON d.id_barang = b.id_barang
                      WHERE d.id_transaksi = '$id_transaksi'";
                    
    $result_detail = $koneksi->query($query_detail);
    
    if ($result_detail) {
        while ($row = $result_detail->fetch_assoc()) {
            $detail_item[] = $row;
        }
    }
} else {
    // Jika ID transaksi tidak ditemukan
    header("location:riwayat_transaksi.php?pesan=transaksi_not_found");
    exit();
}

// Format waktu
$tanggal_waktu = date("d-m-Y H:i:s", strtotime($data_transaksi['tanggal_transaksi'] . " " . $data_transaksi['waktu_transaksi']));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi <?php echo htmlspecialchars($data_transaksi['no_faktur']); ?></title>
    <style>
        /* --- Gaya Dasar & Kontainer (Seragam) --- */
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
            font-size: 2.2em;
            font-weight: 700;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 15px;
        }
        h2 { 
            color: #34495e; 
            font-size: 1.3em; 
            margin-top: 25px; 
            padding-bottom: 5px;
        }
        
        /* --- Informasi Header Transaksi --- */
        .info-header { 
            margin-bottom: 25px; 
            padding: 15px; 
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #fcfcfc;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-header p { margin: 0; line-height: 1.6; }
        .info-header strong { display: inline-block; width: 100px; }

        /* --- Tabel Detail Item --- */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* --- Baris Total & Pembayaran --- */
        .total-row td { 
            font-weight: 700; 
            background-color: #f7f7f7; 
            border-top: 2px solid #34495e; 
        }
        .kembalian-row td {
            font-size: 1.1em;
            font-weight: 700;
            background-color: #e6ffe6; /* Hijau muda */
            color: #27ae60;
        }

        /* --- Tombol Aksi --- */
        .action-buttons { 
            text-align: center; 
            margin-top: 40px; 
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .action-buttons a, .action-buttons button { 
            padding: 12px 25px; 
            margin: 0 10px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            text-decoration: none;
            font-weight: 700;
            transition: background-color 0.2s;
        }
        .btn-back { background-color: #95a5a6; color: white; }
        .btn-back:hover { background-color: #7f8c8d; }
        .btn-print { background-color: #2980b9; color: white; }
        .btn-print:hover { background-color: #3498db; }

        /* --- Style untuk CETAK STRUK --- */
        @media print {
            body { background-color: white; margin: 0; padding: 0;}
            .container { 
                box-shadow: none; 
                border: none;
                max-width: 300px; /* Lebar khas struk */
                padding: 0;
            }
            .action-buttons { display: none; }
            .info-header { 
                border: none;
                display: block;
                text-align: center;
                padding: 0;
                font-size: 14px;
            }
            .info-header p { margin: 2px 0; }
            h1 { font-size: 1.2em; border-bottom: none; margin-bottom: 5px; padding-bottom: 0;}
            h2 { display: none; }
            
            table { margin-top: 10px; }
            table, th, td { border: none !important; padding: 2px 0; font-size: 13px; }
            th { background-color: white; color: black; }
            
            /* Kolom rata kiri/kanan untuk struk */
            .item-col { text-align: left; }
            .qty-col { text-align: center; width: 10%;}
            .price-col, .subtotal-col { text-align: right; }
            
            .separator { border-top: 1px dashed #000; height: 1px; margin: 5px 0; }
            .total-row td, .kembalian-row td { 
                background-color: white !important; 
                border-top: 1px dashed #000 !important;
                padding-top: 5px;
            }
            .footer-info { margin-top: 15px !important; }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="action-buttons">
            <a href="riwayat_transaksi.php" class="btn-back">⬅️ Kembali ke Riwayat</a>
            <button onclick="window.print()" class="btn-print">🖨️ Cetak Struk</button>
        </div>

        <h1>STRUK PENJUALAN</h1>
        
        <div class="info-header print-area">
            <p><strong>Faktur:</strong> <?php echo htmlspecialchars($data_transaksi['no_faktur']); ?></p>
            <p><strong>Tanggal:</strong> <?php echo $tanggal_waktu; ?></p>
            <p><strong>Kasir:</strong> <?php echo htmlspecialchars($data_transaksi['nama_kasir']); ?></p>
            <p><strong>ID Transaksi:</strong> <?php echo htmlspecialchars($data_transaksi['id_transaksi']); ?></p>
        </div>

        <h2>Detail Item</h2>
        <table>
            <thead>
                <tr>
                    <th class="item-col">Barang</th>
                    <th class="qty-col text-center">jumlah</th>
                    <th class="price-col text-right">Harga</th>
                    <th class="subtotal-col text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detail_item as $item): ?>
                    <tr>
                        <td class="item-col"><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                        <td class="qty-col text-center"><?php echo $item['jumlah_beli']; ?> <?php echo htmlspecialchars($item['satuan']); ?></td>
                        <td class="price-col text-right"><?php echo number_format($item['harga_saat_transaksi'], 0, ',', '.'); ?></td>
                        <td class="subtotal-col text-right"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <tr class="separator">
                    <td colspan="4" class="separator"></td>
                </tr>
                
                <tr class="total-row">
                    <td colspan="3" class="text-right" style="padding-top: 10px;">TOTAL BELANJA</td>
                    <td class="text-right" style="padding-top: 10px;">**Rp <?php echo number_format($data_transaksi['total_bayar'], 0, ',', '.'); ?>**</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">UANG DIBAYAR</td>
                    <td class="text-right">Rp <?php echo number_format($data_transaksi['bayar'], 0, ',', '.'); ?></td>
                </tr>
                <tr class="kembalian-row">
                    <td colspan="3" class="text-right">KEMBALIAN</td>
                    <td class="text-right">Rp <?php echo number_format($data_transaksi['kembalian'], 0, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer-info" style="margin-top: 20px; text-align: center;">
            <p>*** TERIMA KASIH TELAH BERBELANJA ***</p>
        </div>
    </div>

</body>
</html>
<?php $koneksi->close(); ?>