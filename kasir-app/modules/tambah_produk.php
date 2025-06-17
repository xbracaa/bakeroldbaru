<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];

    if ($nama && $harga) {
        $stmt = $koneksi->prepare("INSERT INTO produk (nama_produk, harga) VALUES (?, ?)");
        $stmt->bind_param("si", $nama, $harga);
        $stmt->execute();
        header("Location: produk.php");
        exit;
    } else {
        $error = "Nama dan harga wajib diisi.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Produk</title>
</head>
<body>
    <h2>Tambah Produk</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <p>Nama Produk:<br><input type="text" name="nama" required></p>
        <p>Harga:<br><input type="number" name="harga" required></p>
        <p><button type="submit">Simpan</button> <a href="produk.php">Batal</a></p>
    </form>
</body>
</html>
