<?php
session_start();
include("../config/db.php");

// Cek login
if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data produk
$produk = $koneksi->query("SELECT * FROM produk");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Produk</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        h2 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        a.btn {
            background: #28a745; color: white; padding: 6px 12px;
            text-decoration: none; border-radius: 4px;
        }
        a.btn-danger {
            background: #dc3545;
        }
        a.btn-warning {
            background: #ffc107;
            color: black;
        }
    </style>
</head>
<body>
    <a href="../home.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

<h2>🍞 Data Produk</h2>
<p><a href="tambah_produk.php" class="btn">+ Tambah Produk</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($p = $produk->fetch_assoc()): ?>
        <tr>
            <td><?= $p['id_produk'] ?></td>
            <td><?= htmlspecialchars($p['nama_produk']) ?></td>
            <td>Rp <?= number_format($p['harga']) ?></td>
            <td>
                <a href="edit_produk.php?id=<?= $p['id_produk'] ?>" class="btn btn-warning">Edit</a>
                <a href="hapus_produk.php?id=<?= $p['id_produk'] ?>" class="btn btn-danger" onclick="return confirm('Hapus produk ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
