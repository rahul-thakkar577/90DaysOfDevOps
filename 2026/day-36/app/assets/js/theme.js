document.addEventListener('DOMContentLoaded', function() {
    // Theme switching
    const themeSwitch = document.getElementById('theme-switch');
    const body = document.body;
    const icon = themeSwitch.querySelector('i');

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light-theme';
    body.classList.add(savedTheme);
    updateThemeIcon(savedTheme === 'dark-theme');

    // Theme switch functionality
    themeSwitch.addEventListener('click', function() {
        const isDark = body.classList.contains('dark-theme');
        body.classList.remove(isDark ? 'dark-theme' : 'light-theme');
        body.classList.add(isDark ? 'light-theme' : 'dark-theme');
        localStorage.setItem('theme', isDark ? 'light-theme' : 'dark-theme');
        updateThemeIcon(!isDark);
    });

    function updateThemeIcon(isDark) {
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    }

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('show');
            this.setAttribute('aria-expanded', navLinks.classList.contains('show'));
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
            navLinks.classList.remove('show');
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
        }
    });

    // User dropdown
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            const isExpanded = menu.classList.contains('show');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                if (m !== menu) {
                    m.classList.remove('show');
                    m.previousElementSibling.setAttribute('aria-expanded', 'false');
                }
            });

            // Toggle current dropdown
            menu.classList.toggle('show');
            this.setAttribute('aria-expanded', !isExpanded);
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(menu => {
            if (!menu.contains(e.target) && !menu.previousElementSibling.contains(e.target)) {
                menu.classList.remove('show');
                menu.previousElementSibling.setAttribute('aria-expanded', 'false');
            }
        });
    });
});