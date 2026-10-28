<?php
$page_title = "Kelola Pesanan";
$active_page = "kelola-pesanan";

// DATA DUMMY
$pesanan = [
    [
        'kode' => 'LD-004',
        'nama' => 'Athia',
        'tanggal' => '15 Sept 2026, 19.30',
        'layanan' => 'Walk-in',
        'status' => 'Menunggu'
    ],
    [
        'kode' => 'LD-003',
        'nama' => 'Budi Santoso',
        'tanggal' => '10 Sept 2026, 08.25',
        'layanan' => 'Antar-Jemput',
        'status' => 'Diproses'
    ],
    [
        'kode' => 'LD-002',
        'nama' => 'Cindy Callista',
        'tanggal' => '4 Sept 2026, 10.00',
        'layanan' => 'Antar-Jemput',
        'status' => 'Diantar'
    ],
    [
        'kode' => 'LD-001',
        'nama' => 'Raka Putra',
        'tanggal' => '28 Ags 2026, 12.20',
        'layanan' => 'Walk-in',
        'status' => 'Selesai'
    ]
];

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

// Menghitung jumlah pesanan per status
$jumlah = [];

foreach ($statusList as $status) {
    if ($status === 'Semua') {
        $jumlah[$status] = count($pesanan);
    } else {
        $jumlah[$status] = count(array_filter(
            $pesanan,
            fn($p) => $p['status'] === $status
        ));
    }
}

// Filter dan pencarian
$hasil = array_filter($pesanan, function ($p) use ($filter, $cari) {
    $sesuaiStatus = (
        $filter === 'Semua' ||
        $p['status'] === $filter
    );

    $sesuaiCari = (
        stripos($p['kode'], $cari) !== false ||
        stripos($p['nama'], $cari) !== false
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
                        $classStatus = 'status-' .
                            strtolower($p['status']);
                        ?>

                        <tr>
                            <td class="kode-pesanan">#<?= htmlspecialchars($p['kode']) ?></td>
                            <td><?= htmlspecialchars($p['nama']) ?></td>
                            <td><?= htmlspecialchars($p['tanggal']) ?></td>
                            <td><?= htmlspecialchars($p['layanan']) ?></td>
                            <td><span class="label-status <?= htmlspecialchars($classStatus) ?>">
                                    <?= htmlspecialchars($p['status']) ?>
                                </span>
                            </td>

                            <td style="text-align: center;">
                                <a href="detail-pesanan.php?kode=<?= urlencode($p['kode']) ?>"
                                   class="btn-detail"> Lihat Detail &gt;
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
