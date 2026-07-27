<?php
$doctors = $doctors ?? [];
$appointments = $appointments ?? [];
$history = $history ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Rendez-vous - MediTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="/Project/public/assets/css/meditrack-theme.css">
    <style>
        #timeSlots {
            min-height: 48px;
            gap: 8px;
        }
        .time-slot {
            padding: 8px 14px;
            border-radius: var(--mt-radius-md);
            border: 1px solid var(--mt-border, rgba(255,255,255,0.2));
            color: var(--mt-ink, #fff);
            background: rgba(255, 255, 255, 0.05);
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        .time-slot:hover {
            border-color: var(--mt-accent, #ff7426);
            background: rgba(255, 255, 255, 0.12);
        }
        .time-slot.unavailable {
            opacity: 0.35;
            cursor: not-allowed;
        }
        .time-slot.selected {
            background: var(--mt-accent, #ff7426);
            color: #fff;
            border-color: transparent;
        }
    </style>
</head>
<body class="meditrack-surface">
    <main class="medi-layout d-flex flex-column gap-4">
        <section class="hero-panel position-relative fade-down">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                <div>
                    <p class="section-title mb-2">Mes rendez-vous</p>
                    <h1 class="display-6 fw-semibold mb-1">Gestion complète des consultations</h1>
                    <p class="text-white-50 mb-0">Prenez, modifiez ou annulez vos rendez-vous en quelques clics.</p>
                </div>
                <a class="btn btn-outline-light" href="/Project/public/index.php?page=patient_dashboard">
                    <i class="bi bi-arrow-left me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card fade-up">
                    <div class="card-header text-white d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-plus"></i> Nouveau rendez-vous
                    </div>
                    <div class="card-body">
                        <form id="appointmentForm" method="POST" action="/Project/public/index.php?page=patient_appointments">
                            <div class="mb-3">
                                <label class="form-label form-label-light" for="doctorSelect">Médecin</label>
                                <select class="form-select form-control-glass" id="doctorSelect" name="doctor_id" required>
                                    <option value="" selected disabled>Sélectionnez un praticien</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['id'] ?>">Dr. <?= htmlspecialchars($doctor['nom_complet']) ?> (<?= $doctor['specialite'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-light" for="appointmentDate">Date</label>
                                <input type="text" class="form-control form-control-glass" id="appointmentDate" name="date" placeholder="Choisir une date" required disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-light">Heure</label>
                                <input type="hidden" name="time" id="selectedTime">
                                <div id="timeSlots" class="d-flex flex-wrap"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label form-label-light" for="appointmentReason">Motif</label>
                                <textarea class="form-control form-control-glass" id="appointmentReason" name="reason" rows="3" placeholder="Décrivez votre besoin" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="bi bi-calendar-check me-2"></i>Prendre rendez-vous
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card-animated mb-4 fade-up delay-1">
                    <div class="card-header text-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-calendar-week me-2"></i>Rendez-vous à venir</span>
                        <button type="button" class="btn btn-outline-light btn-sm" id="refreshAppointments">
                            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive dark-table">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Médecin</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="upcomingAppointments">
                                <?php if (!empty($appointments)): ?>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <?php
                                            $dateTime = new DateTime($appointment['date']);
                                            $isFuture = $dateTime > new DateTime();
                                            $status = $appointment['statut'] ?? 'planifie';
                                            $statusClass = match ($status) {
                                                'confirme' => 'chip success',
                                                'termine' => 'chip neutral',
                                                'annule' => 'chip danger',
                                                default => 'chip warning',
                                            };
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($dateTime->format('d/m/Y')) ?></td>
                                            <td><?= htmlspecialchars($dateTime->format('H:i')) ?></td>
                                            <td>Dr. <?= htmlspecialchars($appointment['medecin_nom']) ?></td>
                                            <td><span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span></td>
                                            <td class="text-end">
                                                <?php if ($isFuture && !in_array($status, ['annule', 'termine'], true)): ?>
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm cancel-appointment"
                                                            data-id="<?= (int)$appointment['id'] ?>">
                                                        <i class="bi bi-x-circle me-1"></i>Annuler
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-white-50 py-4">Aucun rendez-vous à venir.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-animated fade-up delay-2">
                    <div class="card-header text-white"><i class="bi bi-clock-history me-2"></i>Historique des rendez-vous</div>
                    <div class="card-body">
                        <div class="table-responsive dark-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Médecin</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody id="appointmentHistory">
                                <?php if (!empty($history)): ?>
                                    <?php foreach ($history as $entry): ?>
                                        <?php $historyDate = new DateTime($entry['date']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($historyDate->format('d/m/Y')) ?></td>
                                            <td><?= htmlspecialchars($historyDate->format('H:i')) ?></td>
                                            <td>Dr. <?= htmlspecialchars($entry['medecin_nom']) ?></td>
                                            <td>
                                                <?php
                                                    $status = $entry['statut'] ?? 'termine';
                                                    $statusClass = match ($status) {
                                                        'annule' => 'chip danger',
                                                        'termine' => 'chip neutral',
                                                        default => 'chip warning',
                                                    };
                                                ?>
                                                <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-white-50 py-4">Aucun historique disponible.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer l'annulation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir annuler ce rendez-vous ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-glass" data-bs-dismiss="modal">Conserver</button>
                    <button type="button" class="btn btn-gradient" id="confirmCancel">Oui, annuler</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../partials/notification_drawer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/fr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const datePicker = flatpickr("#appointmentDate", {
                minDate: "today",
                maxDate: new Date().fp_incr(30),
                locale: 'fr',
                dateFormat: "Y-m-d",
                disable: [date => (date.getDay() === 0 || date.getDay() === 6)],
                onChange: () => checkDoctorAvailability()
            });

            document.getElementById('doctorSelect').addEventListener('change', function() {
                const dateInput = document.getElementById('appointmentDate');
                dateInput.disabled = !this.value;
                if (this.value && datePicker.selectedDates.length) {
                    checkDoctorAvailability();
                }
            });

            async function checkDoctorAvailability() {
                const doctorId = document.getElementById('doctorSelect').value;
                const date = document.getElementById('appointmentDate').value;
                if (!doctorId || !date) return;

                const timeSlots = document.getElementById('timeSlots');
                timeSlots.innerHTML = '<div class="w-100 text-center text-white-50 py-3">Chargement des créneaux...</div>';

                try {
                    const response = await fetch(`/Project/public/index.php?page=patient_appointments&action=slots&doctor_id=${doctorId}&date=${date}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error('Réponse invalide');
                    }
                    const payload = await response.json();
                    displayTimeSlots(payload.slots || []);
                } catch (error) {
                    console.error(error);
                    timeSlots.innerHTML = '<p class="text-danger w-100 text-center mb-0">Impossible de charger les créneaux.</p>';
                }
            }

            function displayTimeSlots(slots) {
                const container = document.getElementById('timeSlots');
                container.innerHTML = '';

                if (!slots.length) {
                    container.innerHTML = '<p class="text-white-50 w-100 text-center mb-0">Aucun créneau disponible.</p>';
                    return;
                }

                slots.forEach(slot => {
                    const slotEl = document.createElement('div');
                    slotEl.className = `time-slot ${slot.available ? 'available' : 'unavailable'}`;
                    slotEl.textContent = slot.time;
                    if (slot.available) {
                        slotEl.addEventListener('click', function() {
                            document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
                            this.classList.add('selected');
                            document.getElementById('selectedTime').value = slot.time;
                        });
                    } else if (slot.booked) {
                        slotEl.title = 'Créneau déjà réservé';
                    }
                    container.appendChild(slotEl);
                });
            }

            document.getElementById('appointmentForm').addEventListener('submit', function(e) {
                if (!document.getElementById('selectedTime').value) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un créneau horaire disponible.');
                }
            });

            let appointmentToCancel = null;
            document.querySelectorAll('.cancel-appointment').forEach(button => {
                button.addEventListener('click', function() {
                    appointmentToCancel = this.dataset.id;
                    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
                    modal.show();
                });
            });

            document.getElementById('confirmCancel').addEventListener('click', function() {
                if (!appointmentToCancel) return;
                alert('Rendez-vous annulé avec succès');
                const modal = bootstrap.Modal.getInstance(document.getElementById('cancelModal'));
                modal.hide();
            });

            document.getElementById('refreshAppointments').addEventListener('click', function() {
                alert('Liste des rendez-vous actualisée (simulation)');
            });
        });
    </script>
</body>
</html>
