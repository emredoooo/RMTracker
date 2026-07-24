<?php 
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}
include 'includes/header.php'; 
?>

<div class="row justify-content-center align-items-center min-vh-100 pb-5" style="margin-top: -3rem;">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="text-center mb-4">
            <i class="fas fa-file-medical-alt text-primary" style="font-size: 4rem;"></i>
            <h2 class="mt-3 font-weight-bold">SIMRS RM Tracker</h2>
            <p class="text-muted">Login dengan akun Anda</p>
        </div>

        <?php if(isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>

        <div class="card card-shadow p-4">
            <form action="login_action.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username / NIK</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" id="username" placeholder="Masukkan NIK" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill">Login</button>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">&copy; 2026 IT RS - Sistem Rekam Medis</small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
