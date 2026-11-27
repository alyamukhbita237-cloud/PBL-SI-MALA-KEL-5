<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Pelanggan') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';

$id_user = $_SESSION['id_user'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    if ($password_lama === '' || $password_baru === '' || $konfirmasi_password === '') {
        $error = 'Semua kolom password wajib diisi.';
    } elseif (strlen($password_baru) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = 'Konfirmasi password baru tidak cocok.';
    } else {
        try {
            $query = "SELECT password
                      FROM users
                      WHERE id_user = :id_user";

            $stmt = $pdo->prepare($query);
            $stmt->execute(['id_user' => $id_user]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = 'Data pengguna tidak ditemukan.';
            } elseif (!password_verify($password_lama, $user['password'])) {
                $error = 'Password lama yang kamu masukkan salah.';
            } elseif (password_verify($password_baru, $user['password'])) {
                $error = 'Password baru tidak boleh sama dengan password lama.';
            } else {
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                
                $update = "UPDATE users
                           SET password = :password
                           WHERE id_user = :id_user";

                $stmtUpdate = $pdo->prepare($update);

                $stmtUpdate->execute([
                    'password' => $password_hash,
                    'id_user' => $id_user
                ]);

                header('Location: profile.php?status=password-berhasil');
                exit;
            }

        } catch (PDOException $e) {
            error_log('Ubah password error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan saat mengubah password.';
        }
    }
}

$page_title = "Ubah Password";
$active_page = "profile";

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="main-content">
    <div class="hero-content">
        <h1>Ubah Password</h1>
        <p>Perbarui password akun kamu</p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="edit-profile-page">
        <div class="edit-profile-header">
            <div class="profile-avatar">
                <i data-lucide="lock-keyhole"></i>
            </div>

            <div>
                <h2>Ubah Password</h2>
                <p>Masukkan password lama dan buat password baru.</p>
            </div>
        </div>

        <form method="POST" class="edit-profile-form">
            <div class="edit-form-group">
                <label for="password_lama">Password Lama</label>

                <div class="password-container">
                    <input type="password" id="password_lama" name="password_lama" placeholder="Masukkan password lama" required>
                    <button type="button" class="toggle-password" data-target="password_lama" aria-label="Tampilkan password">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>

            <div class="edit-form-group">
                <label for="password_baru">Password Baru</label>
                <div class="password-container">
                    <input type="password" id="password_baru" name="password_baru" placeholder="Masukkan password baru" minlength="8" required>
                    <button type="button" class="toggle-password" data-target="password_baru" aria-label="Tampilkan password">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
                <small>Password minimal 8 karakter.</small>
            </div>

            <div class="edit-form-group">
                <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                <div class="password-container">
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Masukkan ulang password baru" minlength="8" required>
                    <button type="button" class="toggle-password" data-target="konfirmasi_password" aria-label="Tampilkan password">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>

            <div class="edit-profile-actions">
                <a href="profile.php" class="secondary-button">Batal</a>
                <button type="submit" class="primary-button">Simpan Password</button>
            </div>
        </form>
    </section>
</main>
<script src="assets/js/pelanggan.js"></script>
<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
</body>
</html>