<?php
$doctorName = htmlspecialchars($doctor['nom_complet'] ?? $doctor['nom'] ?? 'Dr. Médecin');
$specialite = htmlspecialchars($doctor['specialite'] ?? 'Spécialité non renseignée');
$recrute = (int)($doctor['recrute_secretaire'] ?? 0) === 1;
$secretary = $secretary ?? null;
$applications = $applications ?? [];
$statusBadges = [
    'pending' => ['label' => 'À valider', 'class' => 'badge bg-warning text-dark'],
    'en_attente' => ['label' => 'En attente', 'class' => 'badge bg-secondary'],
    'refusee' => ['label' => 'Non retenue', 'class' => 'badge bg-danger'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Équipe & Candidatures - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        .staff-hero {
            padding: clamp(28px, 4vw, 48px);
            border-radius: var(--mt-radius-xl);
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--mt-shadow-card);
            position: relative;
            overflow: hidden;
        }
        .staff-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 10% 20%, rgba(255, 116, 38, 0.3), transparent 55%),
                        radial-gradient(circle at 90% 0%, rgba(106, 216, 255, 0.25), transparent 50%);
            pointer-events: none;
        }
        .staff-hero > * {
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
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-card .nav-link i {
            margin: 0;
        }
        .sidebar-card .nav-link.active,
        .sidebar-card .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .avatar-circle {
            width: 105px;
            height: 105px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.6rem;
            color: var(--mt-accent-alt);
            margin: 0 auto 18px;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 6px 16px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .application-card {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--mt-radius-md);
            padding: 18px;
            background: rgba(255, 255, 255, 0.03);
            transition: transform .2s ease, border-color .2s ease;
        }
        .application-card:hover {
            transform: translateY(-3px);
            border-color: var(--mt-accent);
        }
        .empty-state {
            border: 2px dashed rgba(255, 255, 255, 0.25);
            border-radius: var(--mt-radius-lg);
            padding: 32px;
            text-align: center;
            color: #c8d2ff;
            background: rgba(255, 255, 255, 0.02);
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Staff</p>
                    <h1 class="display-6 fw-semibold mb-1">Gestion du staff de <?= $doctorName ?></h1>
                    <p class="text-white-50 mb-0">Suivi des secrétaires et des candidatures</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-check me-2"></i>Agenda</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people me-2"></i>Patients</a>
                </div>
            </div>
        </section>
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1"><?= $doctorName ?></h5>
                        <p class="text-white-50 mb-3"><?= $specialite ?></p>
                    </div>
                    <div class="card-divider"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-check"></i> Mon agenda</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people"></i> Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical"></i> Ordonnances</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($successMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errorMessage) && empty($error)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errorMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="row g-4 mb-3">
                    <div class="col-md-6">
                        <div class="card-animated h-100 fade-up">
                            <div class="card-header d-flex justify-content-between align-items-center text-white">
                                <span><i class="bi bi-gear-wide-connected me-2"></i>Recrutement</span>
                            </div>
                            <div class="card-body">
                                <p class="mb-2 text-white-50">Statut des demandes de secrétaires</p>
                                <div class="status-pill mb-3">
                                    <i class="bi <?= $recrute ? 'bi-check-circle-fill text-success' : 'bi-pause-circle-fill text-warning' ?>"></i>
                                    <?= $recrute ? 'Recrutement actif' : 'Recrutement suspendu' ?>
                                </div>
                                <p class="small text-white-50">
                                    <?= $recrute
                                        ? "Les candidatures reçues s'affichent ci-dessous pour validation."
                                        : "Activez le recrutement pour autoriser les secrétaires à postuler auprès de votre cabinet." ?>
                                </p>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="recruitment_action" value="<?= $recrute ? 'disable' : 'enable' ?>">
                                    <button type="submit" class="btn <?= $recrute ? 'btn-outline-danger' : 'btn-gradient' ?> w-100">
                                        <?php if ($recrute): ?>
                                            <i class="bi bi-person-dash-fill me-2" aria-hidden="true"></i>Désactiver
                                        <?php else: ?>
                                            <i class="bi bi-person-plus-fill me-2" aria-hidden="true"></i>Ouvrir le recrutement
                                        <?php endif; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-animated h-100 fade-up delay-1">
                            <div class="card-header d-flex justify-content-between align-items-center text-white">
                                <span><i class="bi bi-person-badge me-2"></i>Secrétaire actuelle</span>
                            </div>
                            <div class="card-body">
                                <?php if ($secretary): ?>
                                    <h5 class="mb-1 text-white"><?= htmlspecialchars($secretary['full_name'] ?? 'Secrétaire') ?></h5>
                                    <p class="text-white-50 mb-2">
                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($secretary['email'] ?? 'Email non renseigné') ?>
                                    </p>
                                    <p class="small mb-0 text-white-50">Assignée depuis le <?= htmlspecialchars($secretary['assigned_at'] ?? '') ?></p>
                                <?php else: ?>
                                    <div class="empty-state py-3">
                                        <i class="bi bi-person-plus display-6 mb-2"></i>
                                        <p class="mb-0">Aucune secrétaire actuellement associée.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-animated fade-up">
                    <div class="card-header d-flex justify-content-between align-items-center text-white">
                        <span><i class="bi bi-inbox me-2"></i>Candidatures reçues</span>
                        <span class="chip neutral">
                            <?= count($applications) ?> candidature<?= count($applications) > 1 ? 's' : '' ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($applications)): ?>
                            <div class="empty-state">
                                <i class="bi bi-archive mb-3" style="font-size: 2.2rem;"></i>
                                <p class="mb-0">Aucune candidature disponible pour le moment.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($applications as $application): ?>
                                    <?php
                                        $statusKey = $application['statut'] ?? 'pending';
                                        $statusConfig = $statusBadges[$statusKey] ?? $statusBadges['pending'];
                                    ?>
                                    <div class="col-12 col-xl-6">
                                        <div class="application-card h-100">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h5 class="mb-1 text-white"><?= htmlspecialchars(($application['prenom'] ?? '') . ' ' . ($application['nom'] ?? '')) ?></h5>
                                                    <p class="text-white-50 mb-1"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($application['email'] ?? '') ?></p>
                                                </div>
                                                <span class="<?= $statusConfig['class'] ?>"><?= $statusConfig['label'] ?></span>
                                            </div>
                                            <?php if (!empty($application['telephone'])): ?>
                                                <p class="small mb-1 text-white-50"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($application['telephone']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($application['experience'])): ?>
                                                <div class="small text-white-50 mb-2">
                                                    <strong>Expérience :</strong>
                                                    <div><?= nl2br(htmlspecialchars($application['experience'])) ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($application['motivation'])): ?>
                                                <div class="small text-white-50 mb-2">
                                                    <strong>Motivation :</strong>
                                                    <div><?= nl2br(htmlspecialchars($application['motivation'])) ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex flex-column gap-2">
                                                <div class="d-flex justify-content-between align-items-center small text-white-50">
                                                    <span><i class="bi bi-clock-history me-1"></i>Soumise le <?= htmlspecialchars($application['created_at'] ?? '') ?></span>
                                                    <a class="btn btn-sm btn-outline-glass" href="mailto:<?= htmlspecialchars($application['email'] ?? '') ?>?subject=Candidature%20Secr%C3%A9taire">Contacter</a>
                                                </div>
                                                <?php if (in_array($application['statut'] ?? '', ['pending', 'en_attente'], true)): ?>
                                                    <div class="d-flex gap-2">
                                                        <form method="POST" class="w-100">
                                                            <input type="hidden" name="application_id" value="<?= (int) ($application['id'] ?? 0) ?>">
                                                            <input type="hidden" name="application_action" value="accept">
                                                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check-circle me-1"></i>Accepter</button>
                                                        </form>
                                                        <form method="POST" class="w-100">
                                                            <input type="hidden" name="application_id" value="<?= (int) ($application['id'] ?? 0) ?>">
                                                            <input type="hidden" name="application_action" value="reject">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-x-circle me-1"></i>Refuser</button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
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
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
