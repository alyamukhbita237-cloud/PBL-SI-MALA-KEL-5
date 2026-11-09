<?php
$page_title = "Kelola Layanan";
$active_page = "kelola-layanan";

include 'includes/header.php';
include 'includes/sidebar.php';

// DATA DUMMY LAYANAN
$layanan = [
    [
        'id_layanan' => 1,
        'jenis_layanan' => 'Cuci Setrika Kering',
        'harga_per_layanan' => 6000,
        'satuan' => 'kg',
        'estimasi_hari' => 3
    ],
    [
        'id_layanan' => 2,
        'jenis_layanan' => 'Cuci Kering',
        'harga_per_layanan' => 5000,
        'satuan' => 'kg',
        'estimasi_hari' => 3
    ],
    [
        'id_layanan' => 3,
        'jenis_layanan' => 'Setrika',
        'harga_per_layanan' => 5000,
        'satuan' => 'kg',
        'estimasi_hari' => 3
    ],
    [
        'id_layanan' => 4,
        'jenis_layanan' => 'Xpress',
        'harga_per_layanan' => 20000,
        'satuan' => 'pcs',
        'estimasi_hari' => 1
    ],
    [
        'id_layanan' => 5,
        'jenis_layanan' => 'Bedcover',
        'harga_per_layanan' => 6000,
        'satuan' => 'kg',
        'estimasi_hari' => 1
    ],
    [
        'id_layanan' => 6,
        'jenis_layanan' => 'Mukena/Sajadah',
        'harga_per_layanan' => 6000,
        'satuan' => 'kg',
        'estimasi_hari' => 1
    ],
    [
        'id_layanan' => 7,
        'jenis_layanan' => 'Selimut',
        'harga_per_layanan' => 20000,
        'satuan' => 'pcs',
        'estimasi_hari' => 1
    ],
    [
        'id_layanan' => 8,
        'jenis_layanan' => 'Boneka',
        'harga_per_layanan' => 6000,
        'satuan' => 'kg',
        'estimasi_hari' => 1
    ],
    [
        'id_layanan' => 9,
        'jenis_layanan' => 'Sepatu',
        'harga_per_layanan' => 6000,
        'satuan' => 'kg',
        'estimasi_hari' => 1
    ]
];
?>

<main class="main-content halaman-katalog">

    <!-- JUDUL HALAMAN -->
    <section class="judul-halaman">
        <div>
            <h1>Kelola Layanan</h1>
            <p>Kelola daftar layanan dan harga laundry Si-Mala</p>
        </div>

        <button
            type="button"
            class="btn-tambah-layanan"
            id="bukaModalLayanan"
        >
            <span>+</span>
            Tambah Layanan
        </button>
    </section>

    <!-- DAFTAR LAYANAN -->
    <section class="grid-layanan">

        <?php foreach ($layanan as $data) : ?>

            <div class="kartu-layanan">

                <h3>
                    <?= htmlspecialchars($data['jenis_layanan']) ?>
                </h3>

                <p class="harga-layanan">
                    <small>Rp</small>

                    <strong>
                        <?= number_format(
                            $data['harga_per_layanan'],
                            0,
                            ',',
                            '.'
                        ) ?>
                    </strong>

                    <small>
                        /<?= htmlspecialchars($data['satuan']) ?>
                    </small>
                </p>

                <p class="estimasi-layanan">
                    Estimasi Selesai:
                    <?= htmlspecialchars((string) $data['estimasi_hari']) ?>
                    hari
                </p>

                <button
                    type="button"
                    class="btn-pesan-layanan"
                    data-id="<?= $data['id_layanan'] ?>"
                    data-nama="<?= htmlspecialchars(
                        $data['jenis_layanan'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    Pesan Layanan
                </button>

            </div>

        <?php endforeach; ?>

    </section>

</main>

<script src="assets/js/kelola-layanan.js"></script>

<script>
    lucide.createIcons();
</script>

</body>
</html>
