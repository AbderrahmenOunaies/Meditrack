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
    <style>
        body {
            background: linear-gradient(135deg, #e4ecfa 0%, #eef2fa 100%);
            min-height: 100vh;
        }
        .register-container {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,.15);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg,#256DFF 0%,#0A47B5 100%);
            color: #fff;
            padding: 32px 0 16px 0;
            text-align: center;
        }
        .register-header img {
            width: 52px;
            margin-bottom: 8px;
        }
        .role-btns .btn {
            width: 48%;
            margin: 1%;
            font-weight: 500;
            font-size: 1rem;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #256DFF;
        }
        .register-btn {
            background: linear-gradient(135deg,#256DFF,#256DFF 70%,#0A47B5 100%);
            color: #fff;
            font-weight: 500;
            font-size: 1.1rem;
        }
        .register-btn:hover {
            background: linear-gradient(135deg,#0A47B5,#256DFF 70%,#256DFF 100%);
            color: #fff;
        }
        .nav-link {
            color: #256DFF !important;
            text-decoration: underline;
        }
        .form-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <img src="https://images.scalebranding.com/letter-p-pulse-logo-90db443f-d910-4298-843a-8d96d9fdcb3f.jpg" alt="Logo MediTrack"/>
            <h2 class="mb-0">MediTrack</h2>
            <small>Système de Gestion Médicale</small>
        </div>
        <form class="p-4" method="POST" action="?action=register" autocomplete="off">
            <!-- Affichage des erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Sélection du rôle utilisateur -->
            <div class="mb-3 text-center role-btns">
                <label class="form-label">Sélectionnez votre profil</label>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="selectRole('patient')" id="btn-patient">Patient</button>
                    <button type="button" class="btn btn-outline-primary" onclick="selectRole('medecin')" id="btn-medecin">Médecin</button>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="selectRole('secretaire')" id="btn-secretaire">Secrétaire</button>
                    <button type="button" class="btn btn-outline-primary" onclick="selectRole('admin')" id="btn-admin">Administrateur</button>
                </div>
                <input type="hidden" name="role" id="role" value="<?= htmlspecialchars($old['role'] ?? '') ?>" required>
            </div>
            <!-- Nom -->
            <div class="mb-3">
                <input type="text" name="nom" class="form-control" placeholder="Nom"
                       value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required>
            </div>
            <!-- Prénom -->
            <div class="mb-3">
                <input type="text" name="prenom" class="form-control" placeholder="Prénom"
                       value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" required>
            </div>
            <!-- Champs supplémentaires dynamiques -->
            <div class="mb-3" id="champsSupp"></div>
            <!-- Email -->
            <div class="mb-3 position-relative">
                <span class="form-icon"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control ps-5" placeholder="votre.email@example.com"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>
            <!-- Mot de passe -->
            <div class="mb-3 position-relative">
                <span class="form-icon"><i class="bi bi-lock"></i></span>
                <input type="password" name="motDePasse" class="form-control ps-5" placeholder="Mot de passe" required>
            </div>
            <div class="d-grid mb-2">
                <button type="submit" class="btn register-btn">S'inscrire</button>
            </div>
            <div class="text-center">
                <span>Déjà un compte ? <a class="nav-link d-inline" href="login.php">Se connecter</a></span>
            </div>
        </form>
        <div class="bg-light text-center py-1" style="font-size: 0.9em;">
            © 2025 MediTrack - Université SESAME
        </div>
    </div>

    <script>
        // Rôles : highlight + champs supplémentaires
        function selectRole(role) {
            document.getElementById('role').value = role;
            ['patient','medecin','secretaire','admin'].forEach(function(r) {
                document.getElementById('btn-' + r).classList.remove('btn-primary');
            });
            document.getElementById('btn-' + role).classList.add('btn-primary');

            let html = "";
            if (role === "patient") {
                html += '<input type="date" name="date_naissance" class="form-control mb-2" placeholder="Date de naissance">';
                html += '<input type="text" name="adresse" class="form-control mb-2" placeholder="Adresse">';
                html += '<input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone">';
                html += '<input type="text" name="assurance_maladie" class="form-control mb-2" placeholder="Assurance maladie">';
            }
            if (role === "medecin") {
                html += '<input type="text" name="specialite" class="form-control mb-2" placeholder="Spécialité">';
                html += '<input type="text" name="numero_ordre" class="form-control mb-2" placeholder="Numéro Ordre">';
            }
            document.getElementById("champsSupp").innerHTML = html;
        }

        // Sélection automatique du bon rôle après un post-back/erreur
        <?php if (!empty($old['role'])): ?>
            selectRole('<?= htmlspecialchars($old['role']) ?>');
        <?php endif; ?>
    </script>
</body>
</html>