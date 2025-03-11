const sidebar = document.querySelector('#sidebar');
const sidebarToggler = document.querySelector('.sidebar_toggler');
const logoutOverlay = document.getElementById("logoutOverlay");

// Toggle Sidebar
sidebarToggler.addEventListener('click', (e) => {
    e.stopPropagation();
    sidebar.classList.toggle('show');
});

// Prevent closing sidebar when clicking inside it
sidebar.addEventListener('click', (e) => {
    e.stopPropagation();
});

// Ensure sidebar closes only when clicking outside
document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !sidebarToggler.contains(e.target)) {
        sidebar.classList.remove('show');
    }
});

// Logout Modal Functions
function confirmLogout(event) {
    event.preventDefault();
    logoutOverlay.classList.add("show");
}

function closeLogoutModal() {
    logoutOverlay.classList.remove("show");
}

// Close modal when clicking outside
logoutOverlay.addEventListener('click', (e) => {
    if (e.target === logoutOverlay) {
        closeLogoutModal();
    }
});
