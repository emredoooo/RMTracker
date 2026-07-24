<?php 
require_once 'includes/auth.php';
require_once 'config/database.php';

// Filter berdasarkan role
$is_admin = ($user_role === 'superadmin' || $user_role === 'petugas_rm');
$holder_condition = $is_admin ? "" : "AND holder_id = " . (int)$user_id;

// Ambil statistik (Berkas Dipinjam / Tanggungan)
$stmt = $pdo->query("SELECT COUNT(*) as dipinjam FROM rm_current_status WHERE status = 'DIPINJAM' $holder_condition");
$dipinjam = $stmt->fetch()['dipinjam'];

// Ambil statistik Overdue
$stmt2 = $pdo->query("SELECT COUNT(*) as overdue FROM rm_current_status WHERE status = 'DIPINJAM' AND borrowed_at < DATE_SUB(NOW(), INTERVAL 24 HOUR) $holder_condition");
$overdue = $stmt2->fetch()['overdue'];

// Ambil Berkas Aktif Group by No RM
if ($is_admin) {
    // Admin melihat 10 pergerakan berkas terbaru (dikumpulkan per RM yang aktif bergerak)
    $stmt3 = $pdo->query("
        SELECT r.no_rkm_medis, r.status, r.lokasi_terkini, r.updated_at, u.nama as holder_name
        FROM rm_current_status r
        LEFT JOIN users u ON r.holder_id = u.id
        ORDER BY r.updated_at DESC
        LIMIT 10
    ");
} else {
    // Perawat hanya melihat berkas yang SEDANG dia pegang saat ini
    $stmt3 = $pdo->query("
        SELECT r.no_rkm_medis, r.status, r.lokasi_terkini, r.updated_at, u.nama as holder_name
        FROM rm_current_status r
        LEFT JOIN users u ON r.holder_id = u.id
        WHERE r.holder_id = " . (int)$user_id . "
        ORDER BY r.updated_at DESC
    ");
}
$active_files = $stmt3->fetchAll();

include 'includes/header.php'; 
?>

<!-- Header Atas -->
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0">Halo, <strong><?php echo htmlspecialchars($user_nama); ?></strong></h5>
        <small class="text-muted"><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($user_unit); ?></small>
    </div>
    <div class="text-end">
        <span class="badge bg-primary rounded-pill px-3 py-2">Role: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user_role))); ?></span>
    </div>
</div>

<!-- Statistik Cepat -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card card-shadow p-3 text-center border-0 bg-primary text-white h-100">
            <h2 class="mb-0"><?php echo $dipinjam; ?></h2>
            <small><?php echo $is_admin ? 'Total Dipinjam' : 'Tanggungan Berkas'; ?></small>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-shadow p-3 text-center border-0 bg-danger text-white h-100">
            <h2 class="mb-0"><?php echo $overdue; ?></h2>
            <small>Overdue (>24 Jam)</small>
        </div>
    </div>
</div>

<!-- Timeline Pergerakan Terkini (Dibuat per RM) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-secondary mb-0">
        <?php echo $is_admin ? 'Aktivitas Terkini (Semua Ruangan)' : 'Berkas di Tangan Anda'; ?>
    </h6>
</div>

<?php if(count($active_files) > 0): ?>
    <?php foreach($active_files as $file): ?>
    <div class="card card-shadow p-3 mb-3 border-0" style="border-left: 4px solid <?php echo $file['status'] == 'DIPINJAM' ? '#ffc107' : '#0dcaf0'; ?> !important;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0 text-primary">No RM: <?php echo htmlspecialchars($file['no_rkm_medis']); ?></h5>
            <small class="text-muted"><i class="far fa-clock"></i> <?php echo date('H:i, d M', strtotime($file['updated_at'])); ?></small>
        </div>
        
        <div class="d-flex justify-content-between align-items-end mt-2">
            <div>
                <p class="mb-0 text-sm text-secondary">Lokasi Terkini:</p>
                <strong class="text-dark">
                    <?php 
                        if($file['status'] == 'TERSEDIA') {
                            echo "Ruang RM (Tersedia)";
                        } else {
                            echo htmlspecialchars($file['lokasi_terkini']) . " - Pj: " . htmlspecialchars($file['holder_name']);
                        }
                    ?>
                </strong>
            </div>
            <span class="badge 
                <?php echo $file['status'] == 'DIPINJAM' ? 'bg-warning text-dark' : 'bg-info text-dark'; ?>
            ">
                <?php echo $file['status']; ?>
            </span>
        </div>
        
        <?php if($is_admin): ?>
            <!-- Admin bisa melihat history log singkat untuk RM ini (opsional) -->
            <?php 
                $rm_num = $file['no_rkm_medis'];
                $stmtLog = $pdo->prepare("SELECT action, created_at FROM rm_tracking_logs WHERE no_rkm_medis = ? ORDER BY created_at DESC LIMIT 1");
                $stmtLog->execute([$rm_num]);
                $lastLog = $stmtLog->fetch();
                if($lastLog) {
                    echo "<hr class='my-2'><small class='text-muted'>Aksi terakhir: <b>{$lastLog['action']}</b> pada " . date('H:i', strtotime($lastLog['created_at'])) . "</small>";
                }
            ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card card-shadow p-4 text-center border-0 text-muted">
        <i class="fas fa-box-open mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
        <p class="mb-0">Tidak ada berkas yang sedang Anda tangani saat ini.</p>
    </div>
<?php endif; ?>

<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/footer.php'; ?>
