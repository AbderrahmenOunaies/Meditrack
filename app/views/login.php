<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrack - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            background: linear-gradient(135deg, #e8f0ff, #ffffff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-card{
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 460px;
        }
        .login-header{
            background: linear-gradient(100deg, #1B5CFC, #3c4df9, #4b40f8);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .login-header img{
            width: 60px;
            margin-bottom: 10px;
            border-radius: 15px;
        }
    </style>
</head>
<body>
<div class="login-card bg-white">
    <div class="login-header">
        <img src="https://images.scalebranding.com/letter-p-pulse-logo-90db443f-d910-4298-843a-8d96d9fdcb3f.jpg" alt="logo">
        <h2 class="fw-bold">MediTrack</h2>
        <p>Système de Gestion Médicale</p>
    </div>
    <div class="p-4">
        <form action="login.php" method="POST">
            <input type="hidden" name="role" value="patient">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="votre.email@example.com" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="remember">
                <label class="form-check-label" for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Se connecter</button>
        </form>
        <div class="text-center mt-3">
            <a href="#" class="text-decoration-none">Mot de passe oublié?</a>
        </div>
        <div class="text-center mt-3">Pas encore de compte? <a href="register.php" class="text-decoration-none">S'inscrire</a></div>
    </div>
    <div class="text-center py-3 text-secondary" style="font-size: 0.9rem;">
        © 2025 MediTrack - Université SESAME
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>
