    </main> <!-- Fecha a tag <main> aberta no header.php -->

    <!-- JavaScript do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        // Dropdown menu funcional na sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownBtns = document.querySelectorAll('.dropdown-btn');
            
            dropdownBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Encontrar o container dropdown pai
                    const menuDropdown = this.parentElement;
                    const dropdownContent = menuDropdown.querySelector('.dropdown-content');
                    const arrow = this.querySelector('.menu-arrow');
                    
                    // Fechar outros dropdowns
                    document.querySelectorAll('.dropdown-content.active').forEach(content => {
                        if (content !== dropdownContent) {
                            content.classList.remove('active');
                            content.parentElement.querySelector('.menu-arrow').classList.remove('active');
                        }
                    });
                    
                    // Toggle o dropdown atual
                    dropdownContent.classList.toggle('active');
                    arrow.classList.toggle('active');
                });
            });
        });
    </script>

</body>
</html>
