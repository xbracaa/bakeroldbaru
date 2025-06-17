<?php
session_start();
include("config/db.php");

if (!isset($_POST['id_promo'])) {
    $_SESSION['pesan_error'] = "Promo tidak ditemukan.";
    header("Location: transaksi_baru.php");
    exit;
}

$id_promo = intval($_POST['id_promo']);
$promo = $koneksi->query("SELECT * FROM promo WHERE id_promo = $id_promo")->fetch_assoc();

if (!$promo) {
    $_SESSION['pesan_error'] = "Data promo tidak valid.";
    header("Location: transaksi_baru.php");
    exit;
}

// Bersihkan promo/bonus sebelumnya
$_SESSION['keranjang'] = array_filter($_SESSION['keranjang'] ?? [], function($item) {
    return !isset($item['tipe']) || ($item['tipe'] !== 'promo' && $item['tipe'] !== 'bonus');
});
$_SESSION['keranjang'] = array_values($_SESSION['keranjang']);

$jenis = $promo['jenis'];
$nama = $promo['nama_promo'];

if ($jenis == 'paket') {
    $_SESSION['keranjang'][] = [
        'id_produk' => 0,
        'nama_produk' => $nama . " (Paket)",
        'harga' => intval($promo['harga_promo']),
        'qty' => 1,
        'tipe' => 'promo',
        'id_promo' => $promo['id_promo']
    ];
} elseif ($jenis == 'bonus') {
    $_SESSION['keranjang'][] = [
        'id_produk' => 0,
        'nama_produk' => $nama . " (Promo Bonus)",
        'harga' => 0,
        'qty' => 1,
        'tipe' => 'promo',
        'id_promo' => $promo['id_promo']
    ];

    $bonus_id = intval($promo['id_produk_bonus']);
    $bonus = $koneksi->query("SELECT * FROM produk WHERE id_produk = $bonus_id")->fetch_assoc();

    if ($bonus) {
        $_SESSION['keranjang'][] = [
            'id_produk' => $bonus['id_produk'],
            'nama_produk' => $bonus['nama_produk'] . " (Bonus)",
            'harga' => 0,
            'qty' => 1,
            'tipe' => 'bonus',
            'id_promo' => $promo['id_promo']
        ];
    }
}

header("Location: transaksi_baru.php");
exit;
