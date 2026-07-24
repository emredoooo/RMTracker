<?php 
require_once 'includes/auth.php';
require_once 'config/database.php';

$detail_rm = $_GET['no_rm'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 15; // Tampilkan 15 data per halaman
$offset = ($page - 1) * $limit;

include 'includes/header.php'; 

if ($detail_rm): 
    // VIEW DETAIL RM
    $stmt = $pdo->prepare("SELECT r.*, u.nama as holder_name FROM rm_current_status r LEFT JOIN users u ON r.holder_id = u.id WHERE r.no_rkm_medis = ?");
    $stmt->execute([$detail_rm]);
    $file_status = $stmt->fetch();

    $stmt2 = $pdo->prepare("
        SELECT l.*, u.nama as from_name, u2.nama as to_name, u2.unit_asal 
        FROM rm_tracking_logs l 
        LEFT JOIN users u ON l.from_user_id = u.id 
        JOIN users u2 ON l.to_user_id = u2.id 
        WHERE l.no_rkm_medis = ? 
        ORDER BY l.created_at DESC
    ");
    $stmt2->execute([$detail_rm]);
    $logs = $stmt2->fetchAll();
?>

    <div class="d-flex align-items-center mb-4 mt-2">
        <a href="riwayat.php" class="text-dark me-3"><i class="fas fa-arrow-left fa-lg"></i></a>
        <h4 class="fw-bold mb-0">Detail Riwayat RM</h4>
    </div>

    <?php if($file_status): ?>
    <div class="card card-shadow p-3 mb-4 border-0 bg-primary text-white">
        <h3 class="fw-bold"><?php echo htmlspecialchars($file_status['no_rkm_medis']); ?></h3>
        <p class="mb-0">Status: <strong><?php echo $file_status['status']; ?></strong></p>
        <small>Lokasi: <?php echo $file_status['status'] == 'TERSEDIA' ? 'Ruang RM' : htmlspecialchars($file_status['lokasi_terkini']) . ' (Pj: ' . htmlspecialchars($file_status['holder_name']) . ')'; ?></small>
    </div>

    <h6 class="fw-bold text-secondary mb-3">Timeline Lengkap</h6>
    <div class="card card-shadow p-3 mb-5 border-0">
        <div class="timeline">
            <?php foreach($logs as $log): ?>
            <div class="timeline-item">
                <div class="d-flex justify-content-between">
                    <strong><?php echo date('d M Y', strtotime($log['created_at'])); ?></strong>
                    <small class="text-muted"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></small>
                </div>
                <p class="mb-1 text-sm mt-1">
                    <?php if($log['action'] == 'CHECK_OUT'): ?>
                        Dikeluarkan oleh <strong><?php echo htmlspecialchars($log['to_name']); ?> (<?php echo htmlspecialchars($log['unit_asal']); ?>)</strong>
                    <?php elseif($log['action'] == 'TRANSFER'): ?>
                        Ditransfer dari <i><?php echo htmlspecialchars($log['from_name'] ?? 'Tidak diketahui'); ?></i> <br>
                        Mendarat di <strong><?php echo htmlspecialchars($log['to_name']); ?> (<?php echo htmlspecialchars($log['unit_asal']); ?>)</strong>
                    <?php elseif($log['action'] == 'CHECK_IN'): ?>
                        Dikembalikan oleh <strong><?php echo htmlspecialchars($log['to_name']); ?></strong> ke Ruang RM
                    <?php endif; ?>
                </p>
                <span class="badge 
                    <?php echo $log['action'] == 'CHECK_OUT' ? 'bg-warning text-dark' : ($log['action'] == 'TRANSFER' ? 'bg-success' : 'bg-info text-dark'); ?>
                ">
                    <?php echo $log['action']; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">Berkas RM tidak ditemukan di sistem tracking.</div>
    <?php endif; ?>

<?php else: 
    // VIEW DAFTAR RIWAYAT
    // Get Stats
    $stmtStats = $pdo->query("SELECT 
        COUNT(*) as total_rm,
        SUM(CASE WHEN status = 'DIPINJAM' THEN 1 ELSE 0 END) as total_dipinjam,
        SUM(CASE WHEN status = 'DIPINJAM' AND borrowed_at < DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as total_overdue
        FROM rm_current_status");
    $stats = $stmtStats->fetch();

    $params = [];
    $where = "";
    if ($search) {
        $where = "WHERE no_rkm_medis LIKE ?";
        $params[] = "%$search%";
    }
    
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM rm_current_status $where");
    $stmtTotal->execute($params);
    $total = $stmtTotal->fetchColumn();
    $total_pages = ceil($total / $limit);

    $sql = "SELECT r.*, u.nama as holder_name FROM rm_current_status r LEFT JOIN users u ON r.holder_id = u.id $where ORDER BY r.updated_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $files = $stmt->fetchAll();
?>

    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
        <h4 class="fw-bold mb-0">Riwayat Berkas</h4>
    </div>

    <!-- Statistik Horizontal -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="card card-shadow p-2 text-center border-0 bg-info text-dark h-100">
                <h4 class="mb-0 fw-bold"><?php echo (int)$stats['total_rm']; ?></h4>
                <small style="font-size: 0.7rem; line-height: 1.1;">Total Terdaftar</small>
            </div>
        </div>
        <div class="col-4">
            <div class="card card-shadow p-2 text-center border-0 bg-warning text-dark h-100">
                <h4 class="mb-0 fw-bold"><?php echo (int)$stats['total_dipinjam']; ?></h4>
                <small style="font-size: 0.7rem; line-height: 1.1;">Dipinjam</small>
            </div>
        </div>
        <div class="col-4">
            <div class="card card-shadow p-2 text-center border-0 bg-danger text-white h-100">
                <h4 class="mb-0 fw-bold"><?php echo (int)$stats['total_overdue']; ?></h4>
                <small style="font-size: 0.7rem; line-height: 1.1;">Overdue</small>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <form action="riwayat.php" method="GET" class="mb-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="number" name="search" class="form-control border-start-0 ps-0" placeholder="Cari 6 Digit No RM..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

    <p class="text-muted small mb-2">Ditemukan: <?php echo $total; ?> Berkas</p>

    <!-- Daftar Berkas -->
    <div class="list-group card-shadow mb-4 border-0">
        <?php if(count($files) > 0): ?>
            <?php foreach($files as $file): ?>
            <a href="riwayat.php?no_rm=<?php echo $file['no_rkm_medis']; ?>" class="list-group-item list-group-item-action p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-bold text-primary">No RM: <?php echo htmlspecialchars($file['no_rkm_medis']); ?></h6>
                        <small class="text-muted"><i class="far fa-clock"></i> Update: <?php echo date('d M, H:i', strtotime($file['updated_at'])); ?></small>
                    </div>
                    <div class="text-end">
                        <span class="badge <?php echo $file['status'] == 'DIPINJAM' ? 'bg-warning text-dark' : 'bg-info text-dark'; ?> mb-1">
                            <?php echo $file['status']; ?>
                        </span>
                        <br>
                        <small class="text-secondary" style="font-size: 0.7rem;">
                            <?php echo $file['status'] == 'TERSEDIA' ? 'Ruang RM' : htmlspecialchars($file['lokasi_terkini']); ?>
                        </small>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-4 text-center text-muted">
                <p class="mb-0">Tidak ada data ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mb-5 pb-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">Sebelmnya</a>
            </li>
            
            <li class="page-item active">
                <span class="page-link">Hal <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
            </li>
            
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">Slanjutnya</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

<?php endif; // End View ?>

<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/footer.php'; ?>
