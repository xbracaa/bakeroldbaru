<?php

function generateKodeTransaksi($koneksi) {
    $tanggal = date('Ymd');
    $prefix = 'TRX' . $tanggal;

    $query = $koneksi->query("SELECT COUNT(*) as jumlah FROM transaksi WHERE DATE(tanggal) = CURDATE()");
    $data = $query->fetch_assoc();
    $urutan = $data['jumlah'] + 1;

    return $prefix . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);
}
