<?php 
require_once 'includes/auth.php';
include 'includes/header.php'; 
?>

<div class="text-center mb-3">
    <h4 class="fw-bold">Scan Berkas RM</h4>
    <p class="text-muted small">Arahkan kamera ke QR Code pada berkas</p>
</div>

<!-- Area Kamera -->
<div class="card card-shadow border-0 mb-4 overflow-hidden position-relative">
    <div id="reader" style="width: 100%; min-height: 300px; background-color: #000;"></div>
</div>

<!-- Opsi Input Manual -->
<div class="card card-shadow p-3 border-0">
    <h6 class="fw-bold mb-3">Kamera Gagal? Input Manual</h6>
    <div class="input-group">
        <input type="text" id="manualInput" class="form-control" placeholder="Masukkan 6 Digit No. RM" maxlength="6">
        <button class="btn btn-primary" type="button" onclick="processManual()">Cek</button>
    </div>
</div>

<!-- Alert -->
<div class="alert mt-4 d-none" id="scanAlert"></div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i>Konfirmasi Tindakan</h5>
      </div>
      <div class="modal-body">
        <h3 class="text-center fw-bold mb-3" id="confirmRmNumber">000000</h3>
        <div class="alert alert-info" id="confirmInfo" style="white-space: pre-wrap;">
            Memuat informasi...
        </div>
        <p class="text-center mb-0">Apakah Anda yakin ingin memproses berkas ini?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary w-100 mb-2" onclick="cancelScan()">Batal</button>
        <button type="button" class="btn btn-success w-100" onclick="confirmScanAction()">Ya, Lanjutkan</button>
      </div>
    </div>
  </div>
</div>

<script>
let html5QrcodeScanner = null;
let isScanning = false;
let pendingRmNumber = '';
let confirmModal = null;

function onScanSuccess(decodedText, decodedResult) {
    if(isScanning) return; 
    isScanning = true;
    
    if(html5QrcodeScanner) html5QrcodeScanner.pause();
    
    checkScanData(decodedText);
}

function processManual() {
    const rm = document.getElementById('manualInput').value;
    if(rm.length !== 6) {
        showAlert('Format harus 6 digit', 'danger');
        return;
    }
    checkScanData(rm);
}

function checkScanData(rmNumber) {
    pendingRmNumber = rmNumber;
    const formData = new FormData();
    formData.append('no_rm', rmNumber);

    fetch('api/scan_check.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Tampilkan Modal
            document.getElementById('confirmRmNumber').innerText = rmNumber;
            document.getElementById('confirmInfo').innerText = data.info;
            
            if(!confirmModal) {
                confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            }
            confirmModal.show();
        } else {
            showAlert(data.message, 'danger');
            resetScanner();
        }
    })
    .catch(error => {
        showAlert('Terjadi kesalahan koneksi', 'danger');
        resetScanner();
    });
}

function confirmScanAction() {
    if(!pendingRmNumber) return;
    
    if(confirmModal) confirmModal.hide();
    
    const formData = new FormData();
    formData.append('no_rm', pendingRmNumber);

    fetch('api/scan_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'danger');
        }
        resetScanner(2000); // Resume scanner after 2 seconds
    })
    .catch(error => {
        showAlert('Terjadi kesalahan saat memproses', 'danger');
        resetScanner();
    });
}

function cancelScan() {
    if(confirmModal) confirmModal.hide();
    pendingRmNumber = '';
    resetScanner();
}

function resetScanner(delay = 0) {
    setTimeout(() => {
        if(html5QrcodeScanner) {
            html5QrcodeScanner.resume();
        }
        isScanning = false;
        pendingRmNumber = '';
    }, delay);
}

function showAlert(message, type) {
    const alertEl = document.getElementById('scanAlert');
    alertEl.className = `alert mt-4 alert-${type}`;
    alertEl.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i> ${message}`;
}

window.onload = function() {
    html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { fps: 10, qrbox: 200, disableFlip: false }
    );
    html5QrcodeScanner.render(onScanSuccess);
};
</script>

<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/footer.php'; ?>
