<?php
session_start();
include("config/db.php");

// Pastikan koneksi database sudah terjalin
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Cek jika transaksi belum disimpan di session
if (!isset($_SESSION['transaksi_selesai'])) {
    // Jika tidak ada data transaksi di session, redirect ke halaman utama kasir
    // Ini mencegah akses langsung atau refresh setelah data dihapus
    header("Location: transaksi_baru.php");
    exit;
}

// Ambil data transaksi dari session
// Biarkan data tetap ada di session sampai halaman ini direfresh atau user pergi,
// atau pindahkan unset ke sini setelah semua data diambil jika ingin hanya tampil sekali.
$data = $_SESSION['transaksi_selesai'];
// Jika Anda ingin ini hanya tampil sekali saja setelah pembayaran,
// Anda bisa unset di sini setelah semua variabel diambil:
// unset($_SESSION['transaksi_selesai']);

$kode_transaksi    = $data['kode_transaksi'];
$total             = $data['total'];
$bayar             = $data['bayar'];
$kembalian         = $data['kembalian'];
$keranjang         = $data['keranjang'] ?? []; // Keranjang dari session
$id_kasir          = $data['id_kasir'] ?? 0;
$tanggal_transaksi = $data['tanggal_transaksi'] ?? date('Y-m-d'); 
$waktu_transaksi   = $data['waktu_transaksi'] ?? date('H:i:s');

// Ambil nama kasir dari database
$kasir_nama = "Kasir Tak Dikenal"; // Default value
if ($id_kasir > 0) {
    $kasir_q = $koneksi->query("SELECT nama_kasir FROM kasir WHERE id_kasir = $id_kasir");
    if ($kasir_q && $row = $kasir_q->fetch_assoc()) {
        $kasir_nama = $row['nama_kasir'];
    }
}

// Format tanggal dan waktu untuk tampilan
date_default_timezone_set('Asia/Jakarta');
$tanggal_tampil = date('d F Y', strtotime($tanggal_transaksi));
$waktu_tampil = date('H:i:s', strtotime($waktu_transaksi));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Selesai - Baker Old</title>
    <style>
        /* Warna Dasar (sesuai tema Baker Old) */
        :root {
            --color-primary-brown: #5A3F2B; /* Coklat Tua (Teks Utama, Header) */
            --color-secondary-brown: #8B6F5A; /* Coklat Sedang (Aksen, Border) */
            --color-light-brown: #D4B29A; /* Coklat Muda (Latar belakang elemen) */
            --color-cream: #FFF8E1; /* Kuning Cream (Latar belakang utama) */
            --color-yellow: #FFD54F; /* Kuning (Aksen, Tombol Utama) */
            --color-dark-yellow: #FFA000; /* Kuning Gelap (Hover Tombol) */
            --color-text-dark: #333;
            --color-text-light: #fff;
            --color-success: #4CAF50; /* Hijau untuk sukses */
            --color-info: #2196F3; /* Biru untuk info */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .receipt-container {
            background-color: var(--color-text-light);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            max-width: 500px;
            width: 100%;
            border: 2px solid var(--color-light-brown);
            text-align: center; /* Untuk judul utama */
        }

        h1 {
            color: var(--color-primary-brown);
            font-family: 'Georgia', serif;
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        h2 {
            color: var(--color-primary-brown);
            font-family: 'Georgia', serif;
            font-size: 1.8em;
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--color-light-brown);
            padding-bottom: 10px;
        }

        p {
            font-size: 1.1em;
            margin-bottom: 8px;
            color: var(--color-text-dark);
        }

        p strong {
            color: var(--color-primary-brown);
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.95em;
            color: var(--color-secondary-brown);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--color-light-brown);
        }
        .info-header div {
            flex: 1;
            text-align: left;
            padding: 0 5px;
        }
        .info-header div:last-child {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.95em;
        }

        table th, table td {
            border: 1px solid var(--color-light-brown);
            padding: 10px;
            text-align: left;
        }

        table thead th {
            background-color: var(--color-light-brown);
            color: var(--color-primary-brown);
            font-weight: bold;
        }

        table tbody tr:nth-child(even) {
            background-color: var(--color-cream);
        }

        table tfoot td {
            font-weight: bold;
            background-color: var(--color-secondary-brown);
            color: var(--color-text-light);
            font-size: 1.1em;
        }
        
        table tfoot tr:last-child td {
            background-color: var(--color-primary-brown);
            font-size: 1.3em;
        }

        .payment-summary {
            background-color: var(--color-cream);
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid var(--color-light-brown);
            margin-top: 25px;
            text-align: left;
        }
        .payment-summary p {
            margin: 5px 0;
            font-size: 1.1em;
        }
        .payment-summary p strong {
            color: var(--color-primary-brown);
        }
        .payment-summary p:last-child {
            font-size: 1.3em;
            color: var(--color-success);
            font-weight: bold;
            margin-top: 15px;
            border-top: 1px dashed var(--color-light-brown);
            padding-top: 10px;
        }

        .button-group {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .button-group button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.1s;
            width: 100%;
        }

        .button-group button:hover {
            transform: translateY(-2px);
        }

        .button-print {
            background-color: var(--color-secondary-brown);
            color: var(--color-text-light);
        }
        .button-print:hover {
            background-color: var(--color-primary-brown);
        }

        .button-new-transaction {
            background-color: var(--color-yellow);
            color: var(--color-primary-brown);
        }
        .button-new-transaction:hover {
            background-color: var(--color-dark-yellow);
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <h1>Terima Kasih!</h1>
        <p style="font-size: 1.3em; color: var(--color-success); margin-bottom: 20px;">Transaksi Berhasil!</p>

        <div class="info-header">
            <div>
                <strong>Kode Transaksi:</strong> <br> <?= htmlspecialchars($kode_transaksi) ?>
            </div>
            <div>
                <strong>Kasir:</strong> <br> <?= htmlspecialchars($kasir_nama) ?>
            </div>
            <div>
                <strong>Tanggal:</strong> <br> <?= $tanggal_tampil ?>
            </div>
            <div>
                <strong>Waktu:</strong> <br> <?= $waktu_tampil ?>
            </div>
        </div>
        
        <h2>Detail Belanja</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_item_count = 0;
                foreach ($keranjang as $item) :
                    $nama     = htmlspecialchars($item['nama_produk'] ?? $item['nama_promo'] ?? '-');
                    $qty      = $item['qty'] ?? 1;
                    $harga    = $item['harga'] ?? 0;
                    $subtotal = $harga * $qty;
                    $total_item_count += $qty;
                ?>
                <tr>
                    <td><?= $nama ?></td>
                    <td><?= $qty ?></td>
                    <td>Rp <?= number_format($harga, 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><b>Total Item</b></td>
                    <td><b><?= $total_item_count ?></b></td>
                </tr>
                <tr>
                    <td colspan="3"><b>Total Belanja</b></td>
                    <td><b>Rp <?= number_format($total, 0, ',', '.') ?></b></td>
                </tr>
            </tfoot>
        </table>

        <div class="payment-summary">
            <p><strong>Jumlah Dibayar:</strong> Rp <?= number_format($bayar, 0, ',', '.') ?></p>
            <p><strong>Kembalian:</strong> Rp <?= number_format($kembalian, 0, ',', '.') ?></p>
        </div>

        <div class="button-group">
            <a href="struk_print.php?kode=<?= urlencode($kode_transaksi) ?>" target="_blank">
                <button class="button-print">Cetak Struk</button>
            </a>
            <a href="transaksi_baru.php">
                <button class="button-new-transaction">Transaksi Baru</button>
            </a>
        </div>
    </div>
</body>
</html>