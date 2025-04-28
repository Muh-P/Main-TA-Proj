export function initSidebar() {
    const sidebar = document.querySelector('#sidebar');
    const toggleBtn = document.getElementById('menu-btn');

    if (!sidebar || !toggleBtn) return;

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('show');
    });

    sidebar.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });
}


// Fungsi untuk toggle navbar saat di HP//
function toggleNavbar() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.toggle('active');
}

