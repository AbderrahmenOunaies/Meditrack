<?php
$pageTitle = 'Mes factures - MediTrack';
$status = $_GET['status'] ?? 'all';
$page = (int) ($_GET['p'] ?? 1);
$invoices = $invoices ?? [];
$pages = $pages ?? 1;
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
                    <h1 class="display-6 fw-semibold mb-1">Mes factures</h1>
                    <p class="text-white-50 mb-0">Consultez et payez vos factures en ligne.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_dashboard">
                        <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
                    </a>
                </div>
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
                        <span><i class="bi bi-receipt me-2"></i>Liste des factures</span>
                        <select class="form-select form-select-glass" onchange="location.href=`?status=${this.value}`">
                            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Toutes</option>
                            <option value="en_attente" <?= $status === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="paye" <?= $status === 'paye' ? 'selected' : '' ?>>Payées</option>
                            <option value="annule" <?= $status === 'annule' ? 'selected' : '' ?>>Annulées</option>
                            <option value="retard" <?= $status === 'retard' ? 'selected' : '' ?>>En retard</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($invoices)): ?>
                            <div class="table-responsive">
                                <table class="table table-theme">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Montant</th>
                                            <th>Échéance</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $inv): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($inv['date_formatted'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= number_format($inv['montant'], 2, ',', ' ') ?> €</td>
                                                <td><?= htmlspecialchars($inv['echeance_formatted'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <span class="chip <?= match($inv['statutPaiement']) {
                                                        'en_attente' => 'neutral',
                                                        'paye' => 'success',
                                                        'annule' => 'danger',
                                                        'retard' => 'warning',
                                                        default => 'neutral'
                                                    } ?>">
                                                        <?= htmlspecialchars($inv['statutPaiement'], ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="/Project/public/index.php?page=patient_billing&action=view&id=<?= $inv['id'] ?>" class="btn btn-xs btn-outline-glass">Voir</a>
                                                        <?php if ($inv['statutPaiement'] === 'en_attente'): ?>
                                                            <form method="POST" action="/Project/public/index.php?page=patient_billing&action=view&id=<?= $inv['id'] ?>" class="d-inline">
                                                                <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
                                                                <button type="submit" class="btn btn-xs btn-gradient" onclick="return confirm('Confirmer le paiement ?')">Payer</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($pages > 1): ?>
                                <nav>
                                    <ul class="pagination pagination-glass justify-content-center mt-4">
                                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?status=<?= urlencode($status) ?>&p=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-receipt text-white-50 display-6 mb-3"></i>
                                <p class="text-white-50 mb-0">Aucune facture trouvée.</p>
                            </div>
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
