<?php
session_start();
include("config/db.php");

// Pastikan koneksi database sudah terjalin
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil kode transaksi dari URL
$kode_transaksi = $_GET['kode'] ?? '';

if (empty($kode_transaksi)) {
    // Jika kode transaksi tidak ada di URL, redirect atau tampilkan pesan error
    // Ini mencegah akses langsung ke struk_print.php tanpa parameter
    die("Kode transaksi tidak ditemukan. Kembali ke halaman utama.");
    // header("Location: transaksi_baru.php"); // Atau redirect ke halaman lain
    // exit;
}

// --- FASE 1: Ambil data transaksi utama dari tabel 'transaksi' ---
$transaksi_q = $koneksi->prepare("
    SELECT 
        t.id_transaksi,
        t.kode_transaksi, 
        t.tanggal_transaksi, 
        t.waktu_transaksi, 
        t.total_belanja, 
        t.jumlah_bayar, 
        t.kembalian,
        k.nama_kasir 
    FROM transaksi t
    LEFT JOIN kasir k ON t.id_kasir = k.id_kasir
    WHERE t.kode_transaksi = ?
");
$transaksi_q->bind_param("s", $kode_transaksi);
$transaksi_q->execute();
$transaksi_result = $transaksi_q->get_result();
$transaksi_data = $transaksi_result->fetch_assoc();

if (!$transaksi_data) {
    die("Data transaksi tidak ditemukan untuk kode: " . htmlspecialchars($kode_transaksi));
}

// --- FASE 2: Ambil detail item dari tabel 'detail_transaksi' ---
$detail_q = $koneksi->prepare("
    SELECT 
        nama_item, 
        qty, 
        harga_satuan, 
        subtotal 
    FROM detail_transaksi
    WHERE id_transaksi = ?
");
$detail_q->bind_param("i", $transaksi_data['id_transaksi']); // Gunakan id_transaksi dari hasil FASE 1
$detail_q->execute();
$detail_result = $detail_q->get_result();

// --- FASE 3: Siapkan variabel untuk tampilan struk ---
$kode_transaksi_tampil = htmlspecialchars($transaksi_data['kode_transaksi']);
$tanggal_tampil = date('d-m-Y', strtotime($transaksi_data['tanggal_transaksi']));
$waktu_tampil = date('H:i:s', strtotime($transaksi_data['waktu_transaksi']));
$kasir_nama = htmlspecialchars($transaksi_data['nama_kasir'] ?? 'Kasir'); 
$total_belanja = $transaksi_data['total_belanja'];
$jumlah_bayar = $transaksi_data['jumlah_bayar'];
$kembalian = $transaksi_data['kembalian'];

// Asumsi nomor antrian (ini tidak ada di data transaksi dari DB, bisa disesuaikan jika ada sumbernya)
// Jika Anda ingin nomor antrian persisten, tambahkan kolom 'nomor_antrian' ke tabel 'transaksi'
$nomor_antrian = $_SESSION['nomor_antrian_terakhir'] ?? rand(10, 99); 
// Anda bisa menyimpan nomor antrian di session atau database saat transaksi dibuat.
// Untuk demo ini, saya ambil dari session jika ada, kalau tidak random.

// Informasi Toko (bisa diambil dari config/database jika dinamis)
$nama_toko = "Baker Old";
$alamat_toko = "309 - Jayaraga Garut";
$no_meja = "-"; // Sesuaikan jika ada data di DB
$mode_transaksi = "TAKEAWAY"; // Sesuaikan jika ada data di DB

?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Pembelian - <?= $kode_transaksi_tampil ?></title>
    <style>
        body {
            font-family: 'Consolas', 'Courier New', monospace; /* Font monospasi untuk kesan struk */
            font-size: 11px; /* Ukuran font kecil untuk struk */
            width: 280px; /* Lebar struk standar (sekitar 80mm) */
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
            color: #000;
        }

        .header-struk {
            text-align: center;
            margin-bottom: 10px;
        }
        .header-struk img {
            max-width: 100px; /* Ukuran logo */
            margin-bottom: 5px;
        }
        .header-struk h2 {
            margin: 0;
            font-size: 1.2em;
            font-family: 'Georgia', serif; /* Untuk nama toko yang lebih estetik */
            color: #333;
        }
        .header-struk p {
            margin: 0;
            font-size: 0.9em;
        }

        .info-transaksi {
            margin-top: 15px;
            margin-bottom: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .info-transaksi p {
            margin: 2px 0;
            display: flex;
            justify-content: space-between;
        }
        .info-transaksi p span:first-child {
            width: 80px; /* Lebar label */
            text-align: left;
        }
        .info-transaksi p span:last-child {
            flex-grow: 1;
            text-align: left; /* Sesuaikan untuk value ke kanan */
            padding-left: 5px; /* Jarak antara label dan value */
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
        .detail-item .item-name {
            font-weight: bold;
            flex-basis: 50%; /* Mengambil setengah lebar */
            text-align: left;
        }
        .detail-item .item-qty-price {
            flex-basis: 30%; /* Mengambil 30% lebar */
            text-align: right;
            padding-right: 5px;
        }
        .detail-item .item-subtotal {
            flex-basis: 20%; /* Mengambil 20% lebar */
            text-align: right;
        }


        .summary-transaksi {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .summary-transaksi p {
            margin: 2px 0;
            display: flex;
            justify-content: space-between;
        }
        .summary-transaksi p span:first-child {
            text-align: left;
            flex-grow: 1;
        }
        .summary-transaksi p span:last-child {
            text-align: right;
            font-weight: bold;
        }
        .summary-transaksi .grand-total {
            font-size: 1.2em;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }

        .payment-info {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .payment-info p {
            margin: 2px 0;
            display: flex;
            justify-content: space-between;
        }
        .payment-info p span:first-child {
            text-align: left;
            flex-grow: 1;
        }
        .payment-info p span:last-child {
            text-align: right;
            font-weight: bold;
        }
        .payment-info .kembalian {
            font-size: 1.3em;
            color: #000; /* Tetap hitam untuk print */
            margin-top: 5px;
        }

        .footer-struk {
            text-align: center;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 15px;
        }
        .footer-struk .thank-you {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 10px;
        }
        .footer-struk .queue-number-label {
            margin-bottom: 5px;
            font-size: 0.9em;
        }
        .footer-struk .queue-number {
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1;
        }
        .footer-struk .queue-message {
            font-size: 0.85em;
        }

        /* Tombol non-print */
        .no-print {
            display: block; /* Tampilkan di layar */
            text-align: center;
            margin-top: 20px;
        }
        .no-print a {
            padding: 10px 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 1em;
        }
        .no-print a:hover {
            background-color: #e0e0e0;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 80mm; /* Sesuaikan dengan lebar kertas thermal Anda */
                font-size: 10px; /* Mungkin perlu sedikit lebih kecil untuk cetak */
            }
            .header-struk img {
                max-width: 80px; /* Sesuaikan ukuran logo untuk cetak */
            }
            .no-print {
                display: none; /* Sembunyikan tombol saat dicetak */
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header-struk">
        <img src="images/logo.png" class="logo" alt="Logo Baker Old">
        <h2><?= htmlspecialchars($nama_toko) ?></h2>
        <p><?= htmlspecialchars($alamat_toko) ?></p>
    </div>

    <div class="info-transaksi">
        <p><span>No</span> : <span><?= $kode_transaksi_tampil ?></span></p>
        <p><span>Tanggal</span> : <span><?= $tanggal_tampil ?></span></p>
        <p><span>Jam Masuk</span> : <span><?= $waktu_tampil ?></span></p>
        <p><span>No Meja</span> : <span><?= htmlspecialchars($no_meja) ?></span></p>
        <p><span>Mode</span> : <span><?= htmlspecialchars($mode_transaksi) ?></span></p>
        <p><span>Kasir</span> : <span><?= $kasir_nama ?></span></p>
    </div>

    <div class="detail-item">
        <?php 
        $total_items_count = 0;
        if ($detail_result->num_rows > 0) : 
            while ($item = $detail_result->fetch_assoc()) :
                $total_items_count += $item['qty'];
        ?>
        <div class="item-row">
            <span class="item-name"><?= htmlspecialchars($item['nama_item']) ?></span>
            <span class="item-qty-price"><?= $item['qty'] ?>x @<?= number_format($item['harga_satuan'], 0, ',', '.') ?></span>
            <span class="item-subtotal"><?= number_format($item['subtotal'], 0, ',', '.') ?></span>
        </div>
        <?php 
            endwhile;
        else : 
        ?>
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