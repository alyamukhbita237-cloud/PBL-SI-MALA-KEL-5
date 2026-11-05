<?php
$page_title = "Detail Pesanan";
$active_page = "kelola-pesanan";
require_once __DIR__ . '/../includes/koneksi.php';

// Mengambil ID dari URL
$id = $_GET['id'] ?? '';

// Mengambil data pesanan dan pelanggan
$sql = "
    SELECT
        p.*,
        u.nama_user,
        u.no_hp,
        u.email,
        u.alamat AS alamat_pelanggan
    FROM pesanan p
    JOIN users u
        ON p.id_user = u.id_user
    WHERE p.id_pesanan = :id
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$pesanan = $stmt->fetch();

// Jika pesanan tidak ditemukan
if (!$pesanan) {
    die("Pesanan tidak ditemukan.");
}

// Mengambil rincian layanan
$sqlDetail = "
    SELECT
        l.jenis_layanan,
        l.harga_per_layanan,
        l.satuan,
        d.jumlah,
        d.subtotal
    FROM detail_pesanan d
    JOIN layanan l
        ON d.id_layanan = l.id_layanan
    WHERE d.id_pesanan = :id
";

$stmtDetail = $pdo->prepare($sqlDetail);
$stmtDetail->execute(['id' => $id]);
$detailLayanan = $stmtDetail->fetchAll();

// Mengambil informasi pembayaran
$sqlBayar = " SELECT * FROM pembayaran WHERE id_pesanan = :id ";

$stmtBayar = $pdo->prepare($sqlBayar);
$stmtBayar->execute(['id' => $id]);
$pembayaran = $stmtBayar->fetch();

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="main-content halaman-detail">

    <!-- Judul halaman -->
    <section class="judul-halaman">
        <div>
            <div class="judul-dengan-panah">
                <a href="kelola-pesanan.php" class="btn-kembali">
                    <span class="panah-kembali">&lsaquo;</span>
                </a>

                <h1>
                    Detail Pesanan #<?= htmlspecialchars($pesanan['id_pesanan']) ?>
                </h1>
            </div>
            <p>Pantau pesanan dan aktivitas laundry hari ini</p>
        </div>

        <a href="edit-pesanan.php?kode=<?= urlencode($pesanan['id_pesanan']) ?>"
           class="btn-edit-pesanan">
            Edit Pesanan
        </a>
    </section>

    <!-- Informasi pelanggan -->
    <section class="kartu-pelanggan">
        <div class="foto-pelanggan">
            <span>
                <?= strtoupper(substr($pesanan['nama_user'], 0, 1)) ?>
            </span>
        </div>

        <div class="data-pelanggan">
            <h3>
                <?= htmlspecialchars($pesanan['nama_user']) ?>
                <span>|</span>
                <?= htmlspecialchars($pesanan['no_hp']) ?>
            </h3>

            <p>
                <?= htmlspecialchars($pesanan['jenis_layanan']) ?>
                <span>•</span>
                <?= htmlspecialchars($pesanan['tgl_masuk']) ?>
            </p>
        </div>
    </section>

    <!-- Bagian progress dan pembayaran -->
    <div class="detail-grid">

        <!-- Progress pesanan -->
        <section class="kartu-detail">
            <h3>Progress Pesanan</h3>

            <div class="progress-pesanan">

                <?php foreach ($tahapan as $index => $namaTahapan) : ?>

                    <div class="progress-item
                        <?= $index < $progressAktif ? 'selesai' : '' ?>
                        <?= $index === $progressAktif ? 'aktif' : '' ?>">

                        <div class="progress-lingkaran">
                            <?= $index < $progressAktif ? '✓' : '' ?>
                        </div>

                        <span>
                            <?= htmlspecialchars($namaTahapan) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

            <a href="proses-pesanan.php?kode=<?= urlencode($pesanan['id_pesanan']) ?>"
               class="btn-lanjut-proses">
                Lanjut Proses →
            </a>
        </section>

        <!-- Pembayaran -->
        <section class="kartu-detail">
            <div class="judul-kartu">
                <h3>Pembayaran</h3>

                <span class="status-belum-lunas">
                    <?= htmlspecialchars($pesanan['status_pembayaran']) ?>
                </span>
            </div>

            <div class="baris-info">
                <span>Total</span>
                <strong>
                    Rp<?= number_format($pesanan['total'], 0, ',', '.') ?>
                </strong>
            </div>

            <div class="baris-info">
                <span>Tanggal Bayar</span>
                <strong><?= htmlspecialchars($pesanan['tanggal_bayar']) ?></strong>
            </div>

            <div class="baris-info">
                <span>Metode Pembayaran</span>
                <strong><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></strong>
            </div>

            <button type="button" class="btn-kelola-pembayaran" id="bukaModalPembayaran">
                Kelola Pembayaran
            </button>
        </section>

        <!-- Rincian layanan -->
        <section class="kartu-detail">
            <h3>Rincian Layanan</h3>

            <?php foreach ($detailLayanan as $layanan): ?>
                <div class="item-layanan">
                    <strong>
                        <?= htmlspecialchars($layanan['jenis_layanan']) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($layanan['jumlah']) ?>
                        <?= htmlspecialchars($layanan['satuan']) ?>
                    </span>

                    <strong>
                        Rp<?= number_format($layanan['subtotal'], 0, ',', '.') ?>
                    </strong>
                </div>

            <?php endforeach; ?>

            <div class="total-bayar">
                <strong>Total Bayar</strong>
                <strong>
                    Rp<?= number_format($pesanan['total'], 0, ',', '.') ?>
                </strong>
            </div>
        </section>

        <!-- Informasi berdasarkan jenis pesanan -->
        <section class="kartu-detail kartu-info-pesanan">

            <?php if ($pesanan['jenis_layanan'] === 'Antar-Jemput') : ?>

                <h3>Informasi Pengantaran</h3>

                <div class="baris-info">
                    <span>Alamat</span>
                    <strong><?= htmlspecialchars($pesanan['alamat']) ?></strong>
                </div>

                <div class="baris-info">
                    <span>Jadwal Jemput</span>
                    <strong><?= htmlspecialchars($pesanan['jadwal_jemput']) ?></strong>
                </div>

                <div class="baris-info">
                    <span>Estimasi Antar</span>
                    <strong><?= htmlspecialchars($pesanan['estimasi_antar']) ?></strong>
                </div>

            <?php else : ?>

                <h3>Informasi Pesanan</h3>

                <div class="baris-info">
                    <span>Tanggal Masuk</span>
                    <strong><?= htmlspecialchars($pesanan['tanggal_masuk']) ?></strong>
                </div>

                <div class="baris-info">
                    <span>Estimasi Selesai</span>
                    <strong><?= htmlspecialchars($pesanan['estimasi_selesai']) ?></strong>
                </div>

            <?php endif; ?>

        </section>

    </div>

    <!-- MODAL KELOLA PEMBAYARAN -->
<div class="modal-overlay"
     id="modalPembayaran"
     data-total="<?= (int) $pesanan['total'] ?>">

    <div class="modal-pembayaran">

        <!-- Judul modal -->
        <div class="modal-header">
            <h2>Kelola Pembayaran</h2>
        </div>

        <form id="formPembayaran">

            <!-- Ringkasan pesanan -->
            <div class="ringkasan-pembayaran">
                <h4>Rincian Pesanan</h4>

                <div class="ringkasan-grid">

                    <div>
                        <span>Kode Pesanan</span>
                        <strong>
                            #<?= htmlspecialchars($pesanan['id_pesanan']) ?>
                        </strong>
                    </div>

                    <div>
                        <span>Nama Pelanggan</span>
                        <strong>
                            <?= htmlspecialchars($pesanan['nama_user']) ?>
                        </strong>
                    </div>

                    <div>
                        <span>Total Tagihan</span>
                        <strong>
                            Rp<?= number_format($pesanan['total'], 0, ',', '.') ?>
                        </strong>
                    </div>

                </div>
            </div>

            <!-- Metode pembayaran -->
            <div class="form-modal">
                <label class="label-modal">Metode Pembayaran</label>

                <div class="pilihan-metode">

                    <label>
                        <input type="radio" name="metode_pembayaran"
                               value="Cash" checked>
                        <span>Cash</span>
                    </label>

                    <label>
                        <input type="radio" name="metode_pembayaran"
                               value="QRIS">
                        <span>QRIS</span>
                    </label>

                    <label>
                        <input type="radio" name="metode_pembayaran"
                               value="Transfer Bank">
                        <span>Transfer Bank</span>
                    </label>

                </div>
            </div>

            <!-- Jumlah pembayaran -->
            <div class="form-modal">
                <label for="jumlahDibayar" class="label-modal">
                    Jumlah Dibayar
                </label>

                <div class="input-rupiah">
                    <span>Rp</span>

                    <input
                        type="number"
                        id="jumlahDibayar"
                        name="jumlah_dibayar"
                        min="1"
                        max="<?= (int) $pesanan['total'] ?>"
                        value="<?= (int) $pesanan['total'] ?>"
                        required
                    >
                </div>

                <small id="pesanPembayaran" class="pesan-pembayaran"></small>
            </div>

            <!-- Tombol modal -->
            <div class="modal-actions">

                <button type="button" class="btn-modal-batal"
                        id="tutupModalPembayaran">
                    Batal
                </button>

                <button type="submit" class="btn-modal-simpan">
                    Simpan Pembayaran
                </button>

            </div>

        </form>

    </div>
</div>
</main>

<script src="assets/js/detail-pesanan.js"></script>

</body>
</html>