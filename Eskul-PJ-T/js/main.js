import { initSidebar } from './sidebar.js';
import { initLogoutModal } from './logout.js';

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initLogoutModal();
});