<?php
$errors = $errors ?? [];
$old = $old ?? [];
$doctors = $doctors ?? [];

$doctorOptionsHtml = '';
foreach ($doctors as $doctor) {
    $doctorId = (int)($doctor['id'] ?? 0);
    if ($doctorId <= 0) {
        continue;
    }

    $fullName = trim($doctor['full_name'] ?? 'Médecin #' . $doctorId);
    if ($fullName === '') {
        $fullName = 'Médecin #' . $doctorId;
    }

    $hasSecretary = (int)($doctor['has_secretary'] ?? 0) === 1;
    $isRecruiting = (int)($doctor['recrute_secretaire'] ?? 0) === 1;
    $statusLabel = $hasSecretary ? ' (Déjà pourvu)' : ($isRecruiting ? ' (Recherche active)' : ' (Pas de recrutement)');
    $label = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . $statusLabel;

    $doctorOptionsHtml .= '<option value="' . $doctorId . '" data-recrute="' . ($isRecruiting ? '1' : '0') . '" data-has-secretary="' . ($hasSecretary ? '1' : '0') . '">' . $label . '</option>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        body {
            min-height: 100vh;
        }
        .register-hero {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 18px;
        }
        .register-hero img {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.25);
        }
        .register-card {
            padding: clamp(24px, 3vw, 42px);
        }
        .role-btns .btn {
            border-radius: 16px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .role-btns .btn.active {
            background: linear-gradient(120deg, rgba(255, 116, 38, 0.4), rgba(255,116,38,0.15));
            border-color: rgba(255, 255, 255, 0.35);
            color: #fff;
            box-shadow: 0 10px 25px rgba(255, 116, 38, 0.25);
        }
        .info-badge {
            border-radius: 999px;
            padding: 4px 12px;
            background: rgba(255, 255, 255, 0.08);
            color: var(--mt-accent-alt);
            font-weight: 600;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
        }
        .form-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--mt-muted);
        }
        .form-control-glass.ps-5 {
            padding-left: 52px !important;
        }
        .register-divider {
            height: 1px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            margin: 24px 0;
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5">
                <section class="hero-banner register-hero fade-down">
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://images.scalebranding.com/letter-p-pulse-logo-90db443f-d910-4298-843a-8d96d9fdcb3f.jpg" alt="Logo MediTrack"/>
                        <div>
                            <p class="section-title mb-1">Onboarding</p>
                            <h2 class="display-6 fw-bold mb-0 text-white">Rejoignez MediTrack</h2>
                        </div>
                    </div>
                    <p class="text-white-50 mb-0">
                        Plateforme collaborative entre patients, médecins et secrétaires.
                        Synchronisez vos rendez-vous, dossiers et démarches administratives au même endroit.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="chip status-chip">Patients</span>
                        <span class="chip status-chip">Médecins</span>
                        <span class="chip status-chip">Secrétaires</span>
                        <span class="chip status-chip">Administrateurs</span>
                    </div>
                    <div class="register-divider"></div>
                    <div class="text-white-50 small">
                        Besoin d'aide ? <a href="mailto:support@meditrack.local" class="link-light">support@meditrack.local</a>
                    </div>
                </section>
            </div>
            <div class="col-lg-7">
                <div class="glass-card register-card fade-up delay-1">
                    <h3 class="text-white mb-3">Créer un compte</h3>
                    <p class="text-white-50 mb-4">Sélectionnez votre profil et complétez les informations.</p>
                    <form method="POST" action="/Project/public/index.php?page=register&action=register" autocomplete="off">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <div><?= htmlspecialchars($error) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4 role-btns">
                            <label class="form-label-light mb-2">Sélectionnez votre profil</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-glass w-100 role-button" data-role="patient">Patient</button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-glass w-100 role-button" data-role="medecin">Médecin</button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-glass w-100 role-button" data-role="secretaire">Secrétaire</button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-glass w-100 role-button" data-role="admin">Administrateur</button>
                                </div>
                            </div>
                            <input type="hidden" name="role" id="role" value="<?= htmlspecialchars($old['role'] ?? '') ?>" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-light">Nom</label>
                                <input type="text" name="nom" class="form-control form-control-glass" placeholder="Nom"
                                       value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-light">Prénom</label>
                                <input type="text" name="prenom" class="form-control form-control-glass" placeholder="Prénom"
                                       value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="my-3" id="champsSupp"></div>

                        <div class="row g-3">
                            <div class="col-md-6 position-relative">
                                <label class="form-label-light">Email</label>
                                <i class="bi bi-envelope form-icon"></i>
                                <input type="email" name="email" class="form-control form-control-glass ps-5" placeholder="votre.email@example.com"
                                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 position-relative">
                                <label class="form-label-light">Mot de passe</label>
                                <i class="bi bi-lock form-icon"></i>
                                <input type="password" name="motDePasse" class="form-control form-control-glass ps-5" placeholder="Mot de passe" required>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
                            <button type="submit" class="btn btn-gradient px-4">
                                <i class="bi bi-send me-2"></i>S'inscrire
                            </button>
                            <p class="text-white-50 mb-0">
                                Déjà un compte ?
                                <a class="link-light fw-semibold" href="/Project/public/index.php?page=login">Se connecter</a>
                            </p>
                        </div>
                    </form>
                    <p class="text-white-50 small mt-4 mb-0">  MediTrack - Université SESAME</p>
                </div>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script>
        const doctorOptions = <?= json_encode($doctorOptionsHtml, JSON_UNESCAPED_UNICODE); ?>;
        const oldSecretary = <?= json_encode([
            'medecin_id' => $old['medecin_id'] ?? '',
            'telephone' => $old['telephone'] ?? '',
            'experience' => $old['experience'] ?? '',
            'motivation' => $old['motivation'] ?? ''
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const oldDoctor = <?= json_encode([
            'specialite' => $old['specialite'] ?? '',
            'numero_ordre' => $old['numero_ordre'] ?? '',
            'pieces_justificatives' => $old['pieces_justificatives'] ?? ''
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const roleInput = document.getElementById('role');
            const champsSupp = document.getElementById('champsSupp');
            const roleButtons = document.querySelectorAll('.role-button');

            const renderFields = (role) => {
                if (role === 'patient') {
                    return `
                        <input type="date" name="date_naissance" class="form-control mb-2" placeholder="Date de naissance">
                        <input type="text" name="adresse" class="form-control mb-2" placeholder="Adresse">
                        <input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone">
                        <input type="text" name="assurance_maladie" class="form-control mb-2" placeholder="Assurance maladie">
                    `;
                }
                if (role === 'medecin') {
                    return `
                        <input type="text" name="specialite" class="form-control mb-2" placeholder="Spécialité" required>
                        <input type="text" name="numero_ordre" class="form-control mb-2" placeholder="Numéro d'ordre" required>
                        <textarea name="pieces_justificatives" class="form-control" rows="3" placeholder="Précisez vos accréditations / pièces justificatives (URL ou références)"></textarea>
                    `;
                }
                if (role === 'secretaire') {
                    return `
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Médecin référent</label>
                            <span class="info-badge">Nouvelle candidature</span>
                        </div>
                        <select name="medecin_id" id="secretary-doctor-select" class="form-select mb-2" required>
                            <option value="">-- Sélectionnez un médecin --</option>${doctorOptions}
                        </select>
                        <div class="form-text mb-3">Si le médecin n'est pas en recrutement ou dispose déjà d'une secrétaire, votre candidature sera automatiquement mise en attente.</div>
                        <input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone professionnel">
                        <textarea name="experience" class="form-control mb-2" rows="3" placeholder="Expérience professionnelle (facultatif)"></textarea>
                        <textarea name="motivation" class="form-control" rows="3" placeholder="Motivation (facultatif)"></textarea>
                    `;
                }
                return '';
            };

            const selectRole = (role) => {
                roleInput.value = role;
                roleButtons.forEach(btn => {
                    const isActive = btn.dataset.role === role;
                    btn.classList.toggle('btn-primary', isActive);
                    btn.classList.toggle('text-white', isActive);
                    btn.classList.toggle('btn-outline-primary', !isActive);
                });

                champsSupp.innerHTML = renderFields(role);

                if (role === 'medecin') {
                    const specInput = document.querySelector('input[name="specialite"]');
                    const orderInput = document.querySelector('input[name="numero_ordre"]');
                    const piecesInput = document.querySelector('textarea[name="pieces_justificatives"]');
                    if (specInput) specInput.value = oldDoctor.specialite || '';
                    if (orderInput) orderInput.value = oldDoctor.numero_ordre || '';
                    if (piecesInput) piecesInput.value = oldDoctor.pieces_justificatives || '';
                }
                if (role === 'secretaire') {
                    const select = document.getElementById('secretary-doctor-select');
                    if (select && oldSecretary.medecin_id) {
                        select.value = oldSecretary.medecin_id;
                    }
                    const telInput = document.querySelector('input[name="telephone"]');
                    if (telInput) {
                        telInput.value = oldSecretary.telephone || '';
                    }
                    const expField = document.querySelector('textarea[name="experience"]');
                    if (expField) {
                        expField.value = oldSecretary.experience || '';
                    }
                    const motivationField = document.querySelector('textarea[name="motivation"]');
                    if (motivationField) {
                        motivationField.value = oldSecretary.motivation || '';
                    }
                }
            };

            roleButtons.forEach(btn => {
                btn.addEventListener('click', () => selectRole(btn.dataset.role));
            });

            const initialRole = roleInput.value || 'patient';
            selectRole(initialRole);
        });
    </script>
</body>
</html>