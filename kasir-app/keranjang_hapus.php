<?php
session_start();

if (isset($_POST['index'])) {
    $index = intval($_POST['index']);

    if (isset($_SESSION['keranjang'][$index])) {
        unset($_SESSION['keranjang'][$index]);
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']); // reindex
        $_SESSION['pesan_sukses'] = "Item berhasil dihapus dari keranjang.";
    } else {
        $_SESSION['pesan_error'] = "Item tidak ditemukan di keranjang.";
    }
} else {
    $_SESSION['pesan_error'] = "Permintaan tidak valid.";
}

header("Location: transaksi_baru.php");
exit;
