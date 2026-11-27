lucide.createIcons();
const password = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');
if (password && togglePassword) {
    togglePassword.addEventListener('click', function () {
        if (password.type === 'password') {
            password.type = 'text';
            document.getElementById('iconPassword').setAttribute('data-lucide', 'eye-off');
        } else {
            password.type = 'password';
            document.getElementById('iconPassword').setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    });
}

// konfirmasi password
const konfirmasiPassword = document.getElementById('konfirmasi_password');
const toggleKonfirmasiPassword = document.getElementById('toggleKonfirmasiPassword');
if (konfirmasiPassword && toggleKonfirmasiPassword) {
    toggleKonfirmasiPassword.addEventListener('click', function () {
        if (konfirmasiPassword.type === 'password') {
            konfirmasiPassword.type = 'text';
            document.getElementById('iconKonfirmasiPassword').setAttribute('data-lucide', 'eye-off');
        } else {
            konfirmasiPassword.type = 'password';
            document.getElementById('iconKonfirmasiPassword').setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    });
}