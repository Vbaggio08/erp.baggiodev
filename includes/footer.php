
  </div>
<footer class="app-footer py-3 mt-4">
    <div class="container text-center text-muted small">
        Projeto UniSenai PHP - <?php echo date('Y'); ?>
    </div>
</footer>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Alternância de tema
            var btn  = document.getElementById('themeToggle');
            var icon = document.getElementById('themeIcon');

            function applyTheme(dark) {
                if (dark) {
                    document.body.classList.add('theme-dark');
                    icon.className = 'bi bi-sun';
                    btn.setAttribute('title', 'Mudar para tema claro');
                } else {
                    document.body.classList.remove('theme-dark');
                    icon.className = 'bi bi-moon';
                    btn.setAttribute('title', 'Mudar para tema escuro');
                }
            }

            // Estado inicial (já aplicado no <body> pelo inline script)
            applyTheme(document.body.classList.contains('theme-dark'));

            btn.addEventListener('click', function () {
                var isDark = document.body.classList.contains('theme-dark');
                applyTheme(!isDark);
                localStorage.setItem('tema', isDark ? 'light' : 'dark');
            });
        });
    </script>

</body>
</html>