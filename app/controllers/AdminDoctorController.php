<?php

require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../services/EmailService.php';

class AdminDoctorController {
    private PDO $db;
    private NotificationService $notifier;
    private EmailService $mailer;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->notifier = new NotificationService($db);
        $this->mailer = new EmailService($db);
    }

    public function listApplications(string $status = 'pending'): array {
        $query = "SELECT tm.*, CONCAT(u.prenom, ' ', u.nom) AS admin_name
                   FROM temp_medecin tm
                   LEFT JOIN admin a ON tm.admin_id = a.id
                   LEFT JOIN users u ON u.id = a.id
                   ORDER BY tm.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $applications = array_map(function (array $application): array {
            $application['raw_statut'] = $application['statut'] ?? null;
            $application['statut'] = $this->canonicalStatus($application['statut'] ?? 'pending');
            return $application;
        }, $applications);

        $filter = $this->canonicalStatus($status);
        if ($filter === 'all') {
            return $applications;
        }

        return array_values(array_filter($applications, function (array $application) use ($filter): bool {
            return $application['statut'] === $filter;
        }));
    }

    public function handleDecision(int $applicationId, string $action, int $adminId): array {
        if ($applicationId <= 0) {
            return ['type' => 'error', 'message' => "Candidature invalide."];
        }

        if (!in_array($action, ['approve', 'reject'], true)) {
            return ['type' => 'error', 'message' => "Action inconnue."];
        }

        $stmt = $this->db->prepare('SELECT * FROM temp_medecin WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $applicationId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            return ['type' => 'error', 'message' => "Candidature introuvable."];
        }

        $currentStatus = $this->canonicalStatus($application['statut'] ?? 'pending');
        if ($currentStatus !== 'pending') {
            return ['type' => 'error', 'message' => "Cette candidature a déjà été traitée."];
        }

        if ($action === 'reject') {
            $this->updateApplicationStatus($applicationId, 'rejected', $adminId);
            $this->sendOutcomeEmail($application, 'reject');
            $this->notifyDecision($application, 'rejected', $adminId);
            return ['type' => 'success', 'message' => "Candidature rejetée et email envoyé au candidat."];
        }

        if ($this->emailExists($application['email'])) {
            return ['type' => 'error', 'message' => "Impossible d'accepter : cet email est déjà utilisé dans MediTrack."];
        }

        try {
            $this->db->beginTransaction();

            $userInsert = $this->db->prepare(
                "INSERT INTO users (nom, prenom, email, motDePasse, role)
                 VALUES (:nom, :prenom, :email, :password, 'medecin')"
            );
            $userInsert->execute([
                ':nom' => $application['nom'],
                ':prenom' => $application['prenom'],
                ':email' => $application['email'],
                ':password' => $application['mot_de_passe'],
            ]);

            $userId = (int) $this->db->lastInsertId();

            $doctorInsert = $this->db->prepare(
                "INSERT INTO medecin (id, specialite, numero_ordre)
                 VALUES (:id, :specialite, :numero)"
            );
            $doctorInsert->execute([
                ':id' => $userId,
                ':specialite' => $application['specialite'] ?? null,
                ':numero' => $application['numero_ordre'] ?? null,
            ]);

            $this->updateApplicationStatus($applicationId, 'approved', $adminId);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            return ['type' => 'error', 'message' => "Erreur lors de la validation : " . $e->getMessage()];
        }

        $this->sendOutcomeEmail($application, 'approve');
        $this->notifyDecision($application, 'approved', $adminId, $userId);
        return ['type' => 'success', 'message' => "Candidature acceptée, compte créé et email envoyé."];
    }

    private function updateApplicationStatus(int $applicationId, string $status, int $adminId): void {
        $stmt = $this->db->prepare(
            "UPDATE temp_medecin
             SET statut = :status,
                 admin_id = :admin,
                 decision_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':status' => $status,
            ':admin' => $adminId,
            ':id' => $applicationId,
        ]);
    }

    private function emailExists(string $email): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function sendOutcomeEmail(array $application, string $action): void {
        $recipient = $application['email'] ?? '';
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $fullName = trim(($application['prenom'] ?? '') . ' ' . ($application['nom'] ?? ''));
        if ($action === 'approve') {
            $subject = 'Validation de votre compte Médecin MediTrack';
            $body = sprintf(
                "Bonjour %s,\n\nBonne nouvelle ! Votre candidature médecin a été approuvée par notre équipe." .
                "\nVous pouvez désormais vous connecter avec :\n- Email : %s\n- Mot de passe : celui que vous avez saisi lors de l'inscription\n\n" .
                "Pensez à compléter votre profil dès votre connexion.\n\nL'équipe MediTrack",
                $fullName,
                $recipient
            );
        } else {
            $subject = 'Suite à votre candidature Médecin MediTrack';
            $body = sprintf(
                "Bonjour %s,\n\nAprès étude de votre dossier, nous ne pouvons malheureusement pas donner suite à votre candidature." .
                "\nVous pouvez soumettre une nouvelle demande après mise à jour de vos pièces justificatives.\n\nL'équipe MediTrack",
                $fullName
            );
        }

        $this->mailer->send($recipient, $subject, $body);
    }

    private function notifyDecision(array $application, string $status, int $adminId, ?int $newUserId = null): void {
        $fullName = trim(($application['prenom'] ?? '') . ' ' . ($application['nom'] ?? '')) ?: 'Candidat';
        $adminName = $this->getUserDisplayName($adminId) ?: 'Un administrateur';

        if ($status === 'approved' && $newUserId) {
            $message = sprintf(
                "Votre candidature a été approuvée. Rendez-vous sur votre tableau de bord pour finaliser votre profil."
            );
            $this->notifier->notifyUser(
                $newUserId,
                'Votre compte Médecin est actif',
                $message,
                'systeme',
                '/Project/public/index.php?page=medecin_dashboard'
            );
        }

        $adminMessage = sprintf(
            "La candidature médecin de %s a été %s par %s.",
            $fullName,
            $status === 'approved' ? 'approuvée' : 'rejetée',
            $adminName
        );

        $this->notifier->notifyAdmins(
            'Décision sur une candidature Médecin',
            $adminMessage,
            'systeme',
            '/Project/public/index.php?page=admin_medecins',
            ['in_app']
        );
    }

    private function getUserDisplayName(int $userId): ?string {
        $stmt = $this->db->prepare('SELECT prenom, nom FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return null;
        }

        return trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
    }

    private function canonicalStatus(?string $status): string {
        if ($status === null) {
            return 'pending';
        }

        $status = strtolower(trim($status));
        if ($status === '') {
            return 'pending';
        }

        $status = strtr($status, [
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'à' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'î' => 'i',
            'ï' => 'i',
            'ô' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ç' => 'c',
        ]);

        $status = str_replace(["'", '-'], ' ', $status);
        $status = preg_replace('/\s+/', '_', $status);
        $status = trim($status, '_');

        $map = [
            'pending' => 'pending',
            'en_attente' => 'pending',
            'enattente' => 'pending',
            'attente' => 'pending',
            'approved' => 'approved',
            'approuve' => 'approved',
            'approuvee' => 'approved',
            'approuvees' => 'approved',
            'valide' => 'approved',
            'validees' => 'approved',
            'validee' => 'approved',
            'rejected' => 'rejected',
            'rejete' => 'rejected',
            'rejetee' => 'rejected',
            'refuse' => 'rejected',
            'refusee' => 'rejected',
            'refusees' => 'rejected',
            'all' => 'all',
        ];

        return $map[$status] ?? ($status !== '' ? $status : 'pending');
    }
}
