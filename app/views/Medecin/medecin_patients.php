<?php
$doctorName = htmlspecialchars($doctor['nom_complet'] ?? $doctor['nom'] ?? 'Dr. Médecin');
$specialite = htmlspecialchars($doctor['specialite'] ?? 'Spécialité non renseignée');
$patients = $patients ?? [];
$filters = $filters ?? ['search' => '', 'status' => 'all'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Patients - MediTrack</title>
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
        }
        .sidebar-card .nav-link i {
            margin-right: 12px;
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
        .patient-card {
            border-radius: var(--mt-radius-lg);
            background: var(--mt-card);
            color: var(--mt-ink);
            border: 1px solid var(--mt-border);
            box-shadow: var(--mt-shadow-soft);
            padding: 20px;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .patient-card .text-muted {
            color: var(--mt-muted) !important;
        }
        [data-theme='light'] .patient-card {
            background: #fff;
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
        }
        .patient-card:hover {
            transform: translateY(-4px);
            border-color: var(--mt-accent);
            box-shadow: var(--mt-shadow-card);
        }
        .patient-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(29, 125, 250, 0.12);
            color: var(--mt-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Patients</p>
                    <h1 class="display-6 fw-semibold mb-1">Mes patients</h1>
                    <p class="text-white-50 mb-0">Suivi détaillé et historique des consultations</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-check me-2"></i>Agenda</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical me-2"></i>Ordonnances</a>
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
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people"></i> Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical"></i> Ordonnances</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card-animated mb-4 fade-up">
                    <div class="card-header text-white">
                        <i class="bi bi-funnel me-2"></i>Recherche & filtres
                    </div>
                    <div class="card-body">
                        <form class="row g-3 filters" method="GET" action="/Project/public/index.php">
                            <input type="hidden" name="page" value="medecin_patients">
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label form-label-light">Recherche</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control form-control-glass" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Nom, email, téléphone...">
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label form-label-light">Statut</label>
                                <select class="form-select form-control-glass" name="status">
                                    <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>Tous</option>
                                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Actifs</option>
                                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactifs</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label form-label-light">Tri</label>
                                <select class="form-select form-control-glass" name="sort">
                                    <option value="recent">Récents</option>
                                    <option value="name">A → Z</option>
                                    <option value="visits">Consultations</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 d-flex align-items-end">
                                <button class="btn btn-gradient w-100" type="submit">Appliquer</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row g-3">
                    <?php if (empty($patients)): ?>
                        <div class="col-12">
                            <div class="glass-card text-center py-5 fade-up">
                                <i class="bi bi-people display-6 d-block mb-3 text-white-50"></i>
                                <p class="text-white-50 mb-0">Aucun patient trouvé pour le moment.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <?php
                                $name = htmlspecialchars($patient['name'] ?? 'Patient');
                                $email = htmlspecialchars($patient['email'] ?? '');
                                $lastVisit = htmlspecialchars($patient['last_visit'] ?? 'Jamais');
                                $status = htmlspecialchars($patient['status'] ?? 'Actif');
                                $visits = (int)($patient['visits'] ?? 0);
                                $avatarInitials = strtoupper(substr($name, 0, 1));
                                $statusClass = $status === 'Actif' ? 'chip success' : 'chip neutral';
                            ?>
                            <div class="col-sm-6 col-xl-4">
                                <div class="patient-card h-100 fade-up">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="patient-avatar"><?= $avatarInitials ?></div>
                                        <div>
                                            <h6 class="mb-0"><?= $name ?></h6>
                                            <small class="text-muted">Dernière visite : <?= $lastVisit ?></small>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-2"><i class="bi bi-envelope me-1"></i><?= $email ?: 'Email non renseigné' ?></p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="<?= $statusClass ?>"><?= $status ?></span>
                                        <span class="text-muted small"><i class="bi bi-clipboard2-check me-1"></i><?= $visits ?> consultation(s)</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="/Project/public/index.php?page=medecin_patients&action=view&id=<?= urlencode($patient['id'] ?? '') ?>" class="btn btn-outline-glass btn-sm"><i class="bi bi-eye me-1"></i>Dossier</a>
                                        <a href="/Project/public/index.php?page=medecin_patients&action=message&id=<?= urlencode($patient['id'] ?? '') ?>" class="btn btn-gradient btn-sm"><i class="bi bi-chat-dots me-1"></i>Contacter</a>
                                        <a href="/Project/public/index.php?page=medecin_billing&action=create&patient_id=<?= urlencode($patient['id'] ?? '') ?>" class="btn btn-outline-glass btn-sm"><i class="bi bi-receipt me-1"></i>Facturer</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
