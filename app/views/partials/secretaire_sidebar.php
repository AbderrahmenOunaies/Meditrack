<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$secretaireSidebarActive = $secretaireSidebarActive ?? 'dashboard';
$secretaireName = $secretaireName ?? ($_SESSION['user_name'] ?? 'Secrétaire');
?>

<div class="glass-card sidebar-card fade-up">
    <div class="text-center text-white">
        <h5 class="mb-1"><?= htmlspecialchars($secretaireName, ENT_QUOTES, 'UTF-8'); ?></h5>
        <p class="text-white-50 mb-0">Secrétaire médicale</p>
    </div>
    <div class="card-divider my-3"></div>
    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link<?= $secretaireSidebarActive === 'dashboard' ? ' active' : ''; ?>" href="/Project/public/index.php?page=secretaire_dashboard">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $secretaireSidebarActive === 'appointments' ? ' active' : ''; ?>" href="/Project/public/index.php?page=secretaire_appointments">
                <i class="bi bi-calendar-check"></i> Rendez-vous
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $secretaireSidebarActive === 'patients' ? ' active' : ''; ?>" href="/Project/public/index.php?page=secretaire_patients">
                <i class="bi bi-people"></i> Patients
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $secretaireSidebarActive === 'billing' ? ' active' : ''; ?>" href="/Project/public/index.php?page=secretaire_billing">
                <i class="bi bi-receipt"></i> Facturation
            </a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
