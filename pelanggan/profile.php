<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/koneksi.php';

$id_user = $_SESSION['id_user'];

$query = "SELECT id_user, nama_user, email, no_hp, alamat
          FROM users
          WHERE id_user = :id_user";

$stmt = $pdo->prepare($query);
$stmt->execute(['id_user' => $id_user]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Data pengguna tidak ditemukan.");
}

$page_title = "Profil Saya";
$active_page = "profile";
$nama = $user['nama_user'];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">

    <div class="hero-content">
        <h1>Profil Saya</h1>
        <p>Kelola informasi akun kamu</p>
    </div>

    <section class="profile-page">

        <div class="profile-header">
            <div class="profile-avatar">
                <i data-lucide="user"></i>
            </div>

            <div>
                <h2><?= htmlspecialchars($user['nama_user']) ?></h2>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <div class="profile-divider"></div>

        <div class="profile-form">

            <div class="profile-field">
                <label>Nama Lengkap</label>
                <div class="profile-value">
                    <?= htmlspecialchars($user['nama_user'] ?? '-') ?>
                </div>
            </div>

            <div class="profile-field">
                <label>Email</label>
                <div class="profile-value">
                    <?= htmlspecialchars($user['email'] ?? '-') ?>
                </div>
            </div>

            <div class="profile-field">
                <label>No. HP</label>
                <div class="profile-value">
                    <?= htmlspecialchars($user['no_hp'] ?? '-') ?>
                </div>
            </div>

            <div class="profile-field">
                <label>Alamat</label>
                <div class="profile-value">
                    <?= htmlspecialchars($user['alamat'] ?? '-') ?>
                </div>
            </div>

        </div>

        <div class="profile-actions">
            <a href="ubah-password.php" class="secondary-button">
                <i data-lucide="lock-keyhole"></i>
                Ubah Password
            </a>

            <a href="edit_profile.php" class="primary-button">
                <i data-lucide="pencil"></i>
                Ubah Profil
            </a>
        </div>

    </section>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>