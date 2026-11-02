<?php

session_start();


// ==========================================
// CEK LOGIN
// ==========================================

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}


// ==========================================
// KONEKSI DATABASE
// ==========================================

require_once '../../includes/koneksi.php';


// ==========================================
// AMBIL DATA DARI FORM
// ==========================================

$id_user = $_SESSION['id_user'];

$tanggal = $_POST['tanggal'] ?? '';
$alamat = trim($_POST['alamat'] ?? '');

$layanan_ids = $_POST['layanan'] ?? [];


// ==========================================
// VALIDASI
// ==========================================

if (empty($tanggal)) {
    die('Tanggal penjemputan belum dipilih.');
}

if (empty($alamat)) {
    die('Alamat belum diisi.');
}

if (empty($layanan_ids)) {
    die('Belum ada layanan yang dipilih.');
}


// ==========================================
// MULAI TRANSACTION
// ==========================================

try {

    $pdo->beginTransaction();


    // ======================================
    // AMBIL ESTIMASI LAYANAN
    // ======================================

    /*
     * Kalau pelanggan memilih beberapa layanan,
     * kita ambil estimasi paling lama.
     */

    $placeholders = implode(
        ',',
        array_fill(0, count($layanan_ids), '?')
    );


    $queryEstimasi = "SELECT MAX(estimasi_hari)
        FROM layanan
        WHERE id_layanan IN ($placeholders)
    ";

    $stmtEstimasi = $pdo->prepare($queryEstimasi);

    $stmtEstimasi->execute($layanan_ids);

    $estimasi_hari = $stmtEstimasi->fetchColumn();


    if ($estimasi_hari === false || $estimasi_hari === null) {
        $estimasi_hari = 0;
    }


    // ======================================
    // HITUNG ESTIMASI SELESAI
    // ======================================

    $tanggal_masuk = new DateTime($tanggal);

    $tanggal_masuk->modify("+{$estimasi_hari} days");

    $estimasi_selesai = $tanggal_masuk->format('Y-m-d');


    // ======================================
    // INSERT KE TABEL PESANAN
    // ======================================

    $queryPesanan = "INSERT INTO pesanan (
            id_user,
            jenis_pesanan,
            tgl_masuk,
            estimasi_selesai,
            alamat_antar_jemput,
            status_pesanan,
            waktu_update_status,
            total_harga
        )
        VALUES (
            :id_user,
            :jenis_pesanan,
            :tgl_masuk,
            :estimasi_selesai,
            :alamat_antar_jemput,
            :status_pesanan,
            NOW(),
            :total_harga
        ) RETURNING id_pesanan
    ";


    $stmtPesanan = $pdo->prepare($queryPesanan);


    $stmtPesanan->execute([
        'id_user' => $id_user,
        'jenis_pesanan' => 'Antar-Jemput',
        'tgl_masuk' => $tanggal,
        'estimasi_selesai' => $estimasi_selesai,
        'alamat_antar_jemput' => $alamat,
        'status_pesanan' => 'Menunggu Konfirmasi',
        'total_harga' => 0
    ]);


    // Ambil ID pesanan yang baru dibuat
    $id_pesanan = $stmtPesanan->fetchColumn();


    // ======================================
    // INSERT KE DETAIL PESANAN
    // ======================================

    $queryDetail = "INSERT INTO detail_pesanan (
            id_pesanan,
            id_layanan,
            jumlah,
            subtotal
        )
        VALUES (
            :id_pesanan,
            :id_layanan,
            :jumlah,
            :subtotal
        )
    ";


    $stmtDetail = $pdo->prepare($queryDetail);


    foreach ($layanan_ids as $id_layanan) {

        $stmtDetail->execute([
            'id_pesanan' => $id_pesanan,
            'id_layanan' => $id_layanan,
            'jumlah' => 0,
            'subtotal' => 0
        ]);

    }


    // ======================================
    // SIMPAN PERUBAHAN
    // ======================================

    $pdo->commit();


    // ======================================
    // REDIRECT KE PESANAN
    // ======================================

    header('Location: ../pesanan.php');
    exit;


} catch (PDOException $e) {

    // Batalkan transaksi kalau error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    echo "Pesanan gagal disimpan.";
    echo "<br><br>";
    echo "Error: " . htmlspecialchars($e->getMessage());

}