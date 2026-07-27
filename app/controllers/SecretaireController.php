<?php

require_once __DIR__ . '/../services/NotificationService.php';

class SecretaireController {
    private PDO $db;
    private int $secretaireId;
    private int $medecinId;
    private NotificationService $notifier;

    public function __construct(PDO $db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'secretaire') {
            header('Location: /Project/public/index.php?page=login');
            exit();
        }

        $this->db = $db;
        $this->secretaireId = (int) $_SESSION['user_id'];
        $this->medecinId = $this->resolveMedecinId();
        $this->notifier = new NotificationService($db);

        if ($this->medecinId <= 0) {
            $_SESSION['secretaire_error'] = "Aucun médecin n'est associé à ce compte secrétaire.";
            header('Location: /Project/public/index.php?page=login');
            exit();
        }
    }

    public function dashboard(): void {
        try {
            $stats = $this->fetchDashboardStats();
            $todayAppointments = $this->fetchTodayAppointments();
            $pendingTasks = $this->fetchPendingRequests();
            $recentPatients = $this->fetchRecentPatients();

            require_once __DIR__ . '/../views/Secretaire/secretaire_dashboard.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement du tableau de bord : " . $e->getMessage();
            $stats = [
                'todayAppointments' => 0,
                'weekAppointments' => 0,
                'pendingRequests' => 0,
                'totalPatients' => 0,
                'totalDoctors' => 0,
            ];
            $todayAppointments = $pendingTasks = $recentPatients = [];

            require_once __DIR__ . '/../views/Secretaire/secretaire_dashboard.php';
        }
    }

    public function appointments(): void {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleAppointmentAction();
            }

            $filters = $this->extractAppointmentFilters();
            $appointments = $this->fetchAppointments($filters);
            $doctors = $this->fetchDoctorsList();

            require_once __DIR__ . '/../views/Secretaire/secretaire_appointments.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement des rendez-vous : " . $e->getMessage();
            $filters = [
                'selectedDate' => date('Y-m-d'),
                'doctorId' => null,
                'status' => null,
            ];
            $appointments = [];
            $doctors = [];

            require_once __DIR__ . '/../views/Secretaire/secretaire_appointments.php';
        }
    }

    public function patients(): void {
        try {
            $search = trim($_GET['q'] ?? '');
            $patients = $this->fetchPatients($search);

            require_once __DIR__ . '/../views/Secretaire/secretaire_patients.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement des patients : " . $e->getMessage();
            $search = '';
            $patients = [];

            require_once __DIR__ . '/../views/Secretaire/secretaire_patients.php';
        }
    }

    private function fetchDashboardStats(): array {
        $stats = [
            'todayAppointments' => 0,
            'weekAppointments' => 0,
            'pendingRequests' => 0,
            'totalPatients' => 0,
            'totalDoctors' => 1,
        ];

        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN DATE(date) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
                SUM(CASE WHEN date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS week_count,
                SUM(CASE WHEN statut = 'planifie' AND date >= NOW() THEN 1 ELSE 0 END) AS pending_count
            FROM rendezvous
            WHERE idMedecin = :medecinId
        ");
        $stmt->execute([':medecinId' => $this->medecinId]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($counts) {
            $stats['todayAppointments'] = (int) ($counts['today_count'] ?? 0);
            $stats['weekAppointments'] = (int) ($counts['week_count'] ?? 0);
            $stats['pendingRequests'] = (int) ($counts['pending_count'] ?? 0);
        }

        $patientStmt = $this->db->prepare('
            SELECT COUNT(DISTINCT idPatient)
            FROM rendezvous
            WHERE idMedecin = :medecinId
        ');
        $patientStmt->execute([':medecinId' => $this->medecinId]);
        $stats['totalPatients'] = (int) ($patientStmt->fetchColumn() ?: 0);

        return $stats;
    }

    private function fetchTodayAppointments(): array {
        $stmt = $this->db->prepare("
            SELECT r.id,
                   DATE_FORMAT(r.date, '%H:%i') AS time,
                   DATE_FORMAT(r.date, '%d/%m/%Y') AS day,
                   r.statut,
                   COALESCE(r.motif, '') AS motif,
                   CONCAT(ud.prenom, ' ', ud.nom) AS doctor_name,
                   CONCAT(up.prenom, ' ', up.nom) AS patient_name
            FROM rendezvous r
            JOIN users ud ON ud.id = r.idMedecin
            JOIN users up ON up.id = r.idPatient
            WHERE DATE(r.date) = CURDATE()
              AND r.idMedecin = :medecinId
            ORDER BY r.date ASC
        ");
        $stmt->execute([':medecinId' => $this->medecinId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchPendingRequests(): array {
        $stmt = $this->db->prepare("
            SELECT r.id,
                   DATE_FORMAT(r.date, '%d/%m/%Y %H:%i') AS scheduled_at,
                   COALESCE(r.motif, '') AS motif,
                   CONCAT(up.prenom, ' ', up.nom) AS patient_name,
                   CONCAT(ud.prenom, ' ', ud.nom) AS doctor_name
            FROM rendezvous r
            JOIN users up ON up.id = r.idPatient
            JOIN users ud ON ud.id = r.idMedecin
            WHERE r.statut = 'planifie'
              AND r.date >= NOW()
              AND r.idMedecin = :medecinId
            ORDER BY r.date ASC
            LIMIT 6
        ");
        $stmt->execute([':medecinId' => $this->medecinId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchRecentPatients(): array {
        $stmt = $this->db->prepare("
            SELECT u.id,
                   CONCAT(u.prenom, ' ', u.nom) AS full_name,
                   u.email,
                   p.telephone,
                   DATE_FORMAT(u.date_creation, '%d/%m/%Y') AS created_at
            FROM users u
            JOIN patient p ON p.id = u.id
            JOIN rendezvous r ON r.idPatient = u.id
            WHERE r.idMedecin = :medecinId
            GROUP BY u.id, u.prenom, u.nom, u.email, p.telephone, u.date_creation
            ORDER BY u.date_creation DESC
            LIMIT 5
        ");
        $stmt->execute([':medecinId' => $this->medecinId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function handleAppointmentAction(): void {
        $appointmentId = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;
        $action = $_POST['action'] ?? '';

        if ($appointmentId <= 0) {
            $_SESSION['secretaire_error'] = 'Rendez-vous invalide.';
            return;
        }

        $statusMap = [
            'confirm' => 'confirme',
            'cancel' => 'annule',
            'complete' => 'termine',
        ];

        if (!isset($statusMap[$action])) {
            $_SESSION['secretaire_error'] = 'Action non reconnue.';
            return;
        }

        $appointment = $this->getAppointmentForDoctor($appointmentId);
        if (!$appointment) {
            $_SESSION['secretaire_error'] = 'Rendez-vous introuvable ou non autorisé.';
            return;
        }

        $stmt = $this->db->prepare('UPDATE rendezvous SET statut = :status WHERE id = :id');
        $stmt->execute([
            ':status' => $statusMap[$action],
            ':id' => $appointmentId,
        ]);

        if ($stmt->rowCount() > 0) {
            $messages = [
                'confirm' => 'Le rendez-vous a été confirmé.',
                'cancel' => 'Le rendez-vous a été annulé.',
                'complete' => 'Le rendez-vous a été marqué comme terminé.',
            ];
            $appointment['statut'] = $statusMap[$action];
            $_SESSION['secretaire_success'] = $messages[$action] ?? 'Action réalisée.';
            $this->notifyAppointmentAction($action, $appointment);
        } else {
            $_SESSION['secretaire_error'] = 'Impossible de mettre à jour le rendez-vous.';
        }

        $query = [];
        if (!empty($_GET['date'])) {
            $query['date'] = $_GET['date'];
        }
        if (!empty($_GET['doctor'])) {
            $query['doctor'] = $_GET['doctor'];
        }
        if (!empty($_GET['status'])) {
            $query['status'] = $_GET['status'];
        }

        $redirectUrl = '/Project/public/index.php?page=secretaire_appointments';
        if ($query) {
            $redirectUrl .= '&' . http_build_query($query);
        }

        header('Location: ' . $redirectUrl);
        exit();
    }

    private function getAppointmentForDoctor(int $appointmentId): ?array {
        $stmt = $this->db->prepare("
            SELECT rv.*,
                   up.prenom AS patient_prenom,
                   up.nom AS patient_nom,
                   ud.prenom AS doctor_prenom,
                   ud.nom AS doctor_nom
            FROM rendezvous rv
            JOIN users up ON up.id = rv.idPatient
            JOIN users ud ON ud.id = rv.idMedecin
            WHERE rv.id = :id
              AND rv.idMedecin = :doctor
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $appointmentId,
            ':doctor' => $this->medecinId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function notifyAppointmentAction(string $action, array $appointment): void {
        $appointmentDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['date'] ?? '') ?: null;
        $formattedDate = $this->formatAppointmentDate($appointmentDate);

        $patientId = (int) ($appointment['idPatient'] ?? 0);
        $doctorId = (int) ($appointment['idMedecin'] ?? 0);
        $patientName = $this->formatName($appointment['patient_prenom'] ?? '', $appointment['patient_nom'] ?? '');
        $doctorName = $this->formatName($appointment['doctor_prenom'] ?? '', $appointment['doctor_nom'] ?? '', 'Dr');

        switch ($action) {
            case 'confirm':
                $this->notifier->notifyUser(
                    $patientId,
                    'Rendez-vous confirmé',
                    sprintf("Votre rendez-vous avec %s prévu le %s est confirmé.", $doctorName, $formattedDate),
                    'rendez_vous',
                    '/Project/public/index.php?page=patient_appointments'
                );

                $this->notifier->notifyUser(
                    $doctorId,
                    'Rendez-vous confirmé par le secrétariat',
                    sprintf("Le rendez-vous avec %s a été confirmé pour le %s.", $patientName, $formattedDate),
                    'rendez_vous',
                    '/Project/public/index.php?page=medecin_agenda',
                    ['in_app']
                );
                break;

            case 'cancel':
                $this->notifier->notifyUser(
                    $patientId,
                    'Rendez-vous annulé par le cabinet',
                    sprintf("Votre rendez-vous avec %s prévu le %s a été annulé par le secrétariat. Contactez-nous pour reprogrammer.", $doctorName, $formattedDate),
                    'rendez_vous',
                    '/Project/public/index.php?page=patient_appointments'
                );

                $this->notifier->notifyUser(
                    $doctorId,
                    'Rendez-vous annulé',
                    sprintf("Le rendez-vous avec %s prévu le %s a été annulé par votre secrétaire.", $patientName, $formattedDate),
                    'rendez_vous',
                    '/Project/public/index.php?page=medecin_agenda',
                    ['in_app']
                );
                break;

            case 'complete':
                $this->notifier->notifyUser(
                    $patientId,
                    'Rendez-vous clôturé',
                    sprintf("Votre rendez-vous avec %s du %s est clôturé. Retrouvez vos documents dans votre espace.", $doctorName, $formattedDate),
                    'rendez_vous',
                    '/Project/public/index.php?page=patient_dashboard',
                    ['in_app']
                );

                $this->notifier->notifyUser(
                    $doctorId,
                    'Rendez-vous marqué terminé',
                    sprintf("Le rendez-vous avec %s du %s a été marqué comme terminé par le secrétariat.", $patientName, $formattedDate),
                    'rendez_vous',
                    '/Project/public/index.php?page=medecin_agenda',
                    ['in_app']
                );
                break;
        }
    }

    private function formatAppointmentDate(?DateTime $date): string {
        return $date ? $date->format('d/m/Y à H\hi') : 'la date prévue';
    }

    private function formatName(string $prenom, string $nom, string $prefix = ''): string {
        $name = trim($prenom . ' ' . $nom);
        $name = $name !== '' ? $name : 'Patient';

        if ($prefix !== '') {
            return trim($prefix . ' ' . $name);
        }

        return $name;
    }

    private function extractAppointmentFilters(): array {
        $selectedDate = $_GET['date'] ?? '';
        $doctorId = $_GET['doctor'] ?? '';
        $status = $_GET['status'] ?? '';

        $dateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
        if (!$dateObj) {
            $selectedDate = date('Y-m-d');
        } else {
            $selectedDate = $dateObj->format('Y-m-d');
        }

        $doctorId = ctype_digit((string) $doctorId) ? (int) $doctorId : null;

        $statusList = ['planifie', 'confirme', 'termine', 'annule'];
        if (!in_array($status, $statusList, true)) {
            $status = null;
        }

        return [
            'selectedDate' => $selectedDate,
            'doctorId' => $doctorId,
            'status' => $status,
        ];
    }

    private function fetchAppointments(array $filters): array {
        $sql = "
            SELECT r.id,
                   DATE_FORMAT(r.date, '%d/%m/%Y') AS day,
                   DATE_FORMAT(r.date, '%H:%i') AS time,
                   r.statut,
                   COALESCE(r.motif, '') AS motif,
                   CONCAT(up.prenom, ' ', up.nom) AS patient_name,
                   CONCAT(ud.prenom, ' ', ud.nom) AS doctor_name
            FROM rendezvous r
            JOIN users up ON up.id = r.idPatient
            JOIN users ud ON ud.id = r.idMedecin
            WHERE DATE(r.date) = :selectedDate
              AND r.idMedecin = :medecinId
        ";
        $params = [
            ':selectedDate' => $filters['selectedDate'],
            ':medecinId' => $this->medecinId,
        ];

        if ($filters['doctorId'] && $filters['doctorId'] !== $this->medecinId) {
            $_SESSION['secretaire_error'] = "Vous n'êtes pas autorisé à consulter les rendez-vous d'un autre médecin.";
        }

        if ($filters['status']) {
            $sql .= ' AND r.statut = :status';
            $params[':status'] = $filters['status'];
        }

        $sql .= ' ORDER BY r.date ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchDoctorsList(): array {
        $stmt = $this->db->prepare("
            SELECT m.id,
                   CONCAT(u.prenom, ' ', u.nom) AS full_name
            FROM medecin m
            JOIN users u ON u.id = m.id
            WHERE m.id = :medecinId
        ");
        $stmt->execute([':medecinId' => $this->medecinId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchPatients(string $search): array {
        $sql = "
            SELECT u.id,
                   CONCAT(u.prenom, ' ', u.nom) AS full_name,
                   u.email,
                   p.telephone,
                   COALESCE(DATE_FORMAT(MAX(rv.date), '%d/%m/%Y %H:%i'), '—') AS last_appointment,
                   SUM(CASE WHEN rv.date >= NOW() AND rv.statut IN ('planifie', 'confirme') THEN 1 ELSE 0 END) AS upcoming_count
            FROM users u
            JOIN patient p ON p.id = u.id
            LEFT JOIN rendezvous rv ON rv.idPatient = u.id
            WHERE rv.idMedecin = :medecinId
        ";
        $params = [':medecinId' => $this->medecinId];

        if ($search !== '') {
            $sql .= ' AND (u.nom LIKE :searchNom OR u.prenom LIKE :searchPrenom OR u.email LIKE :searchEmail)';
            $like = '%' . $search . '%';
            $params[':searchNom'] = $like;
            $params[':searchPrenom'] = $like;
            $params[':searchEmail'] = $like;
        }

        $sql .= '
            GROUP BY u.id, u.prenom, u.nom, u.email, p.telephone
            ORDER BY u.nom ASC, u.prenom ASC
            LIMIT 40
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function resolveMedecinId(): int {
        $stmt = $this->db->prepare('SELECT medecin_id FROM secretaire WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $this->secretaireId]);

        $medecinId = (int) ($stmt->fetchColumn() ?: 0);

        return $medecinId;
    }
}
