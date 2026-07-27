<?php
$doctorName = htmlspecialchars($doctor['nom_complet'] ?? $doctor['nom'] ?? 'Dr. Médecin');
$specialite = htmlspecialchars($doctor['specialite'] ?? 'Spécialité non renseignée');
$prescriptions = $prescriptions ?? [];
$filters = $filters ?? ['search' => '', 'status' => 'all'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Ordonnances - MediTrack</title>
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
        .prescription-card {
            border-radius: var(--mt-radius-lg);
            border: 1px solid var(--mt-border);
            background: var(--mt-card);
            color: var(--mt-ink);
            padding: 20px;
            box-shadow: var(--mt-shadow-soft);
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .prescription-card:hover {
            transform: translateY(-4px);
            border-color: var(--mt-accent);
            box-shadow: var(--mt-shadow-card);
        }
        .medication-pill {
            border-radius: 999px;
            padding: 4px 10px;
            background: rgba(255, 255, 255, 0.12);
            color: var(--mt-ink);
            font-size: 0.75rem;
            margin-right: 6px;
            margin-bottom: 6px;
            display: inline-block;
        }
        .filters .form-label {
            color: var(--mt-muted);
        }
    </style>
</head>
<body class="meditrack-surface">
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Ordonnances</p>
                    <h1 class="display-6 fw-semibold mb-1">Gestion des ordonnances</h1>
                    <p class="text-white-50 mb-0">Création, suivi et renouvellement des prescriptions</p>
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
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical"></i> Ordonnances</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card-animated mb-4 fade-up">
                    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center text-white">
                        <span><i class="bi bi-funnel me-2"></i>Filtres</span>
                        <div>
                            <a href="/Project/public/index.php?page=medecin_ordonnances&action=new" class="btn btn-gradient"><i class="bi bi-plus-circle me-2"></i>Nouvelle ordonnance</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form class="row g-3 filters" method="GET" action="/Project/public/index.php">
                            <input type="hidden" name="page" value="medecin_ordonnances">
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label form-label-light">Recherche</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control form-control-glass" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Patient, médicament, référence...">
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label form-label-light">Statut</label>
                                <select class="form-select form-control-glass" name="status">
                                    <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>Tous</option>
                                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Actives</option>
                                    <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expirées</option>
                                    <option value="renewal" <?= ($filters['status'] ?? '') === 'renewal' ? 'selected' : '' ?>>À renouveler</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label form-label-light">Période</label>
                                <select class="form-select form-control-glass" name="period">
                                    <option value="recent">Récents</option>
                                    <option value="week">7 derniers jours</option>
                                    <option value="month">30 derniers jours</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 d-flex align-items-end">
                                <button class="btn btn-gradient w-100" type="submit">Appliquer</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row g-3">
                    <?php if (empty($prescriptions)): ?>
                        <div class="col-12">
                            <div class="glass-card text-center py-5 fade-up">
                                <i class="bi bi-file-earmark-text display-6 d-block mb-3 text-white-50"></i>
                                <p class="text-white-50 mb-0">Aucune ordonnance enregistrée.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($prescriptions as $prescription): ?>
                            <?php
                                $patientName = htmlspecialchars($prescription['patient'] ?? 'Patient');
                                $ref = htmlspecialchars($prescription['reference'] ?? 'N/A');
                                $date = htmlspecialchars($prescription['date'] ?? '');
                                $status = htmlspecialchars($prescription['status'] ?? 'Active');
                                $statusClass = match ($status) {
                                    'Active' => 'chip success',
                                    'Expirée' => 'chip danger',
                                    'À renouveler' => 'chip warning',
                                    default => 'chip neutral',
                                };
                                $medications = $prescription['medications'] ?? [];
                            ?>
                            <div class="col-md-6">
                                <div class="prescription-card h-100 fade-up">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1 text-white"><?= $patientName ?></h6>
                                            <small class="text-white-50">Réf. : <?= $ref ?> • <?= $date ?></small>
                                        </div>
                                        <span class="<?= $statusClass ?>"><?= $status ?></span>
                                    </div>
                                    <p class="text-white-50 small mb-2"><i class="bi bi-chat-right-quote me-1"></i><?= htmlspecialchars($prescription['notes'] ?? 'Aucune note spécifiée.') ?></p>
                                    <div class="mb-3">
                                        <?php if (empty($medications)): ?>
                                            <span class="text-white-50 small">Aucun médicament listé.</span>
                                        <?php else: ?>
                                            <?php foreach ($medications as $med): ?>
                                                <span class="medication-pill"><i class="bi bi-capsule me-1"></i><?= htmlspecialchars($med) ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="/Project/public/index.php?page=medecin_ordonnances&action=view&id=<?= urlencode($prescription['id'] ?? '') ?>" class="btn btn-outline-glass btn-sm w-100"><i class="bi bi-eye me-1"></i>Détails</a>
                                        <a href="/Project/public/index.php?page=medecin_ordonnances&action=renew&id=<?= urlencode($prescription['id'] ?? '') ?>" class="btn btn-gradient btn-sm w-100"><i class="bi bi-arrow-repeat me-1"></i>Renouveler</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
