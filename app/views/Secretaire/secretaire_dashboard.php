 <?php
$stats = $stats ?? [
    'todayAppointments' => 0,
    'weekAppointments' => 0,
    'pendingRequests' => 0,
    'totalPatients' => 0,
    'totalDoctors' => 0,
];
$todayAppointments = $todayAppointments ?? [];
$pendingTasks = $pendingTasks ?? [];
$recentPatients = $recentPatients ?? [];
$error = $error ?? null;
$secretaireName = htmlspecialchars($_SESSION['user_name'] ?? 'Secrétaire');

$statusLabels = [
    'planifie' => ['label' => 'Planifié', 'class' => 'chip warning'],
    'confirme' => ['label' => 'Confirmé', 'class' => 'chip success'],
    'termine' => ['label' => 'Terminé', 'class' => 'chip neutral'],
    'annule' => ['label' => 'Annulé', 'class' => 'chip danger'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord Secrétaire - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel fade-down text-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                <div>
                    <p class="section-title mb-2">Espace Secrétaire</p>
                    <h1 class="display-6 fw-semibold mb-1">Bonjour, <?= $secretaireName; ?></h1>
                    <p class="text-white-50 mb-0">Suivi global des plannings et demandes du cabinet</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_appointments"><i class="bi bi-calendar-week me-2"></i>Rendez-vous</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_patients"><i class="bi bi-people me-2"></i>Patients</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1"><?= $secretaireName; ?></h5>
                        <p class="text-white-50 mb-0">Secrétaire médicale</p>
                    </div>
                    <div class="card-divider my-3"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=secretaire_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=secretaire_appointments"><i class="bi bi-calendar-check"></i> Rendez-vous</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=secretaire_patients"><i class="bi bi-people"></i> Patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=secretaire_billing"><i class="bi bi-receipt"></i> Facturation</a></li>
                        <li class="nav-item mt-3"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9">
                <?php foreach (['secretaire_success' => 'success', 'secretaire_error' => 'danger'] as $key => $type): ?>
                    <?php if (!empty($_SESSION[$key])): ?>
                        <div class="alert alert-<?= $type ?> alert-dismissible fade show card-animated mb-3" role="alert">
                            <?= htmlspecialchars($_SESSION[$key]); unset($_SESSION[$key]); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-warning alert-dismissible fade show card-animated mb-3" role="alert">
                        <?= htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>

                <div class="stat-grid mb-4 fade-up delay-1">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-calendar-day"></i></div>
                        <p class="text-white-50 mb-1">Consultations du jour</p>
                        <h3 class="text-white mb-0"><?= (int) $stats['todayAppointments']; ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
                        <p class="text-white-50 mb-1">7 prochains jours</p>
                        <h3 class="text-white mb-0"><?= (int) $stats['weekAppointments']; ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                        <p class="text-white-50 mb-1">En attente</p>
                        <h3 class="text-white mb-0"><?= (int) $stats['pendingRequests']; ?></h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-hospital"></i></div>
                        <p class="text-white-50 mb-1">Structure</p>
                        <h5 class="text-white mb-0"><?= (int) $stats['totalDoctors']; ?> médecins</h5>
                        <small class="text-white-50"><?= (int) $stats['totalPatients']; ?> patients</small>
                    </div>
                </div>

                <div class="card-animated mb-4 fade-up delay-2">
                    <div class="card-header d-flex justify-content-between align-items-center text-white">
                        <span><i class="bi bi-clock-history me-2"></i>Rendez-vous du jour</span>
                        <a href="/Project/public/index.php?page=secretaire_appointments" class="btn btn-outline-light btn-sm">Gérer le planning</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($todayAppointments)): ?>
                            <p class="text-white-50 text-center my-3">Aucun rendez-vous planifié aujourd'hui.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($todayAppointments as $appointment): ?>
                                    <?php
                                        $statusKey = $appointment['statut'] ?? '';
                                        $status = $statusLabels[$statusKey] ?? ['label' => ucfirst($statusKey), 'class' => 'chip neutral'];
                                    ?>
                                    <div class="list-group-item py-3">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                            <div>
                                                <div class="d-flex align-items-center flex-wrap gap-3">
                                                    <span class="chip neutral"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($appointment['time'] ?? ''); ?></span>
                                                    <strong><?= htmlspecialchars($appointment['patient_name'] ?? 'Patient'); ?></strong>
                                                    <span class="text-white-50">avec Dr <?= htmlspecialchars($appointment['doctor_name'] ?? ''); ?></span>
                                                </div>
                                                <?php if (!empty($appointment['motif'])): ?>
                                                    <p class="text-white-50 small mb-0 mt-2"><i class="bi bi-chat-left-dots me-1"></i><?= htmlspecialchars($appointment['motif']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <span class="<?= $status['class']; ?>"><?= htmlspecialchars($status['label']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card-animated h-100 fade-up delay-3">
                            <div class="card-header text-white"><i class="bi bi-flag me-2"></i>Demandes prioritaires</div>
                            <div class="card-body">
                                <?php if (empty($pendingTasks)): ?>
                                    <p class="text-white-50 text-center my-3">Aucune action urgente à traiter.</p>
                                <?php else: ?>
                                    <div class="position-relative ps-4">
                                        <div class="timeline-line"></div>
                                        <?php foreach ($pendingTasks as $task): ?>
                                            <div class="mb-4 position-relative">
                                                <div class="timeline-dot position-absolute" style="left: -24px; top: 6px;"></div>
                                                <div>
                                                    <h6 class="mb-1 text-white">Rdv <?= htmlspecialchars($task['patient_name'] ?? ''); ?></h6>
                                                    <small class="text-white-50 d-block">Dr <?= htmlspecialchars($task['doctor_name'] ?? ''); ?> &middot; <?= htmlspecialchars($task['scheduled_at'] ?? ''); ?></small>
                                                    <?php if (!empty($task['motif'])): ?>
                                                        <p class="text-white-50 small mb-1 mt-1"><i class="bi bi-chat-dots me-1"></i><?= htmlspecialchars($task['motif']); ?></p>
                                                    <?php endif; ?>
                                                    <a class="btn btn-link text-decoration-none p-0 small" href="/Project/public/index.php?page=secretaire_appointments&date=<?= urlencode(date('Y-m-d')); ?>">Voir dans le planning</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card-animated h-100 fade-up delay-4">
                            <div class="card-header d-flex justify-content-between align-items-center text-white">
                                <span><i class="bi bi-person-lines-fill me-2"></i>Nouveaux patients</span>
                                <a class="btn btn-sm btn-outline-light" href="/Project/public/index.php?page=secretaire_patients">Voir la base</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentPatients)): ?>
                                    <p class="text-white-50 text-center my-3">Aucun nouveau patient récemment.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recentPatients as $patient): ?>
                                            <div class="list-group-item py-3">
                                                <h6 class="mb-1 text-white"><?= htmlspecialchars($patient['full_name'] ?? ''); ?></h6>
                                                <small class="text-white-50 d-block"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($patient['email'] ?? ''); ?></small>
                                                <?php if (!empty($patient['telephone'])): ?>
                                                    <small class="text-white-50 d-block"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($patient['telephone']); ?></small>
                                                <?php endif; ?>
                                                <span class="chip neutral mt-2">Inscrit le <?= htmlspecialchars($patient['created_at'] ?? ''); ?></span>
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
