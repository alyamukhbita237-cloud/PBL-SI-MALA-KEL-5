<?php
$page_title = "Dashboard";
$active_page = "dashboard";

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">

<!-- hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Halo, Athia!</h1>
            <p>Kelola kebutuhan laundry kamu dengan mudah</p>
            <div class="hero-buttons">  
                <a href="antar-jemput.php">+ &nbsp; Pesan Layanan</a>
                <a href="pesanan.php">Lihat Pesananmu</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="images/laundry-image.png" alt="laundry">
        </div>
    </section>

<!-- pesanan berjalan -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>Pesanan yang sedang berjalan</h2>
            <a href="pesanan.php">Lihat semua -></a>

        </div>
        <div class="order-card">
            <div class="order-top">
                <div class="order-info">
                    <h3>Pesanan #LD-005</h3>
                    <p>Cuci Setrika &nbsp;•&nbsp; 5kg </p>
                </div>

            <div class="payment">
                <span>Total Bayar: </span>
                <strong>Rp30.000</strong>
            </div>
        </div>
            

            <!-- progress -->

            <div class="progress-wrapper">
                <div class="progress-line">
                    <div class="progress-line-active"></div>
                </div>

                <div class="progress-step completed">
                    <div class="step-circle">
                        <i data-lucide="circle-check"></i>
                    </div>
                    <span>Pesanan Dibuat</span>
                </div>

                <div class="progress-step current">
                    <div class="step-circle">
                        <i data-lucide="loader-circle"></i>
                    </div>
                    <span>Pesanan Dikonfirmasi</span>
                </div>

                <div class="progress-step">
                    <div class="step-circle">
                        <i data-lucide="circle"></i>
                        
                    </div>
                    <span>Pesanan Dikerjakan</span>
                </div>

                <div class="progress-step">
                    <div class="step-circle">
                        <i data-lucide="circle"></i>
                        
                    </div>
                    <span>Pesanan Selesai</span>
                </div>
            </div>

            <!-- lihat detail progress -->
            <div class="order-detail">
                <a href="progress.php">
                    Lihat Detail Progress -> 
                </a>
            </div>
        </div>

    </section>

    <section class="dashboard-section">
        <div class="section-header">
            <h2>Pesanan Sebelumnya</h2>
            <a href="pesanan.php">Lihat Semua -></a>
        </div>

        <div class="history-card">
            <!-- pesanan 1 -->
            <div class="history-item">
                <div class="history-info">
                    <h3>#LD-003</h3>
                    <p>Setrika • 6,5kg</p>
                    <small>15 sept 2026</small>
                </div>
                <div class="history-price">
                    Rp35.000
                </div>
            </div>

            <div class="history-divider"></div>

            <!-- pesanan 2 -->
            <div class="history-item">
                <div class="history-info">
                    <h3>#LD-004</h3>
                    <p>Cuci Kering Setrika • 5kg</p>
                    <small>15 sept 2026</small>
                </div>
                <div class="history-price">
                    Rp30.000
                </div>
            </div>


        </div>
    </section>
</main>
<script>
    lucide.createIcons();
</script>
