<?php
session_start();

// Buat dan isi database SQLite kalau belum ada
$db = new PDO("sqlite:jamu.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buat tabel bahan kalau belum ada
$db->exec("CREATE TABLE IF NOT EXISTS bahan (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nama TEXT NOT NULL,
  deskripsi TEXT NOT NULL,
  harga INTEGER NOT NULL,
  jenis TEXT NOT NULL
)");

// Cek isi tabel bahan, kalau kosong insert data
$count = $db->query("SELECT COUNT(*) FROM bahan")->fetchColumn();
if ($count == 0) {
    $data = [
        ['Kunyit','Antioksidan, antiradang, meningkatkan sistem imun, meredakan nyeri haid',1500,'Bahan utama'],
        ['Jahe','Menghangatkan tubuh, meredakan nyeri otot, meningkatkan imun, mencegah mual',1200,'Bahan utama'],
        ['Temulawak','Melindungi hati, antiinflamasi, meningkatkan nafsu makan',2000,'Bahan utama'],
        ['Kencur','Meredakan nyeri, antibakteri, melancarkan pencernaan, meningkatkan nafsu makan',1500,'Bahan utama'],
        ['Serai','Meredakan demam, melancarkan pencernaan, mengurangi stres',800,'Bahan utama'],
        ['Daun Pepaya','Meningkatkan nafsu makan, membantu pencernaan dengan enzim papain',600,'Bahan utama'],
        ['Mengkudu','Mengelola tekanan darah, pereda nyeri, memperbaiki pencernaan',2100,'Bahan utama'],
        ['Daun Beluntas','Antibakteri, detoksifikasi, menghilangkan bau badan',800,'Bahan utama'],
        ['Asam Jawa','Menurunkan suhu badan, menyegarkan, mendukung kesehatan hati',1000,'Bahan utama'],
        ['Cengkeh','Mengatasi sakit kepala, antibakteri',800,'Rempah tambahan'],
        ['Kayu Manis','Menurunkan gula darah, meningkatkan metabolisme',800,'Rempah tambahan'],
        ['Daun Pandan','Memberi aroma harum, membantu pencernaan',800,'Rempah tambahan'],
        ['Kapulaga','Melancarkan peredaran darah, meningkatkan nafsu makan',500,'Rempah tambahan'],
        ['Bunga Lawang','Memberi aroma khas, membantu pencernaan',500,'Rempah tambahan'],
        ['Daun Sirih','Antiseptik, kesehatan mulut dan organ kewanitaan',500,'Rempah tambahan'],
        ['Gula Merah','Menambah rasa manis alami, sumber energi',1000,'Pemanis'],
        ['Madu','Meningkatkan imun, mempercepat penyembuhan, menambah rasa manis',2000,'Pemanis'],
        ['Tebu','Menambah rasa manis alami, mempercepat penyembuhan',1000,'Pemanis'],
        ['Lemon','Menambah rasa segar, sumber vitamin C',1200,'Bahan tambahan'],
        ['Delima','Antioksidan, meningkatkan stamina',3400,'Bahan tambahan'],
        ['Soda','Memberi sensasi segar dan rasa modern pada jamu',1000,'Bahan tambahan'],
        ['Mint','Memberi sensasi segar, antibakteri',800,'Bahan tambahan'],
        ['Stevia','Menambah rasa manis alami, sumber energi',2000,'Pemanis'],
    ];
    $stmt = $db->prepare("INSERT INTO bahan (nama, deskripsi, harga, jenis) VALUES (?, ?, ?, ?)");
    foreach ($data as $d) {
        $stmt->execute($d);
    }
}

// Fungsi helper
function getAllBahan() {
    global $db;
    return $db->query("SELECT * FROM bahan ORDER BY jenis, nama")->fetchAll(PDO::FETCH_ASSOC);
}

function getBahanById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM bahan WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function tambahKeKeranjang($id, $porsi = 1) {
    if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];
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

// Proses aksi GET/POST
if (isset($_GET['aksi'])) {
    $aksi = $_GET['aksi'];

    if ($aksi === 'tambah' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        tambahKeKeranjang($id);
        header("Location: ?page=keranjang");
        exit;
    }
    if ($aksi === 'hapus' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        hapusDariKeranjang($id);
        header("Location: ?page=keranjang");
        exit;
    }
    if ($aksi === 'hapus_semua') {
        hapusSemuaKeranjang();
        header("Location: ?page=keranjang");
        exit;
    }
    if ($aksi === 'checkout') {
        $total = totalHarga();
        hapusSemuaKeranjang();
    }
}

// Tentukan halaman
$page = $_GET['page'] ?? 'pilih';

// CSS inline supaya gak perlu file terpisah
echo <<<CSS
<style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #fff0f6;
  color: #4b3b5c;
  margin: 0;
  padding: 0;
}
.container {
  max-width: 900px;
  margin: 30px auto;
  padding: 25px 30px;
  background-color: #faf8ff;
  border-radius: 15px;
  box-shadow: 0 8px 18px rgba(167, 139, 246, 0.3);
}
h2 {
  color: #9b59b6;
  font-weight: 700;
  margin-bottom: 25px;
  letter-spacing: 1.1px;
}
.button {
  display: inline-block;
  padding: 11px 20px;
  margin-top: 12px;
  background: linear-gradient(45deg, #f48fb1, #ce93d8);
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
  background-color: #d1c4e9;
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
CSS;

echo '<div class="container">';

if ($page === 'pilih') {
    // Halaman pilih bahan
    echo '<h2>Pilih Bahan Jamu</h2>';
    $bahanList = getAllBahan();
    echo '<table><thead><tr><th>Nama</th><th>Jenis</th><th>Deskripsi</th><th>Harga</th><th>Aksi</th></tr></thead><tbody>';
    foreach ($bahanList as $b) {
        echo '<tr>';
        echo '<td><strong>' . htmlspecialchars($b['nama']) . '</strong></td>';
        echo '<td>' . htmlspecialchars($b['jenis']) . '</td>';
        echo '<td><small>' . htmlspecialchars($b['deskripsi']) . '</small></td>';
        echo '<td>Rp' . number_format($b['harga'],0,',','.') . '</td>';
        echo '<td><a href="?aksi=tambah&id=' . $b['id'] . '" class="button">Tambah</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<a href="?page=keranjang" class="button" style="margin-top:20px;">Lihat Keranjang</a>';
}
elseif ($page === 'keranjang') {
    // Halaman keranjang
    echo '<h2>Keranjang Belanja</h2>';
    if (empty($_SESSION['keranjang'])) {
        echo '<p>Keranjang kosong.</p>';
        echo '<a href="?page=pilih" class="button">Kembali ke Pilih Bahan</a>';
    } else {
        echo '<table><thead><tr><th>Nama</th><th>Deskripsi</th><th>Porsi</th><th>Harga</th><th>Subtotal</th><th>Aksi</th></tr></thead><tbody>';
        foreach ($_SESSION['keranjang'] as $id => $item) {
            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars($item['nama']) . '</strong></td>';
            echo '<td><small>' . htmlspecialchars($item['deskripsi']) . '</small></td>';
            echo '<td>' . $item['porsi'] . '</td>';
            echo '<td>Rp' . number_format($item['harga'],0,',','.') . '</td>';
            echo '<td>Rp' . number_format($item['harga'] * $item['porsi'],0,',','.') . '</td>';
            echo '<td><a href="?page=keranjang&aksi=hapus&id=' . $id . '" class="aksi-link">Hapus</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p style="margin-top: 15px; font-weight: 700; font-size: 18px; color: #6a1b9a;">Total: Rp' . number_format(totalHarga(),0,',','.') . '</p>';
        echo '<div class="flex-row">';
        echo '<a href="?page=keranjang&aksi=hapus_semua" class="button" style="background: #f06292;">Hapus Semua</a>';
        echo '<a href="?page=checkout" class="button" style="background: #ba68c8;">Check Out</a>';
        echo '<a href="?page=pilih" class="button">Kembali Pilih Bahan</a>';
        echo '</div>';
    }
}
elseif ($page === 'checkout') {
    // Halaman checkout
    if (totalHarga() == 0) {
        echo '<p>Keranjang kosong, tidak bisa checkout.</p>';
        echo '<a href="?page=pilih" class="button">Kembali Pilih Bahan</a>';
    } else {
        $total = totalHarga();
        hapusSemuaKeranjang();
        echo '<h2>Terima kasih!</h2>';
        echo '<p>Total pembayaran: <strong>Rp' . number_format($total,0,',','.') . '</strong></p>';
        echo '<a href="?page=pilih" class="button">Pesan Lagi</a>';
    }
}
else {
    echo '<p>Halaman tidak ditemukan.</p>';
    echo '<a href="?page=pilih" class="button">Kembali ke Pilih Bahan</a>';
}

echo '</div>';
