<?php
require_once '../src/fungsi.php';

if (isset($_GET['hapus'])) {
    hapusDariKeranjang($_GET['hapus']);
    header("Location: keranjang.php");
}
?>

<h2>Keranjang Belanja</h2>
<?php if (empty($_SESSION['keranjang'])): ?>
    <p>Keranjang kosong.</p>
<?php else: ?>
    <ul>
    <?php foreach ($_SESSION['keranjang'] as $id => $item): ?>
        <li><?= $item['nama'] ?> – <?= $item['porsi'] ?> porsi – Rp<?= $item['harga'] * $item['porsi'] ?>
            <a href="?hapus=<?= $id ?>">[hapus]</a>
        </li>
    <?php endforeach; ?>
    </ul>
    <p><strong>Total: Rp<?= totalHarga() ?></strong></p>
    <a href="bayar.php">Bayar Sekarang</a>
<?php endif; ?>
<a href="index.php">Kembali</a>
