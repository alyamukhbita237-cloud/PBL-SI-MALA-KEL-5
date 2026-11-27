<?php
require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

if ($token === '' || $password === '' || $konfirmasiPassword === '') {
    header("Location: ../lupa-password.php?status=gagal&pesan=" . urlencode("Data wajib diisi."));
    exit;
}

if (strlen($password) < 8) {
    header("Location: ../reset-password.php?token=" . urlencode($token) . "&status=gagal&pesan=" . urlencode("Password minimal 8 karakter."));
    exit;
}

if ($password !== $konfirmasiPassword) {
    header("Location: ../reset-password.php?token=" . urlencode($token) . "&status=gagal&pesan=" . urlencode("Konfirmasi password tidak sama."));
    exit;
}

try {
    $tokenHash = hash('sha256', $token);

    // Periksa token dan masa berlakunya
    $query = "SELECT id_user
              FROM public.users
              WHERE token_reset = :token
              AND batas_waktu_reset > CURRENT_TIMESTAMP";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':token' => $tokenHash
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../lupa-password.php?status=gagal&pesan=" . urlencode("Link reset tidak valid atau sudah kedaluwarsa."));
        exit;
    }

    // Hash password baru
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Simpan password baru dan hapus token
    $update = "UPDATE public.users
               SET password = :password,
                   token_reset = NULL,
                   batas_waktu_reset = NULL
               WHERE id_user = :id_user
               AND token_reset = :token
               AND batas_waktu_reset > CURRENT_TIMESTAMP";

    $stmtUpdate = $pdo->prepare($update);

    $stmtUpdate->execute([
        ':password' => $passwordHash,
        ':id_user' => $user['id_user'],
        ':token' => $tokenHash
    ]);

    if ($stmtUpdate->rowCount() !== 1) {
        header("Location: ../lupa-password.php?status=gagal&pesan=" . urlencode("Link reset tidak valid atau sudah digunakan."));
        exit;
    }
    header("Location: ../login.php?status=berhasil&pesan=" . urlencode("Password berhasil diubah. Silakan login."));
    exit;

} catch (PDOException $e) {
    error_log("Reset password error: " . $e->getMessage());
    header("Location: ../lupa-password.php?status=gagal&pesan=" . urlencode("Terjadi kesalahan saat mengubah password."));
    exit;
}
?>