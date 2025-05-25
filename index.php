<?php
require_once '../src/fungsi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['bahan'] as $id) {
        tambahKeKeranjang($id, $_POST['porsi']);
    }
    header('Location: keranjang.php');
}
$bahan = getAllBahan();
?>

<h2>Jamuku – Pilih Bahan</h2>
<form method="POST">
    <?php foreach ($bahan as $b): ?>
        <label>
            <input type="checkbox" name="bahan[]" value="<?= $b['id'] ?>"> <?= $b['nama'] ?> (Rp<?= $b['harga'] ?>)
        </label><br>
    <?php endforeach; ?>
    <label>Porsi:
        <input type="number" name="porsi" value="1" min="1">
    </label><br>
    <button type="submit">Tambah ke Keranjang</button>
</form>
<a href="keranjang.php">Lihat Keranjang</a>
