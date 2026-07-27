<?php

require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../services/EmailService.php';

class NotificationController
{
    private NotificationService $service;
    private int $userId;

    public function __construct(PDO $db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $this->respond(['error' => 'Non authentifié'], 401);
            exit();
        }

        $this->service = new NotificationService($db);
        $this->userId = (int) $_SESSION['user_id'];
    }

    public function list(): void
    {
        $limit = (int) ($_GET['limit'] ?? 10);
        $limit = max(1, min($limit, 50));

        $pageParam = $_GET['notif_page'] ?? null;
        if ($pageParam === null && isset($_GET['page']) && ctype_digit((string) $_GET['page'])) {
            $pageParam = $_GET['page'];
        }
        $page = max(1, (int) ($pageParam ?? 1));
        $offset = ($page - 1) * $limit;
        $type = $_GET['type'] ?? null;

        $notifications = $this->service->getNotifications($this->userId, $limit, $offset, $type ?: null);
        $unread = $this->service->getUnreadCount($this->userId);

        $this->respond([
            'notifications' => $notifications,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
            ],
            'unread' => $unread,
        ]);
    }

    public function badge(): void
    {
        $count = $this->service->getUnreadCount($this->userId);
        $this->respond(['unread' => $count]);
    }

    public function markRead(): void
    {
        $payload = $this->readJsonPayload();
        $notificationId = (int) ($payload['id'] ?? ($_POST['id'] ?? 0));

        if ($notificationId <= 0) {
            $this->respond(['error' => 'ID invalide'], 422);
            return;
        }

        $this->service->markAsRead($notificationId, $this->userId);
        $this->badge();
    }

    public function markAll(): void
    {
        $this->service->markAllAsRead($this->userId);
        $this->badge();
    }

    private function respond(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function readJsonPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
