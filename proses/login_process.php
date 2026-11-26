<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}

$email = trim($_POST["email"] ?? '');
$password = $_POST["password"] ?? '';

if ($email === '' || $password === '') {
    header("Location: ../login.php?status=gagal&pesan=" . urlencode("Email dan password wajib diisi."));
    exit;
}

try {
    // Cari pengguna berdasarkan email
    $query = "SELECT * FROM public.users WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../login.php?status=gagal&pesan=" . urlencode("Email atau password salah."));
        exit;
    }

    // Periksa password yang sudah di-hash
    $passwordBenar = password_verify($password, $user['password']);

    // Mendukung akun lama yang password-nya masih berupa teks biasa
    if (!$passwordBenar && $password === $user['password']) {
        $passwordBaru = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare(
            "UPDATE public.users SET password = :password WHERE id_user = :id_user"
        );

        $update->execute([
            ':password' => $passwordBaru,
            ':id_user' => $user['id_user']
        ]);

        $passwordBenar = true;
    }

    if (!$passwordBenar) {
        header("Location: ../login.php?status=gagal&pesan=" . urlencode("Email atau password salah."));
        exit;
    }

    // Periksa role pengguna
    if (!in_array($user['role'], ['Admin', 'Pelanggan'], true)) {
        header("Location: ../login.php?status=gagal&pesan=" . urlencode("Role pengguna tidak dikenali."));
        exit;
    }

    // Buat session login
    session_regenerate_id(true);
    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['nama_user'] = $user['nama_user'];
    $_SESSION['nama'] = $user['nama_user'];
    $_SESSION['role'] = $user['role'];

    // Arahkan sesuai role
    if ($user['role'] === 'Pelanggan') {
        header("Location: ../pelanggan/dashboard.php");
        exit;
    }

    if ($user['role'] === 'Admin') {
        header("Location: ../admin/dashboard.php");
        exit;
    }

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());

    header("Location: ../login.php?status=gagal&pesan=" . urlencode("Terjadi kesalahan saat login."));
    exit;
}
?>