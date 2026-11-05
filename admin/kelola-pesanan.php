<?php
$page_title = "Kelola Pesanan";
$active_page = "kelola-pesanan";

require_once __DIR__ . '/../includes/koneksi.php';

// Daftar status pesanan
$statusList = [
    'Semua',
    'Menunggu',
    'Dijemput',
    'Diproses',
    'Selesai',
    'Diantar',
    'Diambil'
];

// Mengambil filter dari URL
$filter = $_GET['status'] ?? 'Semua';
$cari = trim($_GET['cari'] ?? '');

if (!in_array($filter, $statusList, true)) {
    $filter = 'Semua';
}

// Mengambil data pesanan dan pelanggan dari database
$sql = "
    SELECT
        p.id_pesanan,
        p.id_user,
        p.jenis_pesanan,
        p.tgl_masuk,
        p.status_pesanan,
        p.total_harga,
        u.nama_user
    FROM pesanan p
    JOIN users u ON p.id_user = u.id_user
    ORDER BY p.tgl_masuk DESC
";

$stmt = $pdo->query($sql);
$pesanan = $stmt->fetchAll();

// Menghitung jumlah pesanan per status
$jumlah = [];

foreach ($statusList as $status) {
    if ($status === 'Semua') {
        $jumlah[$status] = count($pesanan);
    } else {
        $jumlah[$status] = count(array_filter(
            $pesanan,
            fn($p) => $p['status_pesanan'] === $status
        ));
    }
}

// Filter dan pencarian
$hasil = array_filter($pesanan, function ($p) use ($filter, $cari) {

    $sesuaiStatus = (
        $filter === 'Semua' ||
        $p['status_pesanan'] === $filter
    );

    $kode = 'LD-' . str_pad($p['id_pesanan'], 3, '0', STR_PAD_LEFT);

    $sesuaiCari = (
        stripos($kode, $cari) !== false ||
        stripos($p['nama_user'], $cari) !== false
    );

    return $sesuaiStatus && $sesuaiCari;
});

// Memanggil header dan sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="main-content">

    <section class="judul-halaman">
        <div>
            <h1>Kelola Pesanan</h1>
            <p>Pantau pesanan dan aktivitas laundry hari ini</p>
        </div>

        <a href="tambah-pesanan.php" class="btn-tambah">
            <i data-lucide="plus" width="16"></i>
            Tambah Pesanan
        </a>
    </section>

    <section class="kartu-pesanan">

        <div class="toolbar-pesanan">

            <div class="filter-status">
                <?php foreach ($statusList as $status): ?>
                    <a
                        href="?status=<?= urlencode($status) ?>&cari=<?= urlencode($cari) ?>"
                        class="<?= $filter === $status ? 'active' : '' ?>"
                    >
                        <?= htmlspecialchars($status) ?>
                        (<?= $jumlah[$status] ?>)
                    </a>
                <?php endforeach; ?>
            </div>

            <form method="GET" class="form-cari">
                <input
                    type="hidden"
                    name="status"
                    value="<?= htmlspecialchars($filter) ?>"
                >

                <input
                    type="text"
                    name="cari"
                    placeholder="Cari pesanan/pelanggan..."
                    value="<?= htmlspecialchars($cari) ?>"
                >

                <button type="submit">Cari</button>
            </form>

        </div>

        <table class="tabel-pesanan">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Tanggal Masuk</th>
                    <th>Jenis Layanan</th>
                    <th>Status Pesanan</th>
                    <th>DETAIL</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($hasil) > 0): ?>
                    
                    <?php foreach ($hasil as $p): ?>

                        <?php
                        // Membuat kode tampilan, misalnya LD-004
                        $kode = 'LD-' . str_pad(
                            $p['id_pesanan'], 3, '0', STR_PAD_LEFT);

                        $classStatus = 'status-' . strtolower(
                            str_replace(' ', '-', $p['status_pesanan']));

                        // Format tanggal
                        $tanggal = date('d M Y, H.i', strtotime($p['tgl_masuk']));
                        ?>

                        <tr>
                            <td class="kode-pesanan"> #<?= htmlspecialchars($kode) ?></td>
                            <td><?= htmlspecialchars($p['nama_user']) ?></td>
                            <td><?= htmlspecialchars($tanggal) ?></td>
                            <td><?= htmlspecialchars($p['jenis_pesanan']) ?></td>
                            <td>
                                <span class="label-status <?= htmlspecialchars($classStatus) ?>">
                                    <?= htmlspecialchars($p['status_pesanan']) ?>
                                </span>
                            </td>

                            <td style="text-align: center;">
                                <a
                                    href="detail-pesanan.php?id=<?= urlencode($p['id_pesanan']) ?>"
                                    class="btn-detail"
                                >
                                    Lihat Detail &gt;
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="data-kosong">
                            Pesanan tidak ditemukan.
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>
        </table>

    </section>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>
