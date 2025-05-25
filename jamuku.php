<?php
session_start();
$db = new PDO("sqlite:db/jamu.db");

// ==== Fungsi ====
function getAllBahan() {
    global $db;
    return $db->query("SELECT * FROM bahan")->fetchAll(PDO::FETCH_ASSOC);
}

function getBahanById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM bahan WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function tambahKeKeranjang($id, $porsi = 1) {
    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }
    if (isset($_SESSION['keranjang'][$id])) {
        $_SESSION['keranjang'][$id]['porsi'] += $porsi;
    } else {
        $bahan = getBahanById($id);
        $_SESSION['keranjang'][$id] = [
            'nama' => $bahan['nama'],
            'harga' => $bahan['harga'],
            'porsi' => $porsi
        ];
    }
}

function hapusDariKeranjang($id) {
    unset($_SESSION['keranjang'][$id]);
}

function totalHarga() {
    $total = 0;
    if (isset($_SESSION['keranjang'])) {
        foreach ($_SESSION['keranjang'] as $item) {
            $total += $item['harga'] * $item['porsi'];
        }
    }
    return $total;
}

// ==== Routing ====
$page = $_GET['page'] ?? 'index';

// ==== Style ====
?>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f7f9fb;
    color: #333;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  h2 {
    color: #2c3e50;
  }

  .button {
    display: inline-block;
    padding: 10px 15px;
    margin-top: 10px;
    background-color: #90caf9;
    color: #fff;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }

  .button:hover {
    background-color: #64b5f6;
  }
</style>

<div class="container">
<?php
// ==== Halaman Index ====
if ($page === 'index') {
    $bahan = getAllBahan();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST['bahan'] as $id) {
            tambahKeKeranjang($id, $_POST['porsi']);
        }
        header('Location: ?page=keranjang');
        exit;
    }
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
        <button type="submit" class="button">Tambah ke Keranjang</button>
    </form>
    <a href="?page=keranjang" class="button">Lihat Keranjang</a>

<?php
// ==== Halaman Keranjang ====
} elseif ($page === 'keranjang') {
    if (isset($_GET['hapus'])) {
        hapusDariKeranjang($_GET['hapus']);
        header("Location: ?page=keranjang");
        exit;
    }
    ?>
    <h2>Keranjang Belanja</h2>
    <?php if (empty($_SESSION['keranjang'])): ?>
        <p>Keranjang kosong.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($_SESSION['keranjang'] as $id => $item): ?>
            <li><?= $item['nama'] ?> – <?= $item['porsi'] ?> porsi – Rp<?= $item['harga'] * $item['porsi'] ?>
                <a href="?page=keranjang&hapus=<?= $id ?>">[hapus]</a>
            </li>
        <?php endforeach; ?>
        </ul>
        <p><strong>Total: Rp<?= totalHarga() ?></strong></p>
        <a href="?page=bayar" class="button">Bayar Sekarang</a>
    <?php endif; ?>
    <br><a href="?page=index" class="button">Kembali</a>

<?php
// ==== Halaman Bayar ====
} elseif ($page === 'bayar') {
    $total = totalHarga();
    session_destroy();
    ?>
    <h2>Terima kasih!</h2>
    <p>Total pembayaran: <strong>Rp<?= $total ?></strong></p>
    <a href="?page=index" class="button">Pesan Lagi</a>
<?php } ?>
</div>
