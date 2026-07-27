<?php

require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../services/EmailService.php';

class MedecinController {
    private PDO $db;
    private int $doctorId;
    private ?array $doctor = null;
    private NotificationService $notifier;
    private EmailService $mailer;

    public function __construct(PDO $db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'medecin') {
            header('Location: /Project/public/index.php?page=login');
            exit();
        }

        $this->db = $db;
        $this->doctorId = (int) $_SESSION['user_id'];
        $this->notifier = new NotificationService($db);
        $this->mailer = new EmailService($db);
    }

    public function dashboard(): void {
        try {
            $doctor = $this->getDoctorProfile();

            $stats = $this->computeDashboardStats();
            $todayAppointments = $this->fetchTodayAppointments();
            $recentActivities = $this->fetchRecentActivities();
            $notifications = $this->fetchNotifications();

            require_once __DIR__ . '/../views/Medecin/medecin_dashboard.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement du tableau de bord : " . $e->getMessage();
            $doctor = $this->doctor ?? [];
            $stats = [
                'todayAppointments' => 0,
                'weekAppointments' => 0,
                'patientsFollowed' => 0,
                'pendingTasks' => 0,
            ];
            $todayAppointments = $recentActivities = $notifications = [];
            require_once __DIR__ . '/../views/Medecin/medecin_dashboard.php';
        }
    }

    private function handlePaymentFormSubmission(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['payment_action'] ?? '') !== 'save') {
            return;
        }

        $redirect = $_POST['redirect_to'] ?? '/Project/public/index.php?page=medecin_agenda';
        if (!is_string($redirect) || stripos($redirect, '/Project/') !== 0) {
            $redirect = '/Project/public/index.php?page=medecin_agenda';
        }

        $appointmentId = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;
        $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
        $status = $_POST['status'] ?? 'en_attente';
        $mode = trim($_POST['mode'] ?? 'espèces');
        $details = trim($_POST['details'] ?? '');
        $dueDateRaw = $_POST['due_date'] ?? null;

        $allowedStatus = ['en_attente', 'paye', 'annule', 'retard'];
        $allowedModes = ['especes', 'carte', 'virement', 'cheque', 'assurance'];

        if (!in_array($status, $allowedStatus, true)) {
            $status = 'en_attente';
        }
        if (!in_array($mode, $allowedModes, true)) {
            $mode = 'especes';
        }

        if ($appointmentId <= 0 || $amount <= 0) {
            $_SESSION['payment_error'] = "Merci de sélectionner un rendez-vous et de saisir un montant valide.";
            header('Location: ' . $redirect);
            exit();
        }

        $appointment = $this->getDoctorAppointment($appointmentId);
        if (!$appointment) {
            $_SESSION['payment_error'] = "Rendez-vous introuvable ou non autorisé.";
            header('Location: ' . $redirect);
            exit();
        }

        $dueDate = null;
        if ($dueDateRaw) {
            $dueDateObj = DateTime::createFromFormat('Y-m-d', $dueDateRaw);
            if ($dueDateObj instanceof DateTime) {
                $dueDate = $dueDateObj->format('Y-m-d');
            }
        }

        $invoiceId = null;
        try {
            $invoiceId = $this->db->prepare('SELECT id FROM facture WHERE idRendezVous = :rv LIMIT 1');
            $invoiceId->execute([':rv' => $appointmentId]);
            $existingId = (int) ($invoiceId->fetchColumn() ?: 0);

            if ($existingId > 0) {
                $update = $this->db->prepare('
                    UPDATE facture
                    SET montant = :amount,
                        statutPaiement = :status,
                        details = :details,
                        mode_paiement = :mode,
                        date_echeance = :due_date,
                        idPatient = :patient
                    WHERE id = :id
                ');
                $update->execute([
                    ':amount' => $amount,
                    ':status' => $status,
                    ':details' => $details !== '' ? $details : null,
                    ':mode' => $mode,
                    ':due_date' => $dueDate,
                    ':patient' => $appointment['idPatient'],
                    ':id' => $existingId,
                ]);
                $invoiceId = $existingId;
                $_SESSION['payment_success'] = "Facture mise à jour avec succès.";
            } else {
                $insert = $this->db->prepare('
                    INSERT INTO facture (idPatient, idRendezVous, montant, date_echeance, statutPaiement, details, mode_paiement)
                    VALUES (:patient, :appointment, :amount, :due_date, :status, :details, :mode)
                ');
                $insert->execute([
                    ':patient' => $appointment['idPatient'],
                    ':appointment' => $appointmentId,
                    ':amount' => $amount,
                    ':due_date' => $dueDate,
                    ':status' => $status,
                    ':details' => $details !== '' ? $details : null,
                    ':mode' => $mode,
                ]);
                $invoiceId = (int) $this->db->lastInsertId();
                $_SESSION['payment_success'] = "Facture créée et associée au rendez-vous.";
            }
        } catch (Throwable $e) {
            $_SESSION['payment_error'] = "Impossible d'enregistrer la facture : " . $e->getMessage();
        }

        if (!isset($_SESSION['payment_error']) && $invoiceId) {
            $this->notifyInvoiceUpdate(
                $appointment,
                $amount,
                $status,
                $invoiceId,
                isset($existingId) && $existingId > 0,
                $dueDate
            );
        }

        header('Location: ' . $redirect);
        exit();
    }

    private function getDoctorAppointment(int $appointmentId): ?array {
        if ($appointmentId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare('
            SELECT r.id,
                   r.idPatient,
                   r.idMedecin,
                   COALESCE(r.motif, \'Consultation\') AS motif,
                   DATE_FORMAT(r.date, \'%d/%m/%Y %H:%i\') AS scheduled_at,
                   up.prenom AS patient_prenom,
                   up.nom AS patient_nom
            FROM rendezvous r
            JOIN users up ON up.id = r.idPatient
            WHERE r.id = :id AND r.idMedecin = :doctor
            LIMIT 1
        ');
        $stmt->execute([
            ':id' => $appointmentId,
            ':doctor' => $this->doctorId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchAssignedSecretary(): ?array {
        $stmt = $this->db->prepare(
            "SELECT s.id AS secretaire_id,
                    CONCAT(u.prenom, ' ', u.nom) AS full_name,
                    u.email,
                    DATE_FORMAT(u.date_creation, '%d/%m/%Y') AS assigned_at
             FROM secretaire s
             JOIN users u ON u.id = s.id
             WHERE s.medecin_id = :doctor
             LIMIT 1"
        );
        $stmt->execute([':doctor' => $this->doctorId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchSecretaryApplications(): array {
        $stmt = $this->db->prepare(
            "SELECT id,
                    nom,
                    prenom,
                    email,
                    telephone,
                    experience,
                    motivation,
                    statut,
                    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') AS created_at
             FROM temp_secretaire
             WHERE medecin_id = :doctor
             ORDER BY created_at DESC"
        );
        $stmt->execute([':doctor' => $this->doctorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function updateRecruitmentStatus(bool $active): void {
        $stmt = $this->db->prepare(
            "UPDATE medecin
             SET recrute_secretaire = :status
             WHERE id = :doctor"
        );
        $stmt->execute([
            ':status' => $active ? 1 : 0,
            ':doctor' => $this->doctorId,
        ]);

        $this->doctor = null;
    }

    private function doctorHasSecretary(): bool {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM secretaire
            WHERE medecin_id = :doctor
        ');
        $stmt->execute([':doctor' => $this->doctorId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function userEmailExists(string $email): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function sendApplicationOutcomeEmail(array $application, string $action): void {
        $to = $application['email'] ?? '';

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $doctor = $this->getDoctorProfile();
        $doctorName = trim($doctor['nom_complet'] ?? 'Votre médecin');

        if ($action === 'accept') {
            $subject = 'Confirmation de votre candidature secrétaire MediTrack';
            $body = sprintf(
                "Bonjour %s %s,\n\nNous sommes heureux de vous informer que %s a accepté votre candidature au poste de secrétaire.\n\nVos identifiants de connexion sont :\n- Email : %s\n- Mot de passe : celui choisi lors de votre candidature\n\nConnectez-vous sur MediTrack pour finaliser votre prise de poste.\n\nBien cordialement,\nL'équipe MediTrack",
                $application['prenom'] ?? '',
                $application['nom'] ?? '',
                $doctorName,
                $to
            );
        } else {
            $subject = 'Suite à votre candidature secrétaire MediTrack';
            $body = sprintf(
                "Bonjour %s %s,\n\nNous vous remercions pour l'intérêt porté à MediTrack. Après étude de votre dossier, %s n'a pas retenu votre candidature.\n\nNous vous souhaitons une pleine réussite dans vos futures démarches.\n\nCordialement,\nL'équipe MediTrack",
                $application['prenom'] ?? '',
                $application['nom'] ?? '',
                $doctorName
            );
        }

        $this->mailer->send($to, $subject, $body);
    }

    private function handleApplicationDecision(int $applicationId, string $action): void {
        if (!in_array($action, ['accept', 'reject'], true)) {
            $_SESSION['staff_error'] = "Action de candidature inconnue.";
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT *
             FROM temp_secretaire
             WHERE id = :id AND medecin_id = :doctor
             LIMIT 1"
        );
        $stmt->execute([
            ':id' => $applicationId,
            ':doctor' => $this->doctorId,
        ]);

        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            $_SESSION['staff_error'] = "Candidature introuvable.";
            return;
        }

        if ($action === 'reject') {
            $update = $this->db->prepare('UPDATE temp_secretaire SET statut = "refusee" WHERE id = :id');
            $update->execute([':id' => $applicationId]);

            $this->sendApplicationOutcomeEmail($application, 'reject');
            $this->announceSecretaryDecision($application, 'reject', null);
            $_SESSION['staff_success'] = "Candidature refusée et email envoyé.";
            return;
        }

        if ($this->doctorHasSecretary()) {
            $_SESSION['staff_error'] = "Vous avez déjà une secrétaire associée.";
            return;
        }

        if ($this->userEmailExists($application['email'])) {
            $_SESSION['staff_error'] = "Impossible d'accepter : l'adresse e-mail est déjà utilisée.";
            return;
        }

        try {
            $this->db->beginTransaction();

            $userInsert = $this->db->prepare(
                "INSERT INTO users (nom, prenom, email, motDePasse, role)
                 VALUES (:nom, :prenom, :email, :password, 'secretaire')"
            );
            $userInsert->execute([
                ':nom' => $application['nom'],
                ':prenom' => $application['prenom'],
                ':email' => $application['email'],
                ':password' => $application['mot_de_passe'],
            ]);

            $userId = (int) $this->db->lastInsertId();

            $secretaryInsert = $this->db->prepare(
                'INSERT INTO secretaire (id, medecin_id) VALUES (:id, :medecin)'
            );
            $secretaryInsert->execute([
                ':id' => $userId,
                ':medecin' => $this->doctorId,
            ]);

            $delete = $this->db->prepare('DELETE FROM temp_secretaire WHERE id = :id');
            $delete->execute([':id' => $applicationId]);

            $this->db->commit();

            $this->updateRecruitmentStatus(false);

            $this->sendApplicationOutcomeEmail($application, 'accept');
            $this->announceSecretaryDecision($application, 'accept', $userId);
            $_SESSION['staff_success'] = "Candidature acceptée, compte secrétaire créé et email envoyé.";
        } catch (Throwable $e) {
            $this->db->rollBack();
            $_SESSION['staff_error'] = "Erreur lors de l'acceptation : " . $e->getMessage();
        }
    }

    public function staff(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['recruitment_action'])) {
                $desired = $_POST['recruitment_action'] === 'enable';

                try {
                    $this->updateRecruitmentStatus($desired);
                    $_SESSION['staff_success'] = $desired
                        ? "Le recrutement est désormais ouvert."
                        : "Le recrutement a été désactivé.";
                } catch (Throwable $e) {
                    $_SESSION['staff_error'] = "Impossible de mettre à jour le recrutement : " . $e->getMessage();
                }
            } elseif (isset($_POST['application_action'], $_POST['application_id'])) {
                $action = $_POST['application_action'];
                $applicationId = (int) $_POST['application_id'];
                $this->handleApplicationDecision($applicationId, $action);
            }

            header('Location: /Project/public/index.php?page=medecin_staff');
            exit();
        }

        try {
            $doctor = $this->getDoctorProfile();
            $secretary = $this->fetchAssignedSecretary();
            $applications = $this->fetchSecretaryApplications();

            $error = null;
            $successMessage = $_SESSION['staff_success'] ?? null;
            $errorMessage = $_SESSION['staff_error'] ?? null;
            unset($_SESSION['staff_success'], $_SESSION['staff_error']);

            require_once __DIR__ . '/../views/Medecin/medecin_staff.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement du personnel : " . $e->getMessage();
            $doctor = $this->doctor ?? [];
            $secretary = null;
            $applications = [];
            $successMessage = null;
            $errorMessage = $error;

            require_once __DIR__ . '/../views/Medecin/medecin_staff.php';
        }
    }

    public function agenda(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePaymentFormSubmission();
            return;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $action = $_GET['action'] ?? null;
                $requestId = isset($_GET['id']) ? (int) $_GET['id'] : null;

                if ($action && $requestId) {
                    $this->handleAgendaAction($action, $requestId);
                }
            }

            $doctor = $this->getDoctorProfile();

            $selectedDate = $_GET['date'] ?? date('Y-m-d');
            $dateObj = DateTime::createFromFormat('Y-m-d', $selectedDate) ?: new DateTime('today');
            $selectedDate = $dateObj->format('Y-m-d');

            $agendaSlots = $this->buildAgendaSlots($selectedDate);
            $upcomingAppointments = $this->fetchUpcomingAppointments();
            $pendingRequests = $this->fetchPendingRequests();
            $paymentsSummary = $this->fetchPaymentsSummary();
            $paymentHistory = $this->fetchPaymentHistory();
            $paymentSuccess = $_SESSION['payment_success'] ?? null;
            $paymentError = $_SESSION['payment_error'] ?? null;
            $paymentRedirect = '/Project/public/index.php?page=medecin_agenda' . ($selectedDate ? '&date=' . urlencode($selectedDate) : '');
            unset($_SESSION['payment_success'], $_SESSION['payment_error']);

            require_once __DIR__ . '/../views/Medecin/medecin_agenda.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement de l'agenda : " . $e->getMessage();
            $doctor = $this->doctor ?? [];
            $selectedDate = date('Y-m-d');
            $agendaSlots = $upcomingAppointments = $pendingRequests = [];
            $paymentsSummary = [
                'month_label' => date('m/Y'),
                'paid_total' => 0,
                'pending_total' => 0,
                'paid_count' => 0,
                'pending_count' => 0,
            ];
            $paymentHistory = [];
            $paymentSuccess = $paymentError = null;
            $paymentRedirect = '/Project/public/index.php?page=medecin_agenda';
            require_once __DIR__ . '/../views/Medecin/medecin_agenda.php';
        }
    }

    public function patients(): void {
        try {
            $doctor = $this->getDoctorProfile();

            $action = $_GET['action'] ?? null;
            $selectedPatientId = isset($_GET['id']) ? (int) $_GET['id'] : null;

            $filters = [
                'search' => $_GET['search'] ?? '',
                'status' => $_GET['status'] ?? 'all',
                'sort'   => $_GET['sort'] ?? 'recent',
            ];

            if ($action === 'view' && $selectedPatientId) {
                $patientInfo = $this->getPatientProfileForDoctor($selectedPatientId);

                if (!$patientInfo) {
                    $error = "Patient introuvable ou non associé à votre suivi.";
                    $patients = $this->fetchPatientsList($filters);
                    require_once __DIR__ . '/../views/Medecin/medecin_patients.php';
                    return;
                }

                $medicalRecord = $this->getPatientMedicalRecord($selectedPatientId);
                $upcomingAppointments = $this->getPatientUpcomingAppointments($selectedPatientId);
                $appointmentsHistory = $this->getPatientAppointmentHistory($selectedPatientId);
                $ordonnances = $this->getPatientOrdonnances($selectedPatientId);
                $medications = $this->buildMedicationsSummary($ordonnances);

                require_once __DIR__ . '/../views/Medecin/medecin_patient_dossier.php';
                return;
            }

            $patients = $this->fetchPatientsList($filters);

            require_once __DIR__ . '/../views/Medecin/medecin_patients.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement des patients : " . $e->getMessage();
            $doctor = $this->doctor ?? [];
            $filters = ['search' => '', 'status' => 'all', 'sort' => 'recent'];
            $patients = [];
            require_once __DIR__ . '/../views/Medecin/medecin_patients.php';
        }
    }

    public function ordonnances(): void {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleOrdonnanceCreation();
                return;
            }

            $action = $_GET['action'] ?? null;
            $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

            if ($action === 'new') {
                $doctor = $this->getDoctorProfile();
                $patients = $this->getDoctorPatientsForSelection();
                $errors = $_SESSION['ordonnance_errors'] ?? [];
                $old = $_SESSION['ordonnance_old'] ?? [];
                unset($_SESSION['ordonnance_errors'], $_SESSION['ordonnance_old']);

                require_once __DIR__ . '/../views/Medecin/medecin_ordonnance_form.php';
                return;
            }

            if ($action && $id) {
                $this->handleOrdonnanceAction($action, $id);
            }

            $doctor = $this->getDoctorProfile();

            $filters = [
                'search' => $_GET['search'] ?? '',
                'status' => $_GET['status'] ?? 'all',
                'period' => $_GET['period'] ?? 'recent',
            ];

            $prescriptions = $this->fetchPrescriptions($filters);
            $success = $_SESSION['ordonnance_success'] ?? null;
            unset($_SESSION['ordonnance_success']);

            require_once __DIR__ . '/../views/Medecin/medecin_ordonnances.php';
        } catch (Throwable $e) {
            $error = "Erreur lors du chargement des ordonnances : " . $e->getMessage();
            $doctor = $this->doctor ?? [];
            $filters = ['search' => '', 'status' => 'all', 'period' => 'recent'];
            $prescriptions = [];
            require_once __DIR__ . '/../views/Medecin/medecin_ordonnances.php';
        }
    }

    private function handleOrdonnanceCreation(): void {
        $patientId = isset($_POST['patient_id']) ? (int) $_POST['patient_id'] : 0;
        $instructions = trim($_POST['contenu'] ?? '');
        $generalPosology = trim($_POST['posologie'] ?? '');
        $treatmentDuration = trim($_POST['duree_traitement'] ?? '');

        $medicationNames = $_POST['medication_name'] ?? [];
        $medicationStarts = $_POST['medication_start'] ?? [];
        $medicationEnds = $_POST['medication_end'] ?? [];
        $medicationDosages = $_POST['medication_dosage'] ?? [];
        $medicationFrequencies = $_POST['medication_frequency'] ?? [];

        $errors = [];

        if ($patientId <= 0) {
            $errors[] = "Veuillez sélectionner un patient.";
        }

        if ($instructions === '') {
            $errors[] = "Veuillez renseigner les instructions générales de l'ordonnance.";
        }

        $medications = [];
        $count = max(count($medicationNames), count($medicationStarts), count($medicationEnds));
        for ($i = 0; $i < $count; $i++) {
            $name = trim($medicationNames[$i] ?? '');
            $start = trim($medicationStarts[$i] ?? '');
            $end = trim($medicationEnds[$i] ?? '');
            $dosage = trim($medicationDosages[$i] ?? '');
            $frequency = trim($medicationFrequencies[$i] ?? '');

            if ($name === '' && $start === '' && $end === '' && $dosage === '' && $frequency === '') {
                continue;
            }

            if ($name === '') {
                $errors[] = "Chaque médicament doit avoir un nom.";
                continue;
            }

            $medications[] = [
                'name' => $name,
                'start_date' => $start ?: null,
                'end_date' => $end ?: null,
                'dosage' => $dosage ?: null,
                'frequency' => $frequency ?: null,
            ];
        }

        if (empty($medications)) {
            $errors[] = "Ajoutez au moins un médicament avec ses informations.";
        }

        if (!$this->isDoctorLinkedToPatient($patientId)) {
            $errors[] = "Ce patient n'est pas associé à votre suivi médical.";
        }

        if (!empty($errors)) {
            $_SESSION['ordonnance_errors'] = $errors;
            $_SESSION['ordonnance_old'] = $_POST;
            header('Location: /Project/public/index.php?page=medecin_ordonnances&action=new');
            exit();
        }

        $stmt = $this->db->prepare("
            INSERT INTO ordonnance (idPatient, idMedecin, date, contenu, medicaments, posologie, duree_traitement)
            VALUES (:patient, :doctor, NOW(), :contenu, :medicaments, :posologie, :duree)
        ");
        $stmt->execute([
            ':patient' => $patientId,
            ':doctor' => $this->doctorId,
            ':contenu' => $instructions,
            ':medicaments' => json_encode($medications, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':posologie' => $generalPosology !== '' ? $generalPosology : null,
            ':duree' => $treatmentDuration !== '' ? $treatmentDuration : null,
        ]);

        $prescriptionId = (int) $this->db->lastInsertId();

        $this->notifyPrescriptionCreated(
            $patientId,
            $prescriptionId,
            $medications,
            $treatmentDuration !== '' ? $treatmentDuration : null
        );

        $_SESSION['ordonnance_success'] = "Ordonnance créée et partagée avec le patient.";
        header('Location: /Project/public/index.php?page=medecin_ordonnances');
        exit();
    }

    private function getDoctorPatientsForSelection(): array {
        $stmt = $this->db->prepare('
            SELECT DISTINCT u.id,
                            CONCAT(u.prenom, " ", u.nom) AS full_name,
                            u.email
            FROM rendezvous r
            JOIN users u ON u.id = r.idPatient
            WHERE r.idMedecin = ?
            ORDER BY u.nom, u.prenom
        ');
        $stmt->execute([$this->doctorId]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(function ($patient) {
            $fullName = trim($patient['full_name'] ?? '');
            if ($fullName === '') {
                $fullName = $patient['email'] ?? 'Patient';
            }
            return [
                'id' => (int) ($patient['id'] ?? 0),
                'name' => $fullName,
                'email' => $patient['email'] ?? '',
            ];
        }, $patients);
    }

    private function getDoctorProfile(): array {
        if ($this->doctor !== null) {
            return $this->doctor;
        }

        $stmt = $this->db->prepare("
            SELECT u.id,
                   CONCAT(u.prenom, ' ', u.nom) AS nom_complet,
                   u.email,
                   m.specialite,
                   m.numero_ordre,
                   m.recrute_secretaire
            FROM users u
            JOIN medecin m ON m.id = u.id
            WHERE u.id = ?
        ");
        $stmt->execute([$this->doctorId]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doctor) {
            throw new RuntimeException("Profil médecin introuvable.");
        }

        $this->doctor = $doctor;
        return $doctor;
    }

    private function computeDashboardStats(): array {
        $stats = [
            'todayAppointments' => 0,
            'weekAppointments' => 0,
            'patientsFollowed' => 0,
            'pendingTasks' => 0,
        ];

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM rendezvous
            WHERE idMedecin = ?
              AND DATE(date) = CURDATE()
              AND statut IN ('planifie', 'confirme', 'termine')
        ");
        $stmt->execute([$this->doctorId]);
        $stats['todayAppointments'] = (int) $stmt->fetchColumn();

        $weekStart = (new DateTime('monday this week'))->setTime(0, 0, 0);
        $weekEnd = (clone $weekStart)->modify('+6 days')->setTime(23, 59, 59);

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM rendezvous
            WHERE idMedecin = ?
              AND date BETWEEN ? AND ?
              AND statut IN ('planifie', 'confirme', 'termine')
        ");
        $stmt->execute([$this->doctorId, $weekStart->format('Y-m-d H:i:s'), $weekEnd->format('Y-m-d H:i:s')]);
        $stats['weekAppointments'] = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT idPatient)
            FROM rendezvous
            WHERE idMedecin = ?
        ");
        $stmt->execute([$this->doctorId]);
        $stats['patientsFollowed'] = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM rendezvous
            WHERE idMedecin = ?
              AND statut = 'planifie'
        ");
        $stmt->execute([$this->doctorId]);
        $stats['pendingTasks'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    private function fetchTodayAppointments(): array {
        $stmt = $this->db->prepare("
            SELECT r.id,
                   TIME_FORMAT(r.date, '%H:%i') AS time,
                   r.motif,
                   r.statut,
                   CONCAT(u.prenom, ' ', u.nom) AS patient
            FROM rendezvous r
            JOIN users u ON u.id = r.idPatient
            WHERE r.idMedecin = ?
              AND DATE(r.date) = CURDATE()
            ORDER BY r.date ASC
        ");
        $stmt->execute([$this->doctorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchRecentActivities(): array {
        $activities = [];

        $stmt = $this->db->prepare("
            SELECT 'Consultation' AS type,
                   CONCAT(u.prenom, ' ', u.nom) AS title,
                   DATE_FORMAT(r.date, '%d/%m/%Y %H:%i') AS time,
                   CONCAT('Statut : ', r.statut) AS description
            FROM rendezvous r
            JOIN users u ON u.id = r.idPatient
            WHERE r.idMedecin = ?
            ORDER BY r.date DESC
            LIMIT 3
        ");
        $stmt->execute([$this->doctorId]);
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        $stmt = $this->db->prepare("
            SELECT 'Ordonnance' AS type,
                   CONCAT('Ordonnance pour ', u.prenom, ' ', u.nom) AS title,
                   DATE_FORMAT(o.date, '%d/%m/%Y') AS time,
                   'Prescription envoyée au patient.' AS description
            FROM ordonnance o
            JOIN users u ON u.id = o.idPatient
            WHERE o.idMedecin = ?
            ORDER BY o.date DESC
            LIMIT 2
        ");
        $stmt->execute([$this->doctorId]);
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        return $activities;
    }

    private function fetchNotifications(): array {
        $stmt = $this->db->prepare("
            SELECT titre AS title,
                   contenu AS message,
                   type,
                   DATE_FORMAT(date_creation, '%d/%m/%Y %H:%i') AS time
            FROM notification
            WHERE iduser = ?
            ORDER BY date_creation DESC
            LIMIT 5
        ");
        $stmt->execute([$this->doctorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function handleAgendaAction(string $action, int $requestId): void {
        $allowed = ['accept' => 'confirme', 'decline' => 'annule'];

        if (!isset($allowed[$action])) {
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE rendezvous
            SET statut = ?
            WHERE id = ? AND idMedecin = ?
        ");
        $stmt->execute([$allowed[$action], $requestId, $this->doctorId]);

        header('Location: /Project/public/index.php?page=medecin_agenda');
        exit();
    }

    private function buildAgendaSlots(string $date): array {
        $stmt = $this->db->prepare("
            SELECT r.id AS appointment_id,
                   TIME_FORMAT(r.date, '%H:%i') AS time,
                   CONCAT(u.prenom, ' ', u.nom) AS patient,
                   COALESCE(r.motif, 'Consultation') AS reason,
                   r.statut AS appointment_status,
                   DATE_FORMAT(r.date, '%d/%m/%Y %H:%i') AS scheduled_at,
                   f.id AS invoice_id,
                   f.montant AS invoice_amount,
                   f.statutPaiement AS invoice_status,
                   DATE_FORMAT(f.date, '%d/%m/%Y') AS invoice_date,
                   DATE_FORMAT(f.date_echeance, '%Y-%m-%d') AS invoice_due,
                   f.details AS invoice_details,
                   f.mode_paiement AS invoice_mode
            FROM rendezvous r
            JOIN users u ON u.id = r.idPatient
            LEFT JOIN facture f ON f.idRendezVous = r.id
            WHERE r.idMedecin = ?
              AND DATE(r.date) = ?
        ");
        $stmt->execute([$this->doctorId, $date]);
        $booked = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $invoice = null;
            if (!empty($row['invoice_id'])) {
                $invoice = [
                    'id' => (int) $row['invoice_id'],
                    'amount' => $row['invoice_amount'] !== null ? (float) $row['invoice_amount'] : null,
                    'status' => $row['invoice_status'] ?? null,
                    'date' => $row['invoice_date'] ?? null,
                    'due' => $row['invoice_due'] ?? null,
                    'details' => $row['invoice_details'] ?? '',
                    'mode' => $row['invoice_mode'] ?? 'especes',
                ];
            }

            $booked[$row['time']] = [
                'time' => $row['time'],
                'patient' => $row['patient'],
                'reason' => $row['reason'],
                'status' => 'booked',
                'appointment_id' => (int) $row['appointment_id'],
                'appointment_status' => $row['appointment_status'],
                'scheduled_at' => $row['scheduled_at'],
                'invoice' => $invoice,
            ];
        }

        $slots = [];
        $start = new DateTime($date . ' 08:00');
        $end = new DateTime($date . ' 17:00');
        $interval = new DateInterval('PT30M');

        for ($time = clone $start; $time < $end; $time->add($interval)) {
            $timeLabel = $time->format('H:i');

            if (isset($booked[$timeLabel])) {
                $slot = $booked[$timeLabel];
            } else {
                $slot = [
                    'time' => $timeLabel,
                    'patient' => null,
                    'reason' => null,
                    'status' => 'free',
                    'appointment_id' => null,
                    'appointment_status' => null,
                    'invoice' => null,
                ];
            }

            $slots[] = $slot;
        }

        return $slots;
    }

    private function fetchPaymentsSummary(): array {
        $start = (new DateTime('first day of this month'))->setTime(0, 0, 0);
        $end = (clone $start)->modify('last day of this month')->setTime(23, 59, 59);

        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN f.statutPaiement = 'paye' THEN f.montant ELSE 0 END) AS paid_total,
                SUM(CASE WHEN f.statutPaiement IN ('en_attente', 'retard') THEN f.montant ELSE 0 END) AS pending_total,
                SUM(CASE WHEN f.statutPaiement = 'paye' THEN 1 ELSE 0 END) AS paid_count,
                SUM(CASE WHEN f.statutPaiement IN ('en_attente', 'retard') THEN 1 ELSE 0 END) AS pending_count
            FROM facture f
            JOIN rendezvous r ON r.id = f.idRendezVous
            WHERE r.idMedecin = :doctor
              AND f.date BETWEEN :start AND :end
        ");
        $stmt->execute([
            ':doctor' => $this->doctorId,
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s'),
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'month_label' => $start->format('m/Y'),
            'paid_total' => (float) ($result['paid_total'] ?? 0),
            'pending_total' => (float) ($result['pending_total'] ?? 0),
            'paid_count' => (int) ($result['paid_count'] ?? 0),
            'pending_count' => (int) ($result['pending_count'] ?? 0),
        ];
    }

    private function fetchPaymentHistory(): array {
        $stmt = $this->db->prepare("
            SELECT f.id,
                   f.montant,
                   f.statutPaiement,
                   DATE_FORMAT(f.date, '%d/%m/%Y') AS created_at,
                   DATE_FORMAT(f.date_echeance, '%d/%m/%Y') AS due_date,
                   COALESCE(f.details, '') AS details,
                   COALESCE(f.mode_paiement, 'especes') AS mode,
                   CONCAT(u.prenom, ' ', u.nom) AS patient,
                   COALESCE(r.motif, 'Consultation') AS reason
            FROM facture f
            JOIN rendezvous r ON r.id = f.idRendezVous
            JOIN users u ON u.id = r.idPatient
            WHERE r.idMedecin = :doctor
            ORDER BY f.date DESC
            LIMIT 8
        ");
        $stmt->execute([':doctor' => $this->doctorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchUpcomingAppointments(): array {
        $stmt = $this->db->prepare("
            SELECT CONCAT(u.prenom, ' ', u.nom) AS patient,
                   DATE_FORMAT(r.date, '%d/%m/%Y') AS date,
                   TIME_FORMAT(r.date, '%H:%i') AS time,
                   COALESCE(r.motif, 'Consultation') AS reason
            FROM rendezvous r
            JOIN users u ON u.id = r.idPatient
            WHERE r.idMedecin = ?
              AND r.date >= NOW()
            ORDER BY r.date ASC
            LIMIT 6
        ");
        $stmt->execute([$this->doctorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchPendingRequests(): array {
        $stmt = $this->db->prepare("
            SELECT r.id,
                   CONCAT(u.prenom, ' ', u.nom) AS patient,
                   COALESCE(r.motif, 'Consultation') AS reason,
                   DATE_FORMAT(r.date, '%d/%m/%Y %H:%i') AS preferred_time
            FROM rendezvous r
            JOIN users u ON u.id = r.idPatient
            WHERE r.idMedecin = ?
              AND r.statut = 'planifie'
            ORDER BY r.date ASC
            LIMIT 6
        ");
        $stmt->execute([$this->doctorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchPatientsList(array $filters): array {
        $stmt = $this->db->prepare("
            SELECT u.id,
                   CONCAT(u.prenom, ' ', u.nom) AS name,
                   u.email,
                   MAX(r.date) AS last_visit,
                   COUNT(r.id) AS visits
            FROM users u
            JOIN patient p ON p.id = u.id
            JOIN rendezvous r ON r.idPatient = p.id
            WHERE r.idMedecin = ?
            GROUP BY u.id, u.prenom, u.nom, u.email
        ");
        $stmt->execute([$this->doctorId]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($patients as &$patient) {
            $patient['last_visit'] = $patient['last_visit'] ? (new DateTime($patient['last_visit']))->format('d/m/Y') : 'Jamais';
            $patient['status'] = $this->computePatientStatus($patient['last_visit']);
        }
        unset($patient);

        if ($filters['search']) {
            $search = mb_strtolower($filters['search']);
            $patients = array_filter($patients, function ($patient) use ($search) {
                return str_contains(mb_strtolower($patient['name']), $search)
                    || str_contains(mb_strtolower($patient['email']), $search);
            });
        }

        if ($filters['status'] !== 'all') {
            $patients = array_filter($patients, fn ($patient) => mb_strtolower($patient['status']) === mb_strtolower($filters['status']));
        }

        $sort = $filters['sort'] ?? 'recent';
        usort($patients, function ($a, $b) use ($sort) {
            return match ($sort) {
                'name' => strcmp($a['name'], $b['name']),
                'visits' => $b['visits'] <=> $a['visits'],
                default => strcmp($b['last_visit'], $a['last_visit']),
            };
        });

        return array_values($patients);
    }

    private function computePatientStatus(string $lastVisitFormatted): string {
        if ($lastVisitFormatted === 'Jamais') {
            return 'Inactive';
        }

        $lastVisit = DateTime::createFromFormat('d/m/Y', $lastVisitFormatted);
        if (!$lastVisit) {
            return 'Inactive';
        }

        $sixMonthsAgo = (new DateTime())->modify('-6 months');
        return $lastVisit >= $sixMonthsAgo ? 'Actif' : 'Inactive';
    }

    private function getPatientProfileForDoctor(int $patientId): ?array {
        if (!$this->isDoctorLinkedToPatient($patientId)) {
            return null;
        }

        $stmt = $this->db->prepare('
            SELECT u.id,
                   u.nom,
                   u.prenom,
                   u.email,
                   p.date_naissance,
                   p.adresse,
                   p.telephone,
                   p.assurance_maladie
            FROM users u
            JOIN patient p ON p.id = u.id
            WHERE u.id = ?
        ');
        $stmt->execute([$patientId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $fullName = trim(($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? ''));
        $data['full_name'] = $fullName !== '' ? $fullName : ($data['nom'] ?? 'Patient');
        $initials = mb_strtoupper(mb_substr($data['prenom'] ?? '', 0, 1) . mb_substr($data['nom'] ?? '', 0, 1));
        $data['initials'] = $initials !== '' ? $initials : 'P';

        if (!empty($data['date_naissance'])) {
            $birthDate = DateTime::createFromFormat('Y-m-d', $data['date_naissance']) ?: DateTime::createFromFormat('d/m/Y', $data['date_naissance']);
            if ($birthDate !== false) {
                $data['date_naissance_formatted'] = $birthDate->format('d/m/Y');
                $data['age'] = (new DateTime())->diff($birthDate)->y;
            }
        }

        return $data;
    }

    private function getPatientMedicalRecord(int $patientId): array {
        $stmt = $this->db->prepare('
            SELECT dm.observations,
                   dm.traitements,
                   dm.allergies,
                   dm.antecedents,
                   dm.date_creation,
                   dm.date_modification
            FROM dossiermedical dm
            WHERE dm.idPatient = ?
            ORDER BY dm.date_modification DESC, dm.date_creation DESC
            LIMIT 1
        ');
        $stmt->execute([$patientId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if (!empty($record['date_modification'])) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $record['date_modification'])
                ?: DateTime::createFromFormat('Y-m-d', $record['date_modification']);
            if ($date) {
                $record['last_update_formatted'] = $date->format('d/m/Y H:i');
            }
        } elseif (!empty($record['date_creation'])) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $record['date_creation'])
                ?: DateTime::createFromFormat('Y-m-d', $record['date_creation']);
            if ($date) {
                $record['last_update_formatted'] = $date->format('d/m/Y H:i');
            }
        }

        return $record;
    }

    private function getPatientUpcomingAppointments(int $patientId): array {
        $stmt = $this->db->prepare('
            SELECT r.id,
                   r.date,
                   r.statut,
                   COALESCE(r.motif, "Consultation") AS motif
            FROM rendezvous r
            WHERE r.idMedecin = ?
              AND r.idPatient = ?
              AND r.date >= NOW()
            ORDER BY r.date ASC
            LIMIT 5
        ');
        $stmt->execute([$this->doctorId, $patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['date']) ?: null;
            if ($date) {
                $row['date_formatted'] = $date->format('d/m/Y');
                $row['time_formatted'] = $date->format('H:i');
            }
        }
        unset($row);

        return $rows;
    }

    private function getPatientAppointmentHistory(int $patientId): array {
        $stmt = $this->db->prepare('
            SELECT r.id,
                   r.date,
                   r.statut,
                   COALESCE(r.motif, "Consultation") AS motif
            FROM rendezvous r
            WHERE r.idMedecin = ?
              AND r.idPatient = ?
              AND r.date < NOW()
            ORDER BY r.date DESC
            LIMIT 6
        ');
        $stmt->execute([$this->doctorId, $patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['date']) ?: null;
            if ($date) {
                $row['date_formatted'] = $date->format('d/m/Y');
                $row['time_formatted'] = $date->format('H:i');
            }
        }
        unset($row);

        return $rows;
    }

    private function getPatientOrdonnances(int $patientId): array {
        $stmt = $this->db->prepare('
            SELECT o.id,
                   o.date,
                   o.contenu,
                   o.medicaments,
                   o.posologie,
                   o.duree_traitement
            FROM ordonnance o
            WHERE o.idMedecin = ?
              AND o.idPatient = ?
            ORDER BY o.date DESC
            LIMIT 6
        ');
        $stmt->execute([$this->doctorId, $patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['date'])
                ?: DateTime::createFromFormat('Y-m-d', $row['date']);
            if ($date) {
                $row['date_formatted'] = $date->format('d/m/Y');
            }
            $row['medications'] = $this->decodeMedications($row['medicaments'] ?? '');
        }
        unset($row);

        return $rows;
    }

    private function buildMedicationsSummary(array $ordonnances): array {
        $medications = [];

        foreach ($ordonnances as $ordonnance) {
            $entries = $this->parseMedicamentsField($ordonnance);
            $medications = array_merge($medications, $entries);
        }

        return $medications;
    }

    private function parseMedicamentsField(array $ordonnance): array {
        $decoded = $this->decodeMedications($ordonnance['medicaments'] ?? '');

        if (!empty($decoded)) {
            return array_map(function ($med) use ($ordonnance) {
                return [
                    'label' => $med['name'] ?? ($med['label'] ?? ''),
                    'posologie' => $med['dosage'] ?? ($ordonnance['posologie'] ?? ''),
                    'frequency' => $med['frequency'] ?? null,
                    'start_date' => $med['start_date'] ?? null,
                    'end_date' => $med['end_date'] ?? null,
                    'date' => $ordonnance['date_formatted'] ?? null,
                    'duree_traitement' => $ordonnance['duree_traitement'] ?? null,
                ];
            }, $decoded);
        }

        return [];
    }

    private function isDoctorLinkedToPatient(int $patientId): bool {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM rendezvous
            WHERE idMedecin = ?
              AND idPatient = ?
        ');
        $stmt->execute([$this->doctorId, $patientId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function decodeMedications(?string $raw): array {
        $raw = $raw ?? '';
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map(function ($item) {
                if (!is_array($item)) {
                    return null;
                }

                $name = trim((string)($item['name'] ?? $item['label'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'start_date' => $item['start_date'] ?? null,
                    'end_date' => $item['end_date'] ?? null,
                    'dosage' => $item['dosage'] ?? ($item['posologie'] ?? null),
                    'frequency' => $item['frequency'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            }, $decoded)));
        }

        $items = preg_split('/\r?\n|,/', $raw) ?: [];
        $fallback = [];
        foreach ($items as $item) {
            $label = trim($item);
            if ($label === '') {
                continue;
            }
            $fallback[] = [
                'name' => $label,
                'start_date' => null,
                'end_date' => null,
                'dosage' => null,
                'frequency' => null,
            ];
        }

        return $fallback;
    }

    private function handleOrdonnanceAction(string $action, int $id): void {
        if ($action === 'renew') {
            $stmt = $this->db->prepare("
                UPDATE ordonnance
                SET date = NOW()
                WHERE id = ? AND idMedecin = ?
            ");
            $stmt->execute([$id, $this->doctorId]);
        }
        // Actions supplémentaires (view, delete, etc.) pourraient être implémentées ici.
    }

    private function fetchPrescriptions(array $filters): array {
        $conditions = ['o.idMedecin = :doctorId'];
        $params = [':doctorId' => $this->doctorId];

        if (!empty($filters['search'])) {
            $searchTerm = trim((string) $filters['search']);
            if ($searchTerm !== '') {
                $conditions[] = '(
                    u.nom LIKE :search_like
                    OR u.prenom LIKE :search_like
                    OR CONCAT(u.prenom, " ", u.nom) LIKE :search_like
                    OR CONCAT(u.nom, " ", u.prenom) LIKE :search_like
                    OR o.medicaments LIKE :search_like
                    OR o.contenu LIKE :search_like
                    OR CONCAT("RX-", LPAD(o.id, 4, "0")) LIKE :search_like
                )';
                $params[':search_like'] = '%' . $searchTerm . '%';

                if (ctype_digit($searchTerm)) {
                    $conditions[] = 'o.id = :search_id';
                    $params[':search_id'] = (int) $searchTerm;
                }
            }
        }

        $whereClause = implode(' AND ', $conditions);

        $stmt = $this->db->prepare("
            SELECT o.id,
                   o.date,
                   o.contenu,
                   o.medicaments,
                   o.posologie,
                   o.duree_traitement,
                   CONCAT(u.prenom, ' ', u.nom) AS patient
            FROM ordonnance o
            JOIN users u ON u.id = o.idPatient
            WHERE $whereClause
            ORDER BY o.date DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $today = new DateTime('today');
        $prescriptions = [];

        foreach ($rows as $row) {
            $date = new DateTime($row['date']);
            $medications = $this->decodeMedications($row['medicaments'] ?? '');

            $status = 'Active';

            $endDates = [];
            foreach ($medications as $medication) {
                if (!empty($medication['end_date'])) {
                    $end = DateTime::createFromFormat('Y-m-d', $medication['end_date'])
                        ?: DateTime::createFromFormat('d/m/Y', $medication['end_date']);
                    if ($end instanceof DateTime) {
                        $endDates[] = $end;
                    }
                }
            }

            if (!empty($endDates)) {
                usort($endDates, fn (DateTime $a, DateTime $b) => $a <=> $b);
                $latestEnd = end($endDates);
                if ($latestEnd instanceof DateTime) {
                    if ($today > $latestEnd) {
                        $status = 'Expirée';
                    } elseif ($today->diff($latestEnd)->days <= 7) {
                        $status = 'À renouveler';
                    }
                }
            } else {
                $monthsAgo = (new DateTime('today'))->modify('-6 months');
                $warningDate = (new DateTime('today'))->modify('-3 months');
                if ($date < $monthsAgo) {
                    $status = 'Expirée';
                } elseif ($date < $warningDate) {
                    $status = 'À renouveler';
                }
            }

            $prescriptions[] = [
                'id' => (int) $row['id'],
                'date' => $date->format('d/m/Y'),
                'date_iso' => $row['date'],
                'patient' => $row['patient'] ?? 'Patient',
                'reference' => sprintf('RX-%04d', $row['id']),
                'status' => $status,
                'medications' => $medications,
                'notes' => $row['contenu'] ?? '',
                'posologie' => $row['posologie'] ?? null,
                'duree_traitement' => $row['duree_traitement'] ?? null,
            ];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $statusFilter = mb_strtolower($filters['status']);
            $prescriptions = array_filter($prescriptions, fn ($prescription) => mb_strtolower($prescription['status']) === $statusFilter);
        }

        if (!empty($filters['period']) && $filters['period'] !== 'recent') {
            $threshold = match ($filters['period']) {
                'week' => (new DateTime('today'))->modify('-7 days'),
                'month' => (new DateTime('today'))->modify('-30 days'),
                default => null,
            };

            if ($threshold instanceof DateTime) {
                $prescriptions = array_filter($prescriptions, function ($prescription) use ($threshold) {
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $prescription['date_iso'])
                        ?: DateTime::createFromFormat('Y-m-d', $prescription['date_iso'])
                        ?: DateTime::createFromFormat('d/m/Y', $prescription['date']);
                    return $date instanceof DateTime && $date >= $threshold;
                });
            }
        }

        return array_values($prescriptions);
    }
}
