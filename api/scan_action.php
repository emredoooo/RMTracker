<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$no_rm = $_POST['no_rm'] ?? '';

if (strlen($no_rm) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Format No RM tidak valid (harus 6 digit)']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_unit = $_SESSION['unit_asal'];

try {
    $pdo->beginTransaction();

    // Cek status berkas saat ini
    $stmt = $pdo->prepare("SELECT * FROM rm_current_status WHERE no_rkm_medis = ? FOR UPDATE");
    $stmt->execute([$no_rm]);
    $current = $stmt->fetch();

    $action = '';
    $message = '';

    if (!$current) {
        // Berkas belum pernah ditracking sama sekali
        if ($user_role === 'petugas_rm' || $user_role === 'superadmin') {
            // Check-out pertama kali
            $action = 'CHECK_OUT';
            $stmtInsert = $pdo->prepare("INSERT INTO rm_current_status (no_rkm_medis, status, holder_id, lokasi_terkini, borrowed_at) VALUES (?, 'DIPINJAM', ?, ?, NOW())");
            $stmtInsert->execute([$no_rm, $user_id, $user_unit]);
            $message = "Berkas $no_rm berhasil dipinjam/dikeluarkan.";
        } else {
            // Perawat scan berkas yang belum ada di database? Harusnya check-out dari RM dulu.
            // Tapi untuk fleksibilitas di lapangan, anggap dia menerima (transfer dari unknown)
            $action = 'TRANSFER';
            $stmtInsert = $pdo->prepare("INSERT INTO rm_current_status (no_rkm_medis, status, holder_id, lokasi_terkini, borrowed_at) VALUES (?, 'DIPINJAM', ?, ?, NOW())");
            $stmtInsert->execute([$no_rm, $user_id, $user_unit]);
            $message = "Berkas $no_rm diterima tanpa tercatat check-out sebelumnya.";
        }
    } else {
        // Berkas sudah ada
        $prev_holder = $current['holder_id'];

        if ($current['status'] === 'TERSEDIA') {
            // Berkas di RM, sekarang discan
            $action = 'CHECK_OUT';
            $stmtUpdate = $pdo->prepare("UPDATE rm_current_status SET status = 'DIPINJAM', holder_id = ?, lokasi_terkini = ?, borrowed_at = NOW() WHERE no_rkm_medis = ?");
            $stmtUpdate->execute([$user_id, $user_unit, $no_rm]);
            $message = "Berkas $no_rm berhasil di-checkout.";
        } else if ($current['status'] === 'DIPINJAM') {
            if ($user_role === 'petugas_rm' || $user_role === 'superadmin') { // Asumsi RM menerima kembali
                $action = 'CHECK_IN';
                $stmtUpdate = $pdo->prepare("UPDATE rm_current_status SET status = 'TERSEDIA', holder_id = NULL, lokasi_terkini = 'RM', borrowed_at = NULL WHERE no_rkm_medis = ?");
                $stmtUpdate->execute([$no_rm]);
                $message = "Berkas $no_rm berhasil dikembalikan ke RM.";
            } else {
                if ($prev_holder == $user_id) {
                    // Dia menscan berkas yang sudah dia pegang
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => "Berkas $no_rm memang sudah ada pada Anda."]);
                    exit;
                } else {
                    // Transfer dari orang lain ke user ini
                    $action = 'TRANSFER';
                    $stmtUpdate = $pdo->prepare("UPDATE rm_current_status SET holder_id = ?, lokasi_terkini = ? WHERE no_rkm_medis = ?");
                    $stmtUpdate->execute([$user_id, $user_unit, $no_rm]);
                    $message = "Berkas $no_rm berhasil ditransfer ke Anda.";
                }
            }
        }
    }

    // Insert to log
    $stmtLog = $pdo->prepare("INSERT INTO rm_tracking_logs (no_rkm_medis, action, from_user_id, to_user_id) VALUES (?, ?, ?, ?)");
    $stmtLog->execute([$no_rm, $action, $prev_holder ?? null, $user_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => $message]);

} catch (\Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>
