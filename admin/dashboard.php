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

$sql = "
    SELECT
        p.id_pesanan,
        p.id_user,
        p.jenis_pesanan,
        p.tgl_masuk,
        p.tgl_selesai,
        p.estimasi_selesai,
        p.alamat_antar_jemput,
        p.status_pesanan,
        p.waktu_update_status,
        p.total_harga,
        u.nama_user,
        COALESCE(l.nama_layanan, '-') AS nama_layanan
    FROM pesanan p
    JOIN users u
        ON p.id_user = u.id_user
    LEFT JOIN (
        SELECT
            dp.id_pesanan,
            STRING_AGG(DISTINCT ly.jenis_layanan, ', ') AS nama_layanan
        FROM detail_pesanan dp
        JOIN layanan ly
            ON dp.id_layanan = ly.id_layanan
        GROUP BY dp.id_pesanan
    ) l
        ON p.id_pesanan = l.id_pesanan
    ORDER BY p.tgl_masuk DESC
";

$stmt = $pdo->query($sql);
$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
        cekStatus($status, 'menunggu') ||
        cekStatus($status, 'konfirmasi')
    ) {
        return 'status-menunggu';
    }

    if (
        cekStatus($status, 'proses') ||
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

$totalNilaiHariIni = 0;

$tanggalHariIni = date('Y-m-d');

foreach ($pesanan as $p) {

    $status = $p['status_pesanan'] ?? '';
    $jenis = $p['jenis_pesanan'] ?? '';

    // Pesanan perlu konfirmasi
    if (
        cekStatus($status, 'menunggu') ||
        cekStatus($status, 'konfirmasi')
    ) {
        $menunggu[] = $p;
    }

    // Pesanan sedang dikerjakan
    elseif (
        cekStatus($status, 'diproses') ||
        cekStatus($status, 'proses') ||
        cekStatus($status, 'dijemput')
    ) {
        $diproses[] = $p;
    }

    // Pesanan siap diambil atau diantar
    if (
        cekStatus($status, 'siap') ||
        cekStatus($status, 'diantar')
    ) {
        $siap[] = $p;
    }

    // Pesanan selesai
    if (
        cekStatus($status, 'selesai') ||
        cekStatus($status, 'diambil')
    ) {
        $selesai[] = $p;
    }

    // Pesanan antar-jemput yang masih aktif
    if (
        cekStatus($jenis, 'antar') &&
        !cekStatus($status, 'selesai') &&
        !cekStatus($status, 'diambil')
    ) {
        $antarJemput[] = $p;
    }

    // Pesanan yang masuk hari ini
    if (!empty($p['tgl_masuk'])) {

        $tanggalMasuk = date(
            'Y-m-d',
            strtotime($p['tgl_masuk'])
        );

        if ($tanggalMasuk === $tanggalHariIni) {
            $pesananHariIni[] = $p;
            $totalNilaiHariIni += (float) $p['total_harga'];
        }
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
                <h3><?= count($pesanan) ?></h3>
                <p>Total Pesanan</p>
            </div>

        </div>

        <div class="statistik-card">

            <div class="statistik-icon kuning">
                <i data-lucide="clock"></i>
            </div>

            <div>
                <h3><?= count($menunggu) ?></h3>
                <p>Perlu Konfirmasi</p>
            </div>

        </div>

        <div class="statistik-card">

            <div class="statistik-icon ungu">
                <i data-lucide="washing-machine"></i>
            </div>

            <div>
                <h3><?= count($diproses) ?></h3>
                <p>Sedang Dikerjakan</p>
            </div>

        </div>

        <div class="statistik-card">

            <div class="statistik-icon hijau">
                <i data-lucide="package-check"></i>
            </div>

            <div>
                <h3><?= count($siap) ?></h3>
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
                                <?= htmlspecialchars($p['status_pesanan']) ?>
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
                                    <?= htmlspecialchars($p['nama_layanan']) ?>
                                </span>

                            </div>

                            <span class="label-status-dashboard <?= warnaStatus($p['status_pesanan']) ?>">
                                <?= htmlspecialchars($p['status_pesanan']) ?>
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

                <a href="kelola-pesanan.php">
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
                                    <?= htmlspecialchars($p['alamat_antar_jemput'] ?? '-') ?>
                                </span>

                            </div>

                            <span class="label-status-dashboard status-siap">
                                Antar-Jemput
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
                                        <?= htmlspecialchars($p['nama_layanan']) ?>
                                    </td>

                                    <td>
                                        <span class="label-status-dashboard <?= warnaStatus($p['status_pesanan']) ?>">
                                            <?= htmlspecialchars($p['status_pesanan']) ?>
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
                <?= rupiah($totalNilaiHariIni) ?>
            </strong>

            <div class="pendapatan-info">
                <i data-lucide="calendar-days"></i>
                <?= count($pesananHariIni) ?> pesanan masuk hari ini
            </div>

        </div>

    </section>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>
