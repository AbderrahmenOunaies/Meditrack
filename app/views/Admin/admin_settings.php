<?php
$defaultPlatformSettings = [
    'maintenance' => false,
    'maxSessions' => 2500,
    'backupWindow' => '02:00 UTC',
    'emailSender' => 'noreply@meditrack.local',
];
$platformSettings = isset($platformSettings) && is_array($platformSettings)
    ? array_merge($defaultPlatformSettings, $platformSettings)
    : $defaultPlatformSettings;
$securityRules = isset($securityRules) && is_array($securityRules) ? $securityRules : [
    ['name' => 'Authentification forte', 'status' => 'Actif', 'description' => '2FA obligatoire pour les admins.'],
    ['name' => 'Verrouillage auto', 'status' => 'Actif', 'description' => '10 tentatives échouées provoquent un blocage de 15 min.'],
    ['name' => 'Audit hebdo', 'status' => 'Planifié', 'description' => 'Rapport exporté chaque lundi.'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres plateforme - MediTrack</title>
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
        .panel {
            border-radius: var(--mt-radius-lg);
            background: var(--mt-card);
            border: 1px solid var(--mt-border);
            box-shadow: var(--mt-shadow-soft);
            padding: 24px;
        }
        .chip {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="meditrack-surface">
    <header class="admin-topbar glass-card fade-down container-lg mt-4">
        <div class="d-flex flex-wrap alignments-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 text-white">
                <i class="bi bi-gear fs-4"></i>
                <div>
                    <strong>Paramètres plateforme</strong>
                    <div class="small text-white-50">Règles globales & sécurité</div>
                </div>
            </div>
            <nav class="nav gap-1">
                <a class="nav-link" href="/Project/public/index.php?page=admin_dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_users"><i class="bi bi-people me-1"></i>Utilisateurs</a>
                <a class="nav-link" href="/Project/public/index.php?page=admin_medecins"><i class="bi bi-file-medical me-1"></i>Candidatures</a>
                <a class="nav-link active" href="/Project/public/index.php?page=admin_settings"><i class="bi bi-gear me-1"></i>Paramètres</a>
            </nav>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-light" type="button"><i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser</button>
                <button class="btn btn-gradient" type="button"><i class="bi bi-floppy me-1"></i>Enregistrer</button>
            </div>
        </div>
    </header>

    <main class="container-lg my-4 medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative text-white fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">MediTrack</p>
                    <h1 class="display-6 fw-semibold mb-1">Paramètres plateforme</h1>
                    <p class="text-white-50 mb-0">Pilotez les règles de sécurité, quotas et intégrations globales.</p>
                </div>
                <span class="badge bg-info-subtle text-info"><i class="bi bi-shield-check me-1"></i>Système nominal</span>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-lg-7">
                <div class="panel fade-up">
                    <p class="section-title mb-3">Configuration générale</p>
                    <form class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label form-label-light">Mode maintenance</label>
                            <select class="form-select form-control-glass" name="maintenance">
                                <option value="0" <?= !$platformSettings['maintenance'] ? 'selected' : ''; ?>>Désactivé</option>
                                <option value="1" <?= $platformSettings['maintenance'] ? 'selected' : ''; ?>>Actif</option>
                            </select>
                            <small class="text-white-50">Affiche une page d'arrêt planifié et bloque les connexions.</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-light">Sessions simultanées max</label>
                                <input type="number" min="1" class="form-control form-control-glass" name="max_sessions" value="<?= (int) $platformSettings['maxSessions']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-light">Fenêtre de sauvegarde</label>
                                <input type="text" class="form-control form-control-glass" name="backup_window" value="<?= htmlspecialchars($platformSettings['backupWindow'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-light">Email expéditeur</label>
                                <input type="email" class="form-control form-control-glass" name="email_sender" value="<?= htmlspecialchars($platformSettings['emailSender'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-light">Fuseau par défaut</label>
                                <select class="form-select form-control-glass" name="timezone">
                                    <option value="UTC">UTC</option>
                                    <option value="Europe/Paris">Europe/Paris</option>
                                    <option value="Africa/Casablanca">Africa/Casablanca</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-outline-light" type="reset">Réinitialiser</button>
                            <button class="btn btn-gradient" type="submit">Sauvegarder</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel h-100 fade-up delay-1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="section-title mb-0">Sécurité</p>
                        <span class="chip bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>Synchronisé</span>
                    </div>
                    <?php if (empty($securityRules)): ?>
                        <p class="text-white-50">Aucune règle configurée.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($securityRules as $rule): ?>
                                <div class="list-group-item bg-transparent text-white-50 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong class="text-white d-block"><?= htmlspecialchars($rule['name'] ?? 'Règle', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <p class="mb-0 small text-white-50"><?= htmlspecialchars($rule['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <span class="chip bg-dark text-white small"><?= htmlspecialchars($rule['status'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <hr class="border-light-subtle">
                    <p class="section-title mb-2">Alertes critiques</p>
                    <div class="d-flex flex-column gap-3">
                        <div class="chip bg-danger-subtle text-danger"><i class="bi bi-shield-exclamation me-2"></i>Rotation des clés API sous 48h</div>
                        <div class="chip bg-info-subtle text-info"><i class="bi bi-hdd-network me-2"></i>Mises à jour serveurs 12/02</div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
