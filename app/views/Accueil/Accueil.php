<?php
$doctor = $doctor ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative text-white fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">MediTrack</p>
                    <h1 class="display-5 fw-semibold mb-2">Bienvenue sur MediTrack</h1>
                    <p class="text-white-50 mb-0">Une plateforme unifiée pour médecins, secrétaires et patients</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Espace Médecin</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=secretaire_dashboard"><i class="bi bi-briefcase me-2"></i>Espace Secrétaire</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_dashboard"><i class="bi bi-person-heart me-2"></i>Espace Patient</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="glass-card fade-up h-100">
                    <div class="card-divider mb-3"></div>
                    <h5 class="text-white mb-2"><i class="bi bi-activity me-2"></i>Suivi médical centralisé</h5>
                    <p class="text-white-50 mb-0">Accédez rapidement aux dossiers patients, ordonnances, rendez-vous et notifications dans une interface moderne.</p>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="glass-card fade-up h-100">
                    <div class="card-divider mb-3"></div>
                    <h5 class="text-white mb-2"><i class="bi bi-people me-2"></i>Collaboration d'équipe</h5>
                    <p class="text-white-50 mb-0">Les médecins et secrétaires travaillent ensemble grâce à des outils de communication et de planification intégrés.</p>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="glass-card fade-up h-100">
                    <div class="card-divider mb-3"></div>
                    <h5 class="text-white mb-2"><i class="bi bi-shield-check me-2"></i>Sécurité & accessibilité</h5>
                    <p class="text-white-50 mb-0">Authentification sécurisée, thèmes clair/sombre, et expérience responsive pour tout appareil.</p>
                </div>
            </div>
        </div>

        <section class="glass-card fade-up">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <h3 class="text-white mb-3">Une solution complète pour votre établissement</h3>
                    <ul class="text-white-50 list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Gestion des agendas et disponibilités</li>
                        <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Dossiers patients enrichis et ordonnances</li>
                        <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Notifications en temps réel</li>
                        <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Suivi des équipes et du staff</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="card-animated">
                        <div class="card-header text-white"><i class="bi bi-box-arrow-in-right me-2"></i>Commencer</div>
                        <div class="card-body">
                            <p class="text-white-50">Connectez-vous pour accéder à votre espace personnalisé.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="/Project/public/index.php?page=login" class="btn btn-gradient"><i class="bi bi-box-arrow-in-right me-2"></i>Se connecter</a>
                                <a href="/Project/public/index.php?page=register" class="btn btn-outline-glass"><i class="bi bi-person-plus me-2"></i>Créer un compte</a>
                            </div>
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
