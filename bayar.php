<?php
require_once '../src/fungsi.php';
$total = totalHarga();
session_destroy();
?>

<h2>Terima kasih!</h2>
<p>Total pembayaran: Rp<?= $total ?></p>
<a href="index.php">Pesan Lagi</a>
