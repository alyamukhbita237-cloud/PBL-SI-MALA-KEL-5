
<?php
$page_title = "Kelola Layanan";
$active_page = "kelola-layanan";

include 'includes/header.php';
include 'includes/sidebar.php';

// Data dummy layanan
$layanan = [
    [
        'id' => 1,
        'nama' => 'Cuci Setrika Kering',
        'harga' => 6000,
        'satuan' => 'kg',
        'estimasi' => 3
    ],
    [
        'id' => 2,
        'nama' => 'Cuci Kering',
        'harga' => 5000,
        'satuan' => 'kg',
        'estimasi' => 3
    ],
    [
        'id' => 3,
        'nama' => 'Setrika',
        'harga' => 5000,
        'satuan' => 'kg',
        'estimasi' => 3
    ],
    [
        'id' => 4,
        'nama' => 'Xpress',
        'harga' => 20000,
        'satuan' => 'pcs',
        'estimasi' => 1
    ],
    [
        'id' => 5,
        'nama' => 'Bedcover',
        'harga' => 6000,
        'satuan' => 'kg',
        'estimasi' => 1
    ],
    [
        'id' => 6,
        'nama' => 'Mukena/Sajadah',
        'harga' => 6000,
        'satuan' => 'kg',
        'estimasi' => 1
    ],
    [
        'id' => 7,
        'nama' => 'Selimut',
        'harga' => 20000,
        'satuan' => 'pcs',
        'estimasi' => 1
    ],
    [
        'id' => 8,
        'nama' => 'Boneka',
        'harga' => 6000,
        'satuan' => 'kg',
        'estimasi' => 1
    ],
    [
        'id' => 9,
        'nama' => 'Sepatu',
        'harga' => 6000,
        'satuan' => 'kg',
        'estimasi' => 1
    ]
];
?>

<main class="main-content halaman-katalog">

    <section class="judul-halaman">
        <div>
            <h1>Kelola Layanan</h1>
            <p>Lihat katalog layanan kami dan mulai pesan</p>
        </div>

        <button type="button"
                class="btn-tambah-layanan"
                id="bukaModalLayanan">
            <span>+</span>
            Tambah Layanan
        </button>
    </section>

    <!-- Daftar layanan -->
    <section class="grid-layanan">

        <?php foreach ($layanan as $data) : ?>

            <div class="kartu-layanan">

                <h3>
                    <?= htmlspecialchars($data['nama']) ?>
                </h3>

                <p class="harga-layanan">
                    <small>Rp</small>
                    <strong>
                        <?= number_format($data['harga'], 0, ',', '.') ?>
                    </strong>
                    <small>/<?= htmlspecialchars($data['satuan']) ?></small>
                </p>

                <p class="estimasi-layanan">
                    Estimasi Selesai: <?= $data['estimasi'] ?> hari
                </p>

                <button
                    type="button"
                    class="btn-pesan-layanan"
                    data-id="<?= $data['id'] ?>"
                    data-nama="<?= htmlspecialchars($data['nama'], ENT_QUOTES) ?>"
                >
                    Pesan Layanan
                </button>

            </div>

        <?php endforeach; ?>

    </section>

</main>

<script src="assets/js/kelola-layanan.js"></script>

</body>
</html>
