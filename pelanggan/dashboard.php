<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/koneksi.php';
$id_user = $_SESSION['id_user'];
$sql = "SELECT p.id_pesanan, p.jenis_pesanan, p.tgl_masuk, p.status_pesanan, p.total_harga,
                l.jenis_layanan, dp.jumlah, l.satuan
        FROM pesanan p
        LEFT JOIN detail_pesanan dp on p.id_pesanan = dp.id_pesanan
        LEFT JOIN layanan l on dp.id_layanan = l.id_layanan
        WHERE p.id_user = :id_user ORDER BY p.tgl_masuk DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id_user' => $id_user]);
$semua_pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pesanan_berjalan = [];
$pesanan_sebelumnya = [];

foreach ($semua_pesanan as $p) {
    if (in_array($p['status_pesanan'], ['Selesai', 'Dibatalkan'])) {
        $pesanan_sebelumnya[] = $p;
    } else {
        $pesanan_berjalan[] = $p;
    }
}

function formatIdPesanan($id_user) {
    return "LD-" . str_pad($id_user, 3, '0', STR_PAD_LEFT);
}

function formatTanggal($tanggal) {
    if (!$tanggal) {
        return '-';
    }
    return date('d-m-y', strtotime($tanggal));
}

$page_title = "Dashboard";
$active_page = "dashboard";
$nama = $_SESSION['nama'] ?? 'Pelanggan';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">

<!-- hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Halo, <?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8')?>!</h1>
            <p>Kelola kebutuhan laundry kamu dengan mudah</p>
            <div class="hero-buttons">  
                <a href="antar_jemput.php">+ &nbsp; Pesan Layanan</a>
                <a href="pesanan.php">Lihat Pesananmu</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="images/laundry-image.png" alt="laundry">
        </div>
    </section>

<!-- pesanan berjalan -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>Pesanan yang sedang berjalan</h2>
            <a href="pesanan.php">Lihat semua -></a>

        </div>

        <?php if (empty($pesanan_berjalan)): ?>
            <p>Belum ada pesanan yang sedang berjalan</p>
        <?php else: ?>
            <?php foreach (array_slice($pesanan_berjalan, 0, 1) as $p): ?>
                <?php 
                    $status = strtolower(trim($p['status_pesanan'] ?? ''));
                    $jenis_pesanan = strtolower(trim($p['jenis_pesanan'] ?? ''));

                    if ($jenis_pesanan === 'antar-jemput') {
                        $progress_status = [
                            'menunggu' => 1,
                            'dijemput' => 2,
                            'diproses' => 3,
                            'selesai' => 4,
                            'diantar' => 5
                        ];
                    } else {
                        $progress_status = [
                            'menunggu' => 1,
                            'diproses' => 2,
                            'selesai' => 3,
                            'diambil' => 4
                        ];
                    }

                    $tahap = $progress_status[$status] ?? 1;
        ?>
        <div class="order-card">
            <div class="order-top">
                <div class="order-info">
                    <h3>Pesanan #<?= htmlspecialchars(formatIdPesanan($p['id_pesanan'])) ?></h3>
                    <p>
                        <?= htmlspecialchars($p['jenis_layanan'] ?? 'Layanan belum tersedia') ?>
                        &nbsp;•&nbsp; 
                        <?= htmlspecialchars($p['jumlah'] ?? '0') ?>
                        <?= htmlspecialchars($p['satuan'] ?? '') ?>
                    </p>
                    <p>
                        Status: <?= htmlspecialchars($p['status_pesanan'] ?? '-') ?>
                    </p>
                </div>

            <div class="payment">
                <span>Total Bayar: </span>
                <strong>Rp<?= number_format((float) $p['total_harga'], 0, ',', '.') ?></strong>
            </div>
        </div>
            

            <!-- progress -->

            <!-- progress -->

            <div class="progress-wrapper">

                <?php
                    // Menentukan tahapan sesuai jenis pesanan
                    if ($jenis_pesanan === 'antar-jemput') {
                        $tahapan = [
                            'Menunggu',
                            'Dijemput',
                            'Diproses',
                            'Selesai',
                            'Diantar'
                        ];
                    } else {
                        $tahapan = [
                            'Menunggu',
                            'Diproses',
                            'Selesai',
                            'Diambil'
                        ];
                    }

                    // Menghitung persentase garis progress
                    $total_tahapan = count($tahapan);
                    $persentase = (($tahap - 1) / ($total_tahapan - 1)) * 100;
                ?>

                <!-- GARIS PROGRESS -->
                <div class="progress-line">
                    <div class="progress-line-active"
                        style="width: <?= $persentase ?>%;">
                    </div>
                </div>

                <!-- TAHAPAN PROGRESS -->
                <?php foreach ($tahapan as $i => $label): ?>

                    <?php
                        $nomor = $i + 1;
                        $kelas = '';

                        if ($nomor < $tahap) {
                            $kelas = 'completed';
                        } elseif ($nomor === $tahap) {
                            $kelas = 'current';
                        }
                    ?>

                    <div class="progress-step <?= $kelas ?>">

                        <div class="step-circle">
                            <?php if ($nomor < $tahap): ?>
                                <i data-lucide="circle-check"></i>
                            <?php elseif ($nomor === $tahap): ?>
                                <i data-lucide="loader-circle"></i>
                            <?php else: ?>
                                <i data-lucide="circle"></i>
                            <?php endif; ?>
                        </div>

                        <span><?= htmlspecialchars($label) ?></span>

                    </div>

                <?php endforeach; ?>

            </div>

            <!-- lihat detail progress -->
            <div class="order-detail">
                <a href="progress.php?id=<?= urlencode($p['id_pesanan']) ?>">
                    Lihat Detail Progress -> 
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <div class="section-header">
            <h2>Pesanan Sebelumnya</h2>
            <a href="pesanan.php">Lihat Semua -></a>
        </div>

        <div class="history-card">
            <?php if (empty($pesanan_sebelumnya)): ?>
                <p>Belum ada riwayat pesanan</p>
            <?php else: ?>
                <?php foreach (array_slice($pesanan_sebelumnya, 0, 3) as $i => $p): ?>
                    <?php if ($i > 0): ?>
                        <div class="history-divider"></div>
                    <?php endif; ?>
            <!-- pesanan 1 -->
            <div class="history-item">
                <div class="history-info">
                    <h3>#<?= htmlspecialchars(formatIdPesanan($p['id_pesanan'])) ?></h3>
                    <p><?= htmlspecialchars($p['jenis_layanan'] ?? 'Layanan belum tersedia') ?>
                         • 
                        <?= htmlspecialchars($p['jumlah'] ?? 0) ?>
                        <?= htmlspecialchars($p['satuan'] ?? '') ?> 
                    </p>
                    <small><?= htmlspecialchars(formatTanggal($p['tgl_masuk'])) ?></small>
                </div>
                <div class="history-price">
                    Rp<?= number_format((float) $p['total_harga'], 0, ',', '.') ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<script>
    lucide.createIcons();
</script>
