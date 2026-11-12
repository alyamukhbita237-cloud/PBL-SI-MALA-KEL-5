
<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/koneksi.php';

$id_user = $_SESSION['id_user'];
$id_pesanan = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_pesanan) {
    header('Location: pesanan.php');
    exit;
}




// Ambil data pesanan milik pelanggan yang sedang login
$query = "SELECT
            id_pesanan,
            id_user,
            jenis_pesanan,
            tgl_masuk,
            estimasi_selesai,
            alamat_antar_jemput,
            status_pesanan,
            waktu_update_status,
            total_harga
          FROM pesanan
          WHERE id_pesanan = :id_pesanan
          AND id_user = :id_user";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'id_pesanan' => $id_pesanan,
    'id_user' => $id_user
]);


$pesanan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pesanan) {
    die("Pesanan tidak ditemukan.");
}

$waktu_status = !empty($pesanan['waktu_update_status'])
    ? date('d M Y, H.i', strtotime($pesanan['waktu_update_status']))
    : '-';

// Ambil detail pesanan
$queryDetail = "SELECT
                    l.jenis_layanan,
                    l.harga_per_layanan,
                    l.satuan,
                    dp.jumlah,
                    dp.subtotal
                FROM detail_pesanan dp
                JOIN layanan l
                    ON dp.id_layanan = l.id_layanan
                WHERE dp.id_pesanan = :id_pesanan";

$stmtDetail = $pdo->prepare($queryDetail);
$stmtDetail->execute(['id_pesanan' => $id_pesanan]);
$detail_layanan = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

// Format tanggal
$formatTanggal = function ($tanggal) {
    if (empty($tanggal)) {
        return '-';
    }

    return date('d M Y, H.i', strtotime($tanggal));
};

// Format rupiah
$formatRupiah = function ($angka) {
    return 'Rp' . number_format((float)$angka, 0, ',', '.');
};

// Status pesanan
$status = strtolower($pesanan['status_pesanan']);

$status_label = [
    'menunggu' => 'Menunggu',
    'dijemput' => 'Dijemput',
    'diproses' => 'Diproses',
    'selesai' => 'Selesai',
    'diantar' => 'Diantar',
    'diambil' => 'Diambil'
];

$status_text = $status_label[$status] ?? ucfirst($status);

// Menentukan posisi prjenisogress
$jenis_pesanan = strtolower(trim($pesanan['jenis_pesanan']));

$is_antar_jemput = ($jenis_pesanan === 'antar-jemput');

if ($is_antar_jemput) {

    //antar jemput
    $steps = [
        'Menunggu',
        'Dijemput',
        'Diproses',
        'Selesai',
        'Diantar'
    ];

    $progress_status = [
        'menunggu' => 0,
        'dijemput' => 1,
        'diproses' => 2,
        'selesai' => 3,
        'diantar' => 4
    ];

} else {

    // walk in
    $steps = [
        'Menunggu',
        'Diproses',
        'Selesai',
        'Diambil'
    ];

    $progress_status = [
        'menunggu' => 0,
        'diproses' => 1,
        'selesai' => 2,
        'diambil' => 3
    ];
}
$active_step = $progress_status[$status] ?? 0;

$page_title = "Progress Pesanan";
$active_page = "pesanan";

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">

    <div class="detail-page-header">
        <a href="pesanan.php" class="detail-back">
            <i data-lucide="chevron-left"></i>
        </a>

        <div>
            <h1>Progress Pesanan</h1>
            <p>Pantau pesanan dan aktivitas laundry kamu</p>
        </div>
    </div>

    <div class="detail-container">

        <!-- PROGRESS PESANAN -->
        <section class="detail-card">

            <h2>
                Kode Pesanan:
                #LD-<?= str_pad($pesanan['id_pesanan'], 3, '0', STR_PAD_LEFT) ?>
            </h2>

            <div class="progress-timeline">

                <?php foreach ($steps as $index => $step): ?>

                    <?php
                    $step_class = '';

                    if ($index < $active_step) {
                        $step_class = 'completed';
                    } elseif ($index === $active_step) {
                        $step_class = 'current';
                    }
                    ?>

                    <div class="progress-step <?= $step_class ?>">

                        <div class="progress-circle">
                            <?php if ($index < $active_step): ?>
                                <i data-lucide="check"></i>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                        </div>

                        <h3><?= htmlspecialchars($step) ?></h3>

                        <?php if ($index === $active_step): ?>
                            <small><?= htmlspecialchars($waktu_status)?></small>
                       
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <div class="detail-current-status">
                Status pesanan:
                <strong><?= htmlspecialchars($status_text) ?></strong>
            </div>

        </section>


        <!-- RINCIAN LAYANAN -->
        <section class="detail-card">

            <div class="detail-card-heading">
                <h2>Rincian Layanan</h2>
            </div>

            <div class="detail-service-list">

                <?php if (count($detail_layanan) > 0): ?>

                    <?php foreach ($detail_layanan as $item): ?>

                        <div class="detail-service-item">

                            <div class="detail-service-info">
                                <h3>
                                    <?= htmlspecialchars($item['jenis_layanan']) ?>
                                </h3>

                                <p>
                                    <?= rtrim(rtrim(number_format((float)$item['jumlah'], 2, '.', ''), '0'), '.') ?>
                                    <?= htmlspecialchars($item['satuan']) ?>
                                    x
                                    <?= $formatRupiah($item['harga_per_layanan']) ?>
                                </p>
                            </div>

                            <strong>
                                <?= $formatRupiah($item['subtotal']) ?>
                            </strong>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>Belum ada rincian layanan.</p>

                <?php endif; ?>

            </div>

            <div class="detail-total">
                <h3>Total Bayar</h3>
                <strong>
                    <?= $formatRupiah($pesanan['total_harga']) ?>
                </strong>
            </div>

        </section>


        <!-- INFORMASI PENGANTARAN -->
        <section class="detail-card">

            <h2>Informasi Pengantaran</h2>

            <div class="detail-info-list">

                <div class="detail-info-item">
                    <span>Alamat</span>

                    <strong>
                        <?= htmlspecialchars($pesanan['alamat_antar_jemput'] ?: '-') ?>
                    </strong>
                </div>

                <div class="detail-info-item">
                    <span>Jadwal Jemput</span>

                    <strong>
                        <?= $formatTanggal($pesanan['tgl_masuk']) ?>
                    </strong>
                </div>

                <div class="detail-info-item">
                    <span>Estimasi Selesai</span>

                    <strong>
                        <?= $formatTanggal($pesanan['estimasi_selesai']) ?>
                    </strong>
                </div>

            </div>

        </section>

    </div>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>
