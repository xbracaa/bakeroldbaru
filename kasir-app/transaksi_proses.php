<?php
session_start();
include("config/db.php");

// Validasi awal
if (!isset($_SESSION['id_kasir']) || empty($_SESSION['keranjang'])) {
    $_SESSION['pesan_error'] = "Transaksi tidak valid.";
    header("Location: transaksi_baru.php");
    exit;
}

$keranjang = $_SESSION['keranjang'];
$id_kasir = $_SESSION['id_kasir'];
$bayar = intval($_POST['bayar'] ?? 0);

$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['qty'];
}

if ($bayar < $total) {
    $_SESSION['pesan_error'] = "Uang pembayaran kurang dari total belanja.";
    header("Location: transaksi_baru.php");
    exit;
}

$kembalian = $bayar - $total;

// Generate kode transaksi
$kode_transaksi = "TRX" . date("YmdHis");

// Simpan ke tabel transaksi
$stmt = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi, id_kasir, tanggal, total, bayar, kembalian) VALUES (?, ?, NOW(), ?, ?, ?)");
$stmt->bind_param("siiii", $kode_transaksi, $id_kasir, $total, $bayar, $kembalian);
$stmt->execute();
$id_transaksi = $stmt->insert_id;
$stmt->close();

// Simpan ke detail_transaksi
$stmt_detail = $koneksi->prepare("INSERT INTO detail_transaksi (id_transaksi, id_produk, id_promo, qty, subtotal) VALUES (?, ?, ?, ?, ?)");
foreach ($keranjang as $item) {
    $id_produk = ($item['tipe'] === 'promo' || $item['tipe'] === 'bonus') ? null : $item['id_produk'];
    $id_promo = isset($item['id_promo']) ? $item['id_promo'] : null;
    $qty = $item['qty'];
    $subtotal = $item['harga'] * $item['qty'];
    $stmt_detail->bind_param("iiiii", $id_transaksi, $id_produk, $id_promo, $qty, $subtotal);
    $stmt_detail->execute();
}
$stmt_detail->close();

// Siapkan data untuk ditampilkan di halaman selesai
$_SESSION['transaksi_selesai'] = [
    'kode_transaksi' => $kode_transaksi,
    'total' => $total,
    'bayar' => $bayar,
    'kembalian' => $kembalian,
    'keranjang' => $keranjang,
    'id_kasir' => $_SESSION['id_kasir'] 
];

// Hapus keranjang
unset($_SESSION['keranjang']);

// Pindah ke halaman transaksi selesai
header("Location: transaksi_selesai.php");
exit;
?>
