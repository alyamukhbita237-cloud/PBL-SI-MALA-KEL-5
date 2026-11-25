<?php
$page_title = "Edit Pesanan";
$active_page = "kelola-pesanan";

require_once __DIR__ . '/../includes/koneksi.php';

$id_pesanan = $_GET['id_pesanan'] ?? '';
if (empty($id_pesanan)) {
    die("ID pesanan tidak ditemukan");
}

$sql = "WITH edit_pesanan AS (
	SELECT u.nama_user, p.id_pesanan, dp.id_detail_pesanan,
        l.jenis_layanan, dp.jumlah AS berat, l.satuan, l.harga_per_layanan,
		(dp.jumlah * l.harga_per_layanan) AS subtotal
	FROM users u
	JOIN pesanan p ON p.id_user = u.id_user
	JOIN detail_pesanan dp ON dp.id_pesanan = p.id_pesanan
	JOIN layanan l ON l.id_layanan = dp.id_layanan
    WHERE p.id_pesanan = :id
    )
    SELECT * FROM edit_pesanan";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id_pesanan]);
$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$pesanan) {
    die("Pesanan tidak ditemukan");
}

$nama_user = $pesanan[0]['nama_user'];
$total_pesanan = 0;
foreach ($pesanan as $item) {
    $total_pesanan += (float) $item['subtotal'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $id_detail = $_POST['id_detail_pesanan'] ?? [];
        $berat = $_POST['jumlah'] ?? [];

        $stmtUpdate = $pdo->prepare(
            "UPDATE detail_pesanan dp
            SET jumlah = ?,
                subtotal = ? * (
                    SELECT harga_per_layanan FROM layanan l
                    WHERE l.id_layanan = dp.id_layanan)
            WHERE id_detail_pesanan = ? AND id_pesanan = ?"
        );

        foreach ($id_detail as $i => $id) {
            $jumlah = (float) ($berat[$i] ?? 0);
            if ($jumlah <= 0) {
                throw new Exception("Berat/jumlah harus lebih dari 0");
            }

            $stmtUpdate->execute([$jumlah, $jumlah, $id, $id_pesanan]);
        }

        // Perbarui total harga pesanan
        $stmtTotal = $pdo->prepare(
            "UPDATE pesanan
            SET total_harga = (
                SELECT COALESCE(SUM(subtotal), 0)
                FROM detail_pesanan
                WHERE id_pesanan = ?
            )
            WHERE id_pesanan = ?");

        $stmtTotal->execute([$id_pesanan, $id_pesanan]);
        $pdo->commit();

        header('Location: edit-pesanan.php?id_pesanan=' .urlencode($id_pesanan));
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        die("Gagal menyimpan perubahan: " . $e->getMessage());
    }
} 

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="main-content halaman-edit-pesanan">
    <section class="judul-halaman">
        <div>
            <div class="judul-dengan-panah">
                <a href="edit-pesanan.php?id_pesanan=<?= urlencode($id_pesanan) ?>">
                    Edit Pesanan
                </a>
            </div>
            <p>Perbarui informasi dan rincian pesanan pelanggan</p>
        </div>
    </section>

    <form method="POST", onsubmit="return confirm('Apakah data sudah benar?')">
        <!-- informasi pesanan -->
        <section class="kartu-detail">
            <h3>Informasi Pesanan</h3>

            <div class="edit-info-grid">
                <div class="edit-group">
                    <label>ID Pesanan</label>
                    <input type="text" class="edit-input" value="<?= htmlspecialchars($id_pesanan) ?>"
                        readonly
                    >
                </div>

                <div class="edit-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" class="edit-input" value="<?= htmlspecialchars($nama_user) ?>"
                        readonly
                    >
                </div>
            </div>
        </section>

        <!-- Detail pesanan -->
        <section class="kartu-detail">
            <h3>Detail Pesanan</h3>
            <?php foreach ($pesanan as $index => $item): ?>
                <div class="edit-item-layanan">
                    <h4>Pesanan <?= $index + 1 ?></h4>
                    <input type="hidden" name="id_detail_pesanan[]"
                        value="<?= htmlspecialchars($item['id_detail_pesanan']) ?>">

                    <div class="edit-detail-grid">
                        <div class="edit-group">
                            <label>Jenis Layanan</label>
                            <input type="text" class="edit-input" value="<?= htmlspecialchars($item['jenis_layanan']) ?>"
                                readonly>
                        </div>

                        <div class="edit-group">
                            <label>Berat</label>
                            <input type="number" name="jumlah[]"  class="edit-input berat"
                                value="<?= htmlspecialchars($item['berat']) ?>"
                                min="0.01" step="0.01" required>
                        </div>

                        <div class="edit-group">
                            <label>Satuan</label>
                            <select name="satuan[]" class="edit-input satuan">
                                <option value="kg" <?= $item['satuan'] == 'kg' ? 'selected' : '' ?>>kg</option>
                                <option value="pcs" <?= $item['satuan'] == 'pcs' ? 'selected' : '' ?>>pcs</option>
                            </select>
                        </div>

                        <div class="edit-group">
                            <label>Harga per Layanan</label>
                            <input type="text" class="edit-input" value="Rp<?= number_format((float)$item['harga_per_layanan'], 0, ',', '.') ?>"
                                readonly>
                        </div>

                        <div class="edit-group">
                            <label>Subtotal</label>
                            <input type="text" class="edit-input harga" 
                                    data-harga="<?= htmlspecialchars($item['harga_per_layanan']) ?>"
                                    value="Rp<?= number_format((float)$item['subtotal'], 0, ',', '.') ?>"
                                readonly>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Tambah pesanan -->
            <a
                href="tambah-pesanan.php?id_pesanan=<?= urlencode($id_pesanan) ?>"
                class="edit-add">
                <span>+</span>
                Tambah Pesanan
            </a>
        </section>

        <!-- Total keseluruhan -->
        <section class="kartu-detail">
            <div class="edit-total">
                <span>Total Keseluruhan</span>

                <strong id="totalKeseluruhan">
                    Rp<?= number_format($total_pesanan, 0, ',', '.') ?>
                </strong>
            </div>
        </section>

        <!-- Tombol aksi -->
        <section class="edit-buttons">
            <a href="kelola-pesanan.php" class="edit-btn edit-cancel">Batal</a>
            <button type="submit" class="edit-btn edit-save">Simpan Perubahan</button>
        </section>
    </form>
</main>

<script src="assets/js/edit-pesanan.js"></script>