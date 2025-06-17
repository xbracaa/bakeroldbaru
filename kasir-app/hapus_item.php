<?php
session_start();

if (isset($_POST['index'])) {
    $index = $_POST['index'];
    unset($_SESSION['keranjang'][$index]);
    $_SESSION['keranjang'] = array_values($_SESSION['keranjang']); // reset indeks
}

header("Location: transaksi_baru.php");
exit;
