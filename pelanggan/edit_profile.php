<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/koneksi.php';

$id_user = $_SESSION['id_user'];

$error = '';
$success = '';

// Ambil data user
$query = "SELECT nama_user, email, no_hp, alamat
          FROM users
          WHERE id_user = :id_user";

$stmt = $pdo->prepare($query);
$stmt->execute(['id_user' => $id_user]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Data pengguna tidak ditemukan.");
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '') {
        $error = 'Nama wajib diisi.';
    } else {

        try {
            $update = "UPDATE users
                       SET nama_user = :nama,
                           no_hp = :no_hp,
                           alamat = :alamat
                       WHERE id_user = :id_user";

            $stmtUpdate = $pdo->prepare($update);

            $stmtUpdate->execute([
                'nama' => $nama,
                'no_hp' => $no_hp,
                'alamat' => $alamat,
                'id_user' => $id_user
            ]);

            // Update nama di session supaya header ikut berubah
            $_SESSION['nama'] = $nama;

            $success = 'Profil berhasil diperbarui.';
            header('Location: profile.php');
            exit;


        } catch (PDOException $e) {
            $error = 'Profil gagal diperbarui.';
        }
    }
}

$page_title = "Edit Profil";
$active_page = "profile";

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">

    <div class="hero-content">
        <h1>Edit Profil</h1>
        <p>Perbarui informasi profil kamu</p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <section class="edit-profile-page">

        <div class="edit-profile-header">
            <div class="profile-avatar">
                <i data-lucide="user"></i>
            </div>

            <div>
                <h2><?= htmlspecialchars($user['nama_user']) ?></h2>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <form method="POST" class="edit-profile-form">

            <div class="edit-form-group">
                <label for="nama">Nama Lengkap</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="<?= htmlspecialchars($user['nama_user']) ?>"
                    required
                >
            </div>

            <div class="edit-form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    value="<?= htmlspecialchars($user['email']) ?>"
                    disabled
                >
                <small>Email tidak dapat diubah.</small>
            </div>

            <div class="edit-form-group">
                <label for="no_hp">Nomor HP</label>
                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>"
                    placeholder="Masukkan nomor HP"
                >
            </div>

            <div class="edit-form-group">
                <label for="alamat">Alamat</label>
                <textarea
                    id="alamat"
                    name="alamat"
                    rows="4"
                    placeholder="Masukkan alamat lengkap"
                ><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
            </div>

            <div class="edit-profile-actions">

                <a href="profile.php" class="secondary-button">
                    Batal
                </a>

                <button type="submit" class="primary-button">
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </section>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>