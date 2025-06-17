<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['id_promo'])) {
    $_SESSION['pesan_error'] = "Permintaan tidak valid: ID Promo tidak ditemukan.";
    header("Location: transaksi_baru.php");
    exit;
}

$id_promo = intval($_POST['id_promo']);
$promo_stmt = $koneksi->prepare("SELECT * FROM promo WHERE id_promo = ?");
$promo_stmt->bind_param("i", $id_promo);
$promo_stmt->execute();
$promo_result = $promo_stmt->get_result();
$promo = $promo_result->fetch_assoc();
$promo_stmt->close();

if (!$promo) {
    $_SESSION['pesan_error'] = "Promo tidak ditemukan atau tidak valid.";
    header("Location: transaksi_baru.php");
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Hapus item promo & bonus sebelumnya
$_SESSION['keranjang'] = array_filter($_SESSION['keranjang'], function ($item) {
    return !isset($item['tipe']) || !in_array($item['tipe'], ['promo', 'bonus']);
});
$_SESSION['keranjang'] = array_values($_SESSION['keranjang']);

$nama_promo = htmlspecialchars($promo['nama_promo']);
$jenis_promo = $promo['jenis'];
$harga_promo = $jenis_promo === 'paket' ? intval($promo['harga_promo']) : 0;

$_SESSION['keranjang'][] = [
    'id_produk' => 0,
    'nama_produk' => $nama_promo . ($jenis_promo === 'paket' ? " (Paket)" : ""),
    'harga' => $harga_promo,
    'qty' => 1,
    'tipe' => 'promo',
    'id_promo' => $promo['id_promo']
];

if ($jenis_promo === 'bonus') {
    $bonus_id = intval($promo['id_produk_bonus']);
    $bonus_stmt = $koneksi->prepare("SELECT id_produk, nama_produk FROM produk WHERE id_produk = ?");
    $bonus_stmt->bind_param("i", $bonus_id);
    $bonus_stmt->execute();
    $bonus = $bonus_stmt->get_result()->fetch_assoc();
    $bonus_stmt->close();

    if ($bonus) {
        $_SESSION['keranjang'][] = [
            'id_produk' => $bonus['id_produk'],
            'nama_produk' => htmlspecialchars($bonus['nama_produk']) . " (Bonus)",
            'harga' => 0,
            'qty' => 1,
            'tipe' => 'bonus',
            'id_promo' => $promo['id_promo']
        ];
    } else {
        $_SESSION['pesan_error'] = "Produk bonus untuk promo '{$nama_promo}' tidak ditemukan. Promo utama sudah ditambahkan.";
    }
}

header("Location: transaksi_baru.php");
exit;
?>
