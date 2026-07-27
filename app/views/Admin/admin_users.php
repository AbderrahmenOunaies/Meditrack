<?php
$users = isset($users) && is_array($users) ? $users : [];
$filters = isset($filters) && is_array($filters) ? $filters : [
    'role' => 'all',
    'status' => 'active',
    'search' => ''
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - MediTrack</title>
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
        .side-panel {
            border-radius: var(--mt-radius-lg);
            border: 1px solid var(--mt-border);
            background: var(--mt-card);
            box-shadow: var(--mt-shadow-soft);
            padding: 20px;
        }
        .side-panel h6 {
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 0.75rem;
            color: var(--mt-muted);
            margin-bottom: 12px;
        }
        .filters .form-control,
        .filters .form-select {
            border-radius: var(--mt-radius-md);
        }
        .data-card {
            border-radius: var(--mt-radius-xl);
            background: var(--mt-card);
            border: 1px solid var(--mt-border);
            box-shadow: var(--mt-shadow-soft);
        }
        .status-chip {
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="meditrack-surface">
    <header class="admin-topbar glass-card fade-down container-lg mt-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 text-white">
                <i class="bi bi-people fs-4"></i>
                <div>
                    <strong>Gestion des utilisateurs</strong>
                    <div class="small text-white-50">Pilotage global</div>
                </div>
            </div>
            <nav class="nav gap-1">
                <a class="nav-link" href="/Project/public/index.php?page=admin_dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                <a class="nav-link active" href="/Project/public/index.php?page=admin_users"><i class="bi bi-people me-1"></i>Utilisateurs</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_medecins"><i class="bi bi-file-medical me-1"></i>Candidatures</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_settings"><i class="bi bi-gear me-1"></i>Paramètres</a>
            </nav>
            <a class="btn btn-outline-light" href="/Project/public/index.php?page=register"><i class="bi bi-person-plus me-1"></i>Nouveau compte</a>
        </div>
    </header>

    <main class="container-lg my-4 medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative text-white fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Administration</p>
                    <h1 class="display-6 fw-semibold mb-1">Gestion des utilisateurs</h1>
                    <p class="text-white-50 mb-0">Pilotez comptes patients, médecins et staff depuis une console unique.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=admin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=register"><i class="bi bi-person-plus me-2"></i>Créer un compte</a>
                </div>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        <?php endif; ?>

        <section class="row g-4">
            <aside class="col-lg-4">
                <div class="side-panel fade-up">
                    <h6>Filtrer les comptes</h6>
                    <form class="filters" method="get" action="">
                        <input type="hidden" name="page" value="admin_users">
                        <div class="mb-3">
                            <label class="form-label form-label-light">Recherche</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-transparent text-white-50"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control form-control-glass" name="search" placeholder="Nom, email, ID" value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-label-light">Rôle</label>
                            <select class="form-select form-control-glass" name="role">
                                <option value="all" <?= (($filters['role'] ?? 'all') === 'all') ? 'selected' : ''; ?>>Tous</option>
                                <option value="patient" <?= (($filters['role'] ?? '') === 'patient') ? 'selected' : ''; ?>>Patients</option>
                                <option value="medecin" <?= (($filters['role'] ?? '') === 'medecin') ? 'selected' : ''; ?>>Médecins</option>
                                <option value="secretaire" <?= (($filters['role'] ?? '') === 'secretaire') ? 'selected' : ''; ?>>Secrétaires</option>
                                <option value="admin" <?= (($filters['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admins</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-label-light">Statut</label>
                            <select class="form-select form-control-glass" name="status">
                                <option value="all" <?= (($filters['status'] ?? 'all') === 'all') ? 'selected' : ''; ?>>Tous</option>
                                <option value="active" <?= (($filters['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Actifs</option>
                                <option value="pending" <?= (($filters['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>En attente</option>
                                <option value="suspended" <?= (($filters['status'] ?? '') === 'suspended') ? 'selected' : ''; ?>>Suspendus</option>
                            </select>
                        </div>
                        <button class="btn btn-gradient w-100" type="submit"><i class="bi bi-funnel me-2"></i>Appliquer</button>
                    </form>

                    <div class="mt-4">
                        <h6>Actions rapides</h6>
                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-light" href="/Project/public/index.php?page=register"><i class="bi bi-person-plus me-2"></i>Nouveau compte</a>
                            <a class="btn btn-outline-light" href="/Project/public/index.php?page=admin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="col-lg-8 fade-up delay-1">
                <div class="data-card">
                    <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center py-4 px-4">
                        <div>
                            <p class="section-title mb-1">Vue principale</p>
                            <h5 class="text-white mb-0">Utilisateurs (<?= count($users); ?>)</h5>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline-light btn-sm" type="button"><i class="bi bi-upload me-1"></i>Importer</button>
                            <button class="btn btn-outline-light btn-sm" type="button"><i class="bi bi-download me-1"></i>Exporter</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 text-white">
                            <thead class="text-white-50">
                                <tr>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Rôle</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Créé le</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-white-50 py-5">Aucun résultat pour les filtres appliqués.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($user['name'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8'); ?></div>
                                                <small class="text-white-50">ID <?= htmlspecialchars($user['id'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($user['role'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($user['email'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php
                                                $status = strtolower($user['status'] ?? 'active');
                                                $classes = [
                                                    'active' => 'bg-success-subtle text-success',
                                                    'pending' => 'bg-warning-subtle text-warning',
                                                    'suspended' => 'bg-danger-subtle text-danger'
                                                ];
                                                $label = [
                                                    'active' => 'Actif',
                                                    'pending' => 'En attente',
                                                    'suspended' => 'Suspendu'
                                                ];
                                                ?>
                                                <span class="status-chip <?= $classes[$status] ?? 'bg-secondary-subtle text-secondary'; ?>">
                                                    <?= $label[$status] ?? ucfirst($status); ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($user['created_at'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a class="btn btn-sm btn-outline-light" href="/Project/public/index.php?page=admin_users&action=edit&id=<?= urlencode($user['id'] ?? ''); ?>">Modifier</a>
                                                    <?php if ($status !== 'active'): ?>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="page" value="admin_users">
                                                            <input type="hidden" name="action" value="set_status">
                                                            <input type="hidden" name="user_id" value="<?= (int) ($user['id'] ?? 0); ?>">
                                                            <input type="hidden" name="new_status" value="active">
                                                            <input type="hidden" name="filter_role" value="<?= htmlspecialchars($filters['role'] ?? 'all', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($filters['status'] ?? 'all', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="filter_search" value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Réactiver ce compte ?');">
                                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Activer
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if ($status !== 'suspended'): ?>
                                                        <form method="post" class="d-inline ms-1">
                                                            <input type="hidden" name="page" value="admin_users">
                                                            <input type="hidden" name="action" value="set_status">
                                                            <input type="hidden" name="user_id" value="<?= (int) ($user['id'] ?? 0); ?>">
                                                            <input type="hidden" name="new_status" value="suspended">
                                                            <input type="hidden" name="filter_role" value="<?= htmlspecialchars($filters['role'] ?? 'all', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($filters['status'] ?? 'all', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="filter_search" value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Suspendre ce compte ?');">
                                                                <i class="bi bi-slash-circle me-1"></i>Suspendre
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if ($status !== 'pending'): ?>
                                                        <form method="post" class="d-inline ms-1">
                                                            <input type="hidden" name="page" value="admin_users">
                                                            <input type="hidden" name="action" value="set_status">
                                                            <input type="hidden" name="user_id" value="<?= (int) ($user['id'] ?? 0); ?>">
                                                            <input type="hidden" name="new_status" value="pending">
                                                            <input type="hidden" name="filter_role" value="<?= htmlspecialchars($filters['role'] ?? 'all', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="filter_status" value="<?= htmlspecialchars($filters['status'] ?? 'all', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="filter_search" value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Marquer ce compte en attente ?');">
                                                                <i class="bi bi-hourglass-split me-1"></i>En attente
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
