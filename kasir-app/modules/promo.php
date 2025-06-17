<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch promo data
$promo_query = $koneksi->query("SELECT * FROM promo ORDER BY tanggal_mulai DESC");
if (!$promo_query) {
    die("Query gagal: " . $koneksi->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Promo - Aplikasi Kasir</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container-fluid {
            padding-top: 30px;
            padding-bottom: 30px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #28a745; /* Bootstrap success green */
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-weight: bold;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }
        .card-header i {
            margin-right: 10px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 600;
            vertical-align: middle; /* Center text vertically in headers */
        }
        .table tbody tr:hover {
            background-color: #f2f2f2;
        }
        .btn-action {
            margin: 2px;
            padding: 5px 10px;
            font-size: 0.85rem;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529; /* Dark text for warning button */
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <a href="../home.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-percent"></i> Daftar Promo Aktif
            </div>
            <div class="card-body">
                <a href="tambah_promo.php" class="btn btn-primary mb-3">
                    <i class="fas fa-plus-circle"></i> Tambah Promo Baru
                </a>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nama Promo</th>
                                <th>Jenis</th>
                                <th>Produk Pemicu</th>
                                <th>Bonus / Harga Promo</th>
                                <th>Min Qty</th>
                                <th>Tanggal Berlaku</th>
                                <th>Jam Berlaku</th>
                                <th>Hari Berlaku</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $promo_query->fetch_assoc()):
                                $trigger_name = '-';
                                if (!empty($p['id_produk_trigger'])) {
                                    $trigger_res = $koneksi->query("SELECT nama_produk FROM produk WHERE id_produk = " . (int)$p['id_produk_trigger']);
                                    $trigger_name = $trigger_res && $trigger_res->num_rows > 0 ? $trigger_res->fetch_assoc()['nama_produk'] : '-';
                                }

                                $bonus_info = '-';
                                if ($p['jenis'] == 'bonus' && !empty($p['id_produk_bonus'])) {
                                    $bonus_res = $koneksi->query("SELECT nama_produk FROM produk WHERE id_produk = " . (int)$p['id_produk_bonus']);
                                    $bonus_info = $bonus_res && $bonus_res->num_rows > 0 ? $bonus_res->fetch_assoc()['nama_produk'] : '-';
                                } elseif ($p['jenis'] == 'paket') {
                                    $bonus_info = 'Rp ' . number_format($p['harga_promo'], 0, ',', '.'); // Format to IDR
                                }
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['nama_promo']) ?></td>
                                    <td><span class="badge badge-<?= ($p['jenis'] == 'paket' ? 'success' : 'info') ?>"><?= ucfirst($p['jenis']) ?></span></td>
                                    <td><?= htmlspecialchars($trigger_name) ?></td>
                                    <td><?= htmlspecialchars($bonus_info) ?></td>
                                    <td><?= (int)$p['minimal_qty'] ?></td>
                                    <td><?= $p['tanggal_mulai'] ?> s/d <?= $p['tanggal_akhir'] ?></td>
                                    <td><?= substr($p['waktu_mulai'], 0, 5) ?> - <?= substr($p['waktu_selesai'], 0, 5) ?></td> <td><?= htmlspecialchars($p['berlaku_hari']) ?></td>
                                    <td class="text-nowrap">
                                        <a href="edit_promo.php?id=<?= $p['id_promo'] ?>" class="btn btn-warning btn-sm btn-action" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="hapus_promo.php?id=<?= $p['id_promo'] ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Apakah Anda yakin ingin menghapus promo ini?')" title="Hapus">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($promo_query->num_rows == 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada data promo tersedia.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>