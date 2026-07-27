<?php
$patientRawName = $patientName ?? ($patient['nom_complet'] ?? (($patient['prenom'] ?? '') . ' ' . ($patient['nom'] ?? '')));
$patientRawName = trim($patientRawName);
$patientDisplayName = $patientRawName !== '' ? $patientRawName : 'Patient';
$patientName = $patientDisplayName;
$appointments = $appointments ?? [];
$prescriptions = $prescriptions ?? [];
$medications = $medications ?? [];
$notifications = $notifications ?? [];
$patientSidebarActive = 'dashboard';
$patientSidebarName = $patientDisplayName;
$stats = [
    'upcoming' => count($appointments),
    'prescriptions' => count($prescriptions),
    'medications' => count($medications),
    'notifications' => count($notifications),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Patient - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Espace patient</p>
                    <h1 class="display-5 fw-semibold text-white mb-1">Bienvenue, <?= htmlspecialchars($patientDisplayName, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="text-white-50 mb-0">Suivi personnalisé de vos soins</p>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <?php include __DIR__ . '/../partials/patient_sidebar.php'; ?>
            </div>

            <div class="col-lg-9">
                <div class="stat-grid mb-4 fade-up delay-1">
                    <div class="stat-card">
                        <div class="stat-icon bg-gradient"><i class="bi bi-calendar-day"></i></div>
                        <p class="text-white-50 mb-1">Rendez-vous à venir</p>
                        <h3 class="text-white mb-0"><?= $stats['upcoming'] ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-file-medical"></i></div>
                        <p class="text-white-50 mb-1">Ordonnances actives</p>
                        <h3 class="text-white mb-0"><?= $stats['prescriptions'] ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-capsule"></i></div>
                        <p class="text-white-50 mb-1">Médicaments suivis</p>
                        <h3 class="text-white mb-0"><?= $stats['medications'] ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-bell"></i></div>
                        <p class="text-white-50 mb-1">Notifications</p>
                        <h3 class="text-white mb-0"><?= $stats['notifications'] ?></h3>
                    </div>
                </div>

                <div class="card-animated mb-4 fade-up delay-2">
                    <div class="card-header d-flex justify-content-between align-items-center text-white">
                        <span><i class="bi bi-calendar-check me-2"></i>Mes prochains rendez-vous</span>
                        <a href="/Project/public/index.php?page=patient_appointments" class="btn btn-outline-light btn-sm">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($appointments)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($appointments as $appointment): ?>
                                    <?php
                                        $appointmentDate = new DateTime($appointment['date'] ?? 'now');
                                        $status = $appointment['statut'] ?? 'planifie';
                                        $statusClass = match ($status) {
                                            'confirme' => 'chip success',
                                            'termine' => 'chip neutral',
                                            'annule' => 'chip danger',
                                            default => 'chip warning',
                                        };
                                    ?>
                                    <div class="list-group-item bg-transparent px-0 py-3 text-white d-flex justify-content-between align-items-center border-0 border-bottom border-secondary-subtle">
                                        <div>
                                            <h6 class="mb-1">Dr. <?= htmlspecialchars($appointment['medecin_nom'] ?? 'Médecin') ?></h6>
                                            <small class="text-white-50"><?= $appointmentDate->format('d/m/Y \à H:i') ?></small>
                                            <?php if (!empty($appointment['motif'])): ?>
                                                <p class="mb-0 small text-white-50">Motif : <?= htmlspecialchars($appointment['motif']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-white-50 text-center my-4">Aucun rendez-vous à venir</p>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="/Project/public/index.php?page=patient_appointments" class="btn btn-gradient">
                                <i class="bi bi-plus-circle me-2"></i>Prendre rendez-vous
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card-animated h-100 fade-up delay-3">
                            <div class="card-header text-white"><i class="bi bi-file-medical me-2"></i>Dernières ordonnances</div>
                            <div class="card-body">
                                <?php if (!empty($prescriptions)): ?>
                                    <?php foreach ($prescriptions as $prescription): ?>
                                        <div class="border-bottom border-secondary-subtle pb-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <?php
                                                    $doctorName = $prescription['doctor']
                                                        ?? $prescription['medecin_nom']
                                                        ?? $prescription['medecin']
                                                        ?? 'Médecin traitant';
                                                ?>
                                                <h6 class="mb-0 text-white"><?= htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8'); ?></h6>
                                                <small class="text-white-50"><?= htmlspecialchars($prescription['date']) ?></small>
                                            </div>
                                            <p class="mb-2 small text-white-50"><?= htmlspecialchars($prescription['description'] ?? ($prescription['contenu'] ?? 'Aucune description disponible')) ?></p>
                                            <a href="/Project/public/index.php?page=patient_ordonnances" class="btn btn-outline-glass btn-sm">Voir détails</a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-white-50 text-center my-4">Aucune ordonnance récente.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="/Project/public/index.php?page=patient_ordonnances" class="btn btn-outline-glass btn-sm">Toutes les ordonnances</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-animated h-100 fade-up delay-4">
                            <div class="card-header text-white"><i class="bi bi-capsule me-2"></i>Mes médicaments</div>
                            <div class="card-body">
                                <?php if (!empty($medications)): ?>
                                    <?php foreach ($medications as $medication): ?>
                                        <?php
                                            $medicationName = $medication['name']
                                                ?? $medication['medicament']
                                                ?? $medication['libelle']
                                                ?? 'Médicament';
                                            $medicationDosage = $medication['dosage']
                                                ?? $medication['posologie']
                                                ?? null;
                                        ?>
                                        <div class="border-bottom border-secondary-subtle pb-3 mb-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-0 text-white"><?= htmlspecialchars($medicationName) ?></h6>
                                                    <?php if (!empty($medicationDosage)): ?>
                                                        <small class="text-white-50"><?= htmlspecialchars($medicationDosage) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="chip <?= ($medication['status'] ?? '') === 'en cours' ? 'success' : 'neutral' ?>">
                                                    <?= ucfirst($medication['status'] ?? 'suivi') ?>
                                                </span>
                                            </div>
                                            <p class="mb-1 small text-white-50">Prochaine prise : <?= htmlspecialchars($medication['next_dose'] ?? 'N/A') ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-white-50 text-center my-4">Aucun traitement suivi pour le moment.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="/Project/public/index.php?page=patient_medicaments" class="btn btn-outline-glass btn-sm">Voir mes médicaments</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
