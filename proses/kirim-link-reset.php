<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/email_config.php';

function kembaliKeLupaPassword($status, $pesan) {
    header('Location: ../lupa-password.php?status=' . urlencode($status) . '&pesan=' . urlencode($pesan));
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        kembaliKeLupaPassword('gagal', 'Permintaan tidak valid.');
    }
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        kembaliKeLupaPassword('gagal', 'Masukkan alamat email yang valid.');
    }

    $sql = "SELECT id_user, nama_user, email
            FROM public.users
            WHERE LOWER(email) = LOWER(:email)
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $pesanSukses = 'Jika email terdaftar, link reset password akan dikirim ke email tersebut.';

    if (!$user) {
        kembaliKeLupaPassword('berhasil', $pesanSukses);
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $sqlUpdate = "UPDATE public.users
                  SET token_reset = :token,
                      batas_waktu_reset =
                          CURRENT_TIMESTAMP + INTERVAL '30 minutes'
                  WHERE id_user = :id_user";

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([':token' => $tokenHash, ':id_user' => $user['id_user']]);
    $baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/');

    $linkReset = $baseUrl . '/reset-password.php?token=' . urlencode($token);
    $mail = buatMailer();
    $mail->addAddress($user['email'], $user['nama_user']);
    $mail->isHTML(true);
    $mail->Subject = 'Reset Password - SI-MALA';
    $nama = htmlspecialchars($user['nama_user'], ENT_QUOTES, 'UTF-8');
    $linkAman = htmlspecialchars($linkReset, ENT_QUOTES, 'UTF-8');
    $mail->Body = "<div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Reset Password SI-MALA</h2>
            <p>Halo, <strong>{$nama}</strong>.</p>
            <p>
                Kami menerima permintaan untuk mengatur ulang
                password akun SI-MALA kamu.
            </p>

            <p>
                Klik tombol berikut untuk membuat password baru:
            </p>

            <p>
                <a href='{$linkAman}'
                   style='background-color: #84b7f1;
                          color: white;
                          padding: 12px 20px;
                          text-decoration: none;
                          border-radius: 6px;
                          display: inline-block;'>
                    Reset Password
                </a>
            </p>

            <p>
                Link ini hanya berlaku selama 30 menit.
            </p>

            <p>
                Jika kamu tidak merasa meminta reset password,
                abaikan email ini.
            </p>

            <p>Salam,<br>SI-MALA Klin-Klin Laundry</p>
        </div>
    ";

    $mail->AltBody =
        "Halo {$user['nama_user']},\n\n" .
        "Gunakan link berikut untuk reset password:\n" .
        $linkReset . "\n\n" .
        "Link berlaku selama 30 menit.";

    if (!$mail->send()) {
        throw new RuntimeException(
            'Email gagal dikirim: ' . $mail->ErrorInfo
        );
    }
    kembaliKeLupaPassword('berhasil', $pesanSukses);
} catch (PDOException $e) {
    error_log('ERROR DATABASE RESET PASSWORD: ' . $e->getMessage());
    kembaliKeLupaPassword('gagal', 'Terjadi kesalahan database. Silakan coba lagi.');
} catch (Throwable $e) {
    error_log('ERROR RESET PASSWORD: ' . $e->getMessage());
    if (isset($user['id_user'])) {
        try {
            $sqlHapus = "UPDATE public.users
                         SET token_reset = NULL,
                             batas_waktu_reset = NULL
                         WHERE id_user = :id_user";

            $stmtHapus = $pdo->prepare($sqlHapus);
            $stmtHapus->execute([':id_user' => $user['id_user']]);
        } catch (Throwable $hapusError) {
            error_log('ERROR MENGHAPUS TOKEN RESET: ' . $hapusError->getMessage());
        }
    }
    kembaliKeLupaPassword('gagal', 'Terjadi kesalahan saat mengirim link reset password. Silakan coba lagi.');
}