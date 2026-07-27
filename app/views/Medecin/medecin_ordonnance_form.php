<?php
$doctorName = htmlspecialchars($doctor['nom_complet'] ?? $doctor['nom'] ?? 'Dr. Médecin');
$patients = $patients ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$success = $success ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle ordonnance - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        .hero-panel {
            padding: clamp(32px, 4vw, 48px);
            border-radius: var(--mt-radius-xl);
            background: var(--mt-gradient-hero);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: var(--mt-shadow-card);
            position: relative;
            overflow: hidden;
        }
        .hero-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 10% 20%, rgba(255, 116, 38, 0.35), transparent 45%),
                        radial-gradient(circle at 90% 0%, rgba(106, 216, 255, 0.3), transparent 40%);
        }
        .hero-panel > * {
            position: relative;
            z-index: 2;
        }
        .sidebar-card .nav-link {
            color: var(--mt-ink);
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-card .nav-link.active,
        .sidebar-card .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .card-animated .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .medication-block {
            border: 1px dashed rgba(255, 255, 255, 0.25);
            border-radius: var(--mt-radius-md);
            padding: 18px;
            position: relative;
            background: rgba(255, 255, 255, 0.02);
        }
        .remove-medication {
            position: absolute;
            top: 12px;
            right: 12px;
        }
        .btn-add-medication {
            border-radius: var(--mt-radius-md);
            padding: 10px 18px;
        }
        .form-section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--mt-ink);
            margin-bottom: 12px;
        }
        .tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <div class="glass-card tag mb-3"><i class="bi bi-file-earmark-plus"></i> Nouvelle ordonnance</div>
                    <h1 class="display-6 fw-semibold mb-1">Créer une ordonnance</h1>
                    <p class="text-white-50 mb-0">Dr. <?= $doctorName ?> — Espace professionnel</p>
                </div>
                <div class="text-lg-end d-flex flex-column align-items-start align-items-lg-end gap-2">
                    <span class="badge bg-light text-dark text-uppercase px-3 py-2"><i class="bi bi-calendar2-plus me-1"></i><?= date('d/m/Y') ?></span>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-arrow-left me-2"></i>Retour aux ordonnances</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1"><?= $doctorName ?></h5>
                        <p class="text-white-50 mb-3">Gestion médicale</p>
                    </div>
                    <div class="card-divider"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-check"></i> Mon agenda</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people"></i> Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical"></i> Ordonnances</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success fade-up">
                        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger fade-up">
                        <h6 class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Veuillez corriger les points suivants :</h6>
                        <ul class="mb-0 ps-4">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="/Project/public/index.php?page=medecin_ordonnances" method="POST" id="ordonnance-form" class="needs-validation fade-up" novalidate>
                    <div class="card-animated mb-4">
                        <div class="card-header d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Informations patient</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="patient_id" class="form-label form-label-light">Patient</label>
                                    <select class="form-select form-control-glass" id="patient_id" name="patient_id" required>
                                        <option value="">Sélectionnez un patient…</option>
                                        <?php foreach ($patients as $patient): ?>
                                            <?php
                                                $id = (int) ($patient['id'] ?? 0);
                                                $selected = ((int)($old['patient_id'] ?? 0) === $id) ? 'selected' : '';
                                                $label = htmlspecialchars($patient['name'] ?? 'Patient');
                                                $email = htmlspecialchars($patient['email'] ?? '');
                                            ?>
                                            <option value="<?= $id ?>" <?= $selected ?>><?= $label ?><?= $email ? ' — ' . $email : '' ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Veuillez choisir un patient suivi.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="duree_traitement" class="form-label form-label-light">Durée globale du traitement (optionnel)</label>
                                    <input type="text" class="form-control form-control-glass" id="duree_traitement" name="duree_traitement" placeholder="Ex. 3 mois" value="<?= htmlspecialchars($old['duree_traitement'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-animated mb-4">
                        <div class="card-header d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-capsule-prescription"></i>
                            <span>Médicaments prescrits</span>
                        </div>
                        <div class="card-body">
                            <p class="text-white-50 small mb-3">Indiquez pour chaque médicament la période d'utilisation, la posologie et la fréquence.</p>

                            <div id="medications-list" class="d-flex flex-column gap-3">
                                <!-- Les items médicaments seront injectés ici -->
                            </div>
                            <button type="button" class="btn btn-outline-light btn-add-medication mt-3" id="add-medication-btn">
                                <i class="bi bi-plus-circle me-2"></i>Ajouter un médicament
                            </button>
                        </div>
                    </div>

                    <div class="card-animated mb-4">
                        <div class="card-header d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-clipboard2-pulse"></i>
                            <span>Instructions générales</span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="contenu" class="form-label form-label-light">Instructions de l'ordonnance *</label>
                                <textarea class="form-control form-control-glass" id="contenu" name="contenu" rows="4" required placeholder="Décrivez les consignes générales, précautions, suivi..."><?= htmlspecialchars($old['contenu'] ?? '') ?></textarea>
                                <div class="invalid-feedback">Merci de renseigner les instructions générales.</div>
                            </div>
                            <div class="mb-3">
                                <label for="posologie" class="form-label form-label-light">Posologie générale (optionnel)</label>
                                <textarea class="form-control form-control-glass" id="posologie" name="posologie" rows="3" placeholder="Précisez la posologie globale si nécessaire."><?= htmlspecialchars($old['posologie'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/Project/public/index.php?page=medecin_ordonnances" class="btn btn-outline-glass">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-gradient">
                            <i class="bi bi-send-check me-2"></i>Enregistrer et partager
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>

    <template id="medication-template">
        <div class="medication-block">
            <button type="button" class="btn btn-sm btn-outline-danger remove-medication" title="Supprimer ce médicament"><i class="bi bi-x-lg"></i></button>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label form-label-light">Nom du médicament *</label>
                    <input type="text" class="form-control form-control-glass medication-name" name="medication_name[]" required>
                    <div class="invalid-feedback">Le nom du médicament est requis.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-light">Début d'utilisation</label>
                    <input type="date" class="form-control form-control-glass" name="medication_start[]">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-light">Fin d'utilisation</label>
                    <input type="date" class="form-control form-control-glass" name="medication_end[]">
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-light">Dosage / Posologie</label>
                    <input type="text" class="form-control form-control-glass" name="medication_dosage[]" placeholder="Ex. 500 mg">
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-light">Fréquence</label>
                    <input type="text" class="form-control form-control-glass" name="medication_frequency[]" placeholder="Ex. 2 fois par jour">
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-light">Notes (optionnel)</label>
                    <input type="text" class="form-control form-control-glass" name="medication_notes[]" placeholder="Conseils, rappel, etc.">
                </div>
            </div>
        </div>
    </template>

    <script>
        (function () {
            const medicationsList = document.getElementById('medications-list');
            const addMedicationBtn = document.getElementById('add-medication-btn');
            const template = document.getElementById('medication-template');
            const oldData = <?php echo json_encode([
                'names' => $old['medication_name'] ?? [],
                'starts' => $old['medication_start'] ?? [],
                'ends' => $old['medication_end'] ?? [],
                'dosages' => $old['medication_dosage'] ?? [],
                'frequencies' => $old['medication_frequency'] ?? [],
                'notes' => $old['medication_notes'] ?? [],
            ], JSON_UNESCAPED_UNICODE); ?>;

            function addMedicationRow(data = {}) {
                const fragment = template.content.cloneNode(true);
                const block = fragment.querySelector('.medication-block');

                fragment.querySelector('input[name="medication_name[]"]').value = data.name || '';
                fragment.querySelector('input[name="medication_start[]"]').value = data.start || '';
                fragment.querySelector('input[name="medication_end[]"]').value = data.end || '';
                fragment.querySelector('input[name="medication_dosage[]"]').value = data.dosage || '';
                fragment.querySelector('input[name="medication_frequency[]"]').value = data.frequency || '';
                fragment.querySelector('input[name="medication_notes[]"]').value = data.notes || '';

                const removeBtn = fragment.querySelector('.remove-medication');
                removeBtn.addEventListener('click', () => {
                    block.remove();
                    if (!medicationsList.children.length) {
                        addMedicationRow();
                    }
                });

                medicationsList.appendChild(fragment);
            }

            function hydrateFromOld() {
                const maxLength = Math.max(
                    oldData.names.length,
                    oldData.starts.length,
                    oldData.ends.length,
                    oldData.dosages.length,
                    oldData.frequencies.length,
                    oldData.notes.length
                );

                if (maxLength === 0) {
                    addMedicationRow();
                    return;
                }

                for (let i = 0; i < maxLength; i++) {
                    addMedicationRow({
                        name: oldData.names[i] || '',
                        start: oldData.starts[i] || '',
                        end: oldData.ends[i] || '',
                        dosage: oldData.dosages[i] || '',
                        frequency: oldData.frequencies[i] || '',
                        notes: oldData.notes[i] || '',
                    });
                }
            }

            addMedicationBtn.addEventListener('click', () => addMedicationRow());
            hydrateFromOld();

            // validation bootstrap
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
