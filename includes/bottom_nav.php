<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
<!-- Floating Button khusus Admin untuk Manajemen Role -->
<a href="roles.php" class="fab-roles" title="Manajemen Role">
    <i class="fas fa-users-cog"></i>
</a>
<?php endif; ?>

<nav class="bottom-nav">
    <a href="index.php" class="bottom-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    
    <a href="riwayat.php" class="bottom-nav-item">
        <i class="fas fa-history"></i>
        <span>Riwayat</span>
    </a>

    <!-- Tombol Tengah yang menonjol untuk SCAN -->
    <a href="scan.php" class="bottom-nav-item scan-btn-wrapper">
        <div class="scan-btn">
            <i class="fas fa-qrcode"></i>
        </div>
    </a>

    <?php if(isset($_SESSION['role']) && ($_SESSION['role'] === 'petugas_rm' || $_SESSION['role'] === 'superadmin')): ?>
    <a href="print.php" class="bottom-nav-item">
        <i class="fas fa-print"></i>
        <span>Cetak QR</span>
    </a>
    <?php endif; ?>

    <!-- Profil / Logout -->
    <a href="logout.php" class="bottom-nav-item text-danger">
        <i class="fas fa-sign-out-alt"></i>
        <span>Keluar</span>
    </a>
</nav>
