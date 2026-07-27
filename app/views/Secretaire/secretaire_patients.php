<?php
$search = $search ?? '';
$patients = $patients ?? [];
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Base patients - Secrétaire</title>
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
                    <p class="section-title mb-2">Base patients</p>
                    <h1 class="display-6 fw-semibold mb-1">Coordination & contacts</h1>
                    <p class="text-white-50 mb-0">Centralisez les informations et suivez les rendez-vous</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_dashboard"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_appointments"><i class="bi bi-calendar-week me-2"></i>Rendez-vous</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1">Gestion des patients</h5>
                        <p class="text-white-50 mb-0">Suivi & coordination</p>
                    </div>
                    <div class="card-divider my-3"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=secretaire_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=secretaire_appointments"><i class="bi bi-calendar-check"></i> Rendez-vous</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=secretaire_patients"><i class="bi bi-people"></i> Patients</a></li>
                        <li class="nav-item mt-3"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
                <div class="glass-card fade-up delay-1">
                    <div class="card-header text-white"><i class="bi bi-info-circle me-2"></i>Note</div>
                    <div class="card-body text-white-50">
                        <p class="mb-0">Exportez régulièrement la base pour assurer la sauvegarde des contacts.</p>
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
                    <div class="card-header text-white"><i class="bi bi-search me-2"></i>Recherche de patients</div>
                    <div class="card-body">
                        <form class="row gy-3 align-items-end" method="get" action="/Project/public/index.php">
                            <input type="hidden" name="page" value="secretaire_patients">
                            <div class="col-md-8">
                                <label for="patient-search" class="form-label form-label-light">Nom, prénom ou email</label>
                                <input type="text" id="patient-search" name="q" class="form-control form-control-glass" value="<?= htmlspecialchars($search); ?>" placeholder="Ex : Dupont, Marie, patient@example.com">
                            </div>
                            <div class="col-md-4 d-grid gap-2">
                                <button type="submit" class="btn btn-gradient"><i class="bi bi-funnel me-1"></i>Rechercher</button>
                                <a href="/Project/public/index.php?page=secretaire_patients" class="btn btn-outline-glass">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-animated fade-up delay-1">
                    <div class="card-header d-flex justify-content-between align-items-center text-white">
                        <span><i class="bi bi-people me-2"></i>Patients enregistrés</span>
                        <span class="chip neutral"><?= count($patients); ?> résultat(s)</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($patients)): ?>
                            <p class="text-white-50 text-center my-4">Aucun patient ne correspond à la recherche actuelle.</p>
                        <?php else: ?>
                            <div class="table-responsive dark-table">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Dernier rendez-vous</th>
                                            <th>Rdv à venir</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($patients as $patient): ?>
                                            <?php
                                                $fullName = $patient['full_name'] ?? 'Patient';
                                                $upcoming = (int) ($patient['upcoming_count'] ?? 0);
                                                $lastAppointment = $patient['last_appointment'] ?? '';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold text-white"><?= htmlspecialchars($fullName); ?></div>
                                                    <small class="text-white-50">ID #<?= (int)($patient['id'] ?? 0); ?></small>
                                                </td>
                                                <td class="text-white-50"><?= htmlspecialchars($patient['email'] ?? ''); ?></td>
                                                <td class="text-white"><?= htmlspecialchars($patient['telephone'] ?? '—'); ?></td>
                                                <td>
                                                    <?php if (!empty($lastAppointment) && $lastAppointment !== '—'): ?>
                                                        <span class="chip neutral"><i class="bi bi-clock-history me-1"></i><?= htmlspecialchars($lastAppointment); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-white-50">Aucun</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="<?= $upcoming > 0 ? 'chip success' : 'chip neutral'; ?>">
                                                        <i class="bi bi-calendar-event me-1"></i><?= $upcoming; ?> à venir
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group" role="group">
                                                        <a href="mailto:<?= htmlspecialchars($patient['email'] ?? ''); ?>" class="btn btn-outline-glass btn-xs" title="Envoyer un email"><i class="bi bi-envelope"></i></a>
                                                        <?php if (!empty($patient['telephone'])): ?>
                                                            <a href="tel:<?= htmlspecialchars($patient['telephone']); ?>" class="btn btn-outline-glass btn-xs" title="Appeler"><i class="bi bi-telephone"></i></a>
                                                        <?php endif; ?>
                                                        <a href="/Project/public/index.php?page=secretaire_appointments&date=<?= urlencode(date('Y-m-d')); ?>" class="btn btn-outline-glass btn-xs" title="Voir planning"><i class="bi bi-calendar-week"></i></a>
                                                        <a href="/Project/public/index.php?page=medecin_billing&action=create&patient_id=<?= urlencode($patient['id'] ?? '') ?>" class="btn btn-outline-glass btn-xs" title="Créer une facture"><i class="bi bi-receipt"></i></a>
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
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
