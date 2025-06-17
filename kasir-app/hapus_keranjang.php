<?php
session_start();

if (isset($_POST['index'])) {
    $index = $_POST['index'];

    if (isset($_SESSION['keranjang'][$index])) {
        unset($_SESSION['keranjang'][$index]);

        // Reindex array biar tidak lompat index
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
    }
}

header("Location: transaksi_baru.php");
exit;
