<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("config/db.php");

// Pastikan kasir sudah login dan keranjang tidak kosong
if (!isset($_SESSION['id_kasir']) || empty($_SESSION['keranjang'])) {
    header("Location: transaksi_baru.php");
    exit;
}

$id_kasir   = $_SESSION['id_kasir'];
$keranjang  = $_SESSION['keranjang'];

// Hitung total langsung dari keranjang
$total = 0;
foreach ($keranjang as $item) {
    // Pastikan harga promo dihitung dengan benar dari 'harga' item
    $total += $item['harga'] * $item['qty'];
}

$bayar      = isset($_POST['bayar']) ? intval($_POST['bayar']) : 0;
$kembalian  = $bayar - $total;
$kode_transaksi = 'TRX-' . date('YmdHis');

// ✅ SIMPAN KE TABEL TRANSAKSI
$stmt = $koneksi->prepare("
    INSERT INTO transaksi (kode_transaksi, id_kasir, total, bayar, kembalian)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("siiii", $kode_transaksi, $id_kasir, $total, $bayar, $kembalian);
$stmt->execute();
$id_transaksi = $stmt->insert_id;
$stmt->close();

// ✅ SIMPAN KE TABEL DETAIL_TRANSAKSI
// ... kode di atas ...

// SIMPAN KE TABEL DETAIL_TRANSAKSI
foreach ($keranjang as $item) {
    $id_produk  = intval($item['id_produk']);

    $nama_produk_yang_disimpan = (string)$item['nama_produk']; 
    if (empty($nama_produk_yang_disimpan)) {
        $nama_produk_yang_disimpan = "Nama Produk Tidak Dikenal"; 
    }

    // --- TAMBAHKAN KODE INI SEMENTARA UNTUK DEBUGGING ---
    echo "Nilai yang akan di-bind untuk nama_produk: ";
    var_dump($nama_produk_yang_disimpan);
    echo "<br>";
    // ----------------------------------------------------

    $qty        = intval($item['qty']);
    $harga      = intval($item['harga']);
    $subtotal   = $qty * $harga;
    $harga_setelah_diskon = $subtotal;

    $stmt2 = $koneksi->prepare("
        INSERT INTO detail_transaksi
        (id_transaksi, id_produk, nama_produk, qty, harga_satuan, subtotal, harga_setelah_diskon)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt2->bind_param("isisiii", $id_transaksi, $id_produk, $nama_produk_yang_disimpan, $qty, $harga, $subtotal, $harga_setelah_diskon);
    $stmt2->execute();
    $stmt2->close();
}

// ... sisa kode di bawah ...

// ✅ Kosongkan keranjang
unset($_SESSION['keranjang']);

// ✅ Redirect ke halaman transaksi_selesai
//header("Location: transaksi_selesai.php?kode=$kode_transaksi");
//exit;
?>