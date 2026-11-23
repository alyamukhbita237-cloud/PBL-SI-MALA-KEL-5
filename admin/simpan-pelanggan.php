<?php
session_start();

require_once __DIR__ . '/../includes/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data-pelanggan.php');
    exit;
}

try {
    $nama = trim($_POST['nama'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama == '' || $noHp == '' || $email == '' || $password == '') {
        throw new Exception('Semua data wajib harus diisi.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid.');
    }

    if (strlen($nama) > 50 || strlen($email) > 50 || strlen($noHp) > 15) {
        throw new Exception('Data terlalu panjang.');
    }

    $sql = "SELECT id_user FROM public.users WHERE email = :email";
    $cek = $pdo->prepare($sql);
    $cek->execute(['email' => $email]);

    if ($cek->fetch()) {
        throw new Exception('Email sudah terdaftar.');
    }

    $sql = "INSERT INTO public.users
            (nama_user, email, password, role, no_hp, alamat)
            VALUES
            (:nama, :email, :password, 'Pelanggan', :no_hp, :alamat)";

    $simpan = $pdo->prepare($sql);

    $simpan->execute([
        'nama' => $nama,
        'email' => $email,
        'password' => 'SUPABASE_AUTH',
        'no_hp' => $noHp,
        'alamat' => $alamat
    ]);

    header('Location: data-pelanggan.php?berhasil=1');
    exit;

} catch (Throwable $e) {
    echo 'Gagal menyimpan data pelanggan: ';
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo '<br><a href="data-pelanggan.php">Kembali</a>';
}