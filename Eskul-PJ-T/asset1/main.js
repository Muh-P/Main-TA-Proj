
const sidebar = document.querySelector('#sidebar');
const sidebarToggler = document.querySelector('.sidebar_toggler');


sidebarToggler.addEventListener('click', () => {
    sidebar.classList.toggle('show');
});


window.addEventListener('click', (e) => {
    if (e.target.id !== 'sidebar' && e.target.className !== 'sidebar_toggler') {
        sidebar.classList.remove('show');
    }
});