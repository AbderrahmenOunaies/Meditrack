<?php

require_once __DIR__ . '/../services/NotificationService.php';

class PatientController {
    private $db;
    private $patientId;
    private NotificationService $notifier;

    public function __construct($db) {
        $this->db = $db;
        // Récupérer l'ID du patient depuis la session
        $this->patientId = $_SESSION['user_id'] ?? null;
        
        if (!$this->patientId) {
            header('Location: /Project/public/index.php?page=login');
            exit();
        }

        $this->notifier = new NotificationService($this->db);
    }

    // Afficher le tableau de bord patient
    public function dashboard() {
        $patient = $this->getPatientData();
        $appointments = $this->getUpcomingAppointments();
        $prescriptions = $this->getRecentPrescriptions();
        $medications = $this->getCurrentMedications();
        $notifications = $this->getPatientNotifications();

        $patientName = trim(($patient['prenom'] ?? '') . ' ' . ($patient['nom'] ?? ''));
        if ($patientName === '') {
            $patientName = $patient['nom'] ?? $patient['prenom'] ?? 'Patient';
        }

        // Charger la vue du tableau de bord
        require_once __DIR__ . '/../views/Patient/patient_dashboard.php';
    }

    // Afficher la page de gestion des rendez-vous
    public function appointments() {
        $patient = $this->getPatientData();
        $appointments = $this->getUpcomingAppointments();
        $history = $this->getAppointmentHistory();
        $doctors = $this->getAvailableDoctors();

        // Charger la vue des rendez-vous
        require_once __DIR__ . '/../views/Patient/rendezvous.php';
    }
    
    // Afficher les ordonnances du patient
    public function ordonnances() {
        $patient = $this->getPatientData();
        $ordonnances = $this->getPatientOrdonnances();
        
        // Charger la vue des ordonnances
        require_once __DIR__ . '/../views/Patient/ordonnances.php';
    }

    // Prendre un nouveau rendez-vous
    public function bookAppointment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $doctorId = $_POST['doctor_id'] ?? null;
            $date = $_POST['date'] ?? null;
            $time = $_POST['time'] ?? null;
            $reason = $_POST['reason'] ?? '';

            // Validation des données
            if (!$doctorId || !$date || !$time) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: /Project/public/index.php?page=patient_appointments');
                exit();
            }

            // Vérifier la disponibilité du médecin
            $appointmentDate = DateTime::createFromFormat('Y-m-d H:i', trim($date . ' ' . $time));
            if (!$appointmentDate) {
                $_SESSION['error'] = "Format de date ou d'heure invalide";
                header('Location: /Project/public/index.php?page=patient_appointments');
                exit();
            }

            if ($this->isDoctorAvailable($doctorId, $appointmentDate)) {
                // Créer le rendez-vous
                $stmt = $this->db->prepare("INSERT INTO rendezvous (idPatient, idMedecin, date, statut, motif, duree) 
                                          VALUES (?, ?, ?, 'planifie', ?, ?)");
                $stmt->execute([
                    $this->patientId,
                    $doctorId,
                    $appointmentDate->format('Y-m-d H:i:s'),
                    $reason,
                    $_POST['duration'] ?? 30
                ]);
                
                $_SESSION['success'] = "Rendez-vous pris avec succès";
                
                $appointmentId = (int) $this->db->lastInsertId();

                $this->sendAppointmentConfirmation(
                    $this->patientId,
                    (int) $doctorId,
                    $appointmentDate,
                    $appointmentId,
                    $reason
                );
                
            } else {
                $_SESSION['error'] = "Ce créneau n'est plus disponible";
            }
            
            header('Location: /Project/public/index.php?page=patient_appointments');
            exit();
        }
    }

    // Annuler un rendez-vous
    public function cancelAppointment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentId = $_POST['appointment_id'] ?? null;
            
            if ($appointmentId) {
                $appointment = $this->findAppointmentForPatient((int) $appointmentId);
                if (!$appointment) {
                    $_SESSION['error'] = "Rendez-vous introuvable.";
                } else {
                    $stmt = $this->db->prepare("UPDATE rendezvous SET statut = 'annule' 
                                              WHERE id = ? AND idPatient = ?");
                    $stmt->execute([$appointmentId, $this->patientId]);
                    
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success'] = "Rendez-vous annulé avec succès";
                        $this->notifyAppointmentCancellation($appointment);
                    } else {
                        $_SESSION['error'] = "Impossible d'annuler ce rendez-vous";
                    }
                }
            }
            
            header('Location: /Project/public/index.php?page=patient_appointments');
            exit();
        }
    }

    // Vérifier la disponibilité d'un médecin
    public function appointmentSlots(): void {
        $doctorId = (int) ($_GET['doctor_id'] ?? 0);
        $date = $_GET['date'] ?? null;
        
        header('Content-Type: application/json');
        if (!$doctorId || !$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètres manquants']);
            return;
        }

        try {
            $parsedDate = new DateTime($date);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Date invalide']);
            return;
        }

        $slots = $this->generateSlotsForDoctor($doctorId, $parsedDate);
        echo json_encode(['slots' => $slots]);
    }

    // Méthodes privées utilitaires
    
    private function getPatientData() {
        $stmt = $this->db->prepare("
            SELECT u.*, p.date_naissance, p.adresse, p.telephone, p.assurance_maladie 
            FROM users u
            LEFT JOIN patient p ON u.id = p.id
            WHERE u.id = ? AND u.role = 'patient'
        ");
        $stmt->execute([$this->patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getUpcomingAppointments() {
        $stmt = $this->db->prepare("
            SELECT rv.id,
                   rv.date,
                   rv.statut,
                   COALESCE(rv.motif, '') AS motif,
                   CONCAT(um.prenom, ' ', um.nom) AS medecin_nom,
                   m.specialite
            FROM rendezvous rv
            JOIN medecin m ON rv.idMedecin = m.id
            JOIN users um ON m.id = um.id
            WHERE rv.idPatient = ?
              AND rv.date >= NOW()
              AND rv.statut IN ('planifie', 'confirme')
            ORDER BY rv.date ASC
            LIMIT 5
        ");
        $stmt->execute([$this->patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getAppointmentHistory() {
        $stmt = $this->db->prepare("
            SELECT rv.id,
                   rv.date,
                   rv.statut,
                   CONCAT(um.prenom, ' ', um.nom) AS medecin_nom
            FROM rendezvous rv
            JOIN medecin m ON rv.idMedecin = m.id
            JOIN users um ON m.id = um.id
            WHERE rv.idPatient = ?
              AND (rv.date < NOW() OR rv.statut = 'annule')
            ORDER BY rv.date DESC
            LIMIT 10
        ");
        $stmt->execute([$this->patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRecentPrescriptions() {
        $stmt = $this->db->prepare("
            SELECT o.id, o.date, o.contenu, o.medicaments, o.posologie, o.duree_traitement,
                   CONCAT(um.prenom, ' ', um.nom) AS medecin_nom
            FROM ordonnance o
            JOIN medecin m ON o.idMedecin = m.id
            JOIN users um ON m.id = um.id
            WHERE o.idPatient = ?
            ORDER BY o.date DESC
            LIMIT 3
        ");
        $stmt->execute([$this->patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getCurrentMedications() {
        $stmt = $this->db->prepare("
            SELECT o.id,
                   o.date,
                   o.medicaments,
                   o.posologie,
                   o.duree_traitement,
                   CONCAT(um.prenom, ' ', um.nom) AS medecin_nom
            FROM ordonnance o
            JOIN medecin m ON o.idMedecin = m.id
            JOIN users um ON m.id = um.id
            WHERE o.idPatient = ?
            ORDER BY o.date DESC
        ");
        $stmt->execute([$this->patientId]);
        $ordonnances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $medications = [];
        foreach ($ordonnances as $ordonnance) {
            $entries = $this->parseMedicamentsField($ordonnance);
            $medications = array_merge($medications, $entries);
        }

        return $medications;
    }
    
    private function getAvailableDoctors() {
        $stmt = $this->db->query("
            SELECT u.id, CONCAT(u.prenom, ' ', u.nom) AS nom_complet, m.specialite
            FROM medecin m
            JOIN users u ON m.id = u.id
            ORDER BY u.nom, u.prenom
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getPatientNotifications(): array {
        $stmt = $this->db->prepare("
            SELECT titre,
                   contenu,
                   type,
                   DATE_FORMAT(date_creation, '%d/%m/%Y %H:%i') AS date_formattee
            FROM notification
            WHERE idUser = ?
            ORDER BY date_creation DESC
            LIMIT 5
        ");
        $stmt->execute([$this->patientId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $notifications = [];

        foreach ($rows as $row) {
            $notifications[] = [
                'title' => $row['titre'] ?? 'Notification',
                'message' => $row['contenu'] ?? '',
                'type' => $row['type'] ?? 'info',
                'time' => $row['date_formattee'] ?? '',
            ];
        }

        return $notifications;
    }

    private function isDoctorAvailable($doctorId, DateTime $appointmentDate) {
        // Vérifier si le médecin a déjà un rendez-vous à cette heure
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM rendezvous
            WHERE idMedecin = ?
              AND DATE(date) = ?
              AND TIME(date) = ?
              AND statut IN ('planifie', 'confirme')
        ");
        $stmt->execute([
            $doctorId,
            $appointmentDate->format('Y-m-d'),
            $appointmentDate->format('H:i:s')
        ]);
        
        return $stmt->fetchColumn() === 0;
    }
    
    private function getPatientOrdonnances() {
        // Récupérer les ordonnances du patient avec les médicaments associés
        $stmt = $this->db->prepare("
            SELECT o.id,
                   o.date,
                   o.contenu,
                   o.medicaments,
                   o.posologie,
                   o.duree_traitement,
                   CONCAT(um.prenom, ' ', um.nom) AS medecin_nom,
                   m.specialite AS medecin_specialite
            FROM ordonnance o
            JOIN medecin m ON o.idMedecin = m.id
            JOIN users um ON m.id = um.id
            WHERE o.idPatient = ?
            ORDER BY o.date DESC
        ");
        $stmt->execute([$this->patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Afficher les médicaments du patient
    public function medicaments() {
        $medicaments = $this->getPatientMedicaments();
        require_once __DIR__ . '/../views/Patient/medicaments.php';
    }

    private function getPatientMedicaments() {
        // Récupérer tous les médicaments prescrits au patient avec les détails des ordonnances
        $stmt = $this->db->prepare("
            SELECT o.id,
                   o.date,
                   o.medicaments,
                   o.posologie,
                   o.duree_traitement,
                   CONCAT(um.prenom, ' ', um.nom) AS medecin_nom
            FROM ordonnance o
            JOIN medecin m ON o.idMedecin = m.id
            JOIN users um ON m.id = um.id
            WHERE o.idPatient = ?
            ORDER BY o.date DESC
        ");
        $stmt->execute([$this->patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $medications = [];
        foreach ($rows as $row) {
            $medications = array_merge($medications, $this->parseMedicamentsField($row));
        }

        return $medications;
    }

    private function parseMedicamentsField(array $ordonnance): array {
        $rawMedicaments = $ordonnance['medicaments'] ?? null;
        if (empty($rawMedicaments)) {
            return [];
        }

        $entries = [];
        $defaultDurationRaw = $ordonnance['duree_traitement'] ?? null;
        $durationNormalizer = static function ($value): int {
            if (is_numeric($value)) {
                return (int) $value;
            }
            if (is_string($value)) {
                if (preg_match('/(\d+)/', $value, $match)) {
                    return (int) $match[1];
                }
            }
            return 0;
        };
        $defaultDuration = $durationNormalizer($defaultDurationRaw);

        $normalizeEntry = function ($data, string $fallbackLabel) use ($ordonnance, $defaultDuration, $defaultDurationRaw, $durationNormalizer) {
            $label = trim($data['nom'] ?? $data['name'] ?? $data['medicament'] ?? $fallbackLabel);
            if ($label === '') {
                return null;
            }

            $posologie = $data['posologie']
                ?? $data['dosage']
                ?? $data['instructions']
                ?? ($ordonnance['posologie'] ?? 'Non précisé');

            $frequence = $data['frequence']
                ?? $data['frequency']
                ?? $data['times_per_day']
                ?? 'Non précisée';

            $rawDuration = $data['duree_jours']
                ?? $data['duration']
                ?? $data['duree']
                ?? $defaultDurationRaw;
            $duration = $durationNormalizer($rawDuration ?? $defaultDuration);
            $durationLabel = null;
            if ($rawDuration !== null && $rawDuration !== '') {
                $durationLabel = is_scalar($rawDuration) ? trim((string) $rawDuration) : null;
            } elseif ($defaultDurationRaw !== null && $defaultDurationRaw !== '') {
                $durationLabel = is_scalar($defaultDurationRaw) ? trim((string) $defaultDurationRaw) : null;
            }

            $status = $data['status']
                ?? $data['statut']
                ?? ($duration > 0 ? 'En cours' : 'À vérifier');

            $prochaine = $data['prochaine_prise']
                ?? $data['next_dose']
                ?? 'À déterminer';

            return [
                'ordonnance_id' => $ordonnance['id'] ?? '—',
                'nom' => $label,
                'date_prescription' => $ordonnance['date'] ?? null,
                'medecin_nom' => $ordonnance['medecin_nom'] ?? '',
                'posologie' => $posologie,
                'frequence' => $frequence,
                'duree_jours' => $duration,
                'duree_label' => $durationLabel,
                'prochaine_prise' => $prochaine,
                'status' => $status,
                'remarques' => $data['remarques'] ?? $data['notes'] ?? null,
                'medication_start' => $data['start_date'] ?? $data['medication_start'] ?? $data['debut'] ?? null,
                'medication_end' => $data['end_date'] ?? $data['medication_end'] ?? $data['fin'] ?? null,
            ];
        };

        $decoded = null;
        if (is_string($rawMedicaments)) {
            $decoded = json_decode($rawMedicaments, true);
        } elseif (is_array($rawMedicaments)) {
            $decoded = $rawMedicaments;
        }

        if (is_array($decoded)) {
            foreach ($decoded as $entry) {
                if (!is_array($entry)) {
                    $entry = ['nom' => trim((string) $entry)];
                }
                $normalized = $normalizeEntry($entry, 'Traitement');
                if ($normalized !== null) {
                    $entries[] = $normalized;
                }
            }
            return $entries;
        }

        // Fallback legacy format: newline/comma separated list
        $legacyList = preg_split('/\r?\n|,/', (string) $rawMedicaments);
        foreach ($legacyList as $rawMedicament) {
            $entry = $normalizeEntry(['nom' => trim($rawMedicament)], 'Traitement');
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    private function sendAppointmentConfirmation(int $patientId, int $doctorId, DateTime $appointmentDate, int $appointmentId, string $reason = ''): void {
        $patient = $this->getUserIdentity($patientId);
        $doctor = $this->getUserIdentity($doctorId);
        $secretaire = $this->getSecretaireByDoctor($doctorId);

        $formattedDate = $this->formatAppointmentDate($appointmentDate);
        $doctorName = $this->formatUserName($doctor, 'Dr');
        $patientName = $this->formatUserName($patient);

        $patientMessage = sprintf(
            "Votre rendez-vous avec %s est enregistré pour le %s. Nous vous informerons dès confirmation.",
            $doctorName,
            $formattedDate
        );
        $this->notifier->notifyUser(
            $patientId,
            'Rendez-vous enregistré',
            $patientMessage,
            'rendez_vous',
            '/Project/public/index.php?page=patient_appointments'
        );

        $doctorMessage = sprintf(
            "%s a demandé un rendez-vous pour le %s.%s",
            $patientName,
            $formattedDate,
            $reason !== '' ? ' Motif : ' . $reason : ''
        );
        $this->notifier->notifyUser(
            $doctorId,
            'Nouveau rendez-vous patient',
            $doctorMessage,
            'rendez_vous',
            '/Project/public/index.php?page=medecin_agenda',
            ['in_app']
        );

        if ($secretaire) {
            $secretaryMessage = sprintf(
                "Nouveau rendez-vous pour %s avec %s le %s.",
                $doctorName,
                $patientName,
                $formattedDate
            );
            $this->notifier->notifyUser(
                (int) $secretaire['id'],
                'Rendez-vous à planifier',
                $secretaryMessage,
                'rendez_vous',
                '/Project/public/index.php?page=secretaire_appointments',
                ['in_app']
            );
        }
    }

    private function notifyAppointmentCancellation(array $appointment): void {
        $appointmentDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['date'] ?? '') ?: null;
        $formattedDate = $appointmentDate ? $this->formatAppointmentDate($appointmentDate) : 'la date prévue';

        $patientId = (int) $appointment['idPatient'];
        $doctorId = (int) $appointment['idMedecin'];

        $patient = $this->getUserIdentity($patientId);
        $doctor = $this->getUserIdentity($doctorId);
        $secretaire = $this->getSecretaireByDoctor($doctorId);

        $patientName = $this->formatUserName($patient);
        $doctorName = $this->formatUserName($doctor, 'Dr');

        $this->notifier->notifyUser(
            $patientId,
            'Rendez-vous annulé',
            sprintf("Vous avez annulé votre rendez-vous avec %s prévu le %s.", $doctorName, $formattedDate),
            'rendez_vous',
            '/Project/public/index.php?page=patient_appointments'
        );

        $this->notifier->notifyUser(
            $doctorId,
            'Patient a annulé',
            sprintf("%s a annulé son rendez-vous prévu le %s.", $patientName, $formattedDate),
            'rendez_vous',
            '/Project/public/index.php?page=medecin_agenda',
            ['in_app']
        );

        if ($secretaire) {
            $this->notifier->notifyUser(
                (int) $secretaire['id'],
                'Rendez-vous annulé',
                sprintf("%s a annulé le rendez-vous du %s avec %s.", $patientName, $formattedDate, $doctorName),
                'rendez_vous',
                '/Project/public/index.php?page=secretaire_appointments',
                ['in_app']
            );
        }
    }

    private function findAppointmentForPatient(int $appointmentId): ?array {
        $stmt = $this->db->prepare("
            SELECT rv.*, up.prenom AS patient_prenom, up.nom AS patient_nom,
                   ud.prenom AS doctor_prenom, ud.nom AS doctor_nom
            FROM rendezvous rv
            JOIN users up ON up.id = rv.idPatient
            JOIN users ud ON ud.id = rv.idMedecin
            WHERE rv.id = :id AND rv.idPatient = :patient
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $appointmentId,
            ':patient' => $this->patientId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function getUserIdentity(int $userId): ?array {
        $stmt = $this->db->prepare('SELECT id, prenom, nom, email FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    private function getSecretaireByDoctor(int $doctorId): ?array {
        $stmt = $this->db->prepare('
            SELECT s.id, u.prenom, u.nom
            FROM secretaire s
            JOIN users u ON u.id = s.id
            WHERE s.medecin_id = :doctor
            LIMIT 1
        ');
        $stmt->execute([':doctor' => $doctorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function formatAppointmentDate(DateTime $date): string {
        return $date->format('d/m/Y à H\hi');
    }

    private function formatUserName(?array $user, string $prefix = ''): string {
        if (!$user) {
            return 'Utilisateur';
        }

        $name = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        $name = $name !== '' ? $name : 'Utilisateur';

        if ($prefix !== '') {
            return trim($prefix . ' ' . $name);
        }

        return $name;
    }

    /**
     * Récupérer les créneaux horaires disponibles pour un médecin à une date donnée
     *
     * @param int $doctorId
     * @param DateTime $date
     * @return array
     */
    private function generateSlotsForDoctor(int $doctorId, DateTime $date): array {
        $startHour = 8;
        $startMinute = 30;
        $endHour = 17;
        $interval = 30; // minutes
        $slots = [];

        $booked = $this->getDoctorDayAppointments($doctorId, $date);
        $bookedSet = [];
        foreach ($booked as $appointment) {
            $bookedSet[$appointment] = true;
        }

        $current = clone $date;
        $current->setTime($startHour, $startMinute, 0);
        $end = clone $date;
        $end->setTime($endHour, 0, 0);
        $lunchStart = clone $date;
        $lunchStart->setTime(12, 30, 0);
        $lunchEnd = clone $date;
        $lunchEnd->setTime(14, 0, 0);

        $now = new DateTime('now');

        while ($current < $end) {
            $slotLabel = $current->format('H:i');
            $isPast = $current <= $now;
            $inLunchBreak = $current >= $lunchStart && $current < $lunchEnd;
            $isBooked = isset($bookedSet[$slotLabel]);
            $slots[] = [
                'time' => $slotLabel,
                'available' => !$isPast && !$isBooked && !$inLunchBreak,
                'booked' => $isBooked,
            ];
            $current->modify("+{$interval} minutes");
        }

        return $slots;
    }

    private function getDoctorDayAppointments(int $doctorId, DateTime $date): array {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(date, '%H:%i') AS slot
            FROM rendezvous
            WHERE idMedecin = :doctor
              AND DATE(date) = :day
              AND statut IN ('planifie', 'confirme')
        ");
        $stmt->execute([
            ':doctor' => $doctorId,
            ':day' => $date->format('Y-m-d'),
        ]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}