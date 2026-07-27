<?php
$ordonnances = $ordonnances ?? [];
$pageTitle = 'Mes Ordonnances - MediTrack';
$patientSidebarActive = 'ordonnances';

$durationNormalizer = static function ($value): array {
    $numeric = null;
    $label = null;
    if (is_numeric($value)) {
        $numeric = (int) $value;
        $label = $numeric . ' jour' . ($numeric > 1 ? 's' : '');
    } elseif (is_string($value) && trim($value) !== '') {
        $label = trim($value);
        if (preg_match('/(\d+)/', $value, $match)) {
            $numeric = (int) $match[1];
            if (!str_contains($label, 'jour')) {
                $label .= ' jour' . ($numeric > 1 ? 's' : '');
            }
        }
    }
    return [$numeric ?? 0, $label];
};
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
        .prescription-card {
            border-radius: var(--mt-radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.02);
            padding: 22px;
            transition: transform .2s ease, border-color .2s ease;
            cursor: pointer;
        }

        .prescription-card:hover {
            transform: translateY(-4px);
            border-color: var(--mt-accent);
        }

        .modal-ordonnance .modal-content {
            border-radius: var(--mt-radius-lg);
            background: var(--mt-card);
            border: 1px solid var(--mt-border);
            color: var(--mt-ink);
            box-shadow: var(--mt-shadow-soft);
        }

        .modal-ordonnance .modal-header,
        .modal-ordonnance .modal-footer {
            border-color: var(--mt-border);
        }

        .modal-ordonnance .modal-content-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .modal-ordonnance .medicament-item {
            border-radius: var(--mt-radius-md);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px;
        }

        :root[data-theme='light'] .modal-ordonnance .medicament-item,
        body[data-theme='light'] .modal-ordonnance .medicament-item {
            background: rgba(15, 23, 42, 0.05);
            border-color: rgba(15, 23, 42, 0.12);
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                <div>
                    <p class="section-title mb-2">Mes ordonnances</p>
                    <h1 class="display-6 fw-semibold mb-1">Historique de prescriptions</h1>
                    <p class="text-white-50 mb-0">Consultez vos traitements et recommandations</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_dashboard"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_appointments"><i class="bi bi-calendar-check me-2"></i>Rendez-vous</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-xl-3 col-lg-4 d-flex flex-column gap-3">
                <?php include __DIR__ . '/../partials/patient_sidebar.php'; ?>
            </div>
            <div class="col-xl-9 col-lg-8 d-flex flex-column gap-4">
                <section class="glass-card flush fade-up">
                    <div class="card-header text-white d-flex justify-content-between align-items-center px-4 py-3">
                        <span><i class="bi bi-file-medical me-2"></i>Liste des ordonnances</span>
                        <span class="chip neutral"><?= count($ordonnances) ?> document<?= count($ordonnances) > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($ordonnances)): ?>
                            <div class="row g-3">
                        <?php foreach ($ordonnances as $ordonnance): ?>
                            <?php
                                $doctorName = $ordonnance['medecin_nom']
                                    ?? $ordonnance['medecin']
                                    ?? 'Médecin traitant';

                                $rawDate = $ordonnance['date_prescription']
                                    ?? $ordonnance['date']
                                    ?? null;
                                $timestamp = $rawDate ? strtotime($rawDate) : false;
                                $formattedDate = $timestamp ? date('d/m/Y', $timestamp) : ($rawDate ?: 'Date inconnue');

                                $contenu = $ordonnance['contenu'] ?? '';

                                [$ordonnanceDurationNumeric, $ordonnanceDurationLabel] = $durationNormalizer($ordonnance['duree_traitement'] ?? null);

                                $medicamentsData = $ordonnance['medicaments'] ?? [];
                                if (is_string($medicamentsData)) {
                                    $decoded = json_decode($medicamentsData, true);
                                    $medicamentsData = is_array($decoded) ? $decoded : [];
                                }
                                if (!is_array($medicamentsData)) {
                                    $medicamentsData = [];
                                }
                                $medicamentsData = array_values(array_filter(array_map(static function ($entry) use ($ordonnanceDurationNumeric, $ordonnanceDurationLabel, $durationNormalizer) {
                                    if (!is_array($entry)) {
                                        return null;
                                    }
                                    $nom = $entry['nom'] ?? $entry['name'] ?? $entry['medicament'] ?? 'Médicament';
                                    $posologie = $entry['posologie'] ?? $entry['dosage'] ?? ($entry['instructions'] ?? 'Non précisé');
                                    $frequence = $entry['frequence'] ?? $entry['frequency'] ?? ($entry['times_per_day'] ?? 'Non précisée');
                                    $rawDuration = $entry['duree_jours'] ?? $entry['duration'] ?? $entry['duree'] ?? $ordonnanceDurationLabel;
                                    [$dureeNumeric, $dureeLabel] = $durationNormalizer($rawDuration ?? $ordonnanceDurationLabel);
                                    $remarques = $entry['remarques'] ?? $entry['notes'] ?? null;
                                    $prochaine = $entry['prochaine_prise'] ?? $entry['next_dose'] ?? null;
                                    $start = $entry['start_date'] ?? $entry['medication_start'] ?? $entry['debut'] ?? null;
                                    $end = $entry['end_date'] ?? $entry['medication_end'] ?? $entry['fin'] ?? null;

                                    return [
                                        'nom' => $nom,
                                        'posologie' => $posologie,
                                        'frequence' => $frequence,
                                        'duree_jours' => $dureeNumeric,
                                        'duree_label' => $dureeLabel,
                                        'remarques' => $remarques,
                                        'prochaine_prise' => $prochaine,
                                        'medication_start' => $start,
                                        'medication_end' => $end,
                                    ];
                                }, $medicamentsData)));
                                $medicamentsJson = htmlspecialchars(
                                    json_encode($medicamentsData, JSON_UNESCAPED_UNICODE),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                $medicamentsCount = count($medicamentsData);
                                $contenuPreview = mb_substr($contenu, 0, 120);
                                $hasMoreContent = mb_strlen($contenu) > 120;
                            ?>
                            <div class="col-md-6">
                                <article class="prescription-card fade-up"
                                         data-bs-toggle="modal"
                                         data-bs-target="#ordonnanceModal"
                                         data-id="<?= $ordonnance['id'] ?>"
                                         data-medecin="<?= htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8'); ?>"
                                         data-date="<?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8'); ?>"
                                         data-contenu="<?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'); ?>"
                                         data-medicaments='<?= $medicamentsJson ?>'>
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="text-white-50 mb-1">Ordonnance #<?= $ordonnance['id'] ?></p>
                                            <h6 class="mb-0 text-white">Dr. <?= htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8'); ?></h6>
                                        </div>
                                        <span class="chip neutral"><?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <p class="text-white-50 small mb-3">
                                        <?= nl2br(htmlspecialchars($contenuPreview, ENT_QUOTES, 'UTF-8')) ?><?= $hasMoreContent ? '...' : '' ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="chip status-chip"><i class="bi bi-capsule me-2"></i><?= $medicamentsCount ?> médicament(s)</span>
                                        <button class="btn btn-outline-glass btn-sm">Voir détails</button>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-file-medical text-white-50 display-6 mb-3"></i>
                        <p class="text-white-50 mb-0">Aucune ordonnance enregistrée.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div class="modal fade modal-ordonnance" id="ordonnanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de l'ordonnance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <h6 class="text-white-50"><i class="bi bi-person-badge me-2"></i>Médecin prescripteur</h6>
                            <p id="modal-medecin" class="mb-0"></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-white-50"><i class="bi bi-calendar-date me-2"></i>Date de prescription</h6>
                            <p id="modal-date" class="mb-0"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h6 class="text-white-50"><i class="bi bi-journal-text me-2"></i>Instructions du médecin</h6>
                        <div id="modal-contenu" class="p-3 rounded modal-content-box"></div>
                    </div>
                    <div>
                        <h6 class="text-white-50"><i class="bi bi-capsule me-2"></i>Médicaments prescrits</h6>
                        <div id="modal-medicaments" class="d-flex flex-column gap-3"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-glass" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-gradient" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ordonnanceModal = document.getElementById('ordonnanceModal');
            ordonnanceModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const medecin = button.getAttribute('data-medecin');
                const date = button.getAttribute('data-date');
                const contenu = button.getAttribute('data-contenu');
                let medicaments = [];
                try {
                    medicaments = JSON.parse(button.getAttribute('data-medicaments')) || [];
                } catch (error) {
                    console.warn('Erreur parsing medicaments', error);
                }

                document.getElementById('modal-medecin').textContent = medecin;
                document.getElementById('modal-date').textContent = date;
                document.getElementById('modal-contenu').innerHTML = contenu.replace(/\n/g, '<br>');

                const medicamentsContainer = document.getElementById('modal-medicaments');
                medicamentsContainer.innerHTML = '';

                if (Array.isArray(medicaments) && medicaments.length) {
                    medicaments.forEach((med) => {
                        const nom = med.nom || med.name || med.medicament || 'Médicament';
                        const posologie = med.posologie || med.dosage || med.instructions || 'Non précisé';
                        const frequence = med.frequence || med.frequency || med.times_per_day || 'Non précisée';
                        const rawDuration = Number(med.duree_jours ?? med.duration ?? med.duree);
                        const hasDuration = Number.isFinite(rawDuration) && rawDuration > 0;
                        const durationLabel = hasDuration ? `${rawDuration} jour${rawDuration > 1 ? 's' : ''}` : 'Non défini';
                        const remarques = med.remarques || med.notes || '';

                        const wrapper = document.createElement('div');
                        wrapper.className = 'medicament-item';
                        wrapper.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">${nom}</h6>
                                    <small class="text-white-50">Posologie : ${posologie}</small>
                                </div>
                                <span class="chip neutral">${durationLabel}</span>
                            </div>
                            <div class="d-flex flex-column gap-1 text-white-50 small">
                                <span><i class="bi bi-clock me-2"></i>Fréquence : ${frequence}</span>
                                ${remarques ? `<span><i class="bi bi-chat-right-dots me-2"></i>Remarques : ${remarques}</span>` : ''}
                            </div>
                        `;
                        medicamentsContainer.appendChild(wrapper);
                    });
                } else {
                    medicamentsContainer.innerHTML = '<p class="text-white-50 mb-0">Aucun médicament listé.</p>';
                }
            });

            ordonnanceModal.addEventListener('hidden.bs.modal', () => {
                document.getElementById('modal-medicaments').innerHTML = '';
            });
        });
    </script>
</body>
</html>
