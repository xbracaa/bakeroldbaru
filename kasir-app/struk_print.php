<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include("config/db.php");

// Pastikan koneksi database sudah terjalin
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil kode transaksi dari URL
$kode_transaksi = $_GET['kode'] ?? '';

if (empty($kode_transaksi)) {
    die("Kode transaksi tidak ditemukan. Kembali ke halaman utama.");
}

// --- FASE 1: Ambil data transaksi utama ---
$transaksi_q = $koneksi->prepare("
    SELECT 
        t.id_transaksi,
        t.kode_transaksi, 
        t.tanggal,  
        t.total AS total_belanja,
        t.bayar AS jumlah_bayar,
        t.kembalian,
        k.nama_kasir 
    FROM transaksi t
    LEFT JOIN kasir k ON t.id_kasir = k.id_kasir
    WHERE t.kode_transaksi = ?
");
if (!$transaksi_q) {
    die("Error prepare transaksi: " . $koneksi->error);
}
$transaksi_q->bind_param("s", $kode_transaksi);
$transaksi_q->execute();
$transaksi_result = $transaksi_q->get_result();
$transaksi_data = $transaksi_result->fetch_assoc();

if (!$transaksi_data) {
    die("Data transaksi tidak ditemukan untuk kode: " . htmlspecialchars($kode_transaksi));
}

// --- FASE 2: Ambil detail item dari tabel detail_transaksi dan produk ---
$detail_q = $koneksi->prepare("
    SELECT 
        dt.qty,
        dt.subtotal,
        p.nama_produk,
        p.harga AS harga_satuan
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    WHERE dt.id_transaksi = ?
");
if (!$detail_q) {
    die("Error prepare detail transaksi: " . $koneksi->error);
}
$detail_q->bind_param("i", $transaksi_data['id_transaksi']);
$detail_q->execute();
$detail_result = $detail_q->get_result();

// --- FASE 3: Siapkan variabel untuk tampilan struk ---
$kode_transaksi_tampil = htmlspecialchars($transaksi_data['kode_transaksi']);
$tanggal_tampil = date('d-m-Y', strtotime($transaksi_data['tanggal']));
$kasir_nama = htmlspecialchars($transaksi_data['nama_kasir'] ?? 'Kasir'); 
$total_belanja = $transaksi_data['total_belanja'];
$jumlah_bayar = $transaksi_data['jumlah_bayar'];
$kembalian = $transaksi_data['kembalian'];

// Nomor antrian
$nomor_antrian = $_SESSION['nomor_antrian_terakhir'] ?? rand(10, 99);

// Info toko
$nama_toko = "Baker Old";
$alamat_toko = "309 - Jayaraga Garut";
$no_meja = "-";
$mode_transaksi = "TAKEAWAY";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Pembelian - <?= $kode_transaksi_tampil ?></title>
    <style>
        body {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 11px;
            width: 280px;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
            color: #000;
        }
        .header-struk { text-align: center; margin-bottom: 10px; }
        .header-struk img { max-width: 100px; margin-bottom: 5px; }
        .header-struk h2 { margin: 0; font-size: 1.2em; font-family: 'Georgia', serif; color: #333; }
        .header-struk p { margin: 0; font-size: 0.9em; }

        .info-transaksi, .summary-transaksi, .payment-info {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .info-transaksi p, .summary-transaksi p, .payment-info p {
            margin: 2px 0;
            display: flex;
            justify-content: space-between;
        }

        .detail-item {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-bottom: 10px;
        }

        .detail-item .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            line-height: 1.3;
        }
        .item-name { font-weight: bold; flex-basis: 50%; text-align: left; }
        .item-qty-price { flex-basis: 30%; text-align: right; padding-right: 5px; }
        .item-subtotal { flex-basis: 20%; text-align: right; }

        .summary-transaksi .grand-total {
            font-size: 1.2em;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }

        .payment-info .kembalian {
            font-size: 1.3em;
            color: #000;
            margin-top: 5px;
        }

        .footer-struk {
            text-align: center;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 15px;
        }
        .thank-you { font-weight: bold; font-size: 1.1em; margin-bottom: 10px; }
        .queue-number-label { margin-bottom: 5px; font-size: 0.9em; }
        .queue-number { font-size: 2.2em; font-weight: bold; margin-bottom: 10px; line-height: 1; }
        .queue-message { font-size: 0.85em; }

        .no-print { display: block; text-align: center; margin-top: 20px; }
        .no-print a {
            padding: 10px 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 1em;
        }
        .no-print a:hover { background-color: #e0e0e0; }

        @media print {
            body { margin: 0; padding: 0; width: 80mm; font-size: 10px; }
            .header-struk img { max-width: 80px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header-struk">
        <img src="images/logo.png" alt="Logo Baker Old">
        <h2><?= htmlspecialchars($nama_toko) ?></h2>
        <p><?= htmlspecialchars($alamat_toko) ?></p>
    </div>

    <div class="info-transaksi">
        <p><span>No       : <span><?= $kode_transaksi_tampil ?></span></p>
        <p><span>Tanggal  : <span><?= $tanggal_tampil ?></span></p>
        <p><span>No Meja  : <span><?= htmlspecialchars($no_meja) ?></span></p>
        <p><span>Mode     : <span><?= htmlspecialchars($mode_transaksi) ?></span></p>
        <p><span>Kasir    : <span><?= $kasir_nama ?></span></p>
    </div>

    <div class="detail-item">
        <?php 
        $total_items_count = 0;
        if ($detail_result->num_rows > 0) :
            while ($item = $detail_result->fetch_assoc()) :
                $total_items_count += $item['qty'];
        ?>
        <div class="item-row">
            <span class="item-name"><?= htmlspecialchars($item['nama_produk']) ?></span>
            <span class="item-qty-price"><?= $item['qty'] ?>x @<?= number_format($item['harga_satuan'], 0, ',', '.') ?></span>
            <span class="item-subtotal"><?= number_format($item['subtotal'], 0, ',', '.') ?></span>
        </div>
        <?php endwhile; else : ?>
        <p style="text-align: center;">Tidak ada detail item untuk transaksi ini.</p>
        <?php endif; ?>
    </div>

    <div class="summary-transaksi">
        <p><span><?= $total_items_count ?> item</span></p>
        <p><span>Subtotal</span> <span>Rp <?= number_format($total_belanja, 0, ',', '.') ?></span></p>
        <p class="grand-total"><span>Grand Total</span> <span>Rp <?= number_format($total_belanja, 0, ',', '.') ?></span></p>
    </div>

    <div class="payment-info">
        <p><span>CASH</span> <span>Rp <?= number_format($jumlah_bayar, 0, ',', '.') ?></span></p>
        <p class="kembalian"><span>Kembalian</span> <span>Rp <?= number_format($kembalian, 0, ',', '.') ?></span></p>
    </div>

    <div class="footer-struk">
        <p class="thank-you">--- Thank You ---</p>
        <p class="queue-number-label">Nomor antrian</p>
        <p class="queue-number"><?= $nomor_antrian ?></p>
        <p class="queue-message">Tunggu nomor kamu dipanggil</p>
    </div>

    <div class="no-print center">
        <a href="transaksi_baru.php">Kembali ke Transaksi Baru</a>
    </div>
</body>
</html>
