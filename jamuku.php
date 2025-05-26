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
        if ($bahan) {
            $_SESSION['keranjang'][$id] = [
                'nama' => $bahan['nama'],
                'harga' => $bahan['harga'],
                'deskripsi' => $bahan['deskripsi'],
                'porsi' => $porsi
            ];
        }
    }
}

function hapusDariKeranjang($id) {
    if (isset($_SESSION['keranjang'][$id])) {
        unset($_SESSION['keranjang'][$id]);
    }
}

function hapusSemuaKeranjang() {
    unset($_SESSION['keranjang']);
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

// ==== Proses aksi tombol hapus semua di keranjang ====
if ($page === 'keranjang' && isset($_GET['hapus_semua'])) {
    hapusSemuaKeranjang();
    header("Location: ?page=keranjang");
    exit;
}

?>

<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #fff0f6; /* pink muda */
    color: #4b3b5c; /* dark lavender */
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 900px;
    margin: 30px auto;
    padding: 25px 30px;
    background-color: #faf8ff; /* lavender putih */
    border-radius: 15px;
    box-shadow: 0 8px 18px rgba(167, 139, 246, 0.3);
  }

  h2 {
    color: #9b59b6; /* purple lavender */
    font-weight: 700;
    margin-bottom: 25px;
    letter-spacing: 1.1px;
  }

  .button {
    display: inline-block;
    padding: 11px 20px;
    margin-top: 12px;
    background: linear-gradient(45deg, #f48fb1, #ce93d8); /* pink ke lavender gradasi */
    color: white;
    border: none;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(206, 147, 216, 0.6);
    transition: background 0.4s ease, transform 0.2s ease;
    text-decoration: none;
    user-select: none;
  }

  .button:hover {
    background: linear-gradient(45deg, #ce93d8, #f48fb1);
    transform: scale(1.05);
  }

  table {
    border-collapse: separate;
    border-spacing: 0 12px;
    width: 100%;
    margin-top: 20px;
  }

  th, td {
    padding: 14px 18px;
    text-align: left;
  }

  thead th {
    background-color: #d1c4e9; /* lavender soft */
    color: #4b3b5c;
    font-weight: 700;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    letter-spacing: 0.8px;
    user-select: none;
  }

  tbody tr {
    background-color: #fff;
    box-shadow: 0 3px 8px rgba(209, 196, 233, 0.6);
    border-radius: 12px;
    transition: box-shadow 0.3s ease;
  }

  tbody tr:hover {
    box-shadow: 0 6px 16px rgba(197, 175, 233, 0.9);
  }

  tbody td {
    vertical-align: middle;
    color: #5e548e;
    font-size: 15px;
  }

  tbody td small {
    color: #9e9e9e;
    font-style: italic;
    display: block;
    margin-top: 4px;
    font-size: 13px;
  }

  input[type=checkbox] {
    transform: scale(1.3);
    margin-right: 10px;
    cursor: pointer;
  }

  input[type=number] {
    width: 70px;
    padding: 7px 8px;
    font-size: 14px;
    border: 1.5px solid #d1c4e9;
    border-radius: 12px;
    color: #4b3b5c;
    text-align: center;
    outline-color: #ce93d8;
    transition: border-color 0.3s ease;
  }

  input[type=number]:focus {
    border-color: #9c27b0;
  }

  .aksi-link {
    color: #e91e63;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
    text-decoration: none;
  }

  .aksi-link:hover {
    text-decoration: underline;
  }

  .flex-row {
    display: flex;
    gap: 15px;
    margin-top: 15px;
    flex-wrap: wrap;
  }

</style>

<div class="container">

<?php
// ==== Halaman Index ====
if ($page === 'index') {
    $bahan = getAllBahan();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['bahan']) && isset($_POST['porsi'])) {
            $porsi = max(1, (int)$_POST['porsi']);
            foreach ($_POST['bahan'] as $id) {
                tambahKeKeranjang($id, $porsi);
            }
        }
        header('Location: ?page=keranjang');
        exit;
    }
    ?>
    <h2>Jamuku – Pilih Bahan</h2>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Pilih</th>
                    <th>Nama Bahan</th>
                    <th>Deskripsi</th>
                    <th>Harga (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bahan as $b): ?>
                <tr>
                    <td><input type="checkbox" name="bahan[]" value="<?= htmlspecialchars($b['id']) ?>"></td>
                    <td><strong><?= htmlspecialchars($b['nama']) ?></strong></td>
                    <td><small><?= htmlspecialchars($b['deskripsi']) ?></small></td>
                    <td><?= number_format($b['harga'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <label style="margin-top: 15px; display: block; font-weight: 600; color: #6a1b9a;">
            Porsi:
            <input type="number" name="porsi" value="1" min="1" required>
        </label>
        <button type="submit" class="button">Tambah ke Keranjang</button>
    </form>
    <a href="?page=keranjang" class="button" style="margin-left: 8px;">Lihat Keranjang</a>

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
        <a href="?page=index" class="button">Kembali ke Pilih Bahan</a>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th>Deskripsi</th>
                    <th>Porsi</th>
                    <th>Harga / Porsi (Rp)</th>
                    <th>Subtotal (Rp)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['keranjang'] as $id => $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['nama']) ?></strong></td>
                    <td><small><?= htmlspecialchars($item['deskripsi']) ?></small></td>
                    <td><?= $item['porsi'] ?></td>
                    <td><?= number_format($item['harga'],0,',','.') ?></td>
                    <td><?= number_format($item['harga'] * $item['porsi'],0,',','.') ?></td>
                    <td><a href="?page=keranjang&hapus=<?= htmlspecialchars($id) ?>" class="aksi-link">Hapus</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top: 15px; font-weight: 700; font-size: 18px; color: #6a1b9a;">Total: Rp<?= number_format(totalHarga(),0,',','.') ?></p>
        <div class="flex-row">
          <a href="?page=keranjang&hapus_semua=1" class="button" style="background: #f06292; box-shadow: 0 4px 14px rgba(240, 98, 146, 0.7);">Hapus Semua</a>
          <a href="?page=bayar" class="button" style="background: #ba68c8; box-shadow: 0 4px 14px rgba(186, 104, 200, 0.7);">Check Out</a>
          <a href="?page=index" class="button" style="background: #ce93d8; box-shadow: 0 4px 14px rgba(206, 147, 216, 0.7);">Kembali Pilih Bahan</a>
        </div>
    <?php endif; ?>

<?php
// ==== Halaman Bayar ====
} elseif ($page === 'bayar') {
    $total = totalHarga();
    session_destroy();
    ?>
    <h2>Terima kasih!</h2>
    <p>Total pembayaran: <strong>Rp<?= number_format($total,0,',','.') ?></strong></p>
    <a href="?page=index" class="button">Pesan Lagi</a>
<?php } ?>

</div>

