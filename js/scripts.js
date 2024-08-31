window.addEventListener('DOMContentLoaded', event => {
    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // Handle the collapse of submenu items
    const navLinks = document.querySelectorAll('.nav-link.collapsed');
    navLinks.forEach(navLink => {
        navLink.addEventListener('click', event => {
            const target = document.querySelector(navLink.dataset.bsTarget);
            if (target) {
                target.classList.toggle('show');
                navLink.classList.toggle('collapsed');
            }
        });
    });
});
