<?php
$page_title = "Laporan Pesanan";
$active_page = "laporan-pesanan";

include 'includes/header.php';
include 'includes/sidebar.php';

require_once __DIR__ . '/../includes/koneksi.php';

// ==========================================
// 1. FILTER LAPORAN
// ==========================================

$tanggalHariIni = date('Y-m-d');
$tanggalMulai = $_GET['mulai'] ?? date('Y-m-01');
$tanggalSampai = $_GET['sampai'] ?? $tanggalHariIni;

$jenisPesanan = $_GET['jenis'] ?? 'Semua';
$status = $_GET['status'] ?? 'Semua';
$cari = trim($_GET['cari'] ?? '');

$statusList = [
    'Semua',
    'Menunggu',
    'Dijemput',
    'Diproses',
    'Siap Diambil',
    'Diantar',
    'Diambil',
    'Selesai'
];

$jenisList = [
    'Semua',
    'Walk-in',
    'Antar-Jemput'
];

// Validasi filter
if (!in_array($jenisPesanan, $jenisList, true)) {
    $jenisPesanan = 'Semua';
}

if (!in_array($status, $statusList, true)) {
    $status = 'Semua';
}

// Validasi tanggal
$formatTanggal = function ($tanggal) {
    $date = DateTime::createFromFormat('!Y-m-d', $tanggal);

    return $date && $date->format('Y-m-d') === $tanggal;
};

if (!$formatTanggal($tanggalMulai)) {
    $tanggalMulai = date('Y-m-01');
}

if (!$formatTanggal($tanggalSampai)) {
    $tanggalSampai = $tanggalHariIni;
}

if ($tanggalMulai > $tanggalSampai) {
    [$tanggalMulai, $tanggalSampai] = [$tanggalSampai, $tanggalMulai];
}


// ==========================================
// 2. MEMBUAT KONDISI FILTER SQL
// ==========================================

$where = [
    "p.tgl_masuk::date BETWEEN :mulai AND :sampai"
];

$params = [
    ':mulai' => $tanggalMulai,
    ':sampai' => $tanggalSampai
];

if ($jenisPesanan !== 'Semua') {
    $where[] = "p.jenis_pesanan = :jenis";
    $params[':jenis'] = $jenisPesanan;
}

if ($status !== 'Semua') {
    $where[] = "p.status_pesanan = :status";
    $params[':status'] = $status;
}

if ($cari !== '') {
    $where[] = "(
        CAST(p.id_pesanan AS TEXT) ILIKE :cari
        OR u.nama_user ILIKE :cari
    )";

    $params[':cari'] = "%$cari%";
}

$whereSQL = implode(' AND ', $where);

// ==========================================
// 3. QUERY DATA PESANAN
// ==========================================

$sqlDasar = "
    FROM pesanan p
    JOIN users u
        ON p.id_user = u.id_user
    LEFT JOIN LATERAL (
        SELECT
            STRING_AGG(
                DISTINCT l.jenis_layanan,
                ', '
            ) AS nama_layanan
        FROM detail_pesanan dp
        JOIN layanan l
            ON dp.id_layanan = l.id_layanan
        WHERE dp.id_pesanan = p.id_pesanan
    ) layanan ON TRUE
    LEFT JOIN LATERAL (
        SELECT
            SUM(jml_bayar) AS total_dibayar,

            (ARRAY_AGG(
                status_pembayaran
                ORDER BY tgl_pembayaran DESC NULLS LAST,
                         id_pembayaran DESC
            ))[1] AS status_pembayaran
        FROM pembayaran
        WHERE id_pesanan = p.id_pesanan
    ) pb ON TRUE
    WHERE $whereSQL
";

$sqlData = "
    SELECT
        p.id_pesanan,
        p.tgl_masuk,
        p.jenis_pesanan,
        p.status_pesanan,
        p.total_harga,
        u.nama_user,
        COALESCE(
            layanan.nama_layanan,
            '-'
        ) AS nama_layanan,
        COALESCE(
            pb.status_pembayaran,
            'Belum Dibayar'
        ) AS status_pembayaran
    $sqlDasar
    ORDER BY
        p.tgl_masuk DESC,
        p.id_pesanan DESC
";

// ==========================================
// 4. EXPORT LAPORAN CSV
// ==========================================

if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    $stmtExport = $pdo->prepare($sqlData);
    $stmtExport->execute($params);

    $dataExport = $stmtExport->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=UTF-8');

    header(
        'Content-Disposition: attachment; filename="laporan-pesanan-' .
        date('Y-m-d') . '.csv"'
    );

    // BOM agar karakter terbaca baik di Excel
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'ID Pesanan',
        'Tanggal Masuk',
        'Nama Pelanggan',
        'Jenis Pesanan',
        'Jenis Layanan',
        'Total Harga',
        'Status Pesanan',
        'Status Pembayaran'
    ], ';');

    foreach ($dataExport as $row) {

        fputcsv($output, [
            $row['id_pesanan'],
            $row['tgl_masuk'],
            $row['nama_user'],
            $row['jenis_pesanan'],
            $row['nama_layanan'],
            $row['total_harga'],
            $row['status_pesanan'],
            $row['status_pembayaran']
        ], ';');
    }

    fclose($output);
    exit;
}

// ==========================================
// 5. MENGAMBIL RINGKASAN STATISTIK
// ==========================================

$sqlRingkasan = "
    SELECT

        COUNT(*) AS total_pesanan,

        COUNT(*) FILTER (
            WHERE LOWER(p.status_pesanan)
            IN ('selesai', 'diambil')
        ) AS pesanan_selesai,

        COUNT(*) FILTER (
            WHERE LOWER(p.status_pesanan)
            IN (
                'dijemput',
                'diproses',
                'diantar',
                'siap diambil',
                'sedang diproses',
                'siap diantar'
            )
        ) AS dalam_proses,

        COALESCE(
            SUM(pb.total_dibayar),
            0
        ) AS total_pendapatan

    $sqlDasar
";

$stmtRingkasan = $pdo->prepare($sqlRingkasan);
$stmtRingkasan->execute($params);
$ringkasan = $stmtRingkasan->fetch(PDO::FETCH_ASSOC);

// ==========================================
// 6. PAGINATION
// ==========================================

$perHalaman = 5;
$sqlCount = "
    SELECT COUNT(*)
    $sqlDasar
";

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalData = (int) $stmtCount->fetchColumn();
$totalHalaman = max(
    1,
    (int) ceil($totalData / $perHalaman)
);

$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $perHalaman;

// ==========================================
// 7. MENGAMBIL DATA SESUAI HALAMAN
// ==========================================

$stmtData = $pdo->prepare(
    $sqlData . " LIMIT :limit OFFSET :offset"
);

foreach ($params as $key => $value) {
    $stmtData->bindValue($key, $value);
}

$stmtData->bindValue(':limit', $perHalaman, PDO::PARAM_INT);
$stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtData->execute();
$pesanan = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// 8. FORMAT TANGGAL INDONESIA
// ==========================================

function tanggalIndonesia($tanggal, $denganJam = false)
{
    if (empty($tanggal)) {
        return '-';
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $waktu = strtotime($tanggal);
    $hasil = date('d', $waktu) . ' ' .
        $bulan[(int) date('n', $waktu)] . ' ' .
        date('Y', $waktu);
    if ($denganJam) {
        $hasil .= ', ' . date('H.i', $waktu);
    }
    return $hasil;
}


// ==========================================
// 9. URL PAGINATION
// ==========================================

function urlHalaman($nomor)
{
    $query = $_GET;
    $query['halaman'] = $nomor;

    return '?' . http_build_query($query);
}
?>

<main class="main-content halaman-laporan">
    <section class="judul-halaman">
        <div>
            <h1>Laporan Pesanan</h1>
            <p>
                Rekap dan pantau seluruh transaksi laundry
            </p>
        </div>
    </section>

    <!-- =====================================
         KARTU RINGKASAN
    ====================================== -->

    <section class="laporan-statistik">
        <div class="statistik-card">
            <div class="statistik-icon icon-pesanan">
                <i data-lucide="shopping-bag"></i>
            </div>
            <div>
                <p>Total Pesanan</p>
                <h2>
                    <?= number_format($ringkasan['total_pesanan'], 0, ',', '.') ?>
                </h2>
            </div>
        </div>

        <div class="statistik-card">
            <div class="statistik-icon icon-selesai">
                <i data-lucide="check-circle"></i>
            </div>
            <div>
                <p>Pesanan Selesai</p>
                <h2>
                    <?= number_format($ringkasan['pesanan_selesai'], 0, ',', '.') ?>
                </h2>
            </div>
        </div>

        <div class="statistik-card">
            <div class="statistik-icon icon-proses">
                <i data-lucide="clock"></i>
            </div>
            <div>
                <p>Dalam Proses</p>
                <h2>
                    <?= number_format($ringkasan['dalam_proses'], 0, ',', '.') ?>
                </h2>
            </div>
        </div>


        <div class="statistik-card">
            <div class="statistik-icon icon-pendapatan">
                <i data-lucide="wallet"></i>
            </div>
            <div>
                <p>Total Pendapatan</p>
                <h2 class="angka-pendapatan">
                    Rp<?= number_format($ringkasan['total_pendapatan'], 0, ',', '.') ?>
                </h2>
            </div>
        </div>
    </section>

    <!-- =====================================
         FILTER LAPORAN
    ====================================== -->
    <section class="filter-laporan">
        <div class="judul-filter">
            <i data-lucide="filter"></i>
            <h3>Filter Laporan</h3>
        </div>

        <form method="GET" class="form-filter-laporan">
            <div class="filter-group">
                <label for="mulai">Periode Laporan</label>
                <div class="input-filter">
                    <i data-lucide="calendar"></i>
                    <input
                        type="date"
                        name="mulai"
                        id="mulai"
                        value="<?= htmlspecialchars($tanggalMulai) ?>"
                        required
                    >
                </div>
            </div>

            <div class="filter-group">
                <label for="sampai">Sampai Tanggal</label>
                <div class="input-filter">
                    <i data-lucide="calendar"></i>
                    <input
                        type="date"
                        name="sampai"
                        id="sampai"
                        value="<?= htmlspecialchars($tanggalSampai) ?>"
                        required
                    >
                </div>
            </div>

            <div class="filter-group">
                <label for="jenis">Jenis Pesanan</label>
                <select name="jenis" id="jenis">
                    <?php foreach ($jenisList as $item): ?>
                        <option
                            value="<?= htmlspecialchars($item) ?>"
                            <?= $jenisPesanan === $item ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <?php foreach ($statusList as $item): ?>
                        <option
                            value="<?= htmlspecialchars($item) ?>"
                            <?= $status === $item ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="cari"
                   value="<?= htmlspecialchars($cari) ?>">
            <button type="submit" class="btn-tampilkan">
                <i data-lucide="chart-no-axes-column"></i>
                Tampilkan Laporan
            </button>
        </form>
    </section>

    <!-- =====================================
         RIWAYAT TRANSAKSI
    ====================================== -->
    <section class="riwayat-transaksi">
        <div class="judul-riwayat">
            <div class="judul-riwayat-text">
                <i data-lucide="list"></i>
                <h3>Riwayat Transaksi</h3>
            </div>

            <form method="GET" class="form-cari-riwayat">
                <input type="hidden" name="mulai"
                       value="<?= htmlspecialchars($tanggalMulai) ?>">
                <input type="hidden" name="sampai"
                       value="<?= htmlspecialchars($tanggalSampai) ?>">
                <input type="hidden" name="jenis"
                       value="<?= htmlspecialchars($jenisPesanan) ?>">
                <input type="hidden" name="status"
                       value="<?= htmlspecialchars($status) ?>">
                <i data-lucide="search"></i>
                <input
                    type="text"
                    name="cari"
                    placeholder="Cari nama pelanggan atau kode pesanan..."
                    value="<?= htmlspecialchars($cari) ?>"
                >
                <button type="submit">Cari</button>
            </form>
        </div>

        <!-- TABEL TRANSAKSI -->
        <div class="table-responsive">
            <table class="tabel-riwayat">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Kode Pesanan</th>
                        <th>Nama Pelanggan</th>
                        <th>Jenis Pesanan</th>
                        <th>Layanan</th>
                        <th>Total Harga</th>
                        <th>Status Pesanan</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($pesanan) > 0): ?>
                        <?php
                        $tanggalTerakhir = '';
                        ?>
                        <?php foreach ($pesanan as $p): ?>
                            <?php
                            $tanggalGrup = date(
                                'Y-m-d',
                                strtotime($p['tgl_masuk'])
                            );
                            $statusClass = strtolower(
                                preg_replace(
                                    '/[^a-z0-9]+/i',
                                    '-',
                                    $p['status_pesanan']
                                )
                            );
                            $bayarClass = strtolower(
                                preg_replace(
                                    '/[^a-z0-9]+/i',
                                    '-',
                                    $p['status_pembayaran']
                                )
                            );
                            ?>

                            <!-- PEMISAH TANGGAL -->
                            <?php if ($tanggalGrup !== $tanggalTerakhir): ?>
                                <tr class="baris-tanggal">
                                    <td colspan="9">
                                        <i data-lucide="calendar-days"></i>
                                        <?= tanggalIndonesia($p['tgl_masuk']) ?>
                                    </td>
                                </tr>

                                <?php
                                $tanggalTerakhir = $tanggalGrup;
                                ?>

                            <?php endif; ?>

                            <!-- DATA PESANAN -->
                            <tr>
                                <td>
                                    <?= tanggalIndonesia(
                                        $p['tgl_masuk'],
                                        true
                                    ) ?>
                                </td>
                                <td class="kode-laporan">
                                    #LD-<?= htmlspecialchars($p['id_pesanan']) ?>
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
                                <td class="harga-laporan">
                                    Rp<?= number_format(
                                        $p['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>
                                <td>
                                    <span class="badge-laporan status-<?= htmlspecialchars($statusClass) ?>">
                                        <?= htmlspecialchars($p['status_pesanan']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-laporan bayar-<?= htmlspecialchars($bayarClass) ?>">
                                        <?= htmlspecialchars($p['status_pembayaran']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a
                                        href="detail-pesanan.php?id=<?= urlencode($p['id_pesanan']) ?>"
                                        class="btn-detail-laporan"
                                    >
                                        <i data-lucide="eye"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="laporan-kosong">
                                <i data-lucide="inbox"></i>
                                <p>Data transaksi tidak ditemukan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- =====================================
             PAGINATION
        ====================================== -->
        <div class="pagination-laporan">
            <p>
                Menampilkan
                <?= $totalData > 0 ? $offset + 1 : 0 ?>
                -
                <?= min($offset + $perHalaman, $totalData) ?>
                dari
                <?= $totalData ?>
                data
            </p>

            <div class="pagination-tombol">
                <?php if ($halaman > 1): ?>
                    <a href="<?= htmlspecialchars(urlHalaman($halaman - 1)) ?>"
                       class="btn-page">
                        <i data-lucide="chevron-left"></i>
                    </a>
                <?php endif; ?>
                <?php
                $awal = max(1, $halaman - 2);
                $akhir = min($totalHalaman, $halaman + 2);
                ?>
                <?php if ($awal > 1): ?>
                    <a href="<?= htmlspecialchars(urlHalaman(1)) ?>"
                       class="btn-page">1</a>
                    <?php if ($awal > 2): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $awal; $i <= $akhir; $i++): ?>
                    <a
                        href="<?= htmlspecialchars(urlHalaman($i)) ?>"
                        class="btn-page <?= $halaman === $i ? 'active' : '' ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($akhir < $totalHalaman): ?>
                    <?php if ($akhir < $totalHalaman - 1): ?>
                        <span>...</span>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars(urlHalaman($totalHalaman)) ?>"
                       class="btn-page">
                        <?= $totalHalaman ?>
                    </a>
                <?php endif; ?>
                <?php if ($halaman < $totalHalaman): ?>
                    <a href="<?= htmlspecialchars(urlHalaman($halaman + 1)) ?>"
                       class="btn-page">
                        <i data-lucide="chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<script>
    lucide.createIcons();
</script>
</body>
</html>
