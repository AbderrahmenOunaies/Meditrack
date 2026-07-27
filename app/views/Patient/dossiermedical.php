<?php
// Vérifier si un patient est connecté

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrack - Dossier Médical de <?php echo htmlspecialchars($patient->prenom . ' ' . $patient->nom); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h4 class="text-white">MediTrack</h4>
            <p class="text-muted">Système de Gestion Médicale</p>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="rendezvous.php">
                    <i class="far fa-calendar-alt me-2"></i> Rendez-vous
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active bg-primary" href="dossiermedical.php">
                    <i class="fas fa-folder-medical me-2"></i> Dossier Médical
                </a>
            </li>
        </ul>
    </div>
</nav>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Top Bar -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dossier Médical</h1>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($patient->prenom . ' ' . $patient->nom); ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Patient Info Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?php echo strtoupper(substr($patient->prenom, 0, 1) . substr($patient->nom, 0, 1)); ?>
                            </div>
                            <div class="ms-4">
                                <h4 class="mb-1"><?php echo htmlspecialchars($patient->prenom . ' ' . $patient->nom); ?></h4>
                                <?php
                                $age = $patient->date_naissance ? (new DateTime($patient->date_naissance))->diff(new DateTime())->y : 'N/A';
                                $groupeSanguin = $patient->groupe_sanguin ?? 'Non spécifié';
                                ?>
                                <p class="text-muted mb-2"><?php echo $age; ?> ans • Groupe sanguin: <strong><?php echo $groupeSanguin; ?></strong></p>
                                <div class="d-flex flex-wrap gap-3">
                                    <?php if (!empty($patient->telephone)): ?>
                                        <span><i class="fas fa-phone me-1 text-primary"></i> <?php echo htmlspecialchars($patient->telephone); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($patient->email)): ?>
                                        <span><i class="fas fa-envelope me-1 text-primary"></i> <?php echo htmlspecialchars($patient->email); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($patient->adresse)): ?>
                                        <span><i class="fas fa-map-marker-alt me-1 text-primary"></i> <?php echo htmlspecialchars($patient->adresse); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="ms-auto">
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-print me-1"></i> Imprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs" id="patientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="resume-tab" data-bs-toggle="tab" data-bs-target="#resume" type="button" role="tab">
                            <i class="fas fa-home me-1"></i> Résumé
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="consultations-tab" data-bs-toggle="tab" data-bs-target="#consultations" type="button" role="tab">
                            <i class="fas fa-notes-medical me-1"></i> Consultations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions" type="button" role="tab">
                            <i class="fas fa-prescription me-1"></i> Ordonnances
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="patientTabsContent">
                    <!-- Résumé Tab -->
                    <div class="tab-pane fade show active" id="resume" role="tabpanel">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Dernières consultations -->
                                <div class="card mb-4">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Dernières consultations</h5>
                                        <a href="#" class="btn btn-sm btn-outline-primary">Voir tout</a>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($dossierMedical->consultations) && !empty($dossierMedical->consultations)): ?>
                                            <?php foreach (array_slice($dossierMedical->consultations, 0, 2) as $consultation): ?>
                                                <div class="border-bottom pb-3 mb-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($consultation['type']); ?></h6>
                                                            <p class="text-muted small mb-1"><?php echo date('d/m/Y', strtotime($consultation['date'])); ?></p>
                                                            <p class="mb-0"><?php echo htmlspecialchars($consultation['diagnostic']); ?></p>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#"><i class="far fa-eye me-2"></i>Voir les détails</a></li>
                                                                <li><a class="dropdown-item" href="#"><i class="far fa-edit me-2"></i>Modifier</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center py-4 text-muted">
                                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                                <p>Aucune consultation récente</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Antécédents médicaux -->
                                <div class="card mb-4">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Antécédents médicaux</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Allergies</h6>
                                                <?php if (isset($dossierMedical->allergies) && !empty($dossierMedical->allergies)): ?>
                                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                                        <?php foreach ($dossierMedical->allergies as $allergie): ?>
                                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                                <i class="fas fa-allergies me-1"></i>
                                                                <?php echo htmlspecialchars($allergie['libelle']); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">Aucune allergie enregistrée</p>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAllergyModal">
                                                    <i class="fas fa-plus me-1"></i> Ajouter une allergie
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Antécédents familiaux</h6>
                                                <?php if (isset($dossierMedical->antecedents) && !empty($dossierMedical->antecedents)): ?>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($dossierMedical->antecedents as $antecedent): ?>
                                                            <li class="mb-2">
                                                                <i class="fas fa-<?php echo $antecedent['type'] === 'familial' ? 'users' : 'user'; ?> 
                                                                   text-<?php echo $antecedent['type'] === 'familial' ? 'info' : 'warning'; ?> me-2"></i>
                                                                <span><?php echo htmlspecialchars($antecedent['description']); ?></span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-muted">Aucun antécédent enregistré</p>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAntecedentModal">
                                                    <i class="fas fa-plus me-1"></i> Ajouter un antécédent
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Traitements en cours -->
                                <div class="card">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Traitements en cours</h5>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTraitementModal">
                                            <i class="fas fa-plus me-1"></i> Nouveau traitement
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($dossierMedical->traitements) && !empty($dossierMedical->traitements)): ?>
                                            <?php foreach ($dossierMedical->traitements as $traitement): ?>
                                                <div class="border-bottom pb-3 mb-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($traitement['medicament']); ?></h6>
                                                            <p class="text-muted small mb-1">
                                                                <?php echo htmlspecialchars($traitement['posologie']); ?> • 
                                                                Jusqu'au <?php echo date('d/m/Y', strtotime($traitement['date_fin'])); ?>
                                                            </p>
                                                            <?php if (!empty($traitement['remarques'])): ?>
                                                                <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($traitement['remarques'])); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#"><i class="far fa-edit me-2"></i>Modifier</a></li>
                                                                <li><a class="dropdown-item text-danger" href="#"><i class="far fa-trash-alt me-2"></i>Arrêter</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center py-4 text-muted">
                                                <i class="fas fa-pills fa-2x mb-2"></i>
                                                <p>Aucun traitement en cours</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Informations vitales -->
                                <div class="card mb-4">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Informations vitales</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="vital-sign mb-4">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small text-muted">Tension artérielle</span>
                                                <span class="small text-muted">Dernière mesure: 15/11/2023</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" style="width: 70%;"></div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <span class="h5">120/80</span>
                                                <span class="text-muted ms-2">mmHg</span>
                                            </div>
                                        </div>
                                        
                                        <div class="vital-sign mb-4">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small text-muted">Poids</span>
                                                <span class="small text-muted">Dernière mesure: 15/11/2023</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-info" style="width: 60%;"></div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <span class="h5">75</span>
                                                <span class="text-muted ms-2">kg</span>
                                            </div>
                                        </div>
                                        
                                        <div class="vital-sign">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small text-muted">Glycémie à jeun</span>
                                                <span class="small text-muted">Dernière mesure: 15/11/2023</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-warning" style="width: 45%;"></div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <span class="h5">1.10</span>
                                                <span class="text-muted ms-2">g/L</span>
                                            </div>
                                        </div>
                                        
                                        <button class="btn btn-outline-primary w-100 mt-3">
                                            <i class="fas fa-plus me-1"></i> Ajouter une mesure
                                        </button>
                                    </div>
                                </div>

                                <!-- Documents récents -->
                                <div class="card">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Documents récents</h5>
                                        <a href="#" class="btn btn-sm btn-outline-primary">Voir tout</a>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($dossierMedical->documents) && !empty($dossierMedical->documents)): ?>
                                            <?php foreach (array_slice($dossierMedical->documents, 0, 3) as $document): ?>
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-2 me-3">
                                                        <i class="far fa-file-pdf text-danger fa-lg"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0 small"><?php echo htmlspecialchars($document['nom']); ?></h6>
                                                        <p class="text-muted small mb-0"><?php echo date('d/m/Y', strtotime($document['date_ajout'])); ?> • <?php echo $document['taille']; ?> Mo</p>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#"><i class="far fa-eye me-2"></i>Voir</a></li>
                                                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Télécharger</a></li>
                                                            <li><a class="dropdown-item" href="#"><i class="far fa-trash-alt me-2"></i>Supprimer</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center py-4 text-muted">
                                                <i class="far fa-folder-open fa-2x mb-2"></i>
                                                <p>Aucun document récent</p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                            <i class="fas fa-upload me-1"></i> Ajouter un document
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consultations Tab -->
                    <div class="tab-pane fade" id="consultations" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Historique des consultations</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addConsultationModal">
                                <i class="fas fa-plus me-1"></i> Nouvelle consultation
                            </button>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Médecin</th>
                                                <th>Diagnostic</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isset($dossierMedical->consultations) && !empty($dossierMedical->consultations)): ?>
                                                <?php foreach ($dossierMedical->consultations as $consultation): ?>
                                                    <tr>
                                                        <td><?php echo date('d/m/Y', strtotime($consultation['date'])); ?></td>
                                                        <td><?php echo htmlspecialchars($consultation['type']); ?></td>
                                                        <td><?php echo htmlspecialchars($consultation['medecin']); ?></td>
                                                        <td><?php echo htmlspecialchars(substr($consultation['diagnostic'], 0, 50)) . '...'; ?></td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" href="#"><i class="far fa-eye me-2"></i>Voir</a></li>
                                                                    <li><a class="dropdown-item" href="#"><i class="far fa-edit me-2"></i>Modifier</a></li>
                                                                    <li><a class="dropdown-item text-danger" href="#"><i class="far fa-trash-alt me-2"></i>Supprimer</a></li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">
                                                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                                        Aucune consultation enregistrée
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prescriptions Tab -->
                    <div class="tab-pane fade" id="prescriptions" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Ordonnances</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPrescriptionModal">
                                <i class="fas fa-plus me-1"></i> Nouvelle ordonnance
                            </button>
                        </div>
                        
                        <div class="row">
                            <?php if (isset($dossierMedical->prescriptions) && !empty($dossierMedical->prescriptions)): ?>
                                <?php foreach ($dossierMedical->prescriptions as $prescription): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Ordonnance du <?php echo date('d/m/Y', strtotime($prescription['date'])); ?></h6>
                                                <span class="badge bg-<?php echo $prescription['statut'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($prescription['statut']); ?>
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text"><strong>Médecin :</strong> <?php echo htmlspecialchars($prescription['medecin']); ?></p>
                                                <p class="card-text"><strong>Médicaments :</strong></p>
                                                <ul class="list-unstyled">
                                                    <?php foreach ($prescription['medicaments'] as $medicament): ?>
                                                        <li class="mb-2">
                                                            <i class="fas fa-pills text-primary me-2"></i>
                                                            <?php echo htmlspecialchars($medicament['nom']); ?>
                                                            <span class="text-muted small d-block ms-4"><?php echo htmlspecialchars($medicament['posologie']); ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <?php if (!empty($prescription['instructions'])): ?>
                                                    <p class="card-text"><strong>Instructions :</strong></p>
                                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($prescription['instructions'])); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer bg-white d-flex justify-content-between">
                                                <a href="#" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-print me-1"></i> Imprimer
                                                </a>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#"><i class="far fa-edit me-2"></i>Modifier</a></li>
                                                        <li><a class="dropdown-item text-danger" href="#"><i class="far fa-trash-alt me-2"></i>Supprimer</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-file-prescription fa-3x mb-3 d-block"></i>
                                        <h5>Aucune ordonnance enregistrée</h5>
                                        <p class="mb-0">Cliquez sur le bouton "Nouvelle ordonnance" pour en créer une.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/app.js"></script>
</body>
</html>