<?php

session_start();

require_once '../includes/koneksi.php';

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}


$page_title = "Katalog Layanan";
$active_page = "katalog";

include 'includes/header.php';
include 'includes/sidebar.php';


$query = "SELECT 
        id_layanan,
        jenis_layanan,
        harga_per_layanan,
        satuan
    FROM layanan
    ORDER BY id_layanan
";

$stmt = $pdo->query($query);

$layanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="main-content">

    <div class="hero-content">
        <h1>Katalog Layanan</h1>
        <p>Lihat katalog layanan kami dan mulai pesan</p>
    </div>

    <section class="service-grid">

        <?php foreach($layanan as $item): ?>

            <article class="service-card">

                <h2>
                    <?= htmlspecialchars($item['jenis_layanan']) ?>
                </h2>

                <div class="service-price">
                    <span>Rp</span>

                    <strong>
                        <?= number_format($item['harga_per_layanan'],
                        0,
                        ',',
                        '.'
                        ) ?>
                    </strong>

                    <small>
                        /<?= htmlspecialchars($item['satuan']) ?>
                    </small>
                </div>

                <p class="service-estimate">
                    Estimasi Selesai: 3 Hari
                    
                </p>

                <a
                    href="antar_jemput.php?layanan=<?= $item['id_layanan'] ?>" class="service-button">
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