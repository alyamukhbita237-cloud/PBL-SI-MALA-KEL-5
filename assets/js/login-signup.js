lucide.createIcons();
const password = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', function () {

    if (password.type === 'password') {
        password.type = 'text';
        document.getElementById('iconPassword')
            .setAttribute('data-lucide', 'eye-off');
    } else {
        password.type = 'password';
        document.getElementById('iconPassword')
            .setAttribute('data-lucide', 'eye');
    }

   lucide.createIcons();
});