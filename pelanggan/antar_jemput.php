<?php

session_start();



if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/koneksi.php';
$id_user = $_SESSION['id_user'];

$queryLayanan = "SELECT id_layanan, jenis_layanan, harga_per_layanan, satuan, estimasi_hari FROM layanan ORDER BY id_layanan";

$stmtLayanan = $pdo->query($queryLayanan);
$layanan_tersedia = $stmtLayanan->fetchAll(PDO::FETCH_ASSOC);

$layanan_awal = $_GET['layanan'] ?? '';

$page_title = "Pesan Antar-Jemput";
$active_page = "antar_jemput";

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="main-content page-content">

    <div class="hero-content">
        <h1>Pesan Antar Jemput</h1>
        <p>Lengkapi data pelanggan dan data pesanan</p>
    </div>

    <form id="pickupForm" class="pickup-card" method="POST" action="proses/antarjemput_process.php">

        <!-- PILIH LAYANAN -->
        <section class="form-section">
            <h2>Pilih Jenis Layanan</h2>
            <div class="form-divider"></div>
            <div id="selectedServices" class="selected-services">
                <?php if ($layanan_awal !== ''): ?>
                    <?php 
                    $layanan_ditemukan = false;
                     foreach($layanan_tersedia as $item):
                        if (
                            $item['id_layanan'] == $layanan_awal || 
                            $item['jenis_layanan'] == $layanan_awal
                        ):

                        $layanan_ditemukan = true;
                    ?>
                    <div class="selected-service">
                        <input type="hidden" name="layanan[]" value="<?= htmlspecialchars($item['id_layanan']) ?>">

                        <span class="service-number">1</span> 
                        <!-- DIBIKIN OTOMATIS -->

                        <span class="selected-service-name">
                            <?= htmlspecialchars($item['jenis_layanan']) ?>
                        </span>

                        <span class="selected-service-price">
                            Rp<?= number_format($item['harga_per_layanan'], 0, ',', '.')?>
                            /<?= htmlspecialchars($item['satuan'])?>
                        </span>

                        <button
                            type="button"
                            class="remove-service-button"
                            title="Hapus layanan">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </div>

                    

                    <?php 
                        endif; 
                        endforeach
                    ?>

                    <?php if (!$layanan_ditemukan):?>
                        <p class="empty-service">Layanan Tidak ditemukan</p>
                    <?php endif; ?>

                    <?php else: ?>
                        <p class="empty-service">Silakan pilih layanan.</p>
                    <?php endif; ?>
            </div>

            <button
                type="button"
                id="addServiceButton"
                class="add-service-button"
            >
                <span>+</span>
                Tambah Layanan
            </button>

            <!-- PILIH LAYANAN TAMBAHAN -->
            <div
                id="servicePicker"
                class="service-picker hidden"
            >

                <select id="serviceSelect">
                    <option value="">Pilih layanan</option>
                    <?php foreach ($layanan_tersedia as $item): ?>
                        <option
                            value="<?= htmlspecialchars($item['id_layanan']) ?>"
                            data-name="<?= htmlspecialchars($item['jenis_layanan']) ?>"
                            data-price="<?= htmlspecialchars($item['harga_per_layanan']) ?>"
                            data-satuan="<?= htmlspecialchars($item['satuan']) ?>"
                        >
                            <?= htmlspecialchars($item['jenis_layanan']) ?>
                            —
                            Rp<?= number_format($item['harga_per_layanan'], 0, ',', '.') ?>
                            /<?= htmlspecialchars($item['satuan']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>
                <button type="button" id="confirmServiceButton">Tambahkan</button>

            </div>

        </section>


        <!-- ALAMAT -->
        <section class="form-section">

            <h2>
                Alamat Pengantaran & Penjemputan
            </h2>

            <div class="form-divider"></div>

            <label for="alamat">
                Alamat
            </label>

            <input
                type="text"
                id="alamat"
                name="alamat"
                placeholder="Masukkan alamat lengkap"
                required
            >
        </section>


        <!-- JADWAL -->
        <section class="form-section">

            <h2>
                Pilih Tanggal Penjemputan
            </h2>

            <div class="form-divider"></div>


            <label for="tanggal">
                Tanggal
            </label>

            <input
                type="date"
                id="tanggal"
                name="tanggal"
                min="<?= date('Y-m-d') ?>"
                required
            >

        </section>


        <button
            type="submit"
            class="confirm-order-button"
        >
            Konfirmasi Pesanan
        </button>

    </form>

</main>

<script src="assets/js/pelanggan.js"></script>

<script>
    lucide.createIcons();
</script>

</body>
</html>