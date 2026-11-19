<?php
$page_title = "Kelola Layanan";
$active_page = "kelola-layanan";

require_once __DIR__ . '/../includes/koneksi.php';

// Mengambil data layanan dari database
$query = "SELECT * FROM layanan ORDER BY id_layanan ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();

$layanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Memanggil header dan sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content halaman-katalog">

    <!-- JUDUL HALAMAN -->
    <section class="judul-halaman">
        <div>
            <h1>Kelola Layanan</h1>
            <p>Kelola daftar layanan dan harga laundry Si-Mala</p>
        </div>
        <button type="button" class="btn-tambah-layanan" id="bukaModalLayanan"><span>+</span>Tambah Layanan</button>
    </section>

    <!-- DAFTAR LAYANAN -->
    <section class="grid-layanan">
        <?php if (count($layanan) > 0) : ?>
            <?php foreach ($layanan as $data) : ?>
                <div class="kartu-layanan">
                    <h3><?= htmlspecialchars($data['jenis_layanan']) ?></h3>

                    <p class="harga-layanan">
                        <small>Rp</small>
                        <strong><?= number_format($data['harga_per_layanan'], 0, ',', '.') ?></strong>
                        <small>/<?= htmlspecialchars($data['satuan']) ?></small>
                    </p>
                    <p class="estimasi-layanan">Estimasi Selesai:<?= htmlspecialchars((string) $data['estimasi_hari']) ?> hari</p>
                    <a href="tambah-pesanan.php?layanan=<?= urlencode((string) $data['id_layanan']) ?>" class="btn-pesan-layanan" data-id="<?= htmlspecialchars((string) $data['id_layanan']) ?>" data-nama="<?= htmlspecialchars($data['jenis_layanan'], ENT_QUOTES, 'UTF-8') ?>">Pesan Layanan</a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Belum ada layanan yang tersedia.</p>
        <?php endif; ?>
    </section>
</main>

<script src="assets/js/kelola-layanan.js"></script>

<script>
    lucide.createIcons();
</script>

</body>
</html>