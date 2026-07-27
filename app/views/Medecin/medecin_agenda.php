<?php
$doctorName = htmlspecialchars($doctor['nom_complet'] ?? $doctor['nom'] ?? 'Dr. Médecin');
$specialite = htmlspecialchars($doctor['specialite'] ?? 'Spécialité non renseignée');
$selectedDate = htmlspecialchars($selectedDate ?? date('Y-m-d'));
$agendaSlots = $agendaSlots ?? [];
$upcomingAppointments = $upcomingAppointments ?? [];
$pendingRequests = $pendingRequests ?? [];
$paymentsSummary = $paymentsSummary ?? [
    'month_label' => date('m/Y'),
    'paid_total' => 0,
    'pending_total' => 0,
    'paid_count' => 0,
    'pending_count' => 0,
];
$paymentHistory = $paymentHistory ?? [];
$paymentSuccess = $paymentSuccess ?? null;
$paymentError = $paymentError ?? null;
$paymentRedirect = $paymentRedirect ?? '/Project/public/index.php?page=medecin_agenda';
$paymentStatusMap = [
    'paye' => ['label' => 'Payée', 'class' => 'bg-success text-white'],
    'en_attente' => ['label' => 'En attente', 'class' => 'bg-warning text-dark'],
    'retard' => ['label' => 'En retard', 'class' => 'bg-danger text-white'],
    'annule' => ['label' => 'Annulée', 'class' => 'bg-secondary text-white'],
];
$paymentModes = [
    'especes' => 'Espèces',
    'carte' => 'Carte bancaire',
    'virement' => 'Virement',
    'cheque' => 'Chèque',
    'assurance' => 'Assurance',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Agenda du Médecin - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        .agenda-hero {
            padding: clamp(28px, 4vw, 48px);
            border-radius: var(--mt-radius-xl);
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--mt-shadow-card);
            position: relative;
            overflow: hidden;
        }
        .agenda-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 10% 20%, rgba(255, 116, 38, 0.3), transparent 55%),
                        radial-gradient(circle at 90% 0%, rgba(106, 216, 255, 0.25), transparent 50%);
            pointer-events: none;
        }
        .agenda-hero > * {
            position: relative;
            z-index: 2;
        }
        .sidebar-card .nav-link {
            color: var(--mt-ink);
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 500;
        }
        .sidebar-card .nav-link i {
            margin-right: 12px;
        }
        .sidebar-card .nav-link.active,
        .sidebar-card .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            color: var(--mt-accent-alt);
            margin: 0 auto 18px;
        }
        .time-slot {
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: var(--mt-radius-md);
            padding: 14px;
            text-align: center;
            font-weight: 500;
            color: var(--mt-ink);
            cursor: pointer;
            transition: all .25s ease;
            background: rgba(255, 255, 255, 0.02);
        }
        .time-slot:hover {
            border-color: var(--mt-accent);
            background: rgba(255, 116, 38, 0.08);
        }
        .time-slot.booked {
            background: rgba(251, 113, 133, 0.15);
            border-color: rgba(251, 113, 133, 0.35);
            color: #ffd5dc;
            cursor: not-allowed;
        }
        .appointment-pill,
        .invoice-status {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .payment-card {
            border-radius: var(--mt-radius-lg);
            background: linear-gradient(135deg,#0A47B5 0%,#022763 100%);
            color: #fff;
            box-shadow: var(--mt-shadow-soft);
        }
        .payment-stat {
            background: rgba(255,255,255,0.08);
            border-radius: var(--mt-radius-md);
            padding: 18px;
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start align-items-lg-center">
                <div>
                    <p class="section-title mb-2">Agenda</p>
                    <h1 class="fw-semibold display-6 mb-1">Agenda de <?= $doctorName ?></h1>
                    <p class="text-white-50 mb-0">Organisation quotidienne & suivi des rendez-vous</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people me-2"></i>Patients</a>
                    <a class="btn btn-outline-light" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-earmark-medical me-2"></i>Ordonnances</a>
                </div>
            </div>
        </section>
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="glass-card sidebar-card fade-up">
                    <div class="text-center text-white">
                        <h5 class="mb-1"><?= $doctorName ?></h5>
                        <p class="text-white-50 mb-3"><?= $specialite ?></p>
                    </div>
                    <div class="card-divider"></div>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/Project/public/index.php?page=medecin_agenda"><i class="bi bi-calendar-check"></i> Mon agenda</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_patients"><i class="bi bi-people"></i> Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_ordonnances"><i class="bi bi-file-medical"></i> Ordonnances</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Project/public/index.php?page=medecin_staff"><i class="bi bi-people-fill"></i> Staff</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="/Project/public/index.php?page=login&action=logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card-animated mb-4 fade-up">
                    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center text-white">
                        <div>
                            <i class="bi bi-calendar3 me-2"></i>Planning du <?= date('d/m/Y', strtotime($selectedDate)) ?>
                        </div>
                        <form class="d-flex flex-wrap gap-2 align-items-center" method="GET" action="/Project/public/index.php">
                            <input type="hidden" name="page" value="medecin_agenda">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="date" name="date" class="form-control form-control-glass" value="<?= $selectedDate ?>">
                            </div>
                            <button class="btn btn-gradient btn-sm" type="submit">Filtrer</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (empty($agendaSlots)): ?>
                            <p class="text-white-50 text-center my-4">Aucun créneau enregistré pour cette journée.</p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($agendaSlots as $slot): ?>
                                    <?php
                                        $time = htmlspecialchars($slot['time'] ?? '');
                                        $patient = htmlspecialchars($slot['patient'] ?? 'Crénau libre');
                                        $status = $slot['status'] ?? 'free';
                                        $isBooked = $status !== 'free';
                                    ?>
                                    <div class="col-sm-6 col-xl-4">
                                    <div class="time-slot <?= $isBooked ? 'booked' : '' ?>">
                                            <div class="fw-semibold mb-1">
                                                <i class="bi bi-clock me-1"></i><?= $time ?>
                                            </div>
                                            <div class="small mb-2 <?= $isBooked ? 'text-danger' : 'text-white-50' ?>">
                                                <?= $isBooked ? $patient : 'Crénau disponible' ?>
                                            </div>
                                            <?php if ($isBooked): ?>
                                                <span class="appointment-pill bg-light text-danger"><i class="bi bi-clipboard-heart me-1"></i><?= htmlspecialchars($slot['reason'] ?? 'Consultation') ?></span>
                                            <?php else: ?>
                                                <span class="appointment-pill status-chip">Libre à la prise</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card-animated h-100 fade-up delay-1">
                            <div class="card-header d-flex justify-content-between align-items-center text-white">
                                <span><i class="bi bi-list-check me-2"></i>Rendez-vous à venir</span>
                                <a href="/Project/public/index.php?page=medecin_agenda&view=list" class="btn btn-outline-light btn-sm">Voir tout</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($upcomingAppointments)): ?>
                                    <p class="text-white-50 text-center my-4">Pas de rendez-vous programmés.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($upcomingAppointments as $appt): ?>
                                            <div class="list-group-item bg-transparent px-0 py-3 text-white border-0 border-bottom border-secondary-subtle">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Patient : <?= htmlspecialchars($appt['patient'] ?? 'Patient') ?></h6>
                                                        <small class="text-white-50">Motif : <?= htmlspecialchars($appt['reason'] ?? 'Consultation') ?></small>
                                                    </div>
                                                    <div class="text-end d-flex flex-column align-items-end gap-2">
                                                        <span class="appointment-pill status-chip"><?= htmlspecialchars($appt['date'] ?? '') ?> - <?= htmlspecialchars($appt['time'] ?? '') ?></span>
                                                        <a href="/Project/public/index.php?page=medecin_billing&action=create&patient_id=<?= urlencode($appt['patient_id'] ?? '') ?>&rendezvous_id=<?= urlencode($appt['id'] ?? '') ?>" class="btn btn-outline-light btn-xs">
                                                            <i class="bi bi-receipt me-1"></i>Facturer
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card-animated h-100 fade-up delay-2">
                            <div class="card-header d-flex justify-content-between align-items-center text-white">
                                <span><i class="bi bi-inbox me-2"></i>Demandes en attente</span>
                                <button class="btn btn-outline-light btn-sm" type="button">Planifier</button>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pendingRequests)): ?>
                                    <p class="text-white-50 text-center my-4">Aucune demande en attente.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($pendingRequests as $request): ?>
                                            <div class="list-group-item bg-transparent px-0 py-3 text-white border-0 border-bottom border-secondary-subtle">
                                                <div class="d-flex align-items-start justify-content-between gap-3">
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($request['patient'] ?? 'Patient') ?></h6>
                                                        <small class="text-white-50 d-block mb-1">Motif : <?= htmlspecialchars($request['reason'] ?? 'Consultation') ?></small>
                                                        <p class="mb-0 small text-white-50">Préférence : <?= htmlspecialchars($request['preferred_time'] ?? 'Non précisée') ?></p>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/Project/public/index.php?page=medecin_agenda&action=accept&id=<?= urlencode($request['id'] ?? '') ?>" class="btn btn-success"><i class="bi bi-check"></i></a>
                                                        <a href="/Project/public/index.php?page=medecin_agenda&action=decline&id=<?= urlencode($request['id'] ?? '') ?>" class="btn btn-outline-light"><i class="bi bi-x"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
