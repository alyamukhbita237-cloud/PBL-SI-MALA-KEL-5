<?php
session_start();
require '../includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    try {
        $query = "SELECT * FROM users 
                  WHERE email = :email 
                  AND password = :password";

        $stmt = $pdo->prepare($query);

        $stmt->execute([
            ":email" => $email,
            ":password" => $password
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            session_regenerate_id(true);

            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama_user'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'Admin') {
                header('Location: ../admin/dashboard.php');
                exit;
            } elseif ($user['role'] === 'Pelanggan') {
                header('Location: ../pelanggan/dashboard.php');
                exit;
            } else {
                echo "Email atau password anda salah!";
            }
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "Terjadi kesalahan saat login";
    }
}
?>