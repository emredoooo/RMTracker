<?php 
require_once 'includes/auth.php';
require_once 'config/database.php';

if ($user_role !== 'superadmin') {
    echo "Akses Ditolak. Khusus Superadmin.";
    exit;
}

// Proses Hapus
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $user_id) { // Jangan hapus diri sendiri
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
    header("Location: roles.php");
    exit;
}

// Proses Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    $unit = $_POST['unit'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nik, password, nama, role, unit_asal) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nik, $pass, $nama, $role, $unit]);
    } catch(PDOException $e) {
        $error = "Gagal menambah data. Pastikan NIK unik.";
    }
}

// Proses Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = $_POST['id'];
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    $unit = $_POST['unit'];
    
    try {
        if (!empty($_POST['password'])) {
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nik=?, password=?, nama=?, role=?, unit_asal=? WHERE id=?");
            $stmt->execute([$nik, $pass, $nama, $role, $unit, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nik=?, nama=?, role=?, unit_asal=? WHERE id=?");
            $stmt->execute([$nik, $nama, $role, $unit, $id]);
        }
        $success = "Berhasil mengubah data pengguna.";
    } catch(PDOException $e) {
        $error = "Gagal mengubah data. Pastikan NIK unik.";
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
include 'includes/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0">Manajemen Role</h4>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus"></i> Akun Baru</button>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Daftar User -->
<div class="list-group card-shadow mb-5">
    <?php foreach($users as $u): ?>
    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
        <div>
            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($u['nama']); ?></h6>
            <small class="text-muted">NIK: <?php echo htmlspecialchars($u['nik']); ?> (<?php echo htmlspecialchars($u['unit_asal']); ?>)</small>
        </div>
        <div class="text-end">
            <span class="badge 
                <?php echo $u['role'] == 'superadmin' ? 'bg-dark' : ($u['role'] == 'petugas_rm' ? 'bg-success' : 'bg-info text-dark'); ?>
             rounded-pill mb-1">
                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $u['role']))); ?>
            </span>
            <br>
            <a href="#" class="text-primary small text-decoration-none me-2" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $u['id']; ?>">Edit</a>
            <a href="roles.php?delete=<?php echo $u['id']; ?>" class="text-danger small text-decoration-none" onclick="return confirm('Hapus pengguna ini?');">Hapus</a>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal<?php echo $u['id']; ?>" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Pengguna</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form method="POST" action="roles.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label>NIK / Username</label>
                    <input type="text" name="nik" class="form-control" value="<?php echo htmlspecialchars($u['nik']); ?>" required>
                </div>
                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($u['nama']); ?>" required>
                </div>
                <div class="mb-3">
                    <label>Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select" required>
                        <option value="perawat" <?php echo $u['role'] == 'perawat' ? 'selected' : ''; ?>>Perawat</option>
                        <option value="petugas_rm" <?php echo $u['role'] == 'petugas_rm' ? 'selected' : ''; ?>>Petugas RM</option>
                        <option value="superadmin" <?php echo $u['role'] == 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Unit / Ruangan</label>
                    <input type="text" name="unit" class="form-control" value="<?php echo htmlspecialchars($u['unit_asal']); ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Pengguna Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="roles.php">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
            <div class="mb-3">
                <label>NIK / Username</label>
                <input type="text" name="nik" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="perawat">Perawat</option>
                    <option value="petugas_rm">Petugas RM</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Unit / Ruangan</label>
                <input type="text" name="unit" class="form-control" required placeholder="Contoh: IGD, Rawat Inap A">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/footer.php'; ?>
