<?php
$pageTitle = 'Créer une facture - MediTrack';
$patient = $patient ?? null;
$rendezvous = $rendezvous ?? null;
$error = $_SESSION['billing_error'] ?? null;
$success = $_SESSION['billing_success'] ?? null;
$medecinSidebarActive = 'billing';
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
                    <h1 class="display-6 fw-semibold mb-1">Créer une facture</h1>
                    <p class="text-white-50 mb-0">
                        <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom'], ENT_QUOTES, 'UTF-8') ?>
                        <?= $rendezvous ? ' • ' . htmlspecialchars($rendezvous['formatted_date'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </p>
                </div>
                <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_patients">
                    <i class="bi bi-arrow-left me-2"></i>Retour aux patients
                </a>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-4 d-flex flex-column gap-3">
                <?php include __DIR__ . '/../partials/medecin_sidebar.php'; ?>
            </div>
            <div class="col-lg-8 d-flex flex-column gap-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <section class="glass-card fade-up">
                    <div class="card-header text-white d-flex align-items-center gap-2">
                        <i class="bi bi-receipt"></i> Nouvelle facture
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/Project/public/index.php?page=medecin_billing&action=create&patient_id=<?= $patient['id'] ?><?= $rendezvous ? '&rendezvous_id=' . $rendezvous['id'] : '' ?>">
                            <div class="mb-3">
                                <label class="form-label form-label-light" for="montant">Montant (€)</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-glass" id="montant" name="montant" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-light" for="echeance_jours">Délai d’échéance (jours)</label>
                                <input type="number" min="1" value="7" class="form-control form-control-glass" id="echeance_jours" name="echeance_jours">
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-light" for="details">Détails / Prestations</label>
                                <textarea class="form-control form-control-glass" id="details" name="details" rows="5" placeholder="Consultation, ordonnance, actes…"></textarea>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="bi bi-send me-2"></i>Envoyer au secrétariat pour validation
                            </button>
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
