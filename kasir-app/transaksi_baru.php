<?php
session_start();
include("config/db.php");

// Pastikan koneksi database sudah terjalin
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data kasir dari sesi atau database (contoh sederhana)
$nama_kasir = $_SESSION['nama_kasir'] ?? 'Kasir Roti Manis'; 

// Set zona waktu ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Tanggal dan waktu realtime
$tanggal_sekarang = date('d F Y'); // Contoh: 17 Juni 2025
$waktu_sekarang = date('H:i:s');   // Contoh: 08:18:17

// Ambil daftar produk
$produk_q = $koneksi->query("SELECT * FROM produk");
if (!$produk_q) {
    die("Error mengambil data produk: " . $koneksi->error);
}

// Ambil promo yang aktif sesuai hari & jam
$tanggal = date('Y-m-d');
$waktu = date('H:i:s');
$hari_map = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$hari = $hari_map[date('l')];

$promo_q = $koneksi->query("
    SELECT * FROM promo 
    WHERE 
        tanggal_mulai <= '$tanggal' AND tanggal_akhir >= '$tanggal' AND 
        waktu_mulai <= '$waktu' AND waktu_selesai >= '$waktu' AND 
        FIND_IN_SET('$hari', berlaku_hari)
");
if (!$promo_q) {
    die("Error mengambil data promo: " . $koneksi->error);
}

// Keranjang
$keranjang = $_SESSION['keranjang'] ?? [];
$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['qty'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir Baker Old</title>
    <style>
        /* Warna Dasar */
        :root {
            --color-primary-brown: #5A3F2B; /* Coklat Tua (Teks Utama, Header) */
            --color-secondary-brown: #8B6F5A; /* Coklat Sedang (Aksen, Border) */
            --color-light-brown: #D4B29A; /* Coklat Muda (Latar belakang elemen) */
            --color-cream: #FFF8E1; /* Kuning Cream (Latar belakang utama) */
            --color-yellow: #FFD54F; /* Kuning (Aksen, Tombol Utama) */
            --color-dark-yellow: #FFA000; /* Kuning Gelap (Hover Tombol) */
            --color-text-dark: #333;
            --color-text-light: #fff;
            --color-success: #66BB6A; /* Hijau untuk sukses */
            --color-error: #EF5350; /* Merah untuk error */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
        }
        .header {
            background-color: var(--color-primary-brown);
            color: var(--color-text-light);
            padding: 20px 40px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-family: 'Georgia', serif; /* Font klasik */
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        .header p {
            margin: 5px 0 0;
            font-size: 1.1em;
            opacity: 0.9;
        }
        .container {
            display: flex;
            gap: 25px;
            max-width: 1300px;
            margin: 0 auto 30px auto;
            align-items: flex-start;
        }
        .left-panel, .right-panel {
            background-color: var(--color-text-light);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border: 1px solid var(--color-light-brown); /* Border halus */
        }
        .left-panel {
            flex: 2;
        }
        .right-panel {
            flex: 1;
            position: sticky;
            top: 20px;
        }
        h2 {
            color: var(--color-primary-brown);
            border-bottom: 2px solid var(--color-light-brown);
            padding-bottom: 12px;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.8em;
            font-family: 'Georgia', serif;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .product-card {
            background-color: var(--color-cream); /* Latar belakang card */
            border: 1px solid var(--color-light-brown);
            border-radius: 10px;
            padding: 18px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .product-card img {
            max-width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 2px solid var(--color-secondary-brown); /* Border gambar */
        }
        .product-card h3 {
            margin: 10px 0 8px;
            font-size: 1.3em;
            color: var(--color-primary-brown);
            line-height: 1.3;
        }
        .product-card p {
            font-size: 1.2em;
            color: var(--color-secondary-brown); /* Warna harga menonjol */
            font-weight: bold;
            margin-bottom: 18px;
        }
        .product-card form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: auto;
        }
        .product-card input[type="number"] {
            width: 80px;
            padding: 10px;
            border: 1px solid var(--color-secondary-brown);
            border-radius: 6px;
            text-align: center;
            margin: 0 auto;
            font-size: 1em;
            background-color: var(--color-cream);
        }
        .product-card button {
            background-color: var(--color-yellow);
            color: var(--color-primary-brown);
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.1s;
        }
        .product-card button:hover {
            background-color: var(--color-dark-yellow);
            transform: translateY(-1px);
        }
        .promo-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed var(--color-light-brown);
        }
        .promo-item {
            background-color: var(--color-cream); /* Latar belakang promo item */
            border: 1px solid var(--color-light-brown);
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95em;
        }
        .promo-item span {
            color: var(--color-primary-brown);
            font-weight: bold;
        }
        .promo-item button {
            background-color: var(--color-secondary-brown);
            color: var(--color-text-light);
            padding: 7px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }
        .promo-item button:hover {
            background-color: var(--color-primary-brown);
        }
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: bold;
        }
        .notification.error {
            background-color: #ffe0e0; /* Merah muda lembut */
            color: var(--color-error);
            border: 1px solid var(--color-error);
        }
        .notification.success {
            background-color: #e0ffe0; /* Hijau muda lembut */
            color: var(--color-success);
            border: 1px solid var(--color-success);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--color-light-brown);
        }
        table th, table td {
            border: 1px solid var(--color-light-brown);
            padding: 12px;
            text-align: left;
        }
        table th {
            background-color: var(--color-light-brown);
            color: var(--color-primary-brown);
            font-weight: bold;
            font-size: 1em;
        }
        table tbody tr:nth-child(even) {
            background-color: var(--color-cream); /* Baris genap lebih cream */
        }
        table tfoot td {
            font-weight: bold;
            background-color: var(--color-secondary-brown);
            font-size: 1.2em;
            color: var(--color-text-light);
        }
        .cart-action-button {
            background-color: var(--color-error);
            color: var(--color-text-light);
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85em;
            transition: background-color 0.3s ease;
        }
        .cart-action-button:hover {
            background-color: #d32f2f; /* Merah lebih gelap */
        }
        .checkout-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed var(--color-light-brown);
        }
        .checkout-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: var(--color-primary-brown);
        }
        .checkout-form input[type="number"] {
            width: calc(100% - 24px);
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid var(--color-secondary-brown);
            border-radius: 6px;
            font-size: 1.1em;
            background-color: var(--color-cream);
        }
        .checkout-form button {
            background-color: var(--color-yellow);
            color: var(--color-primary-brown);
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.1s;
        }
        .checkout-form button:hover {
            background-color: var(--color-dark-yellow);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistem Kasir Baker Old</h1>
        <p>Kasir: <?= htmlspecialchars($nama_kasir) ?> | Tanggal: <?= $tanggal_sekarang ?> | Waktu: <span id="realtime-clock"><?= $waktu_sekarang ?></span></p>
    </div>

    <div class="container">
        <div class="left-panel">
            <?php 
            if (isset($_SESSION['pesan_error'])) {
                echo "<div class='notification error'>" . htmlspecialchars($_SESSION['pesan_error']) . "</div>";
                unset($_SESSION['pesan_error']);
            }
            if (isset($_SESSION['pesan_sukses'])) {
                echo "<div class='notification success'>" . htmlspecialchars($_SESSION['pesan_sukses']) . "</div>";
                unset($_SESSION['pesan_sukses']);
            }
            ?>

            <h2>Menu</h2>
            <div class="product-grid">
                <?php if ($produk_q->num_rows > 0) : ?>
                    <?php while ($p = $produk_q->fetch_assoc()) : ?>
                        <div class="product-card">
                            <?php 
                                // Path ke folder gambar
                                $image_folder = 'images/';
                                
                                // Ubah nama produk menjadi nama file yang valid
                                // Contoh: "Roti Tawar" -> "roti_tawar"
                                // Kemudian cari ekstensi umum seperti .jpg atau .png
                                $clean_product_name = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $p['nama_produk'])));
                                
                                $image_filename = $clean_product_name . '.jpg'; // Coba JPG dulu
                                $image_path = $image_folder . $image_filename;

                                // Jika file .jpg tidak ada, coba .png
                                if (!file_exists($image_path) || is_dir($image_path)) {
                                    $image_filename = $clean_product_name . '.png';
                                    $image_path = $image_folder . $image_filename;
                                }

                                // Jika masih tidak ada (baik JPG maupun PNG), gunakan gambar default
                                if (!file_exists($image_path) || is_dir($image_path)) {
                                    $image_path = $image_folder . 'default.jpg'; 
                                    // Pastikan Anda punya file default.jpg di folder images Anda
                                    // Atau, Anda bisa ganti dengan placeholder lain jika tidak ada default.jpg
                                }
                            ?>
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                            <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
                            <p>Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                            <form action="produk_tambah.php" method="post">
                                <input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>">
                                <input type="number" name="qty" value="1" min="1" required>
                                <button type="submit">Tambah ke Keranjang</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: var(--color-secondary-brown);">Maaf, tidak ada produk roti yang tersedia saat ini.</p>
                <?php endif; ?>
            </div>

            <div class="promo-section">
                <h2>Promo Spesial Hari Ini!</h2>
                <div class="promo-list">
                    <?php if ($promo_q->num_rows > 0) : ?>
                        <?php while ($pr = $promo_q->fetch_assoc()) : ?>
                            <div class="promo-item">
                                <span><?= htmlspecialchars($pr['nama_promo']) ?> (Diskon <?= ucfirst(htmlspecialchars($pr['jenis'])) ?>)</span>
                                <form action="promo_tambah.php" method="post">
                                    <input type="hidden" name="id_promo" value="<?= $pr['id_promo'] ?>">
                                    <button type="submit">Terapkan Promo</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p style="color: var(--color-secondary-brown);">Belum ada promo aktif saat ini. Cek lagi nanti!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <h2>Keranjang Pesanan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keranjang)) : ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: var(--color-secondary-brown);">Keranjang Anda kosong. Yuk, pilih roti favoritmu!</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($keranjang as $index => $item) : ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td><?= $item['qty'] ?></td>
                                <td>Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></td>
                                <td>
                                    <form action="keranjang_hapus.php" method="post" style="display:inline;">
                                        <input type="hidden" name="index" value="<?= $index ?>">
                                        <button type="submit" class="cart-action-button" onclick="return confirm('Hapus roti ini dari keranjang?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total Pembayaran</td>
                        <td colspan="2">Rp <?= number_format($total, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="checkout-form">
                <h2>Pembayaran</h2>
                <form action="transaksi_proses.php" method="post">
                    <label for="bayar">Jumlah Uang Diterima:</label>
                    <input type="number" name="bayar" id="bayar" required min="<?= $total ?>" placeholder="Masukkan uang yang dibayarkan pelanggan">
                    <button type="submit">Selesaikan Transaksi</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk mengupdate jam secara realtime
        function updateRealtimeClock() {
            const clockElement = document.getElementById('realtime-clock');
            if (clockElement) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockElement.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }

        // Update setiap 1 detik
        setInterval(updateRealtimeClock, 1000);

        // Panggil pertama kali saat halaman dimuat
        updateRealtimeClock();
    </script>
</body>
</html>