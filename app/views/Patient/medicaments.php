<?php
$medicaments = $medicaments ?? [];
$pageTitle = 'Mes Médicaments - MediTrack';
$patientSidebarActive = 'medicaments';
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
    <style>
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Mes médicaments</p>
                    <h1 class="display-6 fw-semibold mb-1">Suivi de traitements actifs</h1>
                    <p class="text-white-50 mb-0">Consultez vos posologies et prochaines prises</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_dashboard"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_ordonnances"><i class="bi bi-file-medical me-2"></i>Ordonnances</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3 d-flex flex-column gap-3">
                <?php include __DIR__ . '/../partials/patient_sidebar.php'; ?>
            </div>
            <div class="col-lg-9 d-flex flex-column gap-4">
                <section class="glass-card fade-up">
                    <div class="card-header text-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-capsule me-2"></i>Liste des traitements</span>
                        <span class="chip neutral"><?= count($medicaments) ?> traitement<?= count($medicaments) > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($medicaments)): ?>
                            <?php foreach ($medicaments as $medicament): ?>
                        <?php
                            $ordonnanceId = $medicament['ordonnance_id'] ?? '—';
                            $nomMedicament = $medicament['nom'] ?? 'Traitement';

                            $rawDate = $medicament['date_prescription'] ?? null;
                            $timestamp = $rawDate ? strtotime($rawDate) : false;
                            $datePrescrite = $timestamp ? date('d/m/Y', $timestamp) : ($rawDate ?: 'Date inconnue');

                            $medecin = $medicament['medecin_nom'] ?? 'Médecin traitant';
                            $posologie = $medicament['posologie'] ?? 'Non précisé';
                            $frequence = $medicament['frequence'] ?? 'Non précisée';
                            $duree = isset($medicament['duree_jours']) ? (int) $medicament['duree_jours'] : 0;
                            $prochainePrise = $medicament['prochaine_prise'] ?? 'À déterminer';
                            $status = $medicament['status'] ?? 'À vérifier';
                            $remarques = $medicament['remarques'] ?? null;
                        ?>
                        <article class="medication-card fade-up">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                                <div>
                                    <p class="text-white-50 mb-1">Ordonnance #<?= htmlspecialchars($ordonnanceId, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <h5 class="mb-0 text-white"><?= htmlspecialchars($nomMedicament, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <small class="text-white-50">
                                        Prescrit le <?= htmlspecialchars($datePrescrite, ENT_QUOTES, 'UTF-8'); ?>
                                        • Dr. <?= htmlspecialchars($medecin, ENT_QUOTES, 'UTF-8'); ?>
                                    </small>
                                </div>
                                <span class="chip <?= strtolower($status) === 'en cours' ? 'success' : 'neutral' ?>">
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-block">
                                        <h6 class="text-white mb-1">Posologie</h6>
                                        <p class="text-white-50 small mb-0"><?= nl2br(htmlspecialchars($posologie, ENT_QUOTES, 'UTF-8')); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-block mb-3">
                                        <h6 class="text-white mb-1">Fréquence</h6>
                                        <p class="text-white-50 small mb-0"><?= htmlspecialchars($frequence, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <div class="info-block mb-0">
                                        <h6 class="text-white mb-1">Durée du traitement</h6>
                                        <p class="text-white-50 small mb-1">
                                            <?php
                                                $durationLabel = $medicament['duree_label'] ?? null;
                                                if ($durationLabel) {
                                                    echo htmlspecialchars($durationLabel, ENT_QUOTES, 'UTF-8');
                                                } elseif ($duree > 0) {
                                                    echo $duree . ' jour' . ($duree > 1 ? 's' : '');
                                                } else {
                                                    echo 'Non définie';
                                                }
                                            ?>
                                        </p>
                                        <?php if (!empty($medicament['medication_start'] ?? null) || !empty($medicament['medication_end'] ?? null)): ?>
                                            <small class="text-white-50 d-block">
                                                Du <?= htmlspecialchars($medicament['medication_start'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                                au <?= htmlspecialchars($medicament['medication_end'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($remarques)): ?>
                                <div class="mt-3 p-3 rounded bg-dark-subtle">
                                    <h6 class="text-white-50 mb-1"><i class="bi bi-info-circle me-2"></i>Remarques</h6>
                                    <p class="text-white-50 small mb-0"><?= nl2br(htmlspecialchars($remarques, ENT_QUOTES, 'UTF-8')); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center mt-3 text-white-50 small">
                                <span><i class="bi bi-calendar-check me-2"></i>Prochaine prise : <?= htmlspecialchars($prochainePrise, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span><i class="bi bi-bandaid me-2"></i>Ordonnance #<?= htmlspecialchars($ordonnanceId, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-capsule text-white-50 display-6 mb-3"></i>
                        <p class="text-white-50 mb-0">Aucun médicament prescrit.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
