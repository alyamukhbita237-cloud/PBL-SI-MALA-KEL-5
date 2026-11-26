
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    try {
        $query = "SELECT * FROM users
                  WHERE email = :email";

        $stmt = $pdo->prepare($query);

        $stmt->execute([
            ":email" => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            // Mengecek password yang sudah di-hash
            if (password_verify($password, $user['password'])) {

                $loginBerhasil = true;

            // Mengecek password lama yang masih plain text
            } elseif ($password === $user['password']) {

                $loginBerhasil = true;

            } else {

                $loginBerhasil = false;
            }

            if ($loginBerhasil) {

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

            } else {

                echo "Email atau password anda salah!";
            }

        } else {

            echo "Email atau password anda salah!";
        }

    } catch (PDOException $e) {

        error_log($e->getMessage());
        echo "Terjadi kesalahan saat login";
    }
}
?>
