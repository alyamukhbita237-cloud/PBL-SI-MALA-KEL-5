<?php

require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah-pesanan.php');
    exit;
}

// mengambil data dari form
try {
    $idUser = filter_input(INPUT_POST, 'pelanggan', FILTER_VALIDATE_INT);
    $jenisPesanan = $_POST['jenis_pesanan'] ?? 'Walk-in';
    $layananJson = $_POST['layanan_json'] ?? '';
    $waktuBayar = $_POST['waktu_bayar'] ?? '';
    $metodeBayar = $_POST['metode_bayar'] ?? '';

    if (!$idUser) {
        throw new Exception('Silakan pilih pelanggan.');
    }

    if (!in_array($jenisPesanan, ['Walk-in', 'Antar-Jemput'], true)) {
        throw new Exception('Jenis pesanan tidak valid.');
    }

    if (!in_array($waktuBayar, ['sekarang', 'nanti'], true)) {
        throw new Exception('Pilihan waktu pembayaran tidak valid.');
    }

    if (!in_array($metodeBayar, ['Tunai', 'QRIS', 'Transfer Bank'], true)) {
        throw new Exception('Metode pembayaran tidak valid.');
    }

    $layananDipilih = json_decode($layananJson, true);

    if (!is_array($layananDipilih) || count($layananDipilih) === 0) {
        throw new Exception('Silakan pilih minimal satu layanan.');
    }

    $pdo->beginTransaction();

    // Memastikan pelanggan tersedia
    $queryPelanggan = "SELECT id_user, alamat
                       FROM users
                       WHERE id_user = :id_user
                       AND LOWER(role) = 'pelanggan'";

    $stmtPelanggan = $pdo->prepare($queryPelanggan);
    $stmtPelanggan->execute(['id_user' => $idUser]);
    $dataPelanggan = $stmtPelanggan->fetch(PDO::FETCH_ASSOC);

    if (!$dataPelanggan) {
        throw new Exception('Data pelanggan tidak ditemukan.');
    }

    $alamat = null;

    if ($jenisPesanan === 'Antar-Jemput') {
        $alamat = trim($dataPelanggan['alamat'] ?? '');

        if ($alamat === '') {
            throw new Exception('Alamat pelanggan belum tersedia.');
        }
    }

    // Mengambil harga dan estimasi layanan dari database
    $totalHarga = 0;
    $estimasiHari = 0;
    $detailLayanan = [];

    $queryLayanan = "SELECT id_layanan, harga_per_layanan, estimasi_hari
                     FROM layanan
                     WHERE id_layanan = :id_layanan";

    $stmtLayanan = $pdo->prepare($queryLayanan);

    foreach ($layananDipilih as $item) {
        $idLayanan = filter_var($item['id_layanan'] ?? null, FILTER_VALIDATE_INT);
        $jumlah = filter_var($item['jumlah'] ?? null, FILTER_VALIDATE_INT);
        if (!$idLayanan || !$jumlah || $jumlah < 1) {
            throw new Exception('Data layanan atau jumlah tidak valid.');
        }

        $stmtLayanan->execute(['id_layanan' => $idLayanan]);
        $dataLayanan = $stmtLayanan->fetch(PDO::FETCH_ASSOC);
        
        if (!$dataLayanan) {
            throw new Exception('Salah satu layanan tidak ditemukan.');
        }

        $harga = (float) $dataLayanan['harga_per_layanan'];
        $subtotal = $harga * $jumlah;

        $totalHarga += $subtotal;
        $estimasiHari = max($estimasiHari, (int) $dataLayanan['estimasi_hari']);

        $detailLayanan[] = [
            'id_layanan' => $idLayanan,
            'jumlah' => $jumlah,
            'subtotal' => $subtotal
        ];
    }

    // Menghitung tanggal estimasi selesai
    $tanggalMasuk = new DateTime('today');
    $tanggalEstimasi = clone $tanggalMasuk;
    $tanggalEstimasi->modify('+' . $estimasiHari . ' days');

    // Menyimpan pesanan
    $queryPesanan = "INSERT INTO pesanan (id_user, jenis_pesanan, tgl_masuk, estimasi_selesai, alamat_antar_jemput, status_pesanan, waktu_update_status, total_harga) VALUES (:id_user, :jenis_pesanan, :tgl_masuk, :estimasi_selesai, :alamat, :status_pesanan, CURRENT_TIMESTAMP, :total_harga) RETURNING id_pesanan";

    $stmtPesanan = $pdo->prepare($queryPesanan);
    $stmtPesanan->execute([
        'id_user' => $idUser,
        'jenis_pesanan' => $jenisPesanan,
        'tgl_masuk' => $tanggalMasuk->format('Y-m-d'),
        'estimasi_selesai' => $tanggalEstimasi->format('Y-m-d'),
        'alamat' => $alamat,
        'status_pesanan' => 'Menunggu',
        'total_harga' => $totalHarga
    ]);

    $idPesanan = $stmtPesanan->fetchColumn();

    // Menyimpan detail layanan pesanan
    $queryDetail = "INSERT INTO detail_pesanan (id_pesanan, id_layanan, jumlah, subtotal) VALUES (:id_pesanan, :id_layanan, :jumlah, :subtotal)";

    $stmtDetail = $pdo->prepare($queryDetail);

    foreach ($detailLayanan as $detail) {
        $stmtDetail->execute([
            'id_pesanan' => $idPesanan,
            'id_layanan' => $detail['id_layanan'],
            'jumlah' => $detail['jumlah'],
            'subtotal' => $detail['subtotal']
        ]);
    }

    // Menyimpan pembayaran
    $statusPembayaran = ($waktuBayar === 'sekarang') ? 'Lunas' : 'Belum Lunas';
    $jumlahBayar = ($waktuBayar === 'sekarang') ? $totalHarga : 0;
    $tanggalPembayaran = ($waktuBayar === 'sekarang') ? date('Y-m-d') : null;

    $queryPembayaran = "INSERT INTO pembayaran (id_pesanan, metode_pembayaran, status_pembayaran, tgl_pembayaran, jml_bayar) VALUES (:id_pesanan, :metode_pembayaran, :status_pembayaran, :tgl_pembayaran, :jml_bayar)";

    $stmtPembayaran = $pdo->prepare($queryPembayaran);
    $stmtPembayaran->execute([
        'id_pesanan' => $idPesanan,
        'metode_pembayaran' => $metodeBayar,
        'status_pembayaran' => $statusPembayaran,
        'tgl_pembayaran' => $tanggalPembayaran,
        'jml_bayar' => $jumlahBayar
    ]);

    $pdo->commit();

    header('Location: kelola-pesanan.php?berhasil=1');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Gagal menyimpan pesanan: ' . htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    ));
}