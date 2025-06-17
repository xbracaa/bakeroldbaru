-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Jun 2025 pada 04.07
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakerold`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `buat_transaksi_baru` (IN `p_id_kasir` INT, OUT `p_id_transaksi` INT)   BEGIN
  DECLARE new_kode VARCHAR(50);

  SET new_kode = CONCAT('TRX-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'));

  INSERT INTO transaksi (kode_transaksi, id_kasir, total, bayar, kembalian)
  VALUES (new_kode, p_id_kasir, 0, 0, 0);

  SET p_id_transaksi = LAST_INSERT_ID();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `id_promo` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `id_promo`, `qty`, `subtotal`) VALUES
(1, 1, NULL, 3, 1, 1000),
(2, 1, 1, NULL, 1, 45000),
(3, 2, 1, NULL, 2, 90000),
(4, 2, NULL, 3, 1, 1000),
(5, 3, 1, NULL, 1, 45000),
(6, 3, 4, NULL, 1, 7000),
(7, 4, 9, NULL, 1, 8000),
(8, 5, 1, NULL, 1, 45000),
(9, 6, NULL, 2, 1, 0),
(10, 6, NULL, 2, 1, 0),
(11, 11, 5, NULL, 1, 7000),
(12, 12, 3, NULL, 1, 300000),
(13, 13, 3, NULL, 1, 300000),
(14, 13, 6, NULL, 4, 32000),
(15, 14, 2, NULL, 1, 80000),
(16, 15, 3, NULL, 2, 600000),
(17, 16, 6, NULL, 3, 24000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kasir`
--

CREATE TABLE `kasir` (
  `id_kasir` int(11) NOT NULL,
  `nama_kasir` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kasir`
--

INSERT INTO `kasir` (`id_kasir`, `nama_kasir`, `username`, `password`) VALUES
(1, 'kasir jayaraga garut', 'kasirjayaraga', 'admin123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) DEFAULT 0
) ;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga`, `stok`) VALUES
(1, 'Paket Berbagi Berkah 6 Roti', 45000, 0),
(2, 'Paket Berbagi Berkah 12 Roti', 80000, 0),
(3, 'Paket Berbagi Berkah 50 Roti', 300000, 0),
(4, 'Roti Vanila', 7000, 0),
(5, 'Roti Ori', 7000, 0),
(6, 'Roti Keju', 8000, 0),
(7, 'Roti Coklat', 8000, 0),
(8, 'Roti Pandan Banana', 7000, 0),
(9, 'Roti Pandan Coklat', 8000, 0),
(10, 'Roti Pandan Butter', 7000, 0),
(11, 'Es Krim', 7000, 0),
(12, 'Roti Es Krim', 12000, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `promo`
--

CREATE TABLE `promo` (
  `id_promo` int(11) NOT NULL,
  `nama_promo` varchar(100) NOT NULL,
  `jenis` enum('paket','bonus') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `berlaku_hari` varchar(100) DEFAULT NULL,
  `minimal_qty` int(11) NOT NULL DEFAULT 1,
  `id_produk_trigger` int(11) DEFAULT NULL,
  `id_produk_bonus` int(11) DEFAULT NULL,
  `harga_promo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promo`
--

INSERT INTO `promo` (`id_promo`, `nama_promo`, `jenis`, `deskripsi`, `tanggal_mulai`, `tanggal_akhir`, `waktu_mulai`, `waktu_selesai`, `berlaku_hari`, `minimal_qty`, `id_produk_trigger`, `id_produk_bonus`, `harga_promo`) VALUES
(1, 'Paket Roti 20rb', 'paket', 'Beli 3 roti bebas varian hanya Rp 20.000', '2025-06-01', '2025-06-30', '10:00:00', '22:00:00', 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu', 3, NULL, NULL, 20000),
(2, 'Bonus Es Krim Pagi', 'bonus', 'Beli 3 roti dapat es krim', '2025-06-15', '2025-06-30', '07:00:00', '10:00:00', 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu', 3, NULL, 11, NULL),
(3, 'promo 24/7', 'paket', 'semua rasa seribu', '2025-06-15', '2025-06-21', '00:05:00', '23:59:00', 'Senin,Selasa,Rabu,Kamis,Jumat', 1, NULL, NULL, 1000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `id_kasir` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total` int(11) DEFAULT NULL,
  `bayar` int(11) DEFAULT NULL,
  `kembalian` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `kode_transaksi`, `id_kasir`, `tanggal`, `total`, `bayar`, `kembalian`) VALUES
(1, 'TRX20250617015812', 1, '2025-06-17 06:58:12', 46000, 100000, 54000),
(2, 'TRX20250617020247', 1, '2025-06-17 07:02:47', 91000, 100000, 9000),
(3, 'TRX20250617020452', 1, '2025-06-17 07:04:52', 52000, 55000, 3000),
(4, 'TRX20250617020604', 1, '2025-06-17 07:06:04', 8000, 10000, 2000),
(5, 'TRX20250617020809', 1, '2025-06-17 07:08:09', 45000, 100000, 55000),
(6, 'TRX20250617021820', 1, '2025-06-17 07:18:20', 0, 1000, 1000),
(7, 'TRX-20250617030748', 1, '2025-06-17 08:07:48', 7000, 8000, 1000),
(8, 'TRX-20250617030914', 1, '2025-06-17 08:09:14', 7000, 8000, 1000),
(9, 'TRX-20250617030921', 1, '2025-06-17 08:09:21', 7000, 8000, 1000),
(10, 'TRX-20250617031104', 1, '2025-06-17 08:11:04', 7000, 8000, 1000),
(11, 'TRX20250617031116', 1, '2025-06-17 08:11:16', 7000, 8000, 1000),
(12, 'TRX20250617032059', 1, '2025-06-17 08:20:59', 300000, 300000, 0),
(13, 'TRX20250617034407', 1, '2025-06-17 08:44:07', 332000, 400000, 68000),
(14, 'TRX20250617034545', 1, '2025-06-17 08:45:45', 80000, 100000, 20000),
(15, 'TRX20250617035539', 1, '2025-06-17 08:55:39', 600000, 600000, 0),
(16, 'TRX20250617035646', 1, '2025-06-17 08:56:46', 24000, 25000, 1000);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_promo` (`id_promo`);

--
-- Indeks untuk tabel `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`id_kasir`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indeks untuk tabel `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id_promo`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_kasir` (`id_kasir`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `kasir`
--
ALTER TABLE `kasir`
  MODIFY `id_kasir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `promo`
--
ALTER TABLE `promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `detail_transaksi_ibfk_3` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_kasir`) REFERENCES `kasir` (`id_kasir`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
