<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$medecinSidebarActive = $medecinSidebarActive ?? 'dashboard';
$doctorName = $doctorName ?? ($_SESSION['user_name'] ?? 'Dr. Médecin');
$specialite = $specialite ?? 'Spécialité non renseignée';
?>

<div class="glass-card sidebar-card fade-up">
    <div class="text-center text-white">
        <h5 class="mb-1"><?= htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8'); ?></h5>
        <p class="text-white-50 mb-0"><?= htmlspecialchars($specialite, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="card-divider my-3"></div>
    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link<?= $medecinSidebarActive === 'dashboard' ? ' active' : ''; ?>" href="/Project/public/index.php?page=medecin_dashboard">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $medecinSidebarActive === 'agenda' ? ' active' : ''; ?>" href="/Project/public/index.php?page=medecin_agenda">
                <i class="bi bi-calendar-check"></i> Mon agenda
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $medecinSidebarActive === 'patients' ? ' active' : ''; ?>" href="/Project/public/index.php?page=medecin_patients">
                <i class="bi bi-people"></i> Mes patients
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $medecinSidebarActive === 'ordonnances' ? ' active' : ''; ?>" href="/Project/public/index.php?page=medecin_ordonnances">
                <i class="bi bi-file-medical"></i> Ordonnances
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $medecinSidebarActive === 'staff' ? ' active' : ''; ?>" href="/Project/public/index.php?page=medecin_staff">
                <i class="bi bi-people-fill"></i> Staff
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $medecinSidebarActive === 'billing' ? ' active' : ''; ?>" href="/Project/public/index.php?page=medecin_patients">
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
