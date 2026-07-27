<?php

class AdminUserController
{
    private PDO $db;
    private const STATUS_ACTIVE = 'active';
    private const STATUS_PENDING = 'pending';
    private const STATUS_SUSPENDED = 'suspended';
    private const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_PENDING,
        self::STATUS_SUSPENDED,
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ensureAccountStatusColumn();
    }

    public function listUsers(array $filters): array
    {
        $query = 'SELECT id, nom, prenom, email, role, date_creation, account_status FROM users';
        $conditions = [];
        $bindings = [];

        $role = $filters['role'] ?? 'all';
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';

        if ($role !== 'all') {
            $conditions[] = 'role = :role';
            $bindings[':role'] = $role;
        }

        if ($status !== 'all') {
            $conditions[] = 'account_status = :status';
            $bindings[':status'] = $status;
        }

        if ($search !== '') {
            $conditions[] = '(nom LIKE :search OR prenom LIKE :search OR email LIKE :search)';
            $bindings[':search'] = '%' . $search . '%';
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY id DESC';

        $stmt = $this->db->prepare($query);
        $stmt->execute($bindings);

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? '')) ?: 'Utilisateur',
                'role' => $row['role'] ?? '—',
                'email' => $row['email'] ?? '',
                'status' => $row['account_status'] ?? self::STATUS_ACTIVE,
                'created_at' => $row['date_creation'] ?? '—',
            ];
        }

        return $users;
    }

    public function updateStatus(int $userId, string $status): array
    {
        $status = strtolower($status);
        if ($userId <= 0 || !in_array($status, self::ALLOWED_STATUSES, true)) {
            return ['type' => 'error', 'message' => "Statut ou utilisateur invalide."];
        }

        $stmt = $this->db->prepare('UPDATE users SET account_status = :status WHERE id = :id');
        $success = $stmt->execute([
            ':status' => $status,
            ':id' => $userId,
        ]);

        if (!$success || $stmt->rowCount() === 0) {
            return ['type' => 'error', 'message' => "Impossible de mettre à jour l'utilisateur."];
        }

        $labels = [
            self::STATUS_ACTIVE => 'réactivé',
            self::STATUS_PENDING => 'marqué en attente',
            self::STATUS_SUSPENDED => 'suspendu',
        ];

        return [
            'type' => 'success',
            'message' => "Utilisateur #{$userId} " . ($labels[$status] ?? 'mis à jour') . '.',
        ];
    }

    private function ensureAccountStatusColumn(): void
    {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'account_status'");
            $exists = $stmt && $stmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                return;
            }

            $this->db->exec(
                "ALTER TABLE users ADD COLUMN account_status ENUM('active','pending','suspended') DEFAULT 'active' AFTER role"
            );
        } catch (Throwable $e) {
            // Si la colonne existe déjà ou que l'altération échoue, on ignore l'erreur pour ne pas bloquer l'interface.
        }
    }
}
