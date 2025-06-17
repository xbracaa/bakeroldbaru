<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID transaksi tidak ditemukan.";
    exit;
}

$id = (int)$_GET['id'];

// Ambil info transaksi
$transaksi = $koneksi->query("
    SELECT t.*, k.nama_kasir 
    FROM transaksi t 
    JOIN kasir k ON t.id_kasir = k.id_kasir 
    WHERE t.id_transaksi = $id
")->fetch_assoc();

if (!$transaksi) {
    echo "Transaksi tidak ditemukan.";
    exit;
}

// Ambil detail produk dalam transaksi
$items = $koneksi->query("
    SELECT dt.*, p.nama_produk 
    FROM detail_transaksi dt 
    JOIN produk p ON dt.id_produk = p.id_produk 
    WHERE dt.id_transaksi = $id
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi #<?= $id ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            padding: 30px;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        h2 {
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🧾 Detail Transaksi #<?= $id ?></h2>
    <p><strong>Kasir:</strong> <?= htmlspecialchars($transaksi['nama_kasir']) ?></p>
    <p><strong>Tanggal:</strong> <?= date('d-m-Y H:i:s', strtotime($transaksi['tanggal'])) ?></p>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="text-center">
                <tr>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                    <th>Setelah Diskon</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td class="text-center"><?= $row['qty'] ?></td>
                    <td class="text-right">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                    <td class="text-right">
                        <?= $row['harga_setelah_diskon'] !== null ? "Rp " . number_format($row['harga_setelah_diskon'], 0, ',', '.') : "-" ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <th colspan="4" class="text-right">Total</th>
                    <th class="text-right">Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></th>
                </tr>
            </tbody>
        </table>
    </div>

    <a href="transaksi.php" class="btn btn-secondary mt-3">&larr; Kembali ke Riwayat</a>
</div>

</body>
</html>
