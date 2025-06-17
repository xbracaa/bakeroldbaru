<?php
function get_produk($conn) {
    $query = "SELECT * FROM produk";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function format_rupiah($angka) {
    return number_format($angka, 0, ',', '.');
}
