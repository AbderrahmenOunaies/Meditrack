<?php
$pageTitle = 'Détails de la facture - MediTrack';
$invoice = $invoice ?? null;
$error = $_SESSION['billing_error'] ?? null;
$success = $_SESSION['billing_success'] ?? null;
$patientSidebarActive = 'billing';
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
                    <h1 class="display-6 fw-semibold mb-1">Détails de la facture</h1>
                    <p class="text-white-50 mb-0">
                        Facture #<?= $invoice['id'] ?> •
                        <?= htmlspecialchars($invoice['date_formatted'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
                <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_billing">
                    <i class="bi bi-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <?php include __DIR__ . '/../partials/patient_sidebar.php'; ?>
            </div>
            <div class="col-lg-9 d-flex flex-column gap-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <section class="glass-card fade-up">
                    <div class="card-header text-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-receipt me-2"></i>Facture #<?= $invoice['id'] ?></span>
                        <span class="chip <?= match($invoice['statutPaiement']) {
                            'en_attente' => 'neutral',
                            'paye' => 'success',
                            'annule' => 'danger',
                            'retard' => 'warning',
                            default => 'neutral'
                        } ?>">
                            <?= htmlspecialchars($invoice['statutPaiement'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <h6 class="text-white-50">Date d’émission</h6>
                                <p class="mb-0"><?= htmlspecialchars($invoice['date_formatted'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-white-50">Date d’échéance</h6>
                                <p class="mb-0"><?= htmlspecialchars($invoice['echeance_formatted'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-white-50 mb-2">Détails / Prestations</h6>
                            <div class="p-3 rounded bg-dark-subtle">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($invoice['details'] ?? '—', ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-white mb-0">Total à payer</h5>
                                <p class="text-white-50 mb-0">TVA incluse (non applicable)</p>
                            </div>
                            <div class="text-end">
                                <h4 class="text-white mb-0"><?= number_format($invoice['montant'], 2, ',', ' ') ?> €</h4>
                                <?php if ($invoice['mode_paiement']): ?>
                                    <p class="text-white-50 mb-0">Payé par : <?= htmlspecialchars($invoice['mode_paiement'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($invoice['statutPaiement'] === 'en_attente'): ?>
                            <hr class="my-4">
                            <form method="POST" action="/Project/public/index.php?page=patient_billing&action=pay" id="payment-form">
                                <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label form-label-light d-block">Mode de paiement</label>
                                    <div class="btn-group" role="group" aria-label="Choisir le mode de paiement">
                                        <input type="radio" class="btn-check" name="mode_paiement" id="pay-card" value="carte" autocomplete="off" checked>
                                        <label class="btn btn-outline-light" for="pay-card"><i class="bi bi-credit-card me-1"></i>Carte bancaire</label>

                                        <input type="radio" class="btn-check" name="mode_paiement" id="pay-transfer" value="virement" autocomplete="off">
                                        <label class="btn btn-outline-light" for="pay-transfer"><i class="bi bi-bank me-1"></i>Virement bancaire</label>
                                    </div>
                                </div>

                                <div class="payment-panel card-panel" data-payment-panel="card">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label form-label-light" for="card-number">Numéro de carte</label>
                                            <input type="text" id="card-number" class="form-control form-control-glass" placeholder="•••• •••• •••• ••••" maxlength="19">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label form-label-light" for="card-expiry">Expiration</label>
                                            <input type="text" id="card-expiry" class="form-control form-control-glass" placeholder="MM/AA" maxlength="5">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label form-label-light" for="card-cvv">CVV</label>
                                            <input type="text" id="card-cvv" class="form-control form-control-glass" placeholder="123" maxlength="4">
                                        </div>
                                    </div>
                                    <small class="text-white-50 d-block mt-2">Les paiements sont sécurisés et chiffrés.</small>
                                </div>

                                <div class="payment-panel card-panel d-none" data-payment-panel="transfer">
                                    <h6 class="text-white-50">Coordonnées bancaires</h6>
                                    <ul class="text-white list-unstyled mb-3">
                                        <li><strong>IBAN :</strong> FR76 3000 6000 1000 1234 5678 901</li>
                                        <li><strong>BIC :</strong> BNPAFRPP</li>
                                        <li><strong>Banque :</strong> Banque MediTrack</li>
                                    </ul>
                                    <label class="form-label form-label-light" for="transfer-reference">Référence de virement</label>
                                    <input type="text" id="transfer-reference" class="form-control form-control-glass" name="reference_virement" placeholder="Ex : FACT-<?= $invoice['id'] ?>">
                                    <small class="text-white-50 d-block mt-2">Merci d'indiquer la référence lors du transfert.</small>
                                </div>

                                <button type="submit" class="btn btn-gradient w-100 mt-4" onclick="return confirm('Confirmer le paiement ?')">
                                    <i class="bi bi-credit-card me-2"></i>Confirmer le paiement
                                </button>
                            </form>
                            <script>
                                (function () {
                                    const form = document.getElementById('payment-form');
                                    if (!form) return;
                                    const panels = document.querySelectorAll('.payment-panel');
                                    const togglePanels = () => {
                                        const selected = form.querySelector('input[name="mode_paiement"]:checked')?.value || 'carte';
                                        panels.forEach((panel) => {
                                            const type = panel.getAttribute('data-payment-panel');
                                            panel.classList.toggle('d-none', type !== selected && !(selected === 'carte' && type === 'card'));
                                        });
                                    };
                                    form.querySelectorAll('input[name="mode_paiement"]').forEach((input) => {
                                        input.addEventListener('change', togglePanels);
                                    });
                                    togglePanels();
                                })();
                            </script>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
