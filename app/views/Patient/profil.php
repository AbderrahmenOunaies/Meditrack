<?php
// app/views/patient/profil.php
$title = 'Profil patient - ' . $patient['prenom'] . ' ' . $patient['nom'];
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-user me-2"></i><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/patients">Patients</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profil</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="/patients/<?= $patient['id'] ?>/modifier" class="btn btn-outline-primary me-2">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        <a href="/patients" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="avatar mx-auto" style="width: 120px; height: 120px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #6c757d; margin: 0 auto 1rem;">
                        <?= strtoupper(substr($patient['prenom'], 0, 1) . substr($patient['nom'], 0, 1)) ?>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h4>
                    <p class="text-muted mb-0">Patient #<?= $patient['id'] ?></p>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="/rendez-vous/nouveau?patient_id=<?= $patient['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Nouveau rendez-vous
                    </a>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations personnelles</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        <a href="mailto:<?= htmlspecialchars($patient['email']) ?>"><?= htmlspecialchars($patient['email']) ?></a>
                    </li>
                    <?php if (!empty($patient['telephone'])): ?>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2 text-muted"></i>
                        <a href="tel:<?= htmlspecialchars($patient['telephone']) ?>"><?= htmlspecialchars($patient['telephone']) ?></a>
                    </li>
                    <?php endif; ?>
                    <li class="mb-2">
                        <i class="fas fa-birthday-cake me-2 text-muted"></i>
                        <?= date('d/m/Y', strtotime($patient['date_naissance'])) ?>
                        (<?= (new DateTime($patient['date_naissance']))->diff(new DateTime('today'))->y ?> ans)
                    </li>
                    <?php if (!empty($patient['genre'])): ?>
                    <li class="mb-2">
                        <i class="fas fa-venus-mars me-2 text-muted"></i>
                        <?= $patient['genre'] === 'M' ? 'Masculin' : ($patient['genre'] === 'F' ? 'Féminin' : 'Autre') ?>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($patient['adresse'])): ?>
                    <li>
                        <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                        <?= nl2br(htmlspecialchars($patient['adresse'])) ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Dossier médical</h5>
                <a href="/dossiers/<?= $patient['id'] ?>/modifier" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Modifier
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($dossier) && $dossier): ?>
                    <div class="mb-4">
                        <h6>Antécédents médicaux</h6>
                        <p class="text-muted"><?= !empty($dossier['antecedents']) ? nl2br(htmlspecialchars($dossier['antecedents'])) : 'Aucun antécédent enregistré' ?></p>
                    </div>
                    <div class="mb-4">
                        <h6>Traitements en cours</h6>
                        <p class="text-muted"><?= !empty($dossier['traitements']) ? nl2br(htmlspecialchars($dossier['traitements'])) : 'Aucun traitement en cours' ?></p>
                    </div>
                    <div>
                        <h6>Allergies</h6>
                        <p class="text-muted"><?= !empty($dossier['allergies']) ? nl2br(htmlspecialchars($dossier['allergies'])) : 'Aucune allergie connue' ?></p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun dossier médical n'a encore été créé pour ce patient.</p>
                        <a href="/dossiers/nouveau?patient_id=<?= $patient['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer un dossier médical
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Derniers rendez-vous</h5>
                <a href="/rendez-vous?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-outline-primary">
                    Voir tout
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($rendezVous)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($rendezVous as $rdv): ?>
                            <a href="/rendez-vous/<?= $rdv['id'] ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($rdv['motif']) ?></h6>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($rdv['date_heure'])) ?></small>
                                </div>
                                <p class="mb-1">
                                    <i class="fas fa-user-md me-2 text-muted"></i>
                                    Dr. <?= htmlspecialchars($rdv['medecin_nom'] . ' ' . $rdv['medecin_prenom']) ?>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?= ucfirst($rdv['statut']) ?>
                                </small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun rendez-vous programmé.</p>
                        <a href="/rendez-vous/nouveau?patient_id=<?= $patient['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Prendre rendez-vous
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>