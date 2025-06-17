<?php
include("config/db.php");

$kode = $_GET['kode'] ?? '';
if (empty($kode)) {
    die("Kode transaksi tidak ditemukan!");
}

// Ambil data transaksi utama
$stmt = $koneksi->prepare("SELECT * FROM transaksi WHERE kode_transaksi = ?");
$stmt->bind_param("s", $kode);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Transaksi dengan kode '$kode' tidak ditemukan.");
}

// Ambil data kasir
$q_kasir = $koneksi->query("SELECT nama_kasir FROM kasir WHERE id_kasir = {$data['id_kasir']}");
if (!$q_kasir) {
    die("Error query kasir: " . $koneksi->error);
}
$kasir = $q_kasir->fetch_assoc();
if (!$kasir) {
    $kasir['nama_kasir'] = 'Kasir Tidak Ditemukan';
}


// Ambil detail transaksi (langsung ambil nama_produk dari detail_transaksi)
$id_transaksi = $data['id_transaksi'];
echo "<!-- ID transaksi: $id_transaksi -->";
$items = $koneksi->query("
    SELECT qty, harga_satuan AS harga, subtotal, nama_produk
    FROM detail_transaksi 
    WHERE id_transaksi = '$id_transaksi'
");

if (!$items) {
    die("Error mengambil detail transaksi: " . $koneksi->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Transaksi</title>
    <style>
        body { font-family: monospace; font-size: 14px; width: 280px; margin: auto; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .right { float: right; }
        @media print {
            .no-print { display: none; }
        }
        .logo {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body onload="window.print()">

<div class="center">
    <img src="images/logo.png" alt="Logo Baker Old" class="logo">
    <h2>BAKER OLD</h2>
    <p>309 - Jayaraga Garut</p>
</div>

<div class="line"></div>
<p>
    No : <?= htmlspecialchars($data['kode_transaksi']) ?><br>
    Tanggal : <?= date('d-m-Y H:i', strtotime($data['tanggal'])) ?><br>
    Jam Masuk : <?= date('d-m-Y H:i', strtotime($data['tanggal'])) ?><br>
    Kasir : <?= htmlspecialchars($kasir['nama_kasir']) ?>
</p>

<div class="line"></div>

<?php
$total_item = 0;
if ($items->num_rows > 0) {
    while ($item = $items->fetch_assoc()):
        $total_item++;
?>
    <p>
        <?= htmlspecialchars($item['nama_produk']) ?><br>
        <?= $item['qty'] ?>x @<?= number_format($item['harga'], 0, ',', '.') ?>
        <span class="right"><?= number_format($item['subtotal'], 0, ',', '.') ?></span>
    </p>
<?php
    endwhile;
} else {
    echo "<p>Tidak ada detail item untuk transaksi ini.</p>";
}
?>

<div class="line"></div>
<p><?= $total_item ?> item</p>

<p>
    Subtotal : <span class="right">Rp <?= number_format($data['total'], 0, ',', '.') ?></span><br>
    <strong>Grand Total : <span class="right">Rp <?= number_format($data['total'], 0, ',', '.') ?></span></strong><br>
    CASH : <span class="right">Rp <?= number_format($data['bayar'], 0, ',', '.') ?></span><br>
    <strong>Kembalian : <span class="right">Rp <?= number_format($data['kembalian'] ?? 0, 0, ',', '.') ?></span></strong>
</p>

<div class="line"></div>
<div class="center">
    -- Thank You --<br>
    <strong>Nomor antrian</strong><br>
    <h2>32</h2>
    <p>Tunggu nomor kamu dipanggil</p>
</div>

<div class="no-print center">
    <a href="transaksi_baru.php">Kembali</a>
</div>

</body>
</html>
