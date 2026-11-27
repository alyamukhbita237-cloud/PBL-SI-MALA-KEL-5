<?php
$pesan = $_GET['pesan'] ?? '';
$status = $_GET['status'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-MALA - LUPA PASSWORD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<main class="login-page flex min-h-screen flex-col lg:flex-row">

    <!-- Bagian kiri -->
    <section class="login-left lg:w-1/2 relative">
        <div class="brand flex items-center gap-3">
            <span>Si-Mala</span>
        </div>

        <div class="welcoming-text">
            <h1>Lupa Password? <br>Kami bantu! 🧺</h1>
            <p>
                Jangan khawatir, kamu bisa mendapatkan
                kembali akses ke akun SI-MALA.
            </p>
        </div>
    </section>

    <!-- Bagian kanan -->
    <section class="login-right w-full lg:w-1/2 min-h-screen flex items-center justify-center">
        <div class="login-form-container w-full">
            <div class="form-header text-center">
                <h2>Reset Password</h2>
                <p>Masukkan email yang terdaftar pada akunmu</p>
            </div>

            <?php if ($pesan !== ''): ?>
                <p style="color: <?= $status === 'berhasil' ? 'green' : 'red' ?>; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form action="proses/kirim-link-reset.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email terdaftar" required>
                </div>

                <button type="submit" class="login-button w-full">Kirim Link Reset Password</button>
            </form>

            <!-- Kembali ke login -->
            <p class="signup-text text-center">Ingat kata sandi kamu?<a href="login.php"> Masuk Sekarang</a></p>
        </div>
    </section>
</main>
</body>
</html>