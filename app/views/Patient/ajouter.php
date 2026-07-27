<?php
// app/views/patient/ajouter.php
$title = 'Ajouter un patient';
$error = $error ?? null;
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2"></i>Nouveau patient</h1>
    <a href="/patients" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/patients/creer" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                        <div class="invalid-feedback">Veuillez entrer le prénom du patient.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                        <div class="invalid-feedback">Veuillez entrer le nom du patient.</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
                        <div class="invalid-feedback">Veuillez entrer la date de naissance.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="genre" class="form-label">Genre</label>
                        <select class="form-select" id="genre" name="genre">
                            <option value="">Sélectionner...</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                            <option value="A">Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <textarea class="form-control" id="adresse" name="adresse" rows="2"></textarea>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="/patients" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$scripts = '
<script>
    // Validation des champs du formulaire
    (function () {
        "use strict";
        
        var forms = document.querySelectorAll(".needs-validation");
        
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener("submit", function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add("was-validated");
            }, false);
        });
    })();
</script>';

include __DIR__ . '/../layouts/admin.php';
?>