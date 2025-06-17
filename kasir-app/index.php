<?php
session_start();
if (!isset($_SESSION['id_kasir'])) {
    header("Location: login.php");
    exit;
} else {
    header("Location: home.php"); // atau index dashboard kamu
    exit;
}
