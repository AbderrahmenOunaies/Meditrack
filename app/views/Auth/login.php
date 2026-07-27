<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrack - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        .login-shell {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: clamp(16px, 4vw, 48px);
        }
        .card-login {
            width: min(480px, 100%);
            border-radius: var(--mt-radius-xl);
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(5, 11, 24, 0.85);
            box-shadow: var(--mt-shadow-card);
            overflow: hidden;
            animation: fadeDown 0.7s ease forwards;
        }
        .card-login .brand-panel {
            padding: 42px 28px;
            text-align: center;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.04));
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .card-login .brand-panel img {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            margin-bottom: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        }
        .card-login form {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .input-icon-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: var(--mt-radius-md);
            padding: 0.85rem 1rem;
        }
        .input-icon-wrapper i {
            color: var(--mt-muted);
            font-size: 1.1rem;
        }
        .input-icon-wrapper .form-control-glass {
            flex: 1;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
            color: var(--mt-ink);
        }
        .input-icon-wrapper .form-control-glass:focus {
            box-shadow: none;
            background: transparent;
        }
        .cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body class="meditrack-surface">
<main class="login-shell">
    <section class="card-login">
        <div class="brand-panel fade-down">
            <img src="https://images.scalebranding.com/letter-p-pulse-logo-90db443f-d910-4298-843a-8d96d9fdcb3f.jpg" alt="MediTrack">
            <p class="text-uppercase small mb-1" style="letter-spacing:0.4em;color:var(--mt-muted);">Connexion</p>
            <h2 class="fw-bold mb-1 text-white">MediTrack</h2>
            <p class="text-white-50 mb-0">Système de Gestion Médicale</p>
        </div>
        <form action="/Project/public/index.php?page=login" method="POST" class="fade-up delay-1">
            <input type="hidden" name="role" value="patient">
            <div>
                <label for="email" class="form-label-light">Email</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-envelope"></i>
                    <input type="email" class="form-control form-control-glass" id="email" name="email" placeholder="votre.email@example.com" required>
                </div>
            </div>
            <div>
                <label for="password" class="form-label-light">Mot de passe</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock"></i>
                    <input type="password" class="form-control form-control-glass" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="form-check text-white-50">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                <a href="#" class="link-light small">Mot de passe oublié ?</a>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-gradient w-100 mt-2">Se connecter</button>
            <div class="text-center text-white-50 small">
                Pas encore de compte ?
                <a href="/Project/public/index.php?page=register" class="link-light fw-semibold">S'inscrire</a>
            </div>
        </form>
        <div class="text-center text-white-50 py-3 small border-top border-light-subtle">
            © <?= date('Y'); ?> MediTrack - Université SESAME
        </div>
    </section>
</main>
<?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
