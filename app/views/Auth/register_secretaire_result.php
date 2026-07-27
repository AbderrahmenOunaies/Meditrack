<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Candidature secrétaire - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #e4ecfa 0%, #eef2fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
        }
        .result-card {
            max-width: 520px;
            width: 100%;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 12px 40px rgba(31,38,135,0.18);
            overflow: hidden;
        }
        .result-header {
            background: linear-gradient(135deg,#256DFF 0%,#0A47B5 100%);
            color: #fff;
            padding: 32px 24px 24px 24px;
            text-align: center;
        }
        .result-header i {
            font-size: 3rem;
        }
        .result-header .status-label {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,0.2);
            font-weight: 600;
        }
        .result-body {
            padding: 28px 30px;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #3f4a6b;
        }
        .detail-item span {
            font-weight: 500;
        }
        .actions {
            padding: 20px 30px;
            background: #f6f8ff;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn-gradient {
            background: linear-gradient(135deg,#256DFF,#0A47B5);
            color: #fff;
            border: none;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg,#0A47B5,#256DFF);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="result-card">
        <div class="result-header">
            <i class="bi bi-person-workspace"></i>
            <h3 class="mt-3 mb-1">Candidature reçue</h3>
            <div class="status-label">
                <?= $status === 'pending' ? 'En validation' : ($status === 'en_attente' ? 'En attente' : 'Non retenue'); ?>
            </div>
        </div>
        <div class="result-body">
            <p class="mb-4"><?= htmlspecialchars($message); ?></p>
            <div class="detail-item">
                <span>Médecin référent :</span>
                <strong><?= htmlspecialchars($doctorName); ?></strong>
            </div>
            <div class="detail-item">
                <span>Candidat :</span>
                <strong><?= htmlspecialchars(($baseData['prenom'] ?? '') . ' ' . ($baseData['nom'] ?? '')); ?></strong>
            </div>
            <?php if (!empty($extra['telephone'])): ?>
                <div class="detail-item">
                    <span>Téléphone :</span>
                    <strong><?= htmlspecialchars($extra['telephone']); ?></strong>
                </div>
            <?php endif; ?>
            <?php if (!empty($extra['experience'])): ?>
                <div class="mt-4">
                    <h6 class="text-muted text-uppercase fw-semibold mb-2">Expérience</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($extra['experience'])); ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($extra['motivation'])): ?>
                <div class="mt-4">
                    <h6 class="text-muted text-uppercase fw-semibold mb-2">Motivation</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($extra['motivation'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <div class="actions">
            <a href="/Project/public/index.php?page=login" class="btn btn-gradient flex-grow-1">Retour à la connexion</a>
            <a href="/Project/public/index.php?page=register" class="btn btn-outline-primary flex-grow-1">Nouvelle inscription</a>
        </div>
    </div>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
</body>
</html>
