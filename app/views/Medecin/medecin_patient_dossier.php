<?php
$doctorName = htmlspecialchars($doctor['nom_complet'] ?? $doctor['nom'] ?? 'Dr. Médecin');
$specialite = htmlspecialchars($doctor['specialite'] ?? 'Spécialité non renseignée');
$patient = $patientInfo ?? [];
$record = $medicalRecord ?? [];
$upcomingAppointments = $upcomingAppointments ?? [];
$appointmentsHistory = $appointmentsHistory ?? [];
$ordonnances = $ordonnances ?? [];
$medications = $medications ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier Patient - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        .hero-panel {
            padding: clamp(32px, 4vw, 48px);
            border-radius: var(--mt-radius-xl);
            background: var(--mt-gradient-hero);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: var(--mt-shadow-card);
            position: relative;
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
        .sidebar-card .nav-link {
            color: var(--mt-ink);
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-card .nav-link.active,
        .sidebar-card .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .record-card {
            border-radius: var(--mt-radius-lg);
            background: var(--mt-card);
            color: var(--mt-ink);
            border: 1px solid var(--mt-border);
            box-shadow: var(--mt-shadow-soft);
            padding: 24px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .record-card:hover {
            transform: translateY(-3px);
            border-color: var(--mt-accent);
        }
        .record-card h5 {
            font-weight: 600;
        }
        .record-card .text-muted {
            color: var(--mt-muted) !important;
        }
        .timeline {
            position: relative;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: rgba(15, 23, 42, 0.12);
        }
        .timeline-item {
            position: relative;
            padding-left: 48px;
            margin-bottom: 24px;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-dot {
            position: absolute;
            left: 12px;
            top: 6px;
            width: 12px;
            height: 12px;
            background: var(--mt-accent);
            border-radius: 50%;
            box-shadow: 0 0 0 6px rgba(255, 116, 38, 0.18);
        }
        .empty-state {
            border-radius: var(--mt-radius-lg);
            background: rgba(255, 255, 255, 0.04);
            padding: 28px;
            text-align: center;
            color: var(--mt-muted);
            border: 1px dashed var(--mt-border);
        }
        .tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <div class="glass-card tag mb-3"><i class="bi bi-folder2-open"></i> Dossier patient</div>
                    <h1 class="display-6 fw-semibold mb-1"><?= htmlspecialchars($patient['full_name'] ?? 'Patient') ?></h1>
                    <p class="text-white-50 mb-0">Consultation détaillée et suivi clinique personnalisé</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/Project/public/index.php?page=medecin_patients" class="btn btn-outline-light"><i class="bi bi-arrow-left me-2"></i>Retour aux patients</a>
                    <a href="/Project/public/index.php?page=medecin_ordonnances&action=new&patient=<?= urlencode($patient['id'] ?? '') ?>" class="btn btn-gradient"><i class="bi bi-file-earmark-plus me-2"></i>Nouvelle ordonnance</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1"><?= $doctorName ?></h5>
                        <p class="text-white-50 mb-3"><?= $specialite ?></p>
                    </div>
                    <div class="card-divider"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-check"></i> Mon agenda</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people"></i> Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical"></i> Ordonnances</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="record-card h-100 fade-up">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="rounded-circle bg-gradient text-white fw-bold d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                                    <?= htmlspecialchars($patient['initials'] ?? 'P') ?>
                                </div>
                                <div>
                                    <h5 class="mb-1">Informations générales</h5>
                                    <p class="text-muted small mb-0">Dernière mise à jour <?= htmlspecialchars($record['last_update_formatted'] ?? 'Non disponible') ?></p>
                                </div>
                            </div>
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted d-block">Âge</span>
                                    <strong><?= htmlspecialchars((string)($patient['age'] ?? 'N/A')) ?> ans</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block">Date de naissance</span>
                                    <strong><?= htmlspecialchars($patient['date_naissance_formatted'] ?? 'Non renseignée') ?></strong>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted d-block">Adresse</span>
                                    <strong><?= htmlspecialchars($patient['adresse'] ?? 'Non renseignée') ?></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block">Téléphone</span>
                                    <strong><?= htmlspecialchars($patient['telephone'] ?? 'Non renseigné') ?></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block">Assurance</span>
                                    <strong><?= htmlspecialchars($patient['assurance_maladie'] ?? 'Non renseignée') ?></strong>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted d-block">Email</span>
                                    <strong><?= htmlspecialchars($patient['email'] ?? 'Non renseigné') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="record-card h-100 fade-up">
                            <h5 class="mb-3">Résumé médical</h5>
                            <?php if (!empty($record)): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted text-uppercase small mb-1">Observations</h6>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($record['observations'] ?? '')) ?: '<span class="text-muted">Aucune observation renseignée.</span>' ?></p>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted text-uppercase small mb-1">Traitements en cours</h6>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($record['traitements'] ?? '')) ?: '<span class="text-muted">Non renseigné</span>' ?></p>
                                </div>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <h6 class="text-muted text-uppercase small mb-1">Allergies</h6>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($record['allergies'] ?? '')) ?: '<span class="text-muted">Non renseigné</span>' ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <h6 class="text-muted text-uppercase small mb-1">Antécédents</h6>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($record['antecedents'] ?? '')) ?: '<span class="text-muted">Non renseigné</span>' ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-clipboard2-pulse display-6 d-block mb-3"></i>
                                    Aucun dossier médical enregistré pour ce patient.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-xl-6">
                        <div class="record-card h-100 fade-up">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Rendez-vous à venir</h5>
                                <span class="badge bg-primary-subtle text-primary"><?= count($upcomingAppointments) ?> programmés</span>
                            </div>
                            <?php if (empty($upcomingAppointments)): ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-calendar2-week d-block mb-2"></i>
                                    Aucun rendez-vous planifié.
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($upcomingAppointments as $appointment): ?>
                                        <div class="list-group-item py-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong><?= htmlspecialchars($appointment['motif'] ?? 'Consultation') ?></strong>
                                                    <div class="text-muted small">Statut : <?= htmlspecialchars($appointment['statut'] ?? '') ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($appointment['date_formatted'] ?? '') ?></span><br>
                                                    <span class="text-muted small">à <?= htmlspecialchars($appointment['time_formatted'] ?? '') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="record-card h-100 fade-up">
                            <h5 class="mb-3">Historique des consultations</h5>
                            <?php if (empty($appointmentsHistory)): ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-clock-history d-block mb-2"></i>
                                    Aucun historique enregistré.
                                </div>
                            <?php else: ?>
                                <div class="position-relative timeline">
                                    <?php foreach ($appointmentsHistory as $history): ?>
                                        <div class="timeline-item">
                                            <span class="timeline-dot"></span>
                                            <h6 class="mb-1"><?= htmlspecialchars($history['motif'] ?? 'Consultation') ?></h6>
                                            <div class="text-muted small">Le <?= htmlspecialchars($history['date_formatted'] ?? '') ?> à <?= htmlspecialchars($history['time_formatted'] ?? '') ?></div>
                                            <span class="badge bg-secondary-subtle text-secondary">Statut : <?= htmlspecialchars($history['statut'] ?? '') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="record-card h-100 fade-up">
                            <h5 class="mb-3">Ordonnances récentes</h5>
                            <?php if (empty($ordonnances)): ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-file-medical d-block mb-2"></i>
                                    Aucune ordonnance disponible.
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($ordonnances as $item): ?>
                                        <div class="list-group-item py-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong>Ordonnance #<?= htmlspecialchars((string)($item['id'] ?? '')) ?></strong>
                                                    <p class="text-muted small mb-1"><?= nl2br(htmlspecialchars($item['contenu'] ?? '')) ?></p>
                                                    <?php if (!empty($item['posologie'])): ?>
                                                        <span class="badge bg-info-subtle text-info">Posologie : <?= htmlspecialchars($item['posologie']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($item['date_formatted'] ?? '') ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="record-card h-100 fade-up">
                            <h5 class="mb-3">Médications en cours</h5>
                            <?php if (empty($medications)): ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-capsule d-block mb-2"></i>
                                    Aucun traitement actif enregistré.
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($medications as $medication): ?>
                                        <div class="list-group-item py-3">
                                            <strong><?= htmlspecialchars($medication['label'] ?? '') ?></strong>
                                            <div class="text-muted small">Posologie : <?= htmlspecialchars($medication['posologie'] ?? 'Non précisée') ?></div>
                                            <?php if (!empty($medication['duree_traitement'])): ?>
                                                <span class="badge bg-warning-subtle text-warning">Durée : <?= htmlspecialchars($medication['duree_traitement']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($medication['date'])): ?>
                                                <div class="text-muted small mt-1">Prescrit le <?= htmlspecialchars($medication['date']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
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
