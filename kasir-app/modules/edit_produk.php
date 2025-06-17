<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: produk.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];

    if ($nama && $harga) {
        $stmt = $koneksi->prepare("UPDATE produk SET nama_produk = ?, harga = ? WHERE id_produk = ?");
        $stmt->bind_param("sii", $nama, $harga, $id);
        $stmt->execute();
        header("Location: produk.php");
        exit;
    } else {
        $error = "Nama dan harga wajib diisi.";
    }
}

$produk = $koneksi->query("SELECT * FROM produk WHERE id_produk = $id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Produk</title>
</head>
<body>
    <h2>Edit Produk</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <p>Nama Produk:<br><input type="text" name="nama" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required></p>
        <p>Harga:<br><input type="number" name="harga" value="<?= $produk['harga'] ?>" required></p>
        <p><button type="submit">Simpan</button> <a href="produk.php">Batal</a></p>
    </form>
</body>
</html>
