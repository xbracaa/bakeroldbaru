<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            padding: 30px;
            background-color: #f8f9fa;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        h2 {
            margin-bottom: 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../home.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <h2><i class="fas fa-history"></i> Riwayat Transaksi</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
                    <th>ID</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Jumlah Item</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = $koneksi->query("
                    SELECT t.id_transaksi, t.tanggal, k.nama_kasir, t.total,
                           COUNT(dt.id_detail) AS jumlah_item
                    FROM transaksi t
                    JOIN kasir k ON t.id_kasir = k.id_kasir
                    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                    GROUP BY t.id_transaksi
                    ORDER BY t.tanggal DESC
                ");

                while ($row = $query->fetch_assoc()):
                ?>
                <tr>
                    <td class="text-center"><?= $row['id_transaksi'] ?></td>
                    <td><?= date('d-m-Y H:i:s', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                    <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= $row['jumlah_item'] ?></td>
                    <td class="text-center">
                        <a href="detail_transaksi.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
