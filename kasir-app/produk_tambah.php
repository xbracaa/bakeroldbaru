<?php
session_start();
include("config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produk = intval($_POST['id_produk']);
    $qty = intval($_POST['qty']);

    $produk = $koneksi->query("SELECT * FROM produk WHERE id_produk = $id_produk")->fetch_assoc();
    if (!$produk) {
        $_SESSION['pesan_error'] = "Produk tidak ditemukan.";
        header("Location: transaksi_baru.php");
        exit;
    }

    if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];

    $found = false;
    foreach ($_SESSION['keranjang'] as &$item) {
        if ($item['id_produk'] == $id_produk && !isset($item['tipe'])) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['keranjang'][] = [
            'id_produk' => $produk['id_produk'],
            'nama_produk' => $produk['nama_produk'],
            'harga' => $produk['harga'],
            'qty' => $qty
        ];
    }

    header("Location: transaksi_baru.php");
    exit;
}
