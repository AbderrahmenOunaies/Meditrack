<?php
// app/views/patient/liste.php
$title = 'Gestion des patients';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>Patients</h1>
    <a href="/patients/ajouter" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau patient
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="patientsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date d'inscription</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td><?= $patient['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3" style="width: 40px; height: 40px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #6c757d;">
                                    <?= strtoupper(substr($patient['prenom'], 0, 1) . substr($patient['nom'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></div>
                                    <small class="text-muted"><?= $patient['date_naissance'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($patient['email']) ?></td>
                        <td><?= htmlspecialchars($patient['telephone'] ?? 'N/A') ?></td>
                        <td><?= date('d/m/Y', strtotime($patient['created_at'])) ?></td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="/patients/<?= $patient['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/patients/<?= $patient['id'] ?>/modifier" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $patient['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <!-- Modal de confirmation de suppression -->
                            <div class="modal fade" id="deleteModal<?= $patient['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmer la suppression</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Êtes-vous sûr de vouloir supprimer le patient <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?> ?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <form action="/patients/<?= $patient['id'] ?>/supprimer" method="POST" class="d-inline">
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$scripts = '
<script>
    $(document).ready(function() {
        $("#patientsTable").DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json"
            },
            order: [[1, "asc"]],
            responsive: true
        });
    });
</script>';

include __DIR__ . '/../layouts/admin.php';
?>