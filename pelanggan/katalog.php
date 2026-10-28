<?php
$page_title = "Katalog Layanan";
$active_page = "katalog";

include 'includes/header.php';
include 'includes/sidebar.php';

$layanan = [
    [
        'nama' => 'Cuci Setrika Kering',
        'harga' => '6.000',
        'satuan' => 'kg',
        'estimasi' => '3 hari'
    ],
    [
        'nama' => 'Cuci Kering',
        'harga' => '5.000',
        'satuan' => 'kg',
        'estimasi' => '3 hari'
    ],
    [
        'nama' => 'Setrika',
        'harga' => '5.000',
        'satuan' => 'kg',
        'estimasi' => '3 hari'
    ],
    [
        'nama' => 'Xpress',
        'harga' => '20.000',
        'satuan' => 'pcs',
        'estimasi' => '1 hari'
    ],
    [
        'nama' => 'Bedcover',
        'harga' => '20.000',
        'satuan' => 'pcs',
        'estimasi' => '1 hari'
    ],
    [
        'nama' => 'Mukena/Sajadah',
        'harga' => '6.000',
        'satuan' => 'kg',
        'estimasi' => '1 hari'
    ],
    [
        'nama' => 'Selimut',
        'harga' => '20.000',
        'satuan' => 'pcs',
        'estimasi' => '1 hari'
    ],
    [
        'nama' => 'Boneka',
        'harga' => '6.000',
        'satuan' => 'kg',
        'estimasi' => '1 hari'
    ],
    [
        'nama' => 'Sepatu',
        'harga' => '6.000',
        'satuan' => 'kg',
        'estimasi' => '1 hari'
    ]
];
?>

<main class="main-content page-content">

    <div class="page-heading">
        <h1>Katalog Layanan</h1>
        <p>Lihat katalog layanan kami dan mulai pesan</p>
    </div>

    <section class="service-grid">

        <?php foreach ($layanan as $item): ?>

            <article class="service-card">

                <h2>
                    <?= htmlspecialchars($item['nama']) ?>
                </h2>

                <div class="service-price">
                    <span>Rp</span>

                    <strong>
                        <?= htmlspecialchars($item['harga']) ?>
                    </strong>

                    <small>
                        /<?= htmlspecialchars($item['satuan']) ?>
                    </small>
                </div>

                <p class="service-estimate">
                    Estimasi Selesai:
                    <?= htmlspecialchars($item['estimasi']) ?>
                </p>

                <a
                    href="antar-jemput.php?layanan=<?= urlencode($item['nama']) ?>"
                    class="service-button"
                >
                    Pesan Layanan
                </a>

            </article>

        <?php endforeach; ?>

    </section>

</main>

<script src="assets/js/pelanggan.js"></script>

<script>
    lucide.createIcons();
</script>

</body>
</html>