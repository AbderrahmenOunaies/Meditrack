<?php
/** @var string $message */
/** @var string $reference */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Candidature Médecin – MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, rgba(15,32,75,.75), rgba(2,9,27,1));
            font-family: "Poppins", "Segoe UI", sans-serif;
            color: #fff;
            padding: 20px;
        }
        .card-result {
            max-width: 520px;
            background: rgba(255,255,255,0.05);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .reference-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            font-weight: 600;
            letter-spacing: 0.08em;
        }
    </style>
</head>
<body>
<div class="card-result text-center">
    <div class="mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/2920/2920256.png" alt="Doctor" width="80" height="80">
    </div>
    <h2 class="fw-bold mb-3">Candidature envoyée</h2>
    <p class="text-white-50 mb-4"><?= htmlspecialchars($message ?? "Votre dossier a été reçu.", ENT_QUOTES, 'UTF-8'); ?></p>
    <div class="reference-pill mb-4">
        <span>Référence dossier</span>
        <span><?= htmlspecialchars($reference ?? 'DOC-00000', ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <p class="small text-white-50 mb-4">
        Vous recevrez un email lorsque l'équipe d'administration aura validé vos pièces justificatives et accréditations.
    </p>
    <a href="/Project/public/index.php?page=login" class="btn btn-light text-dark w-100">Retour à la connexion</a>
</div>
<?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
</body>
</html>
