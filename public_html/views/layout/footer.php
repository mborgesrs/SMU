    </main>

    <footer class="lg:ml-64 bg-white border-t border-gray-100 py-6 px-4">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 text-sm text-gray-500">
            <div class="flex items-center space-x-2">
                <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($empresaNome); ?></span>
                <?php if (!empty($empresaConfig['cnpj'])): ?>
                    <span class="text-gray-300">|</span>
                    <span>CNPJ: <?php echo htmlspecialchars($empresaConfig['cnpj']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center space-x-6">
                <?php if (!empty($empresaConfig['email'])): ?>
                    <a href="mailto:<?php echo $empresaConfig['email']; ?>" class="hover:text-indigo-600 transition flex items-center">
                        <i class="far fa-envelope mr-2"></i> <?php echo htmlspecialchars($empresaConfig['email']); ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($empresaConfig['telefone'])): ?>
                    <span class="flex items-center">
                        <i class="fas fa-phone-alt mr-2"></i> <?php echo htmlspecialchars($empresaConfig['telefone']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="text-xs text-gray-400">
                &copy; <?php echo date('Y'); ?> SMU Sistema de Gestão.
            </div>
        </div>
    </footer>

    <script src="../../assets/js/main.js"></script>
    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        function toggleSidebar() {
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
            if (overlay) {
                overlay.classList.toggle('hidden');
            }
        }

        toggleBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });

        // Close when clicking overlay
        overlay?.addEventListener('click', toggleSidebar);

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar && !sidebar.classList.contains('-translate-x-full')) {
                toggleSidebar();
            }
        });
    </script>
</body>
</html>
