<aside class="sidebar">
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="sidebar-link" <?= $active_page === 'dashboard' ? 'active' : ''?>>
            Dashboard
        </a>

        <a href="pesanan.php" class="sidebar-link" <?= $active_page === 'pesanan' ? 'active' : ''?>>
            Pesanan Saya
        </a>

        <a href="katalog.php" class="sidebar-link" <?= $active_page === 'katalog' ? 'active' : ''?>>
            Katalog Layanan
        </a>

        <a href="antar-jemput.php" class="sidebar-link" <?= $active_page === 'antar-jemput' ? 'active' : ''?>>
            Pesan Antar-Jemput
        </a>

    </nav>

</aside>