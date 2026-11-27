<?php
require_once __DIR__ . '/includes/koneksi.php';

$token = $_GET['token'] ?? '';
$tokenValid = false;

if ($token !== '') {
    try {
        $tokenHash = hash('sha256', $token);

        $query = "SELECT id_user
                  FROM public.users
                  WHERE token_reset = :token
                  AND batas_waktu_reset > CURRENT_TIMESTAMP";

        $stmt = $pdo->prepare($query);
        $stmt->execute([':token' => $tokenHash]);
        $tokenValid = $stmt->fetch(PDO::FETCH_ASSOC) !== false;

    } catch (PDOException $e) {
        error_log("Cek token reset error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-MALA - RESET PASSWORD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<main class="login-page flex min-h-screen flex-col lg:flex-row">

    <!-- left side -->
    <section class="login-left lg:w-1/2 relative">
        <div class="brand flex items-center gap-3">
            <span>Si-Mala</span>
        </div>

        <div class="welcoming-text">
            <h1>Buat Password <br>Baru 🧺</h1>
            <p>
                Buat kata sandi baru agar kamu
                bisa kembali menggunakan SI-MALA.
            </p>
        </div>
    </section>

    <!-- right side -->
    <section class="login-right w-full lg:w-1/2 min-h-screen flex items-center justify-center">
        <div class="login-form-container w-full">

            <div class="form-header text-center">
                <h2>Reset Password</h2>
                <p>Masukkan kata sandi baru untuk akunmu</p>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <p style="color: <?= ($_GET['status'] ?? '') === 'berhasil' ? 'green' : 'red' ?>; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($_GET['pesan'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if (!$tokenValid): ?>

                <p style="color:red; text-align:center; margin-bottom:20px;">
                    Link reset password tidak valid atau sudah kedaluwarsa.
                </p>

                <p class="signup-text text-center">
                    <a href="lupa-password.php">Minta link reset baru</a>
                </p>

            <?php else: ?>

                <form action="proses/reset-password-process.php" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <!-- password baru -->
                    <div class="form-group">
                        <label for="password">Password Baru</label>

                        <div class="password-container">
                            <input type="password" id="password" name="password" placeholder="Masukkan password baru" minlength="8" required>

                            <button type="button" id="togglePassword">
                                <i data-lucide="eye" id="iconPassword"></i>
                            </button>
                        </div>
                    </div>

                    <!-- konfirmasi password -->
                    <div class="form-group">
                        <label for="konfirmasi_password">Konfirmasi Password</label>

                        <div class="password-container">
                            <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Masukkan ulang password baru" minlength="8" required>

                            <button type="button" id="toggleKonfirmasiPassword">
                                <i data-lucide="eye" id="iconKonfirmasiPassword"></i>
                            </button>
                        </div>
                    </div>

                    <!-- button -->
                    <button type="submit" class="login-button w-full">
                        Simpan Password Baru
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="assets/js/login-signup.js"></script>
</body>
</html>