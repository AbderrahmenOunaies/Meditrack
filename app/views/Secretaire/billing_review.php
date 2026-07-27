<?php
$pageTitle = 'Revoir la facture - MediTrack';
$invoice = $invoice ?? null;
$error = $_SESSION['billing_error'] ?? null;
$success = $_SESSION['billing_success'] ?? null;
$secretaireSidebarActive = 'billing';
unset($_SESSION['billing_error'], $_SESSION['billing_success']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                <div>
                    <p class="section-title mb-2">Facturation</p>
                    <h1 class="display-6 fw-semibold mb-1">Revoir la facture</h1>
                    <p class="text-white-50 mb-0">
                        <?= htmlspecialchars($invoice['prenom'] . ' ' . $invoice['nom'], ENT_QUOTES, 'UTF-8') ?> •
                        <?= htmlspecialchars($invoice['date_formatted'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
                <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_billing">
                    <i class="bi bi-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <?php include __DIR__ . '/../partials/secretaire_sidebar.php'; ?>
            </div>
            <div class="col-lg-9 d-flex flex-column gap-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <section class="glass-card fade-up">
                    <div class="card-header text-white d-flex align-items-center gap-2">
                        <i class="bi bi-receipt"></i> Facture #<?= $invoice['id'] ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/Project/public/index.php?page=secretaire_billing&action=review&id=<?= $invoice['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label form-label-light" for="montant">Montant (€)</label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-glass" id="montant" name="montant" value="<?= htmlspecialchars($invoice['montant'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-light" for="echeance_jours">Délai d’échéance (jours)</label>
                                    <input type="number" min="1" class="form-control form-control-glass" id="echeance_jours" name="echeance_jours" value="7">
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-light" for="details">Détails / Prestations</label>
                                    <textarea class="form-control form-control-glass" id="details" name="details" rows="6"><?= htmlspecialchars($invoice['details'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" name="action" value="issue" class="btn btn-gradient">
                                    <i class="bi bi-send me-2"></i>Émettre la facture
                                </button>
                                <button type="submit" name="action" value="cancel" class="btn btn-outline-danger" onclick="return confirm('Annuler cette facture ?')">
                                    <i class="bi bi-x-circle me-2"></i>Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
