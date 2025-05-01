export function initSidebar() {
    const menuBtn = document.getElementById('menu-btn');
    const closeBtn = document.getElementById('close-btn');
    const sidebar = document.getElementById('sidebar');

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => sidebar.classList.add('show'));
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', () => sidebar.classList.remove('show'));
    }

    document.addEventListener('click', (e) => {
        if (sidebar && !sidebar.contains(e.target) && e.target !== menuBtn) {
            sidebar.classList.remove('show');
        }
    });
}