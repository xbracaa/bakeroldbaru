<?php
session_start();
include("config/db.php"); // Pastikan path ini benar

// Cek apakah kasir sudah login
if (!isset($_SESSION['id_kasir'])) {
    header("Location: login.php");
    exit;
}

$nama_kasir = $_SESSION['nama_kasir'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Kasir - Jayaraga Garut</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef; /* Light gray background */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #343a40; /* Darker navbar */
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
        }
        .navbar-brand:hover, .nav-link:hover {
            color: #d4d4d4 !important;
        }
        .container-fluid.main-content {
            flex: 1; /* Occupy remaining vertical space */
            padding-top: 30px;
            padding-bottom: 30px;
        }
        .jumbotron {
            background-color: #ffffff;
            padding: 2rem 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .jumbotron h1 {
            color: #007bff;
            font-weight: 700;
        }
        .jumbotron h2 {
            color: #495057;
            font-weight: 500;
        }
        .jumbotron p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .card-stats {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease-in-out;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .card-stats .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 1rem;
            display: flex;
            align-items: center;
        }
        .card-stats .card-header i {
            margin-right: 10px;
        }
        .card-stats .card-body {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #343a40;
            padding: 1.5rem;
        }
        .card-stats .card-body span {
            display: block;
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
            font-weight: normal;
        }
        footer {
            background-color: #343a40;
            color: white;
            padding: 15px 0;
            text-align: center;
            font-size: 0.9em;
            margin-top: auto; /* Push footer to the bottom */
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">
            <i class="fas fa-cash-register"></i> Baker OLD
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#"><i class="fas fa-home"></i> Beranda <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="transaksi_baru.php"><i class="fas fa-shopping-cart"></i> Transaksi Baru</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="modules/produk.php"><i class="fas fa-bread-slice"></i> Data Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="modules/promo.php"><i class="fas fa-tags"></i> Data Promo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="modules/transaksi.php"><i class="fas fa-history"></i> Riwayat Transaksi</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" onclick="return confirm('Yakin ingin logout?')" title="Logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid main-content">
        <div class="jumbotron">
            <h1 class="display-4"><i class="fas fa-chart-line"></i> Dashboard Kasir</h1>
            <p class="lead">Selamat datang, <?= htmlspecialchars($nama_kasir) ?>!</p>
            <hr class="my-4">
            <p>
                Tanggal: <?= date("d F Y") ?> | Jam: <span id="jam"></span> WIB
            </p>
        </div>

        <h3 class="mb-4 text-center text-muted">Statistik Hari Ini</h3>
        <div class="row">
            <?php
            $tgl = date("Y-m-d");
            $data = $koneksi->query("SELECT COUNT(*) as jml, SUM(total) as omset FROM transaksi WHERE DATE(tanggal) = '$tgl'")->fetch_assoc();
            $promo = $koneksi->query("SELECT COUNT(*) as aktif FROM promo WHERE tanggal_mulai <= '$tgl' AND tanggal_akhir >= '$tgl'")->fetch_assoc();
            ?>
            <div class="col-md-4 mb-4">
                <div class="card card-stats text-center">
                    <div class="card-header bg-primary">
                        <i class="fas fa-receipt"></i> Total Transaksi
                    </div>
                    <div class="card-body">
                        <?= $data['jml'] ?>
                        <span>Transaksi</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card card-stats text-center">
                    <div class="card-header bg-success">
                        <i class="fas fa-dollar-sign"></i> Total Omset
                    </div>
                    <div class="card-body">
                        Rp <?= number_format($data['omset'] ?? 0, 0, ',', '.') ?>
                        <span>Rupiah</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card card-stats text-center">
                    <div class="card-header bg-info">
                        <i class="fas fa-percent"></i> Promo Aktif
                    </div>
                    <div class="card-body">
                        <?= $promo['aktif'] ?>
                        <span>Promo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> Baker OLD Jayaraga Garut. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function updateTime() {
            const now = new Date();
            // Options for toLocaleTimeString to get HH:MM:SS
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            document.getElementById('jam').textContent = now.toLocaleTimeString('id-ID', options);
        }
        setInterval(updateTime, 1000);
        updateTime(); // Initial call to display time immediately
    </script>

</body>
</html>