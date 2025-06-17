<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: promo.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil data promo berdasarkan ID
$promo_query = $koneksi->prepare("SELECT * FROM promo WHERE id_promo = ?");
$promo_query->bind_param("i", $id);
$promo_query->execute();
$promo_result = $promo_query->get_result();
$promo = $promo_result->fetch_assoc();

if (!$promo) {
    die("Promo tidak ditemukan.");
}

$produk = $koneksi->query("SELECT * FROM produk");

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_promo      = $_POST['nama_promo'];
    $jenis           = $_POST['jenis'];
    $deskripsi       = $_POST['deskripsi'];
    $tanggal_mulai   = $_POST['tanggal_mulai'];
    $tanggal_akhir   = $_POST['tanggal_akhir'];
    $waktu_mulai     = $_POST['waktu_mulai'];
    $waktu_selesai   = $_POST['waktu_selesai'];
    $berlaku_hari    = $_POST['berlaku_hari'];
    $minimal_qty     = intval($_POST['minimal_qty']);
    $id_produk_trigger = !empty($_POST['id_produk_trigger']) ? intval($_POST['id_produk_trigger']) : null;
    $id_produk_bonus   = !empty($_POST['id_produk_bonus']) ? intval($_POST['id_produk_bonus']) : null;
    $harga_promo       = !empty($_POST['harga_promo']) ? intval($_POST['harga_promo']) : null;

    $stmt = $koneksi->prepare("UPDATE promo SET
        nama_promo=?, jenis=?, deskripsi=?, tanggal_mulai=?, tanggal_akhir=?,
        waktu_mulai=?, waktu_selesai=?, berlaku_hari=?, minimal_qty=?,
        id_produk_trigger=?, id_produk_bonus=?, harga_promo=?
        WHERE id_promo=?");
    $stmt->bind_param("ssssssssiisii",
        $nama_promo, $jenis, $deskripsi, $tanggal_mulai, $tanggal_akhir,
        $waktu_mulai, $waktu_selesai, $berlaku_hari, $minimal_qty,
        $id_produk_trigger, $id_produk_bonus, $harga_promo, $id);
    $stmt->execute();

    header("Location: promo.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Promo - Aplikasi Kasir</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .card-header {
            background-color: #ffc107; /* Warna kuning Bootstrap */
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
        .form-group label {
            font-weight: 600;
            color: #343a40;
        }
        .btn-warning {
            background-color: #ffc107; /* Warna kuning Bootstrap */
            border-color: #ffc107;
            transition: background-color 0.2s ease;
            color: #212529; /* Warna teks gelap agar terbaca */
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
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

    <div class="container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Form Edit Promo
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="form-group">
                        <label for="nama_promo">Nama Promo:</label>
                        <input type="text" class="form-control" id="nama_promo" name="nama_promo" value="<?= htmlspecialchars($promo['nama_promo']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="jenis">Jenis Promo:</label>
                        <select class="form-control" id="jenis" name="jenis" required>
                            <option value="paket" <?= $promo['jenis'] == 'paket' ? 'selected' : '' ?>>Paket</option>
                            <option value="bonus" <?= $promo['jenis'] == 'bonus' ? 'selected' : '' ?>>Bonus</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Promo:</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($promo['deskripsi']) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="tanggal_mulai">Tanggal Mulai:</label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?= $promo['tanggal_mulai'] ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tanggal_akhir">Tanggal Akhir:</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?= $promo['tanggal_akhir'] ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="waktu_mulai">Jam Mulai:</label>
                            <input type="time" class="form-control" id="waktu_mulai" name="waktu_mulai" value="<?= $promo['waktu_mulai'] ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="waktu_selesai">Jam Selesai:</label>
                            <input type="time" class="form-control" id="waktu_selesai" name="waktu_selesai" value="<?= $promo['waktu_selesai'] ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="berlaku_hari">Hari Berlaku (pisahkan koma):</label>
                        <input type="text" class="form-control" id="berlaku_hari" name="berlaku_hari" value="<?= htmlspecialchars($promo['berlaku_hari']) ?>" placeholder="Contoh: Senin,Selasa,Jumat" required>
                        <small class="form-text text-muted">Pisahkan hari dengan koma (contoh: Senin,Selasa,Minggu).</small>
                    </div>

                    <div class="form-group">
                        <label for="minimal_qty">Kuantitas Minimal Pembelian:</label>
                        <input type="number" class="form-control" id="minimal_qty" name="minimal_qty" value="<?= $promo['minimal_qty'] ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="id_produk_trigger">Produk Pemicu (opsional):</label>
                        <select class="form-control" id="id_produk_trigger" name="id_produk_trigger">
                            <option value="">-- Pilih Produk --</option>
                            <?php
                            $produk->data_seek(0); // Reset pointer for "Produk Trigger"
                            while ($row = $produk->fetch_assoc()):
                            ?>
                                <option value="<?= $row['id_produk'] ?>" <?= ($promo['id_produk_trigger'] == $row['id_produk']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_produk']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Pilih produk yang harus dibeli untuk memicu promo ini.</small>
                    </div>

                    <div class="form-group">
                        <label for="id_produk_bonus">Produk Bonus (opsional, untuk jenis 'Bonus'):</label>
                        <select class="form-control" id="id_produk_bonus" name="id_produk_bonus">
                            <option value="">-- Pilih Produk --</option>
                            <?php
                            $produk->data_seek(0); // Reset pointer again for "Produk Bonus"
                            while ($row = $produk->fetch_assoc()):
                            ?>
                                <option value="<?= $row['id_produk'] ?>" <?= ($promo['id_produk_bonus'] == $row['id_produk']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_produk']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Pilih produk yang akan diberikan sebagai bonus.</small>
                    </div>

                    <div class="form-group">
                        <label for="harga_promo">Harga Promo (untuk jenis 'Paket', opsional):</label>
                        <input type="number" class="form-control" id="harga_promo" name="harga_promo" value="<?= $promo['harga_promo'] ?>" placeholder="Contoh: 15000">
                        <small class="form-text text-muted">Isi harga diskon jika jenis promo adalah 'Paket'.</small>
                    </div>

                    <button type="submit" class="btn btn-warning btn-block"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>