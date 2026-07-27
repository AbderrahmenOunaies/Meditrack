<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$patientSidebarActive = $patientSidebarActive ?? 'dashboard';

$sidebarNameCandidate = isset($patientSidebarName) ? $patientSidebarName : null;
$patientNameCandidate = isset($patientName) ? $patientName : null;
$patientData = isset($patient) ? $patient : null;
$sessionUser = $_SESSION['user'] ?? null;

$resolvePatientName = static function () use ($sidebarNameCandidate, $patientNameCandidate, $patientData, $sessionUser) {
    if (!empty($sidebarNameCandidate ?? '')) {
        return $sidebarNameCandidate;
    }
    if (!empty($patientNameCandidate ?? '')) {
        return $patientNameCandidate;
    }
    if (is_array($patientData)) {
        if (!empty($patientData['nom_complet'] ?? '')) {
            return $patientData['nom_complet'];
        }
        $composed = trim(($patientData['prenom'] ?? '') . ' ' . ($patientData['nom'] ?? ''));
        if ($composed !== '') {
            return $composed;
        }
    }
    if (is_array($sessionUser)) {
        if (!empty($sessionUser['nom_complet'] ?? '')) {
            return $sessionUser['nom_complet'];
        }
        $composed = trim(($sessionUser['prenom'] ?? '') . ' ' . ($sessionUser['nom'] ?? ''));
        if ($composed !== '') {
            return $composed;
        }
    }
    return '';
};

$patientSidebarNameResolved = trim($resolvePatientName());
$patientSidebarName = $patientSidebarNameResolved !== '' ? $patientSidebarNameResolved : 'Patient MediTrack';

$patientNavLinks = $patientNavLinks ?? [
    ['key' => 'dashboard', 'label' => 'Tableau de bord', 'href' => '/Project/public/index.php?page=patient_dashboard', 'icon' => 'bi bi-speedometer2'],
    ['key' => 'appointments', 'label' => 'Rendez-vous', 'href' => '/Project/public/index.php?page=patient_appointments', 'icon' => 'bi bi-calendar-check'],
    ['key' => 'ordonnances', 'label' => 'Ordonnances', 'href' => '/Project/public/index.php?page=patient_ordonnances', 'icon' => 'bi bi-file-medical'],
    ['key' => 'medicaments', 'label' => 'Mes médicaments', 'href' => '/Project/public/index.php?page=patient_medicaments', 'icon' => 'bi bi-capsule'],
    ['key' => 'billing', 'label' => 'Facturation', 'href' => '/Project/public/index.php?page=patient_billing', 'icon' => 'bi bi-receipt'],
];
?>
<div class="glass-card sidebar-card fade-up">
    <div class="text-center text-white">
        <h5 class="mb-1"><?= htmlspecialchars($patientSidebarName, ENT_QUOTES, 'UTF-8'); ?></h5>
        <p class="text-white-50 mb-0">Patient MediTrack</p>
    </div>
    <div class="card-divider my-3"></div>
    <ul class="nav flex-column gap-1">
        <?php foreach ($patientNavLinks as $link): ?>
            <li class="nav-item">
                <a class="nav-link<?= $link['key'] === $patientSidebarActive ? ' active' : ''; ?>" href="<?= $link['href']; ?>">
                    <i class="<?= $link['icon']; ?>"></i> <?= $link['label']; ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="nav-item">
            <a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
