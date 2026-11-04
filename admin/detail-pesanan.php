<?php
$page_title = "Detail Pesanan";
$active_page = "kelola-pesanan";

include 'includes/header.php';
include 'includes/sidebar.php';

// Data dummy pesanan
$pesanan = [
    'kode_pesanan' => 'LD-003',
    'nama_pelanggan' => 'Budi Santoso',
    'no_hp' => '0812-3459-3334',
    'jenis_layanan' => 'Antar-Jemput',
    'status' => 'Dijemput',

    'alamat' => 'Jl Danau Sentani No 51',
    'tanggal_masuk' => '10 Sept 2026, 10.00',
    'jadwal_jemput' => '10 Sept 2026, 10.00',
    'estimasi_antar' => '13 Sept 2026, 10.00',
    'estimasi_selesai' => '13 Sept 2026, 10.00',

    'total' => 33000,
    'status_pembayaran' => 'Belum Lunas',
    'tanggal_bayar' => '-',
    'metode_pembayaran' => '-'
];

// Data layanan pesanan
$layanan = [
    [
        'nama' => 'Cuci Kering Setrika',
        'jumlah' => '5.5 kg',
        'harga' => 6000,
        'subtotal' => 33000
    ]
];

// Tahapan progress pesanan
$tahapan = [
    'Konfirmasi',
    'Dijemput',
    'Diproses',
    'Selesai',
    'Diantar'
];

$progressAktif = array_search($pesanan['status'], $tahapan);

if ($progressAktif === false) {
    $progressAktif = 0;
}
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
                    Detail Pesanan #<?= htmlspecialchars($pesanan['kode_pesanan']) ?>
                </h1>
            </div>
            <p>Pantau pesanan dan aktivitas laundry hari ini</p>
        </div>

        <a href="edit-pesanan.php?kode=<?= urlencode($pesanan['kode_pesanan']) ?>"
           class="btn-edit-pesanan">
            Edit Pesanan
        </a>
    </section>

    <!-- Informasi pelanggan -->
    <section class="kartu-pelanggan">
        <div class="foto-pelanggan">
            <span>
                <?= strtoupper(substr($pesanan['nama_pelanggan'], 0, 1)) ?>
            </span>
        </div>

        <div class="data-pelanggan">
            <h3>
                <?= htmlspecialchars($pesanan['nama_pelanggan']) ?>
                <span>|</span>
                <?= htmlspecialchars($pesanan['no_hp']) ?>
            </h3>

            <p>
                <?= htmlspecialchars($pesanan['jenis_layanan']) ?>
                <span>•</span>
                <?= htmlspecialchars($pesanan['tanggal_masuk']) ?>
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

            <a href="proses-pesanan.php?kode=<?= urlencode($pesanan['kode_pesanan']) ?>"
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

            <?php foreach ($layanan as $item) : ?>

                <div class="rincian-layanan">
                    <div>
                        <strong><?= htmlspecialchars($item['nama']) ?></strong>
                        <p>
                            <?= htmlspecialchars($item['jumlah']) ?>
                            × Rp<?= number_format($item['harga'], 0, ',', '.') ?>
                        </p>
                    </div>

                    <strong>
                        Rp<?= number_format($item['subtotal'], 0, ',', '.') ?>
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
                            #<?= htmlspecialchars($pesanan['kode_pesanan']) ?>
                        </strong>
                    </div>

                    <div>
                        <span>Nama Pelanggan</span>
                        <strong>
                            <?= htmlspecialchars($pesanan['nama_pelanggan']) ?>
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