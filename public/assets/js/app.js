// Activer les tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Gérer le menu latéral sur mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Fermer le menu latéral lors du clic sur un élément
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('show');
            }
        });
    });
});

// Gérer les onglets
const triggerTabList = [].slice.call(document.querySelectorAll('a[data-bs-toggle="tab"]'));
triggerTabList.forEach(function (triggerEl) {
    const tabTrigger = new bootstrap.Tab(triggerEl);
    
    triggerEl.addEventListener('click', function (event) {
        event.preventDefault();
        tabTrigger.show();
    });
});