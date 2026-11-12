
<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/koneksi.php';
$id_user = $_SESSION['id_user'];

$query = "SELECT * FROM pesanan WHERE id_user = :id_user ORDER BY id_pesanan desc";

$stmt = $pdo->prepare($query);
$stmt->execute(['id_user' => $id_user]);

$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Pesanan Saya";
$active_page = "pesanan";

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="main-content">
    <!-- Hero -->
    <div class="hero-content">
        <h1>Pesanan Saya</h1>
        <p>Lihat semua pesanan dan status laundry kamu.</p>
    </div>


   
    <section class="orders-card">
        <h2>Daftar Pesanan</h2>
        <div class="orders-table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>KODE PESANAN</th>
                        <th>WAKTU</th>
                        <th>JENIS</th>
                        <th>STATUS</th>
                        <th>DETAIL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pesanan)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">
                                Belum ada pesanan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pesanan as $item): ?>
                            <?php
                            $status = strtolower(trim($item['status_pesanan'] ?? ''));
                            $status_class = match ($status) {
                                'menunggu' => 'status-menunggu',
                                'dijemput' => 'status-dijemput',
                                'diproses' => 'status-diproses',
                                'selesai' => 'status-selesai',
                                'diantar' => 'status-diantar',
                                'diambil' => 'status-diambil',
                                default => 'status-default'
                            };

                            ?>
                            <tr>
                                <td class="order-code">
                                    #LD-<?= htmlspecialchars($item['id_pesanan']) ?>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($item['tgl_masuk'])) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['jenis_pesanan']) ?>
                                </td>
                                <td>
                                    <span class="status-badge <?= htmlspecialchars($status_class) ?>">
                                        <?= htmlspecialchars($item['status_pesanan']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="progress.php?id=<?= htmlspecialchars($item['id_pesanan']) ?>" class="detail-button">
                                        Lihat Detail
                                        <span>›</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="assets/js/pelanggan.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>