<?php
$adminName = htmlspecialchars($admin['nom_complet'] ?? trim(($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? '')), ENT_QUOTES, 'UTF-8');
$adminName = $adminName ?: 'Administrateur';
$adminRole = htmlspecialchars($admin['role_label'] ?? 'Superviseur global', ENT_QUOTES, 'UTF-8');

$baseStats = [
    'usersTotal' => 0,
    'activeSessions' => 0,
    'pendingTickets' => 0,
    'doctorsVerified' => 0,
];
$stats = isset($stats) && is_array($stats) ? array_merge($baseStats, $stats) : $baseStats;

$recentUsers = (isset($recentUsers) && is_array($recentUsers)) ? $recentUsers : [];
$activities = (isset($activities) && is_array($activities)) ? $activities : [];
$systemHealth = isset($systemHealth) && is_array($systemHealth) ? array_merge([
    'uptime' => '—',
    'latency' => '—',
    'dbStatus' => 'Inconnu',
    'backup' => 'Non planifié',
], $systemHealth) : [
    'uptime' => '99.98 %',
    'latency' => '142 ms',
    'dbStatus' => 'Opérationnel',
    'backup' => 'Planifié 02:00',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - MediTrack</title>
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
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        .stat-card {
            background: var(--mt-card);
            border: 1px solid var(--mt-border);
            border-radius: var(--mt-radius-lg);
            padding: 20px;
            box-shadow: var(--mt-shadow-soft);
            transition: transform .2s ease, border-color .2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--mt-accent);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--mt-radius-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
            color: var(--mt-accent);
            font-size: 1.3rem;
        }
        .glass-panel {
            border-radius: var(--mt-radius-lg);
            border: 1px solid var(--mt-border);
            background: var(--mt-card);
            box-shadow: var(--mt-shadow-soft);
            padding: 24px;
        }
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid var(--mt-border);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .system-chip {
            border-radius: var(--mt-radius-md);
            border: 1px dashed var(--mt-border);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            color: var(--mt-ink);
        }
    </style>
</head>
<body class="meditrack-surface">
    <header class="admin-topbar glass-card fade-down container-lg mt-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 text-white">
                <i class="bi bi-shield-lock fs-4"></i>
                <div>
                    <strong>MediTrack Admin</strong>
                    <div class="small text-white-50"><?= $adminRole ?></div>
                </div>
            </div>
            <nav class="nav gap-1">
                <a class="nav-link active" href="/Project/public/index.php?page=admin_dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_users"><i class="bi bi-people me-1"></i>Utilisateurs</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_medecins"><i class="bi bi-file-medical me-1"></i>Candidatures</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_settings"><i class="bi bi-gear me-1"></i>Paramètres</a>
            </nav>
            <a class="btn btn-outline-light" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
        </div>
    </header>

    <main class="container-lg my-4 d-flex flex-column gap-4 medi-layout">
        <section class="hero-panel position-relative text-white fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Centre de contrôle</p>
                    <h1 class="display-5 fw-semibold mb-1"><?= $adminName ?></h1>
                    <p class="text-white-50 mb-0">Pilotage global de MediTrack et surveillance temps réel</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=admin_users"><i class="bi bi-person-plus me-2"></i>Nouveau compte</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=admin_medecins"><i class="bi bi-list-check me-2"></i>Candidatures</a>
                </div>
            </div>
        </section>

        <section class="stat-grid fade-up">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="stat-icon"><i class="bi bi-people"></i></span>
                    <small class="text-white-50">Comptes</small>
                </div>
                <h3 class="text-white mb-1"><?= number_format((int) $stats['usersTotal'], 0, ',', ' ') ?></h3>
                <p class="text-white-50 mb-0">Tous rôles confondus</p>
            </div>
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="stat-icon"><i class="bi bi-lightning-charge"></i></span>
                    <small class="text-white-50">Sessions actives</small>
                </div>
                <h3 class="text-white mb-1"><?= number_format((int) $stats['activeSessions'], 0, ',', ' ') ?></h3>
                <p class="text-white-50 mb-0">Dernières 2h</p>
            </div>
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="stat-icon"><i class="bi bi-inboxes"></i></span>
                    <small class="text-white-50">Tickets ouverts</small>
                </div>
                <h3 class="text-white mb-1"><?= number_format((int) $stats['pendingTickets'], 0, ',', ' ') ?></h3>
                <p class="text-white-50 mb-0">Support & conformité</p>
            </div>
            <div class="stat-card">
                <div class="d-flex justify-content-between alignments-center mb-3">
                    <span class="stat-icon"><i class="bi bi-shield-check"></i></span>
                    <small class="text-white-50">Médecins validés</small>
                </div>
                <h3 class="text-white mb-1"><?= number_format((int) $stats['doctorsVerified'], 0, ',', ' ') ?></h3>
                <p class="text-white-50 mb-0">Vérification KYC</p>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-lg-7">
                <div class="glass-panel h-100 fade-up">
                    <div class="panel-header">
                        <div>
                            <h5 class="text-white mb-1">Arrivées récentes</h5>
                            <p class="text-white-50 mb-0">Derniers comptes créés ou promus</p>
                        </div>
                        <a class="btn btn-outline-light btn-sm" href="/Project/public/index.php?page=admin_users">Gérer</a>
                    </div>
                    <?php if (empty($recentUsers)): ?>
                        <p class="text-white-50 text-center mb-0 py-4">Aucun nouveau compte pour le moment.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentUsers as $user): ?>
                                <div class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($user['name'] ?? 'Compte', ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span class="badge bg-info-subtle text-info ms-2"><?= htmlspecialchars($user['role'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8'); ?></span>
                                        <p class="small text-white-50 mb-0">Créé le <?= htmlspecialchars($user['created_at'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <span class="text-white-50 small">ID <?= htmlspecialchars($user['id'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass-panel h-100 fade-up delay-1">
                    <div class="panel-header">
                        <div>
                            <h5 class="text-white mb-1">Santé du système</h5>
                            <p class="text-white-50 mb-0">Surveillance temps réel</p>
                        </div>
                        <span class="chip badge bg-success-subtle text-success"><i class="bi bi-circle-fill small me-1"></i>Nominal</span>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="system-chip"><span>Disponibilité</span><span><?= htmlspecialchars($systemHealth['uptime'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                        <div class="system-chip"><span>Latence</span><span><?= htmlspecialchars($systemHealth['latency'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                        <div class="system-chip"><span>Base de données</span><span><?= htmlspecialchars($systemHealth['dbStatus'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                        <div class="system-chip"><span>Fenêtre sauvegarde</span><span><?= htmlspecialchars($systemHealth['backup'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-lg-6">
                <div class="glass-panel h-100 fade-up">
                    <div class="panel-header">
                        <div>
                            <h5 class="text-white mb-1">Flux d'activités</h5>
                            <p class="text-white-50 mb-0">Actions administrateur & système</p>
                        </div>
                        <button class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Actualiser</button>
                    </div>
                    <?php if (empty($activities)): ?>
                        <p class="text-white-50 text-center py-4 mb-0">Rien de nouveau dans les dernières 24 heures.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($activities as $activity): ?>
                                <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--mt-border);">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong><?= htmlspecialchars($activity['title'] ?? 'Activité', ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span class="text-white-50 small"><?= htmlspecialchars($activity['time'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <p class="text-white-50 small mb-2"><?= htmlspecialchars($activity['detail'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php if (!empty($activity['link'])): ?>
                                        <a class="btn btn-outline-light btn-sm" href="<?= htmlspecialchars($activity['link'], ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-arrow-right-circle me-1"></i>Consulter</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-panel h-100 fade-up delay-1">
                    <div class="panel-header">
                        <div>
                            <h5 class="text-white mb-1">Actions rapides</h5>
                            <p class="text-white-50 mb-0">Gestion quotidienne</p>
                        </div>
                    </div>
                    <div class="stat-grid">
                        <div class="stat-card text-white text-center">
                            <i class="bi bi-person-plus display-6 mb-2"></i>
                            <h6>Ajouter un utilisateur</h6>
                            <p class="text-white-50 small">Patients, médecins ou staff</p>
                            <a href="/Project/public/index.php?page=register" class="btn btn-outline-light btn-sm">Créer</a>
                        </div>
                        <div class="stat-card text-white text-center">
                            <i class="bi bi-gear display-6 mb-2"></i>
                            <h6>Paramètres plateforme</h6>
                            <p class="text-white-50 small">Rôles & quotas</p>
                            <a href="/Project/public/index.php?page=admin_settings" class="btn btn-outline-light btn-sm">Configurer</a>
                        </div>
                        <div class="stat-card text-white text-center">
                            <i class="bi bi-list-check display-6 mb-2"></i>
                            <h6>Import contrôlé</h6>
                            <p class="text-white-50 small">Listes homologuées</p>
                            <button class="btn btn-outline-light btn-sm" type="button">Importer</button>
                        </div>
                        <div class="stat-card text-white text-center">
                            <i class="bi bi-shield-lock display-6 mb-2"></i>
                            <h6>Audit sécurité</h6>
                            <p class="text-white-50 small">Journaux critiques</p>
                            <button class="btn btn-outline-light btn-sm" type="button">Exporter</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
