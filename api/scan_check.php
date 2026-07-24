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

try {
    $stmt = $pdo->prepare("SELECT r.*, u.nama as holder_name, u.unit_asal as holder_unit FROM rm_current_status r LEFT JOIN users u ON r.holder_id = u.id WHERE r.no_rkm_medis = ?");
    $stmt->execute([$no_rm]);
    $current = $stmt->fetch();

    if ($current) {
        $info = "Status: " . $current['status'] . "\n";
        if ($current['status'] == 'DIPINJAM') {
            $info .= "Dipegang oleh: " . ($current['holder_name'] ?? 'Tidak diketahui') . " (" . ($current['holder_unit'] ?? '') . ")\n";
        } else {
            $info .= "Lokasi: " . $current['lokasi_terkini'] . "\n";
        }
        echo json_encode(['success' => true, 'found' => true, 'info' => $info]);
    } else {
        echo json_encode(['success' => true, 'found' => false, 'info' => "Berkas belum pernah ditracking. Ini akan menjadi Check-Out pertama."]);
    }

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>
