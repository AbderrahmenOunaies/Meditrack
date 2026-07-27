<?php
$filters = $filters ?? [
    'selectedDate' => date('Y-m-d'),
    'doctorId' => null,
    'status' => null,
];
$appointments = $appointments ?? [];
$doctors = $doctors ?? [];
$error = $error ?? null;
$statusOptions = [
    'planifie' => ['label' => 'Planifié', 'class' => 'bg-warning'],
    'confirme' => ['label' => 'Confirmé', 'class' => 'bg-success'],
    'termine' => ['label' => 'Terminé', 'class' => 'bg-secondary'],
    'annule' => ['label' => 'Annulé', 'class' => 'bg-danger'],
];

$queryParameters = [
    'date' => $filters['selectedDate'] ?? null,
    'doctor' => $filters['doctorId'] ?? null,
    'status' => $filters['status'] ?? null,
];
$queryParameters = array_filter($queryParameters, static function ($value) {
    return $value !== null && $value !== '';
});
$actionUrl = '/Project/public/index.php?page=secretaire_appointments' . ($queryParameters ? '&' . http_build_query($queryParameters) : '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des rendez-vous - Secrétaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel fade-down text-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                <div>
                    <p class="section-title mb-2">Gestion des rendez-vous</p>
                    <h1 class="display-6 fw-semibold mb-1">Coordination patients & médecins</h1>
                    <p class="text-white-50 mb-0">Planification centralisée pour le secrétariat</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_dashboard"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_patients"><i class="bi bi-people me-2"></i>Patients</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1">Espace Secrétaire</h5>
                        <p class="text-white-50 mb-0">Organisation & suivi</p>
                    </div>
                    <div class="card-divider my-3"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=secretaire_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=secretaire_appointments"><i class="bi bi-calendar-check"></i> Rendez-vous</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=secretaire_patients"><i class="bi bi-people"></i> Patients</a></li>
                        <li class="nav-item mt-3"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
                <div class="glass-card fade-up delay-1">
                    <div class="card-header text-white"><i class="bi bi-bell me-2"></i>Rappels</div>
                    <div class="card-body text-white-50">
                        <p class="mb-2 small">1. Confirmez les créneaux critiques avant midi.</p>
                        <p class="mb-0 small">2. Prévenez les patients en cas de changement.</p>
                    </div>
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

                <div class="card-animated mb-4 fade-up">
                    <div class="card-header d-flex flex-column flex-lg-row justify-content-between text-white">
                        <span><i class="bi bi-funnel me-2"></i>Filtrer les rendez-vous</span>
                        <a href="/Project/public/index.php?page=secretaire_appointments" class="btn btn-outline-light btn-sm mt-2 mt-lg-0">Réinitialiser</a>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="/Project/public/index.php">
                            <input type="hidden" name="page" value="secretaire_appointments">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label form-label-light">Date</label>
                                    <input type="date" class="form-control form-control-glass" name="date" value="<?= htmlspecialchars($filters['selectedDate']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-light">Médecin</label>
                                    <select class="form-select form-control-glass" name="doctor">
                                        <option value="">Tous les médecins</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= htmlspecialchars($doctor['id']); ?>" <?= ($filters['doctorId'] ?? null) === (int)($doctor['id'] ?? 0) ? 'selected' : ''; ?>>
                                                Dr <?= htmlspecialchars($doctor['full_name'] ?? 'Médecin'); ?> (<?= htmlspecialchars($doctor['specialite'] ?? ''); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-light">Statut</label>
                                    <select class="form-select form-control-glass" name="status">
                                        <option value="">Tous les statuts</option>
                                        <?php foreach ($statusOptions as $value => $definition): ?>
                                            <option value="<?= $value ?>" <?= ($filters['status'] ?? null) === $value ? 'selected' : ''; ?>>
                                                <?= $definition['label']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-gradient">
                                    <i class="bi bi-search me-2"></i>Rechercher
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-animated mb-4 fade-up delay-1">
                    <div class="card-header d-flex justify-content-between align-items-center text-white">
                        <span><i class="bi bi-calendar-check me-2"></i>Rendez-vous</span>
                        <span class="chip neutral"><?= count($appointments); ?> résultat(s)</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($appointments)): ?>
                            <p class="text-white-50 text-center my-3">Aucun rendez-vous trouvé pour les critères sélectionnés.</p>
                        <?php else: ?>
                            <div class="table-responsive dark-table">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Médecin</th>
                                            <th>Date</th>
                                            <th>Heure</th>
                                            <th>Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                            <?php
                                                $statusKey = $appointment['statut'] ?? '';
                                                $status = $statusOptions[$statusKey] ?? ['label' => ucfirst($statusKey), 'class' => 'bg-light text-dark'];
                                                $appointmentDate = $appointment['date'] ?? ($appointment['scheduled_at'] ?? '');
                                                $appointmentTime = $appointment['time'] ?? ($appointment['scheduled_time'] ?? '');
                                            ?>
                                            <tr>
                                                <td class="text-white"><?= htmlspecialchars($appointment['patient_name']); ?></td>
                                                <td class="text-white-50"><?= htmlspecialchars($appointment['doctor_name']); ?></td>
                                                <td class="text-white-50"><?= htmlspecialchars($appointmentDate); ?></td>
                                                <td class="text-white"><?= htmlspecialchars($appointmentTime); ?></td>
                                                <td><span class="<?= $status['class']; ?>"><?= htmlspecialchars($status['label']); ?></span></td>
                                                <td class="text-end">
                                                    <div class="btn-group">
                                                        <?php if (($appointment['statut'] ?? '') !== 'confirme'): ?>
                                                            <form method="POST" action="<?= $actionUrl; ?>" class="d-inline">
                                                                <input type="hidden" name="appointment_id" value="<?= (int)$appointment['id']; ?>">
                                                                <input type="hidden" name="action" value="confirm">
                                                                <?php foreach ($queryParameters as $name => $value): ?>
                                                                    <input type="hidden" name="<?= htmlspecialchars($name); ?>" value="<?= htmlspecialchars($value); ?>">
                                                                <?php endforeach; ?>
                                                                <button type="submit" class="btn btn-sm btn-outline-glass"><i class="bi bi-check"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if (($appointment['statut'] ?? '') !== 'annule'): ?>
                                                            <form method="POST" action="<?= $actionUrl; ?>" class="d-inline">
                                                                <input type="hidden" name="appointment_id" value="<?= (int)$appointment['id']; ?>">
                                                                <input type="hidden" name="action" value="cancel">
                                                                <?php foreach ($queryParameters as $name => $value): ?>
                                                                    <input type="hidden" name="<?= htmlspecialchars($name); ?>" value="<?= htmlspecialchars($value); ?>">
                                                                <?php endforeach; ?>
                                                                <button type="submit" class="btn btn-sm btn-outline-glass text-danger"><i class="bi bi-x"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if (($appointment['statut'] ?? '') !== 'termine' && ($appointment['statut'] ?? '') !== 'annule'): ?>
                                                            <form method="POST" action="<?= $actionUrl; ?>" class="d-inline">
                                                                <input type="hidden" name="appointment_id" value="<?= (int)$appointment['id']; ?>">
                                                                <input type="hidden" name="action" value="complete">
                                                                <?php foreach ($queryParameters as $name => $value): ?>
                                                                    <input type="hidden" name="<?= htmlspecialchars($name); ?>" value="<?= htmlspecialchars($value); ?>">
                                                                <?php endforeach; ?>
                                                                <button type="submit" class="btn btn-sm btn-outline-glass"><i class="bi bi-check-all"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <a href="/Project/public/index.php?page=medecin_billing&action=create&patient_id=<?= urlencode($appointment['patient_id'] ?? '') ?>&rendezvous_id=<?= urlencode($appointment['id'] ?? '') ?>" class="btn btn-xs btn-outline-glass" title="Facturer ce rendez-vous">
                                                            <i class="bi bi-receipt"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-animated fade-up delay-2">
                    <div class="card-header text-white"><i class="bi bi-plus-circle me-2"></i>Ajouter un rendez-vous</div>
                    <div class="card-body">
                        <form method="POST" action="/Project/public/index.php?page=secretaire_appointments&action=add">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label form-label-light">Patient</label>
                                    <input type="text" class="form-control form-control-glass" name="patient_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-light">Médecin</label>
                                    <select class="form-select form-control-glass" name="doctor_id" required>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= htmlspecialchars($doctor['id']); ?>">Dr <?= htmlspecialchars($doctor['full_name']); ?> (<?= htmlspecialchars($doctor['specialite'] ?? ''); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-light">Date</label>
                                    <input type="date" class="form-control form-control-glass" name="date" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-light">Heure</label>
                                    <input type="time" class="form-control form-control-glass" name="time" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-light">Motif</label>
                                    <textarea class="form-control form-control-glass" name="motif" rows="3" placeholder="Détails du rendez-vous"></textarea>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-gradient">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
