<?php
require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../signup.php");
    exit;
}

$nama = trim($_POST["nama"] ?? '');
$no_hp = trim($_POST["no_hp"] ?? '');
$email = trim($_POST["email"] ?? '');
$password = $_POST["password"] ?? '';

if ($nama === '' || $no_hp === '' || $email === '' || $password === '') {
    header("Location: ../signup.php?status=gagal&pesan=" . urlencode("Semua data wajib diisi."));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../signup.php?status=gagal&pesan=" . urlencode("Format email tidak valid."));
    exit;
}

if (strlen($password) < 8) {
    header("Location: ../signup.php?status=gagal&pesan=" . urlencode("Password minimal 8 karakter."));
    exit;
}

try {
    // Periksa apakah email sudah terdaftar
    $cek = $pdo->prepare("SELECT id_user FROM public.users WHERE email = :email");
    $cek->execute([':email' => $email]);
    if ($cek->fetch()) {
        header("Location: ../signup.php?status=gagal&pesan=" . urlencode("Email sudah terdaftar."));
        exit;
    }

    // Amankan password sebelum disimpan
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Role harus sama dengan yang diperiksa pada login
    $role = "Pelanggan";

    $query = "INSERT INTO public.users
              (nama_user, email, password, role, no_hp)
              VALUES
              (:nama, :email, :password, :role, :no_hp)";

    $stmt = $pdo->prepare($query);

    $stmt->execute([
        ':nama' => $nama,
        ':email' => $email,
        ':password' => $passwordHash,
        ':role' => $role,
        ':no_hp' => $no_hp
    ]);

    // Jika berhasil, arahkan ke halaman login
    header("Location: ../login.php?status=berhasil&pesan=" . urlencode("Pendaftaran berhasil. Silakan login."));
    exit;

} catch (PDOException $e) {
    error_log("Signup error: " . $e->getMessage());
    header("Location: ../signup.php?status=gagal&pesan=" . urlencode("Pendaftaran gagal. Silakan coba lagi."));
    exit;
}
?>