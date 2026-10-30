<!DOCTYPE html>
<html lang="id">
<head>
    <meta charseat="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Si-Mala</title>

    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="assets/css/admin.css">
    <!-- icon -->
    <script src="https://unpkg.com/lucide@latest"></script> 

</head>
<body>
    <header class="topbar">
        <div class="logo">
            Si-Mala
        </div>
        <button type="button" class="tombol-menu" id="tombolMenu"
            aria-label="Buka menu" aria-expanded="false">
            ☰
        </button>
    </header>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const tombolMenu = document.getElementById('tombolMenu');
    const sidebar = document.querySelector('.sidebar');

    if (!tombolMenu || !sidebar) return;

    tombolMenu.addEventListener('click', function () {
        const terbuka = sidebar.classList.toggle('menu-terbuka');

        tombolMenu.setAttribute('aria-expanded', terbuka);
        tombolMenu.setAttribute(
            'aria-label',
            terbuka ? 'Tutup menu' : 'Buka menu'
        );
    });

    sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('menu-terbuka');
                tombolMenu.setAttribute('aria-expanded', 'false');
                tombolMenu.setAttribute('aria-label', 'Buka menu');
            }
        });
    });
});
</script>
</body>
</html>