<?php

require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah-pesanan.php');
    exit;
}

try {
    $idUser = $_POST['pelanggan'] ?? '';
    $jenisPesanan = $_POST['jenis_pesanan'] ?? '';
    $layananJson = $_POST['layanan_json'] ?? '';
    $waktuBayar = $_POST['waktu_bayar'] ?? '';
    $metodeBayar = $_POST['metode_bayar'] ?? '';

    if ($idUser == '') {
        throw new Exception('Silakan pilih pelanggan.');
    }

    if (!in_array($jenisPesanan, ['Walk-in', 'Antar-Jemput'])) {
        throw new Exception('Jenis pesanan tidak valid.');
    }

    if (!in_array($waktuBayar, ['sekarang', 'nanti'])) {
        throw new Exception('Pilihan pembayaran tidak valid.');
    }

    if (!in_array($metodeBayar, ['Tunai', 'QRIS', 'Transfer Bank'])) {
        throw new Exception('Metode pembayaran tidak valid.');
    }

    $layananDipilih = json_decode($layananJson, true);

    if (!is_array($layananDipilih) || empty($layananDipilih)) {
        throw new Exception('Silakan pilih minimal satu layanan.');
    }

    $pdo->beginTransaction();

    $sql = "SELECT id_user, alamat
            FROM users
            WHERE id_user = :id_user
            AND LOWER(role) = 'pelanggan'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_user' => $idUser]);
    $pelanggan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pelanggan) {
        throw new Exception('Data pelanggan tidak ditemukan.');
    }

    $alamat = null;

    if ($jenisPesanan == 'Antar-Jemput') {
        $alamat = trim($pelanggan['alamat'] ?? '');
        if ($alamat == '') {
            throw new Exception('Alamat pelanggan belum tersedia.');
        }
    }

    $totalHarga = 0;
    $estimasiHari = 0;
    $detailLayanan = [];

    foreach ($layananDipilih as $item) {
        $idLayanan = $item['id_layanan'] ?? 0;
        $jumlah = $item['jumlah'] ?? 0;

        if (!filter_var($idLayanan, FILTER_VALIDATE_INT) ||
            !filter_var($jumlah, FILTER_VALIDATE_INT) ||
            $jumlah < 1) {
            throw new Exception('Data layanan atau jumlah tidak valid.');
        }

        $sql = "SELECT harga_per_layanan, estimasi_hari
                FROM layanan
                WHERE id_layanan = :id_layanan";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_layanan' => $idLayanan]);
        $layanan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$layanan) {
            throw new Exception('Layanan tidak ditemukan.');
        }

        $harga = (float) $layanan['harga_per_layanan'];
        $subtotal = $harga * $jumlah;
        $totalHarga += $subtotal;

        if ($layanan['estimasi_hari'] > $estimasiHari) {
            $estimasiHari = (int) $layanan['estimasi_hari'];
        }

        $detailLayanan[] = [
            'id_layanan' => $idLayanan,
            'jumlah' => $jumlah,
            'subtotal' => $subtotal
        ];
    }

    $tanggalMasuk = date('Y-m-d');
    $tanggalEstimasi = date('Y-m-d', strtotime('+' . $estimasiHari . ' days'));

    $sql = "INSERT INTO pesanan
            (id_user, jenis_pesanan, tgl_masuk, estimasi_selesai,
             alamat_antar_jemput, status_pesanan, waktu_update_status, total_harga)
            VALUES
            (:id_user, :jenis_pesanan, :tgl_masuk, :estimasi_selesai,
             :alamat, :status_pesanan, CURRENT_TIMESTAMP, :total_harga)
            RETURNING id_pesanan";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_user' => $idUser,
        'jenis_pesanan' => $jenisPesanan,
        'tgl_masuk' => $tanggalMasuk,
        'estimasi_selesai' => $tanggalEstimasi,
        'alamat' => $alamat,
        'status_pesanan' => 'Menunggu',
        'total_harga' => $totalHarga
    ]);

    $idPesanan = $stmt->fetchColumn();

    $sql = "INSERT INTO detail_pesanan
            (id_pesanan, id_layanan, jumlah, subtotal)
            VALUES
            (:id_pesanan, :id_layanan, :jumlah, :subtotal)";

    $stmt = $pdo->prepare($sql);

    foreach ($detailLayanan as $detail) {
        $stmt->execute([
            'id_pesanan' => $idPesanan,
            'id_layanan' => $detail['id_layanan'],
            'jumlah' => $detail['jumlah'],
            'subtotal' => $detail['subtotal']
        ]);
    }

    if ($waktuBayar == 'sekarang') {
        $statusPembayaran = 'Lunas';
        $jumlahBayar = $totalHarga;
        $tanggalPembayaran = date('Y-m-d');
    } else {
        $statusPembayaran = 'Belum Lunas';
        $jumlahBayar = 0;
        $tanggalPembayaran = null;
    }

    $sql = "INSERT INTO pembayaran
            (id_pesanan, metode_pembayaran, status_pembayaran, tgl_pembayaran, jml_bayar)
            VALUES
            (:id_pesanan, :metode_pembayaran, :status_pembayaran, :tgl_pembayaran, :jml_bayar)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_pesanan' => $idPesanan,
        'metode_pembayaran' => $metodeBayar,
        'status_pembayaran' => $statusPembayaran,
        'tgl_pembayaran' => $tanggalPembayaran,
        'jml_bayar' => $jumlahBayar
    ]);

    $pdo->commit();

    header('Location: kelola-pesanan.php?berhasil=1');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Gagal menyimpan pesanan: ' . htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    ));
}