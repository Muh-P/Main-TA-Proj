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
     // Sidebar toggle functionality
     const menuBtn = document.getElementById('menu-btn');
     const closeBtn = document.getElementById('close-btn');
     const sidebar = document.getElementById('sidebar');

     menuBtn.addEventListener('click', () => {
         sidebar.classList.add('open');
     });

     closeBtn.addEventListener('click', () => {
         sidebar.classList.remove('open');
     });