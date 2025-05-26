<?php
session_start();

// Koneksi ke database
$db = new PDO('sqlite:jamuku.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tambah ke keranjang
if (isset($_POST['tambah'])) {
    $jamu_id = $_POST['jamu_id'];
    $porsi = (int)$_POST['porsi'];
    $bahan_dipilih = isset($_POST['bahan']) ? implode(', ', $_POST['bahan']) : '-';

    if (!isset($_SESSION['keranjang'][$jamu_id])) {
        $_SESSION['keranjang'][$jamu_id] = [
            'porsi' => $porsi,
            'bahan' => $bahan_dipilih
        ];
    } else {
        $_SESSION['keranjang'][$jamu_id]['porsi'] += $porsi;
    }
}

// Hapus dari keranjang (kurangi 1 porsi)
if (isset($_GET['hapus'])) {
    $jamu_id = $_GET['hapus'];
    if (isset($_SESSION['keranjang'][$jamu_id])) {
        $_SESSION['keranjang'][$jamu_id]['porsi']--;
        if ($_SESSION['keranjang'][$jamu_id]['porsi'] <= 0) {
            unset($_SESSION['keranjang'][$jamu_id]);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jamuku</title>
    <style>
        table { border-collapse: collapse; width: 80%; margin: auto; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        .container { display: flex; justify-content: center; gap: 50px; margin-top: 30px; }
    </style>
</head>
<body>
    <h2 style="text-align:center">Daftar Jamu</h2>
    <div class="container">
        <table>
            <tr>
                <th>Nama Jamu</th>
                <th>Harga</th>
                <th>Porsi</th>
                <th>Bahan Tambahan</th>
                <th>Aksi</th>
            </tr>
            <?php
            $jamu = $db->query("SELECT * FROM jamu");
            foreach ($jamu as $j) {
            ?>
            <tr>
                <form method="POST">
                    <td><?= htmlspecialchars($j['nama']) ?></td>
                    <td>Rp <?= number_format($j['harga'], 0, ',', '.') ?></td>
                    <td>
                        <input type="number" name="porsi" value="1" min="1" required>
                    </td>
                    <td>
                        <label><input type="checkbox" name="bahan[]" value="Jahe"> Jahe</label><br>
                        <label><input type="checkbox" name="bahan[]" value="Kunyit"> Kunyit</label><br>
                        <label><input type="checkbox" name="bahan[]" value="Temulawak"> Temulawak</label>
                    </td>
                    <td>
                        <input type="hidden" name="jamu_id" value="<?= $j['id'] ?>">
                        <button type="submit" name="tambah">Tambah</button>
                    </td>
                </form>
            </tr>
            <?php } ?>
        </table>
    </div>

    <h2 style="text-align:center; margin-top:40px">Keranjang</h2>
    <div style="display: flex; justify-content: center">
        <table>
            <tr>
                <th>Nama Jamu</th>
                <th>Porsi</th>
                <th>Bahan Tambahan</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
            <?php
            $total = 0;
            if (!empty($_SESSION['keranjang'])) {
                foreach ($_SESSION['keranjang'] as $id => $item) {
                    $stmt = $db->prepare("SELECT * FROM jamu WHERE id = ?");
                    $stmt->execute([$id]);
                    $jamu = $stmt->fetch();
                    $subtotal = $jamu['harga'] * $item['porsi'];
                    $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($jamu['nama']) ?></td>
                <td><?= $item['porsi'] ?></td>
                <td><?= htmlspecialchars($item['bahan']) ?></td>
                <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                <td><a href="?hapus=<?= $id ?>">Hapus 1</a></td>
            </tr>
            <?php 
                }
            } else {
            ?>
            <tr>
                <td colspan="5">Keranjang kosong.</td>
            </tr>
            <?php } ?>
            <tr>
                <th colspan="3">Total</th>
                <th colspan="2">Rp <?= number_format($total, 0, ',', '.') ?></th>
            </tr>
        </table>
    </div>
</body>
</html>
