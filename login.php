<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-MALA - LOGIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="login-page flex min-h-screen flex-col lg:flex-row">

    <!-- left side -->
    <section class="login-left hidden lg:flex lg:w-1/2 relative">
        <div class="brand flex items-center gap-3">
            <span>Si-Mala</span>
        </div>

        <div class="welcoming-text">
            <h1>Laundry kamu, <br>kami bantu urus 🧺</h1>
            <p>Mulai dari penjemputan hingga<br>laundry siap kembali.</p>   
        </div>
        
       
    </section>

    <!-- right side -->

    <section class="login-right w-full lg:w-1/2 min-h-screen flex items-center justify-center" >
        <div class="login-form-container w-full">
            <div class="form-header text-center">
                <h2>Selamat Datang!</h2>
                <p>Masuk kembali ke akunmu</p>
            </div>

            <form action="#" method="POST">
                <!-- email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email kamu" required>
                </div>

                <!-- password -->
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan kata sandi kamu" required>
                </div>

                <!-- option -->
                <div class="form-option flex items-center justify-between">
                    
                <label class="remember flex items-center gap-2">
                    <input type="checkbox" name="remember">
                    <span>Ingat saya</span>
                </label>

                    <a href="#">Lupa Kata Sandi?</a>
                </div>

                <!-- button -->
                <button type="submit" class="login-button w-full">Masuk</button>
            </form>

            <p class="signup-text text-center">Belum punya akun?<a href="signup.php"> Daftar Sekarang</a>
            </p>
        </div>
    </section>
    
</main>
</body>
</html>