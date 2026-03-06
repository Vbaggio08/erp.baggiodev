    </main> <!-- Fecha a tag <main> aberta no header.php -->

    <footer class="footer mt-auto py-3 bg-dark border-top" style="border-color: #333 !important;">
        <div class="container text-center">
            <span class="text-muted">&copy; <?php echo date("Y"); ?> Ripfire System. Todos os direitos reservados.</span>
        </div>
    </footer>

    <!-- JavaScript do Bootstrap (Essencial para componentes como menu dropdown, modais, etc) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        // Dropdown menu manual com fallback
        document.addEventListener('DOMContentLoaded', function() {
            // Encontrar todos os toggle de dropdown
            const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Encontrar o menu correspondente
                    const menuId = this.getAttribute('aria-labelledby');
                    const menu = document.querySelector('[aria-labelledby="' + menuId + '"]');
                    
                    if (menu) {
                        // Fechar todos os outros menus
                        document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                            if (m !== menu) {
                                m.classList.remove('show');
                            }
                        });
                        
                        // Toggle o menu atual
                        menu.classList.toggle('show');
                    }
                });
            });
            
            // Fechar dropdown quando clicar fora
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.nav-item.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
            
            // Fechar dropdown quando clicar em um item
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                });
            });
        });
    </script>

</body>
</html>
