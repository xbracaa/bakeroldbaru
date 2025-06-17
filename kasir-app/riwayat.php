SELECT t.*, COUNT(dt.id_detail) as jumlah_item 
FROM transaksi t 
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi 
WHERE t.id_kasir = ? 
GROUP BY t.id_transaksi
