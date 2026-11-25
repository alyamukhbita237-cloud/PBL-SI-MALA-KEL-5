<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

date_default_timezone_set('Asia/Jakarta');
require_once '../includes/koneksi.php';
$nama = $_SESSION['nama_user'] ?? 'Admin';
$page_title = "Dashboard";
$active_page = "dashboard";

include 'includes/header.php';
include 'includes/sidebar.php';

$sqlDashboard = "WITH dashboard AS (
    SELECT COUNT(p.id_pesanan) AS total_pesanan, 
        COUNT(p.id_pesanan) FILTER (
            WHERE p.status_pesanan ILIKE '%menunggu%') AS perlu_konfirmasi,
        COUNT(p.id_pesanan) FILTER (
            WHERE p.status_pesanan ILIKE '%diproses%'
            OR p.status_pesanan ILIKE '%dijemput%') AS sedang_dikerjakan,
        COUNT(p.id_pesanan) FILTER (
            WHERE p.status_pesanan ILIKE '%selesai%'
            OR p.status_pesanan ILIKE '%diambil'
            OR p.status_pesanan ILIKE '%diantar%') AS siap_diambil,
        COUNT(p.id_pesanan) FILTER (
            WHERE p.tgl_masuk::date = CURRENT_DATE) AS pesanan_hari_ini,
        COALESCE(SUM(p.total_harga) FILTER (
            WHERE p.tgl_masuk::date = CURRENT_DATE), 0) AS total_hari_ini
    FROM pesanan p)
    SELECT * FROM dashboard";

$stmtDashboard = $pdo->query($sqlDashboard);
$statistik = $stmtDashboard->fetch(PDO::FETCH_ASSOC);

$sqlPesanan = "WITH data_pesanan AS (
    SELECT p.id_pesanan, u.nama_user, p.status_pesanan AS status, p.jenis_pesanan,
        STRING_AGG(DISTINCT l.jenis_layanan, ', ') AS layanan
    FROM users u
    LEFT JOIN pesanan p ON p.id_user = u.id_user
    LEFT JOIN detail_pesanan dp ON dp.id_pesanan = p.id_pesanan
    LEFT JOIN layanan l ON l.id_layanan = dp.id_layanan
    GROUP BY p.id_pesanan, u.nama_user, p.status_pesanan, p.jenis_pesanan)
    SELECT * FROM data_pesanan dt ORDER BY dt.id_pesanan ASC";

$stmtPesanan = $pdo->query($sqlPesanan);
$pesanan = $stmtPesanan->fetchAll(PDO::FETCH_ASSOC);
// ========================================
// FUNGSI BANTU
// ========================================

function kodePesanan($id) {
    return 'LD-' . str_pad($id, 3, '0', STR_PAD_LEFT);
}

function cekStatus($status, $kata) {
    return stripos((string) $status, $kata) !== false;
}

function rupiah($angka) {
    return 'Rp' . number_format((float) $angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    if (empty($tanggal)) {
        return '-';
    }

    return date('d M Y, H.i', strtotime($tanggal));
}

function warnaStatus($status) {

    if (
        cekStatus($status, 'menunggu')
    ) {
        return 'status-menunggu';
    }

    if (
        cekStatus($status, 'diproses') ||
        cekStatus($status, 'dijemput')
    ) {
        return 'status-proses';
    }

    if (
        cekStatus($status, 'selesai') ||
        cekStatus($status, 'diambil')
    ) {
        return 'status-selesai';
    }

    if (
        cekStatus($status, 'siap') ||
        cekStatus($status, 'diantar')
    ) {
        return 'status-siap';
    }

    return 'status-default';
}


// ========================================
// MENGELOMPOKKAN PESANAN
// ========================================

$menunggu = [];
$diproses = [];
$siap = [];
$selesai = [];
$antarJemput = [];
$pesananHariIni = [];

foreach ($pesanan as $p) {

    $status = $p['status'] ?? '';
    $jenis = $p['jenis_pesanan'] ?? '';

    // Pesanan perlu konfirmasi
    if (in_array($status, [
        cekStatus($status, 'menunggu')
    ])) {
        $menunggu[] = $p;
    }

    // Pesanan sedang dikerjakan
    elseif (in_array($status, [
        cekStatus($status, 'diproses') ||
        cekStatus($status, 'proses') ||
        cekStatus($status, 'dijemput')
    ])) {
        $diproses[] = $p;
    }

    // Pesanan siap diambil atau diantar
    if (in_array($status, [
        cekStatus($status, 'siap') ||
        cekStatus($status, 'diantar')
    ])) {
        $siap[] = $p;
    }

    // Pesanan selesai
    if (in_array($status, [
        cekStatus($status, 'selesai') ||
        cekStatus($status, 'diambil')
    ])) {
        $selesai[] = $p;
    }

    // Pesanan antar-jemput yang masih aktif
    if (
        strcasecmp(trim($jenis), 'Antar-Jemput') === 0 &&
        !cekStatus($status, 'selesai') &&
        !cekStatus($status, 'diantar') &&
        !cekStatus($status, 'diambil')
    ) {
        $antarJemput[] = $p;
    }
}


// ========================================
// DATA YANG DITAMPILKAN
// ========================================

$daftarMenunggu = array_slice($menunggu, 0, 4);
$daftarDiproses = array_slice($diproses, 0, 4);
$daftarAntarJemput = array_slice($antarJemput, 0, 4);
$pesananTerbaru = array_slice($pesanan, 0, 5);

?>

<main class="main-content dashboard-admin">

    <!-- WELCOME -->
    <section class="dashboard-welcome">

        <div>
            <span class="tanggal-dashboard">
                <?= date('d F Y') ?>
            </span>

            <h1>Dashboard Admin</h1>

            <p>Pantau pesanan dan aktivitas laundry hari ini</p>
        </div>

        <a href="tambah-pesanan.php" class="btn-tambah-dashboard">
            <i data-lucide="plus"></i>
            Tambah Pesanan
        </a>

    </section>


    <!-- STATISTIK -->
    <section class="dashboard-statistik">

        <div class="statistik-card">

            <div class="statistik-icon biru">
                <i data-lucide="shopping-bag"></i>
            </div>

            <div>
                <h3><?= $statistik['total_pesanan'] ?></h3>
                <p>Total Pesanan</p>
            </div>

        </div>

        <div class="statistik-card">

            <div class="statistik-icon kuning">
                <i data-lucide="clock"></i>
            </div>

            <div>
                <h3><?= $statistik['perlu_konfirmasi'] ?></h3>
                <p>Perlu Konfirmasi</p>
            </div>

        </div>

        <div class="statistik-card">

            <div class="statistik-icon ungu">
                <i data-lucide="washing-machine"></i>
            </div>

            <div>
                <h3><?= $statistik['sedang_dikerjakan'] ?></h3>
                <p>Sedang Dikerjakan</p>
            </div>

        </div>

        <div class="statistik-card">

            <div class="statistik-icon hijau">
                <i data-lucide="package-check"></i>
            </div>

            <div>
                <h3><?= $statistik['siap_diambil'] ?></h3>
                <p>Siap Diambil/Diantar</p>
            </div>

        </div>

    </section>


    <!-- AKSES CEPAT DAN KONFIRMASI -->
    <section class="dashboard-grid">

        <!-- AKSES CEPAT -->
        <div class="dashboard-card akses-card">

            <div class="card-heading">
                <div>
                    <h3>Akses Cepat</h3>
                    <p>Pintasan fitur administrasi</p>
                </div>
            </div>

            <div class="akses-grid">

                <a href="tambah-pesanan.php" class="akses-item">
                    <i data-lucide="plus-circle"></i>
                    <span>Tambah Pesanan</span>
                </a>

                <a href="data-pelanggan.php" class="akses-item">
                    <i data-lucide="users"></i>
                    <span>Data Pelanggan</span>
                </a>

                <a href="kelola-layanan.php" class="akses-item">
                    <i data-lucide="shirt"></i>
                    <span>Kelola Layanan</span>
                </a>

                <a href="laporan-pesanan.php" class="akses-item">
                    <i data-lucide="file-chart-column"></i>
                    <span>Laporan Pesanan</span>
                </a>

            </div>

        </div>


        <!-- PERLU KONFIRMASI -->
        <div class="dashboard-card daftar-card">

            <div class="card-heading">
                <div>
                    <h3>Pesanan Perlu Konfirmasi</h3>
                    <p>Pesanan yang membutuhkan tindakan admin</p>
                </div>

                <a href="kelola-pesanan.php?status=Menunggu">
                    Lihat Semua →
                </a>
            </div>

            <?php if (count($daftarMenunggu) > 0): ?>

                <div class="daftar-pesanan">

                    <?php foreach ($daftarMenunggu as $p): ?>

                        <div class="daftar-item">

                            <div class="daftar-info">

                                <strong>
                                    #<?= htmlspecialchars(kodePesanan($p['id_pesanan'])) ?>
                                    - <?= htmlspecialchars($p['nama_user']) ?>
                                </strong>

                                <span>
                                    <?= htmlspecialchars($p['jenis_pesanan']) ?>
                                </span>

                            </div>

                            <span class="label-status-dashboard status-menunggu">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>

                            <a href="detail-pesanan.php?id=<?= urlencode($p['id_pesanan']) ?>"
                               class="btn-lihat-dashboard">
                                Detail
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="dashboard-kosong">
                    <i data-lucide="check-circle"></i>
                    <p>Tidak ada pesanan yang perlu dikonfirmasi.</p>
                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- PESANAN DIKERJAKAN DAN ANTAR JEMPUT -->
    <section class="dashboard-grid">

        <!-- SEDANG DIKERJAKAN -->
        <div class="dashboard-card daftar-card">

            <div class="card-heading">
                <div>
                    <h3>Pesanan Sedang Dikerjakan</h3>
                    <p>Pesanan yang sedang diproses</p>
                </div>

                <a href="kelola-pesanan.php?status=Diproses">
                    Lihat Semua →
                </a>
            </div>

            <?php if (count($daftarDiproses) > 0): ?>

                <div class="daftar-pesanan">

                    <?php foreach ($daftarDiproses as $p): ?>

                        <div class="daftar-item">

                            <div class="daftar-info">

                                <strong>
                                    #<?= htmlspecialchars(kodePesanan($p['id_pesanan'])) ?>
                                    - <?= htmlspecialchars($p['nama_user']) ?>
                                </strong>

                                <span>
                                    <?= htmlspecialchars($p['jenis_pesanan']) ?>
                                </span>

                            </div>

                            <span class="label-status-dashboard <?= warnaStatus($p['status']) ?>">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>

                            <a href="detail-pesanan.php?id=<?= urlencode($p['id_pesanan']) ?>"
                               class="btn-lihat-dashboard">
                                Detail
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="dashboard-kosong">
                    <i data-lucide="inbox"></i>
                    <p>Belum ada pesanan yang sedang dikerjakan.</p>
                </div>

            <?php endif; ?>

        </div>


        <!-- PERMINTAAN ANTAR JEMPUT -->
        <div class="dashboard-card daftar-card">

            <div class="card-heading">
                <div>
                    <h3>Permintaan Antar Jemput</h3>
                    <p>Pesanan delivery yang masih aktif</p>
                </div>

                <a href="kelola-pesanan.php?jenis=Antar-Jemput">
                    Lihat Semua →
                </a>
            </div>

            <?php if (count($daftarAntarJemput) > 0): ?>

                <div class="daftar-pesanan">

                    <?php foreach ($daftarAntarJemput as $p): ?>

                        <div class="daftar-item">

                            <div class="daftar-info">

                                <strong>
                                    #<?= htmlspecialchars(kodePesanan($p['id_pesanan'])) ?>
                                    - <?= htmlspecialchars($p['nama_user']) ?>
                                </strong>

                                <span>
                                    <?= htmlspecialchars($p['jenis_pesanan']) ?>
                                </span>

                            </div>

                            <span class="label-status-dashboard <?= warnaStatus($p['status']) ?>">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>

                            <a href="detail-pesanan.php?id=<?= urlencode($p['id_pesanan']) ?>"
                               class="btn-lihat-dashboard">
                                Detail
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="dashboard-kosong">
                    <i data-lucide="truck"></i>
                    <p>Tidak ada permintaan antar jemput aktif.</p>
                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- PESANAN TERBARU DAN PENDAPATAN -->
    <section class="dashboard-grid dashboard-grid-bawah">

        <!-- PESANAN TERBARU -->
        <div class="dashboard-card terbaru-card">

            <div class="card-heading">
                <div>
                    <h3>Pesanan Terbaru</h3>
                    <p>Pesanan terakhir yang masuk ke sistem</p>
                </div>

                <a href="kelola-pesanan.php">
                    Lihat Semua →
                </a>
            </div>

            <div class="tabel-responsive">

                <table class="tabel-dashboard">

                    <thead>
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Jenis Pesanan</th>
                            <th>Layanan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($pesananTerbaru) > 0): ?>

                            <?php foreach ($pesananTerbaru as $p): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(kodePesanan($p['id_pesanan'])) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($p['nama_user']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($p['jenis_pesanan']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($p['layanan']) ?>
                                    </td>

                                    <td>
                                        <span class="label-status-dashboard <?= warnaStatus($p['status']) ?>">
                                            <?= htmlspecialchars($p['status']) ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="data-kosong">
                                    Belum ada pesanan.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- NILAI PESANAN HARI INI -->
        <div class="dashboard-card pendapatan-card">

            <div class="card-heading">

                <div>
                    <h3>Nilai Pesanan Hari Ini</h3>
                    <p>Total nilai pesanan yang masuk hari ini</p>
                </div>

                <div class="pendapatan-icon">
                    <i data-lucide="wallet"></i>
                </div>

            </div>

            <strong>
                <?= rupiah($statistik['total_hari_ini']) ?>
            </strong>

            <div class="pendapatan-info">
                <i data-lucide="calendar-days"></i>
                <?= $statistik['pesanan_hari_ini'] ?> pesanan masuk hari ini
            </div>

        </div>

    </section>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>
