<?php

require_once __DIR__ . '/EmailService.php';

class NotificationService
{
    private PDO $db;
    private EmailService $mailer;
    private const NOTIFICATION_TYPES = ['rendez_vous', 'ordonnance', 'facture', 'rappel', 'systeme'];
    private const DIGEST_INTERVALS = [
        'daily' => 'P1D',
        'weekly' => 'P7D',
    ];

    public function __construct(PDO $db, ?EmailService $mailer = null)
    {
        $this->db = $db;
        $this->mailer = $mailer ?? new EmailService($db);
    }

    /**
     * Notifie un ensemble d'utilisateurs (id ou rôle)
     */
    public function notify(array $recipients, string $title, string $message, string $type = 'systeme', ?string $link = null, array $channels = ['in_app', 'email']): void
    {
        $userIds = $this->resolveRecipients($recipients);
        foreach ($userIds as $userId) {
            $this->notifyUser((int) $userId, $title, $message, $type, $link, $channels);
        }
    }

    public function notifyUser(int $userId, string $title, string $message, string $type = 'systeme', ?string $link = null, array $channels = ['in_app', 'email']): void
    {
        $settings = $this->getUserSettings($userId);
        if (in_array('in_app', $channels, true) && $this->shouldSendThroughChannel($settings, $type, 'in_app')) {
            $stmt = $this->db->prepare('
                INSERT INTO notification (iduser, titre, contenu, type, lien, lu, date_creation)
                VALUES (:user, :title, :message, :type, :link, 0, NOW())
            ');

            $stmt->execute([
                ':user' => $userId,
                ':title' => $title,
                ':message' => $message,
                ':type' => $type,
                ':link' => $link,
            ]);
        }

        if (!in_array('email', $channels, true) || !$this->shouldSendThroughChannel($settings, $type, 'email')) {
            return;
        }

        if (($settings['digest_frequency'] ?? 'none') !== 'none') {
            // email sera envoyée via la prochaine exécution du digest
            return;
        }

        $user = $this->fetchUser($userId);
        if ($user && !empty($user['email'])) {
            $subject = sprintf('[MediTrack] %s', $title);
            $body = $this->buildEmailBody($user['prenom'] ?? '', $message, $link);
            $this->mailer->send($user['email'], $subject, $body);
        }
    }

    public function notifyRole(string $role, string $title, string $message, string $type = 'systeme', ?string $link = null, array $channels = ['in_app', 'email']): void
    {
        $this->notify([['role' => $role]], $title, $message, $type, $link, $channels);
    }

    public function notifyRoles(array $roles, string $title, string $message, string $type = 'systeme', ?string $link = null, array $channels = ['in_app', 'email']): void
    {
        $recipients = array_map(fn ($role) => ['role' => $role], $roles);
        $this->notify($recipients, $title, $message, $type, $link, $channels);
    }

    public function notifyAdmins(string $title, string $message, string $type = 'systeme', ?string $link = null, array $channels = ['in_app', 'email']): void
    {
        $this->notifyRole('admin', $title, $message, $type, $link, $channels);
    }

    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notification WHERE iduser = :user AND lu = 0');
        $stmt->execute([':user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function getNotifications(int $userId, int $limit = 10, int $offset = 0, ?string $type = null): array
    {
        $sql = '
            SELECT id, titre, contenu, type, lien, lu,
                   DATE_FORMAT(date_creation, "%d/%m/%Y %H:%i") AS date_formattee
            FROM notification
            WHERE iduser = :user
        ';
        $params = [':user' => $userId];

        if ($type) {
            $sql .= ' AND type = :type';
            $params[':type'] = $type;
        }

        $sql .= ' ORDER BY date_creation DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        if ($type) {
            $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        $stmt = $this->db->prepare('
            UPDATE notification
            SET lu = 1,
                date_lu = NOW()
            WHERE id = :id AND iduser = :user
        ');
        $stmt->execute([
            ':id' => $notificationId,
            ':user' => $userId,
        ]);
    }

    public function markAllAsRead(int $userId): void
    {
        $stmt = $this->db->prepare('
            UPDATE notification
            SET lu = 1,
                date_lu = NOW()
            WHERE iduser = :user AND lu = 0
        ');
        $stmt->execute([':user' => $userId]);
    }

    private function resolveRecipients(array $recipients): array
    {
        $userIds = [];
        foreach ($recipients as $recipient) {
            if (is_int($recipient)) {
                $userIds[] = $recipient;
                continue;
            }

            if (is_array($recipient) && isset($recipient['role'])) {
                $stmt = $this->db->prepare('SELECT id FROM users WHERE role = :role');
                $stmt->execute([':role' => $recipient['role']]);
                $roleIds = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $userIds = array_merge($userIds, array_map('intval', $roleIds));
            }
        }

        return array_values(array_unique($userIds));
    }

    public function getUserSettings(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT channel_email, channel_inapp, digest_frequency, preferences FROM user_notification_settings WHERE user_id = :user LIMIT 1');
        $stmt->execute([':user' => $userId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$settings) {
            $settings = [
                'channel_email' => 1,
                'channel_inapp' => 1,
                'digest_frequency' => 'none',
                'preferences' => null,
            ];
            $this->createDefaultSettings($userId);
        }

        $settings['channel_email'] = (int) ($settings['channel_email'] ?? 1);
        $settings['channel_inapp'] = (int) ($settings['channel_inapp'] ?? 1);
        $settings['digest_frequency'] = $settings['digest_frequency'] ?? 'none';
        $settings['preferences'] = $this->decodePreferences($settings['preferences']);

        return $settings;
    }

    public function updateUserSettings(int $userId, array $payload): array
    {
        $settings = $this->getUserSettings($userId);
        $settings['channel_email'] = isset($payload['channel_email']) ? (int) (bool) $payload['channel_email'] : $settings['channel_email'];
        $settings['channel_inapp'] = isset($payload['channel_inapp']) ? (int) (bool) $payload['channel_inapp'] : $settings['channel_inapp'];

        $digest = $payload['digest_frequency'] ?? $settings['digest_frequency'] ?? 'none';
        if (!in_array($digest, ['none', 'daily', 'weekly'], true)) {
            $digest = 'none';
        }
        $settings['digest_frequency'] = $digest;

        $typePreferences = $settings['preferences']['types'] ?? [];
        foreach (self::NOTIFICATION_TYPES as $type) {
            $typePreferences[$type]['in_app'] = isset($payload['types'][$type]['in_app'])
                ? (bool) $payload['types'][$type]['in_app']
                : ($typePreferences[$type]['in_app'] ?? true);
            $typePreferences[$type]['email'] = isset($payload['types'][$type]['email'])
                ? (bool) $payload['types'][$type]['email']
                : ($typePreferences[$type]['email'] ?? true);
        }

        $settings['preferences']['types'] = $typePreferences;
        $settings['preferences']['digest'] = $settings['preferences']['digest'] ?? [];

        $stmt = $this->db->prepare('
            INSERT INTO user_notification_settings (user_id, channel_email, channel_inapp, digest_frequency, preferences)
            VALUES (:user, :email, :inapp, :digest, :preferences)
            ON DUPLICATE KEY UPDATE
                channel_email = VALUES(channel_email),
                channel_inapp = VALUES(channel_inapp),
                digest_frequency = VALUES(digest_frequency),
                preferences = VALUES(preferences),
                updated_at = CURRENT_TIMESTAMP
        ');
        $stmt->execute([
            ':user' => $userId,
            ':email' => $settings['channel_email'],
            ':inapp' => $settings['channel_inapp'],
            ':digest' => $settings['digest_frequency'],
            ':preferences' => json_encode($settings['preferences'], JSON_UNESCAPED_UNICODE),
        ]);

        return $settings;
    }

    public function getAvailableTypes(): array
    {
        return self::NOTIFICATION_TYPES;
    }

    public function sendDueDigests(?string $frequencyFilter = null): array
    {
        $params = [];
        $sql = 'SELECT user_id, digest_frequency, preferences FROM user_notification_settings WHERE digest_frequency != "none"';
        if ($frequencyFilter && in_array($frequencyFilter, array_keys(self::DIGEST_INTERVALS), true)) {
            $sql .= ' AND digest_frequency = :frequency';
            $params[':frequency'] = $frequencyFilter;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $sent = 0;
        foreach ($rows as $row) {
            $userId = (int) $row['user_id'];
            $frequency = $row['digest_frequency'];
            $preferences = $this->decodePreferences($row['preferences']);

            if (!$this->isDigestDue($preferences, $frequency)) {
                continue;
            }

            $windowStart = $this->computeDigestWindowStart($preferences, $frequency);
            $notifications = $this->fetchNotificationsForDigest($userId, $windowStart);
            if (empty($notifications)) {
                $this->markDigestRun($userId, $preferences, $frequency);
                continue;
            }

            $user = $this->fetchUser($userId);
            if (!$user || empty($user['email'])) {
                $this->markDigestRun($userId, $preferences, $frequency);
                continue;
            }

            $body = $this->buildDigestEmailBody($user, $notifications, $frequency);
            $subject = sprintf('[MediTrack] Résumé %s de vos notifications', $frequency === 'daily' ? 'quotidien' : 'hebdomadaire');
            $this->mailer->send($user['email'], $subject, $body);
            $this->markDigestRun($userId, $preferences, $frequency);
            $sent++;
        }

        return ['digests_sent' => $sent];
    }

    private function shouldSendThroughChannel(array $settings, string $type, string $channel): bool
    {
        if ($channel === 'email' && (int) ($settings['channel_email'] ?? 1) === 0) {
            return false;
        }
        if ($channel === 'in_app' && (int) ($settings['channel_inapp'] ?? 1) === 0) {
            return false;
        }

        $preferences = $settings['preferences']['types'][$type] ?? null;
        if ($preferences === null) {
            return true;
        }

        return (bool) ($preferences[$channel === 'email' ? 'email' : 'in_app'] ?? true);
    }

    private function getUserSettingsRow(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT user_id, channel_email, channel_inapp, digest_frequency, preferences FROM user_notification_settings WHERE user_id = :user LIMIT 1');
        $stmt->execute([':user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function decodePreferences($preferences): array
    {
        if (empty($preferences)) {
            return [
                'types' => [],
                'digest' => [],
            ];
        }

        if (is_string($preferences)) {
            $decoded = json_decode($preferences, true);
            if (is_array($decoded)) {
                $preferences = $decoded;
            }
        }

        if (!is_array($preferences)) {
            $preferences = [];
        }

        $preferences['types'] = $preferences['types'] ?? [];
        $preferences['digest'] = $preferences['digest'] ?? [];

        return $preferences;
    }

    private function createDefaultSettings(int $userId): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO user_notification_settings (user_id, channel_email, channel_inapp, digest_frequency, preferences)
            VALUES (:user, 1, 1, "none", :preferences)
            ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP
        ');
        $stmt->execute([
            ':user' => $userId,
            ':preferences' => json_encode(['types' => [], 'digest' => []], JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function isDigestDue(array $preferences, string $frequency): bool
    {
        $lastRun = $preferences['digest']['last_' . $frequency] ?? null;
        if (!$lastRun) {
            return true;
        }
        $lastDate = DateTime::createFromFormat('Y-m-d H:i:s', $lastRun);
        if (!$lastDate) {
            return true;
        }

        $interval = new DateInterval(self::DIGEST_INTERVALS[$frequency]);
        $nextRun = (clone $lastDate)->add($interval);

        return new DateTime() >= $nextRun;
    }

    private function computeDigestWindowStart(array $preferences, string $frequency): DateTime
    {
        $lastRun = $preferences['digest']['last_' . $frequency] ?? null;
        if ($lastRun) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $lastRun);
            if ($date) {
                return $date;
            }
        }

        $now = new DateTime();
        $interval = new DateInterval(self::DIGEST_INTERVALS[$frequency]);

        return $now->sub($interval);
    }

    private function fetchNotificationsForDigest(int $userId, DateTime $since): array
    {
        $stmt = $this->db->prepare('
            SELECT titre, contenu, type, lien, DATE_FORMAT(date_creation, "%d/%m/%Y %H:%i") AS date_formattee
            FROM notification
            WHERE iduser = :user AND date_creation >= :since
            ORDER BY date_creation DESC
        ');
        $stmt->execute([
            ':user' => $userId,
            ':since' => $since->format('Y-m-d H:i:s'),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function buildDigestEmailBody(array $user, array $notifications, string $frequency): string
    {
        $greeting = ($user['prenom'] ?? '') !== '' ? 'Bonjour ' . $user['prenom'] . ',' : 'Bonjour,';
        $intro = $frequency === 'daily'
            ? "Voici le récapitulatif de vos notifications des dernières 24 heures :"
            : "Voici le récapitulatif de vos notifications de la semaine :";

        $lines = array_map(function ($notif) {
            $title = $notif['titre'] ?? 'Notification';
            $message = $notif['contenu'] ?? '';
            $date = $notif['date_formattee'] ?? '';
            $link = $notif['lien'] ?? null;
            $line = sprintf("- [%s] %s", $date, $title);
            if ($message !== '') {
                $line .= ' — ' . $message;
            }
            if ($link) {
                $line .= ' (' . $link . ')';
            }
            return $line;
        }, $notifications);

        $body = $greeting . "\n\n" . $intro . "\n\n" . implode("\n", $lines);
        $body .= "\n\nPour modifier vos préférences : " . '/Project/public/index.php?page=notification_settings';
        $body .= "\n\n--\nEquipe MediTrack";

        return $body;
    }

    private function markDigestRun(int $userId, array $preferences, string $frequency): void
    {
        $preferences['digest']['last_' . $frequency] = (new DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare('
            UPDATE user_notification_settings
            SET preferences = :preferences, updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user
        ');
        $stmt->execute([
            ':preferences' => json_encode($preferences, JSON_UNESCAPED_UNICODE),
            ':user' => $userId,
        ]);
    }

    private function fetchUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, email, prenom, nom FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    private function buildEmailBody(string $firstName, string $message, ?string $link): string
    {
        $greeting = $firstName !== '' ? "Bonjour $firstName," : 'Bonjour,';
        $body = $greeting . "\n\n" . $message;

        if ($link) {
            $body .= "\n\nAccéder : " . $link;
        }

        $body .= "\n\n--\nEquipe MediTrack";

        return $body;
    }
}
