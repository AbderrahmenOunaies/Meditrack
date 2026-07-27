<?php

require_once __DIR__ . '/../services/NotificationService.php';

class BillingController {
    private PDO $db;
    private ?int $userId;
    private ?string $userRole;
    private ?int $medecinId;
    private NotificationService $notifier;

    public function __construct(PDO $db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = $db;
        $this->userId = $_SESSION['user_id'] ?? null;
        $this->userRole = $_SESSION['user_role'] ?? null;
        $this->notifier = new NotificationService($db);

        if ($this->userRole === 'medecin') {
            $this->medecinId = $this->userId;
        } elseif ($this->userRole === 'secretaire') {
            $this->medecinId = $this->resolveMedecinIdForSecretaire();
        } else {
            $this->medecinId = null;
        }
    }

    // === Medecin actions ===
    public function create(): void {
        $this->requireRole('medecin');
        $patientId = (int) ($_GET['patient_id'] ?? 0);
        $rendezvousId = (int) ($_GET['rendezvous_id'] ?? 0);

        if ($patientId <= 0) {
            $_SESSION['billing_error'] = 'Patient invalide.';
            header('Location: /Project/public/index.php?page=medecin_patients');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeDraftFromMedecin($patientId, $rendezvousId);
            exit;
        }

        $patient = $this->fetchPatient($patientId);
        $rendezvous = $rendezvousId > 0 ? $this->fetchRendezvous($rendezvousId) : null;

        require_once __DIR__ . '/../views/Medecin/billing_create.php';
    }

    private function storeDraftFromMedecin(int $patientId, ?int $rendezvousId): void {
        $amount = filter_var($_POST['montant'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $details = trim($_POST['details'] ?? '');
        $dueDays = (int) ($_POST['echeance_jours'] ?? 7);
        $dateEcheance = (new DateTime())->add(new DateInterval("P{$dueDays}D"))->format('Y-m-d');

        $stmt = $this->db->prepare('
            INSERT INTO facture (idPatient, idRendezVous, montant, date_echeance, statutPaiement, details)
            VALUES (:patientId, :rvId, :montant, :echeance, "en_attente", :details)
        ');
        $stmt->execute([
            ':patientId' => $patientId,
            ':rvId' => $rendezvousId ?: null,
            ':montant' => $amount,
            ':echeance' => $dateEcheance,
            ':details' => $details,
        ]);

        $factureId = (int) $this->db->lastInsertId();

        // Notify secretaire that a draft is ready
        $this->notifier->notifyUser(
            $this->getSecretaireId(),
            'Nouvelle facture à valider',
            'Une facture a été créée par le médecin et nécessite votre validation.',
            'facture',
            "/Project/public/index.php?page=secretaire_billing&action=review&id={$factureId}"
        );

        $_SESSION['billing_success'] = 'Facture créée et envoyée au secrétariat.';
        header("Location: /Project/public/index.php?page=medecin_patients");
        exit;
    }

    // === Secretaire actions ===
    public function manage(): void {
        $this->requireRole('secretaire');
        $status = $_GET['status'] ?? 'all';
        $page = max(1, (int) ($_GET['p'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $invoices = $this->fetchInvoicesForSecretaire($status, $limit, $offset);
        $total = $this->countInvoicesForSecretaire($status);
        $pages = ceil($total / $limit);

        require_once __DIR__ . '/../views/Secretaire/billing_manage.php';
    }

    public function review(): void {
        $this->requireRole('secretaire');
        $invoiceId = (int) ($_GET['id'] ?? 0);
        $invoice = $this->fetchInvoiceById($invoiceId);

        if (!$invoice) {
            $_SESSION['billing_error'] = 'Facture introuvable.';
            header('Location: /Project/public/index.php?page=secretaire_billing');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processSecretaireUpdate($invoiceId);
            exit;
        }

        require_once __DIR__ . '/../views/Secretaire/billing_review.php';
    }

    private function processSecretaireUpdate(int $invoiceId): void {
        $action = $_POST['action'] ?? '';
        $amount = filter_var($_POST['montant'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $details = trim($_POST['details'] ?? '');
        $dueDays = (int) ($_POST['echeance_jours'] ?? 7);
        $dateEcheance = (new DateTime())->add(new DateInterval("P{$dueDays}D"))->format('Y-m-d');

        if ($action === 'issue') {
            $stmt = $this->db->prepare('
                UPDATE facture
                SET montant = :montant, details = :details, date_echeance = :echeance, statutPaiement = "en_attente"
                WHERE id = :id
            ');
            $stmt->execute([
                ':montant' => $amount,
                ':details' => $details,
                ':echeance' => $dateEcheance,
                ':id' => $invoiceId,
            ]);

            $invoice = $this->fetchInvoiceById($invoiceId);
            $this->notifier->notifyUser(
                $invoice['idPatient'],
                'Nouvelle facture émise',
                'Une nouvelle facture est disponible dans votre espace.',
                'facture',
                "/Project/public/index.php?page=patient_billing&action=view&id={$invoiceId}"
            );

            $_SESSION['billing_success'] = 'Facture émise et notifiée au patient.';
        } elseif ($action === 'cancel') {
            $stmt = $this->db->prepare('UPDATE facture SET statutPaiement = "annule" WHERE id = :id');
            $stmt->execute([':id' => $invoiceId]);

            $_SESSION['billing_success'] = 'Facture annulée.';
        }

        header('Location: /Project/public/index.php?page=secretaire_billing');
        exit;
    }

    // === Patient actions ===
    public function invoices(): void {
        $this->requireRole('patient');
        $status = $_GET['status'] ?? 'all';
        $page = max(1, (int) ($_GET['p'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $invoices = $this->fetchInvoicesForPatient($status, $limit, $offset);
        $total = $this->countInvoicesForPatient($status);
        $pages = ceil($total / $limit);

        require_once __DIR__ . '/../views/Patient/billing_invoices.php';
    }

    public function view(): void {
        $this->requireRole('patient');
        $invoiceId = (int) ($_GET['id'] ?? 0);
        $invoice = $this->fetchInvoiceById($invoiceId);

        if (!$invoice || $invoice['idPatient'] != $this->userId) {
            $_SESSION['billing_error'] = 'Facture introuvable.';
            header('Location: /Project/public/index.php?page=patient_billing');
            exit;
        }

        require_once __DIR__ . '/../views/Patient/billing_view.php';
    }

    public function pay(): void {
        $this->requireRole('patient');
        $invoiceId = (int) ($_POST['invoice_id'] ?? 0);
        $method = $_POST['mode_paiement'] ?? '';

        if ($invoiceId <= 0 || !in_array($method, ['especes', 'carte', 'virement'], true)) {
            $_SESSION['billing_error'] = 'Requête invalide.';
            header('Location: /Project/public/index.php?page=patient_billing');
            exit;
        }

        $invoice = $this->fetchInvoiceById($invoiceId);
        if (!$invoice || $invoice['idPatient'] != $this->userId || $invoice['statutPaiement'] !== 'en_attente') {
            $_SESSION['billing_error'] = 'Facture non payable.';
            header('Location: /Project/public/index.php?page=patient_billing');
            exit;
        }

        $stmt = $this->db->prepare('
            UPDATE facture
            SET statutPaiement = "paye", mode_paiement = :mode
            WHERE id = :id
        ');
        $stmt->execute([':mode' => $method, ':id' => $invoiceId]);

        $_SESSION['billing_success'] = 'Paiement enregistré. Merci !';
        header('Location: /Project/public/index.php?page=patient_billing');
        exit;
    }

    // === Helpers ===
    private function requireRole(string $role): void {
        if ($this->userRole !== $role) {
            http_response_code(403);
            echo 'Accès refusé';
            exit;
        }
    }

    private function fetchPatient(int $patientId): ?array {
        $stmt = $this->db->prepare('
            SELECT u.id, u.nom, u.prenom, u.email, p.telephone, p.assurance_maladie
            FROM users u
            LEFT JOIN patient p ON p.id = u.id
            WHERE u.id = :id AND u.role = "patient"
        ');
        $stmt->execute([':id' => $patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchRendezvous(int $rvId): ?array {
        $stmt = $this->db->prepare('
            SELECT r.id, DATE_FORMAT(r.date, "%d/%m/%Y %H:%i") AS formatted_date, r.motif
            FROM rendezvous r
            WHERE r.id = :id AND r.idMedecin = :medecinId
        ');
        $stmt->execute([':id' => $rvId, ':medecinId' => $this->medecinId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchInvoiceById(int $id): ?array {
        $stmt = $this->db->prepare('
            SELECT f.*, u.prenom, u.nom, u.email,
                   DATE_FORMAT(f.date, "%d/%m/%Y %H:%i") AS date_formatted,
                   DATE_FORMAT(f.date_echeance, "%d/%m/%Y") AS echeance_formatted
            FROM facture f
            JOIN users u ON u.id = f.idPatient
            WHERE f.id = :id
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchInvoicesForSecretaire(string $status, int $limit, int $offset): array {
        $sql = '
            SELECT f.*, u.prenom, u.nom, u.email,
                   DATE_FORMAT(f.date, "%d/%m/%Y %H:%i") AS date_formatted,
                   DATE_FORMAT(f.date_echeance, "%d/%m/%Y") AS echeance_formatted
            FROM facture f
            JOIN users u ON u.id = f.idPatient
        ';
        $params = [];

        if ($status !== 'all') {
            $sql .= ' WHERE f.statutPaiement = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY f.date DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countInvoicesForSecretaire(string $status): int {
        $sql = 'SELECT COUNT(*) FROM facture';
        $params = [];

        if ($status !== 'all') {
            $sql .= ' WHERE statutPaiement = :status';
            $params[':status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function fetchInvoicesForPatient(string $status, int $limit, int $offset): array {
        $sql = '
            SELECT f.*,
                   DATE_FORMAT(f.date, "%d/%m/%Y %H:%i") AS date_formatted,
                   DATE_FORMAT(f.date_echeance, "%d/%m/%Y") AS echeance_formatted
            FROM facture f
            WHERE f.idPatient = :patientId
        ';
        $params = [':patientId' => $this->userId];

        if ($status !== 'all') {
            $sql .= ' AND f.statutPaiement = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY f.date DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countInvoicesForPatient(string $status): int {
        $sql = 'SELECT COUNT(*) FROM facture WHERE idPatient = :patientId';
        $params = [':patientId' => $this->userId];

        if ($status !== 'all') {
            $sql .= ' AND statutPaiement = :status';
            $params[':status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function resolveMedecinIdForSecretaire(): int {
        $stmt = $this->db->prepare('SELECT medecin_id FROM secretaire WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $this->userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function getSecretaireId(): ?int {
        $stmt = $this->db->prepare('SELECT id FROM secretaire WHERE medecin_id = :medecinId LIMIT 1');
        $stmt->execute([':medecinId' => $this->medecinId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }
}
