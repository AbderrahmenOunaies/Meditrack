<?php
/** @var array $applications */
/** @var string $status */
/** @var array|null $flash */
/** @var int $pendingDoctorCount */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatures médecins - MediTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        .admin-topbar {
            border-radius: var(--mt-radius-lg);
            padding: 14px 24px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--mt-border);
            box-shadow: var(--mt-shadow-soft);
        }
        .admin-topbar .nav-link {
            color: var(--mt-ink);
            font-weight: 500;
            padding: 10px 16px;
            border-radius: var(--mt-radius-md);
        }
        .admin-topbar .nav-link.active,
        .admin-topbar .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .status-pill {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .glass-panel {
            border-radius: var(--mt-radius-lg);
            border: 1px solid var(--mt-border);
            background: var(--mt-card);
            box-shadow: var(--mt-shadow-soft);
            padding: 24px;
        }
        .table-theme {
            --bs-table-bg: rgba(9, 18, 33, 0.85);
            --bs-table-color: var(--mt-ink);
            --bs-table-border-color: rgba(255, 255, 255, 0.08);
            --bs-table-striped-bg: rgba(255, 255, 255, 0.03);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
            background: var(--bs-table-bg);
            color: var(--bs-table-color);
        }
        .table-theme th {
            color: var(--bs-table-color);
            border-color: var(--bs-table-border-color);
        }
        .table-theme td {
            border-color: var(--bs-table-border-color);
        }
        :root[data-theme='light'] .table-theme,
        body[data-theme='light'] .table-theme {
            --bs-table-bg: var(--mt-bg-light);
            --bs-table-color: var(--mt-ink);
            --bs-table-border-color: rgba(15, 23, 42, 0.08);
            --bs-table-striped-bg: rgba(15, 23, 42, 0.04);
            --bs-table-hover-bg: rgba(15, 23, 42, 0.06);
        }
    </style>
</head>
<body class="meditrack-surface">
    <header class="admin-topbar glass-card fade-down container-lg mt-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 text-white">
                <i class="bi bi-file-medical fs-4"></i>
                <div>
                    <strong>Candidatures médecins</strong>
                    <div class="small text-white-50">Homologation & vérifications</div>
                </div>
            </div>
            <nav class="nav gap-1">
                <a class="nav-link" href="/Project/public/index.php?page=admin_dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_users"><i class="bi bi-people me-1"></i>Utilisateurs</a>
                <a class="nav-link active" href="/Project/public/index.php?page=admin_medecins"><i class="bi bi-file-medical me-1"></i>Candidatures</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_settings"><i class="bi bi-gear me-1"></i>Paramètres</a>
            </nav>
            <div class="text-center">
                <p class="text-white-50 mb-1 small">Dossiers en attente</p>
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><?= (int) ($pendingDoctorCount ?? 0); ?></span>
            </div>
        </div>
    </header>

    <main class="container-lg my-4 medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative text-white fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Administration</p>
                    <h1 class="display-6 fw-semibold mb-1">Candidatures médecins</h1>
                    <p class="text-white-50 mb-0">Vérifiez les accréditations et approuvez les nouveaux praticiens MediTrack.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=admin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=admin_settings"><i class="bi bi-gear me-2"></i>Paramètres</a>
                </div>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <section class="glass-panel fade-up">
            <form class="row g-3 align-items-end" method="get" action="">
                <input type="hidden" name="page" value="admin_medecins">
                <div class="col-md-4">
                    <label class="form-label form-label-light">Statut</label>
                    <select class="form-select form-control-glass" name="status">
                        <option value="pending" <?= $status === 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : ''; ?>>Approuvées</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : ''; ?>>Rejetées</option>
                        <option value="all" <?= $status === 'all' ? 'selected' : ''; ?>>Tous les dossiers</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-gradient w-100" type="submit"><i class="bi bi-funnel me-2"></i>Filtrer</button>
                </div>
                <div class="col-md-5 text-md-end">
                    <a href="/Project/public/index.php?page=admin_dashboard" class="btn btn-outline-light me-2"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    <a href="/Project/public/index.php?page=admin_settings" class="btn btn-outline-light"><i class="bi bi-gear me-2"></i>Paramètres</a>
                </div>
            </form>
        </section>

        <section class="glass-panel fade-up delay-1">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <p class="section-title mb-1">Dossiers</p>
                    <h5 class="text-white mb-0">Liste des candidatures</h5>
                </div>
                <span class="badge bg-info-subtle text-info"><i class="bi bi-info-circle me-1"></i>Filtre : <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-theme">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Identité</th>
                        <th>Contact</th>
                        <th>Spécialité</th>
                        <th>Références</th>
                        <th>Statut</th>
                        <th>Soumis</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-white-50 py-5">Aucun dossier pour ce filtre.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td>#<?= (int) $application['id']; ?></td>
                                <td>
                                    <strong><?= htmlspecialchars(($application['prenom'] ?? '') . ' ' . ($application['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <div class="text-white-50 small">N° ordre : <?= htmlspecialchars($application['numero_ordre'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($application['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="text-white-50">
                                        <?= htmlspecialchars($application['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($application['specialite'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="small text-white-50" style="max-width: 240px; white-space: pre-wrap;">
                                    <?= htmlspecialchars($application['pieces_justificatives'] ?? 'Aucune pièce fournie', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClasses = [
                                        'pending' => 'bg-warning text-dark',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger'
                                    ];
                                    $statusLabel = [
                                        'pending' => 'En attente',
                                        'approved' => 'Approuvée',
                                        'rejected' => 'Rejetée'
                                    ];
                                    $currentStatus = $application['statut'] ?? 'pending';
                                    ?>
                                    <span class="status-pill <?= $statusClasses[$currentStatus] ?? 'bg-secondary'; ?>">
                                        <?= $statusLabel[$currentStatus] ?? ucfirst($currentStatus); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-white-50">
                                        <?= htmlspecialchars($application['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($application['statut'] ?? 'pending') === 'pending'): ?>
                                        <form method="post" class="d-flex flex-wrap gap-2">
                                            <input type="hidden" name="application_id" value="<?= (int) $application['id']; ?>">
                                            <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" name="action" value="approve" onclick="return confirm('Approuver cette candidature ?');">
                                                <i class="bi bi-check2"></i>
                                                <span class="fw-semibold">Accepter</span>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" name="action" value="reject" onclick="return confirm('Rejeter cette candidature ?');">
                                                <i class="bi bi-x"></i>
                                                <span class="fw-semibold">Refuser</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="text-white-50 small">
                                            Traité par <?= htmlspecialchars($application['admin_name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?><br>
                                            le <?= htmlspecialchars($application['decision_at'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
