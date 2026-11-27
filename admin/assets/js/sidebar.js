document.addEventListener('DOMContentLoaded', function () {

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const hamburger = document.getElementById('hamburger');
    const sidebar = document.querySelector('.sidebar');

    if (!hamburger || !sidebar) return;

    hamburger.addEventListener('click', function () {
        sidebar.classList.toggle('menu-terbuka');
        hamburger.classList.toggle('aktif');
    });

});