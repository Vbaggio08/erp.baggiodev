    </main> <!-- Fecha a tag <main> aberta no header.php -->

    <!-- Dependências globais usadas por views legadas -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript do Bootstrap (já inclui Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        (function () {
            const STORAGE_KEY = 'erp-theme';
            const body = document.body;
            const metaTheme = document.querySelector('meta[name="theme-color"]');

            function applyTheme(theme) {
                body.setAttribute('data-theme', theme);
                if (metaTheme) {
                    metaTheme.setAttribute('content', theme === 'dark' ? '#1e232b' : '#f3f5f8');
                }
                const toggle = document.getElementById('themeToggle');
                if (toggle) {
                    const iconClass = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
                    const title = theme === 'dark' ? 'Alternar para tema claro' : 'Alternar para tema escuro';
                    toggle.innerHTML = '<i class="' + iconClass + '" aria-hidden="true"></i>';
                    toggle.setAttribute('title', title);
                    toggle.setAttribute('aria-label', title);
                }
            }

            const saved = localStorage.getItem(STORAGE_KEY);
            applyTheme(saved === 'dark' ? 'dark' : 'light');

            document.addEventListener('click', function (event) {
                const toggle = event.target.closest('#themeToggle');
                if (!toggle) return;
                const current = body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem(STORAGE_KEY, next);
                applyTheme(next);
            });
        })();
    </script>

</body>
</html>
