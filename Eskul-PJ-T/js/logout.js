export function initLogoutModal() {
    const logoutOverlay = document.getElementById("logoutOverlay");

    if (!logoutOverlay) return;

    // Show modal
    window.confirmLogout = function(event) {
        event.preventDefault();
        logoutOverlay.classList.add("show");
    };

    // Hide modal
    window.closeLogoutModal = function() {
        logoutOverlay.classList.remove("show");
    };

    // Close when clicking outside modal
    logoutOverlay.addEventListener('click', (e) => {
        if (e.target === logoutOverlay) {
            closeLogoutModal();
        }
    });
}
