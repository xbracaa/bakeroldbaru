<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $koneksi->query("DELETE FROM promo WHERE id_promo = $id");
}

header("Location: promo.php");
exit;
?>
