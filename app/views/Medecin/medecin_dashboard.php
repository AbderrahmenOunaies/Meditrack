<?php
$doctorName = htmlspecialchars($doctor['nom_complet'] ?? $doctor['nom'] ?? 'Dr. Médecin');
$specialite = htmlspecialchars($doctor['specialite'] ?? 'Spécialité non renseignée');
$stats = $stats ?? [
    'todayAppointments' => 0,
    'weekAppointments' => 0,
    'patientsFollowed' => 0,
    'pendingTasks' => 0
];
$todayAppointments = $todayAppointments ?? [];
$recentActivities = $recentActivities ?? [];
$notifications = $notifications ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Médecin - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        .hero-panel {
            padding: clamp(32px, 4vw, 48px);
            border-radius: var(--mt-radius-xl);
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: var(--mt-shadow-card);
            overflow: hidden;
        }
        .hero-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 10% 20%, rgba(255, 116, 38, 0.35), transparent 45%),
                        radial-gradient(circle at 90% 0%, rgba(106, 216, 255, 0.3), transparent 40%);
        }
        .hero-panel > * {
            position: relative;
            z-index: 2;
        }
        .sidebar-card {
            padding: clamp(18px, 3vw, 28px);
        }
        .sidebar-card .nav {
            padding-left: 0;
            margin-bottom: 0;
        }
        .sidebar-card .nav-link {
            color: var(--mt-ink);
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 500;
            transition: background 0.2s ease, color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-card .nav-link i {
            margin-right: 12px;
        }
        .sidebar-card .nav-link.active,
        .sidebar-card .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .timeline-step::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 0;
            bottom: -18px;
            width: 2px;
            background: rgba(255, 255, 255, 0.15);
        }
        .timeline-step:last-child::before {
            display: none;
        }
        .pill-badge {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Tableau de bord</p>
                    <h1 class="display-5 fw-semibold text-white mb-1">Bonjour, <?= $doctorName ?></h1>
                    <p class="text-white-50 mb-0"><?= $specialite ?></p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-week me-2"></i>Agenda</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people me-2"></i>Patients</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill me-2"></i>Staff</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1"><?= $doctorName ?></h5>
                        <p class="text-white-50 mb-0"><?= $specialite ?></p>
                    </div>
                    <div class="card-divider my-3"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-check"></i> Mon agenda</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people"></i> Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical"></i> Ordonnances</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-receipt"></i> Facturation</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="stat-grid mb-4 fade-up delay-1">
                    <div class="stat-card">
                        <div class="stat-icon bg-gradient"><i class="bi bi-calendar-day"></i></div>
                        <p class="text-white-50 mb-1">Consultations aujourd'hui</p>
                        <h3 class="text-white mb-0"><?= (int)($stats['todayAppointments'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
                        <p class="text-white-50 mb-1">Cette semaine</p>
                        <h3 class="text-white mb-0"><?= (int)($stats['weekAppointments'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-people"></i></div>
                        <p class="text-white-50 mb-1">Patients suivis</p>
                        <h3 class="text-white mb-0"><?= (int)($stats['patientsFollowed'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-clipboard-check"></i></div>
                        <p class="text-white-50 mb-1">Tâches en attente</p>
                        <h3 class="text-white mb-0"><?= (int)($stats['pendingTasks'] ?? 0) ?></h3>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-xl-7">
                        <div class="card-animated h-100 fade-up delay-2">
                            <div class="card-header d-flex justify-content-between align-items-center text-white">
                                <span><i class="bi bi-clock-history me-2"></i>Consultations du jour</span>
                                <a href="/Project/public/index.php?page=medecin_agenda" class="btn btn-outline-light btn-sm">Voir l'agenda</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($todayAppointments)): ?>
                                    <p class="text-white-50 text-center my-4">Aucun rendez-vous programmé pour le moment.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($todayAppointments as $appointment): ?>
                                            <div class="list-group-item bg-transparent px-0 py-3 text-white d-flex justify-content-between align-items-center border-0 border-bottom border-secondary-subtle">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($appointment['patient'] ?? 'Patient') ?></h6>
                                                    <small class="text-white-50">Motif : <?= htmlspecialchars($appointment['reason'] ?? 'Non précisé') ?></small>
                                                </div>
                                                <span class="chip status-chip"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($appointment['time'] ?? '') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card-animated h-100 fade-up delay-3">
                            <div class="card-header text-white"><i class="bi bi-activity me-2"></i>Dernières activités</div>
                            <div class="card-body">
                                <?php if (empty($recentActivities)): ?>
                                    <p class="text-white-50 text-center my-4">Aucune activité récente.</p>
                                <?php else: ?>
                                    <div class="position-relative ps-4">
                                        <?php foreach ($recentActivities as $index => $activity): ?>
                                            <div class="mb-4 position-relative timeline-step list-activity">
                                                <div class="activity-item delay-<?= min($index, 5); ?>">
                                                    <div class="ms-2">
                                                        <h6 class="mb-1 text-white"><?= htmlspecialchars($activity['title'] ?? '') ?></h6>
                                                        <small class="text-white-50 d-block mb-1"><?= htmlspecialchars($activity['time'] ?? '') ?></small>
                                                        <p class="mb-0 small text-white-50"><?= htmlspecialchars($activity['description'] ?? '') ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
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
