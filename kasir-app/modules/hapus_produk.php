<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $koneksi->prepare("DELETE FROM produk WHERE id_produk = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: produk.php");
exit;
