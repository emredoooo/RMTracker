<?php include 'includes/header.php'; ?>

<div class="text-center mb-4 mt-2">
    <h4 class="fw-bold">Cetak Label QR</h4>
    <p class="text-muted small">Khusus Petugas RM</p>
</div>

<div class="card card-shadow p-4 border-0 mb-4">
    <form>
        <div class="mb-3">
            <label class="form-label fw-bold">Nomor Rekam Medis (6 Digit)</label>
            <input type="number" class="form-control form-control-lg text-center" placeholder="Misal: 123456" maxlength="6" id="rmNumberInput">
        </div>
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary btn-lg" onclick="generateQR()">Generate QR</button>
        </div>
    </form>
</div>

<!-- Hasil Generate QR -->
<div class="card card-shadow p-4 border-0 text-center d-none" id="qrResultCard">
    <h6 class="text-muted mb-3">Preview Stiker (3x3 cm)</h6>
    <div class="d-inline-block border p-3 bg-white" style="width: 150px; height: 150px;">
        <!-- Mockup QR Code Image -->
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=000000" id="qrImage" alt="QR Code" class="img-fluid">
    </div>
    <h3 class="mt-2 fw-bold" id="qrLabel">000000</h3>
    
    <button class="btn btn-success mt-3 w-100"><i class="fas fa-print me-2"></i> Cetak Sekarang</button>
</div>

<script>
function generateQR() {
    const input = document.getElementById('rmNumberInput').value;
    if(input.length > 0) {
        document.getElementById('qrResultCard').classList.remove('d-none');
        document.getElementById('qrImage').src = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' + input;
        document.getElementById('qrLabel').innerText = input;
    } else {
        alert("Masukkan No RM terlebih dahulu");
    }
}
</script>

<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/footer.php'; ?>
