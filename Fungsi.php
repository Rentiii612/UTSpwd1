<?php
session_start();
$db = new PDO("sqlite:../db/jamu.db");

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
?>
