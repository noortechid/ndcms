document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();

    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    const closeSidebar = () => {
        sidebar?.classList.remove('is-open');
        overlay?.classList.remove('is-visible');
    };

    toggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('is-open');
        overlay?.classList.toggle('is-visible');
    });

    overlay?.addEventListener('click', closeSidebar);
});
