<?php
$page_title = "Kelola Pesanan";
$active_page = "kelola-pesanan";

require_once __DIR__ . '/../includes/koneksi.php';

// Daftar status pesanan
$statusList = ['Semua', 'Menunggu', 'Dijemput', 'Diproses', 'Selesai', 'Diantar', 'Diambil'];

// Mengambil filter dari URL
$filter = $_GET['status'] ?? 'Semua';
$cari = trim($_GET['cari'] ?? '');
$jenis = $_GET['jenis'] ?? '';

if (!in_array($filter, $statusList, true)) {
    $filter = 'Semua';
}

// Mengambil data pesanan, pelanggan, dan jenis layanan dari view v_kelola_pesanan
$sql = "SELECT * FROM v_kelola_pesanan
        WHERE kode_pesanan IS NOT NULL
        ORDER BY kode_pesanan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Menghitung jumlah pesanan per status
$jumlah = [];

foreach ($statusList as $status) {
    if ($status === 'Semua') {
        $jumlah[$status] = count($pesanan);
    } else {
        $jumlah[$status] = count(array_filter($pesanan, fn($p) => $p['status_pesanan'] === $status));
    }
}

// Filter dan pencarian
$hasil = array_filter($pesanan, function ($p) use ($filter, $cari, $jenis) {
    $sesuaiStatus = ($filter === 'Semua' || $p['status_pesanan'] === $filter);
    $sesuaiJenis = ($jenis === '' || strcasecmp(trim($p['jenis_pesanan']), trim($jenis)) === 0);
    $kode = 'LD-' . str_pad($p['kode_pesanan'], 3, '0', STR_PAD_LEFT);
    $namaPelanggan = $p['nama_pelanggan'] ?? '';
    $jenisLayanan = $p['jenis_layanan'] ?? '';
    $jenisPesanan = $p['jenis_pesanan'] ?? '';
    $sesuaiCari = (stripos($kode, trim($cari)) !== false || stripos($namaPelanggan, trim($cari)) !== false || 
                stripos($jenisLayanan, trim($cari)) !== false || stripos($jenisPesanan, trim($cari)) !== false);
    return $sesuaiStatus && $sesuaiJenis && $sesuaiCari;
});

// Memanggil header dan sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">

    <!-- JUDUL HALAMAN -->
    <section class="judul-halaman">
        <div>
            <h1>Kelola Pesanan</h1>
            <p>Pantau pesanan dan aktivitas laundry hari ini</p>
        </div>
        <a href="tambah-pesanan.php" class="btn-tambah"><i data-lucide="plus" width="16"></i>Tambah Pesanan</a>
    </section>

    <!-- DAFTAR PESANAN -->
    <section class="kartu-pesanan">
        <div class="toolbar-pesanan">

            <!-- FILTER STATUS -->
            <div class="filter-status">
                <?php foreach ($statusList as $status): ?>
                    <a href="?status=<?= urlencode($status) ?>&cari=<?= urlencode($cari) ?>" class="<?= $filter === $status ? 'active' : '' ?>"><?= htmlspecialchars($status) ?>(<?= $jumlah[$status] ?>)</a>
                <?php endforeach; ?>
            </div>
            
            <!-- PENCARIAN PESANAN -->
            <form method="GET" class="form-cari">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>">
                <input type="text" name="cari" placeholder="Cari pesanan/pelanggan..." value="<?= htmlspecialchars($cari) ?>">
                <button type="submit">Cari</button>
            </form>
        </div>

        <!-- TABEL PESANAN -->
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
                        // Membuat kode pesanan
                        $kode = 'LD-' . str_pad($p['kode_pesanan'], 3, '0', STR_PAD_LEFT);

                        // Membuat class status
                        $classStatus = 'status-' . strtolower(str_replace(' ', '-', $p['status_pesanan']));

                        // Format tanggal
                        $tanggal = date('d M Y, H.i', strtotime($p['tanggal_masuk']));
                        ?>

                        <tr>
                            <td class="kode-pesanan"> #<?= htmlspecialchars($kode) ?></td>
                            <td><?= htmlspecialchars($p['nama_pelanggan']) ?></td>
                            <td><?= htmlspecialchars($tanggal) ?></td>
                            <td><?= htmlspecialchars($p['jenis_layanan']) ?></td>
                            <td>
                                <span class="label-status <?= htmlspecialchars($classStatus) ?>"><?= htmlspecialchars($p['status_pesanan']) ?></span>
                            </td>

                            <td style="text-align: center;">
                                <a href="detail-pesanan.php?id=<?= urlencode($p['kode_pesanan']) ?>" class="btn-detail">Lihat Detail &gt;</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="data-kosong">Pesanan tidak ditemukan.</td>
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