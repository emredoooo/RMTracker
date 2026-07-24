    </div> <!-- End Main Content Container -->

    <!-- Bootstrap 5 Bundle JS (Includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Optional: HTML5 QR Code Scanner Library (untuk halaman scan) -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <!-- Custom JS -->
    <script>
        // Simulasi simple routing/active state pada bottom nav
        const currentPage = window.location.pathname.split("/").pop();
        if(currentPage) {
            const activeLink = document.querySelector(`.bottom-nav-item[href="${currentPage}"]`);
            if(activeLink) activeLink.classList.add('active');
        } else {
            const homeLink = document.querySelector(`.bottom-nav-item[href="index.php"]`);
            if(homeLink) homeLink.classList.add('active');
        }
    </script>
</body>
</html>
