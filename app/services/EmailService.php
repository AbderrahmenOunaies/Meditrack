<?php

class EmailService
{
    private PDO $db;
    private string $defaultFrom;

    public function __construct(PDO $db, string $defaultFrom = 'MediTrack <no-reply@meditrack.local>')
    {
        $this->db = $db;
        $this->defaultFrom = $defaultFrom;
    }

    public function send(string $to, string $subject, string $body, array $headers = [], bool $queueOnly = false): int
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return 0;
        }

        $headers = $this->normalizeHeaders($headers);
        $queueId = $this->queueEmail($to, $subject, $body, $headers);

        if ($queueOnly) {
            return $queueId;
        }

        $success = $this->sendMailTransport($to, $subject, $body, $headers['compiled']);
        $this->markQueueStatus($queueId, $success ? 'sent' : 'failed');

        return $queueId;
    }

    public function queue(string $to, string $subject, string $body, array $headers = []): int
    {
        return $this->send($to, $subject, $body, $headers, true);
    }

    public function processQueue(int $limit = 50): array
    {
        $stmt = $this->db->prepare('
            SELECT id, recipient, subject, body, headers
            FROM email_queue
            WHERE status IN ("queued", "failed")
            ORDER BY created_at ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $processed = 0;
        $sent = 0;
        foreach ($rows as $row) {
            $processed++;
            $headers = $this->hydrateHeaders($row['headers'] ?? null);
            $compiled = $this->compileHeaderString($headers);
            $headers['compiled'] = $compiled;

            $success = $this->sendMailTransport(
                $row['recipient'],
                $row['subject'],
                $row['body'],
                $headers['compiled']
            );
            $this->markQueueStatus((int) $row['id'], $success ? 'sent' : 'failed');
            if ($success) {
                $sent++;
            }
        }

        return [
            'checked' => $processed,
            'sent' => $sent,
            'failed' => $processed - $sent,
        ];
    }

    private function sendMailTransport(string $to, string $subject, string $body, string $compiledHeaders): bool
    {
        return @mail($to, $subject, $body, $compiledHeaders);
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = array_change_key_case($headers, CASE_LOWER);

        $from = $normalized['from'] ?? $this->defaultFrom;
        $replyTo = $normalized['reply-to'] ?? 'no-reply@meditrack.local';
        $contentType = $normalized['content-type'] ?? 'text/plain; charset=UTF-8';

        $compiled = $this->compileHeaderString([
            'from' => $from,
            'reply-to' => $replyTo,
            'content-type' => $contentType,
        ]);

        return [
            'from' => $from,
            'reply-to' => $replyTo,
            'content-type' => $contentType,
            'compiled' => $compiled,
        ];
    }

    private function hydrateHeaders(?string $json): array
    {
        $headers = [
            'from' => $this->defaultFrom,
            'reply-to' => 'no-reply@meditrack.local',
            'content-type' => 'text/plain; charset=UTF-8',
        ];

        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $headers['from'] = $decoded['from'] ?? $headers['from'];
                $headers['reply-to'] = $decoded['reply_to'] ?? $headers['reply-to'];
                $headers['content-type'] = $decoded['content_type'] ?? $headers['content-type'];
            }
        }

        return $headers;
    }

    private function compileHeaderString(array $headers): string
    {
        return implode("\r\n", [
            'From: ' . ($headers['from'] ?? $this->defaultFrom),
            'Reply-To: ' . ($headers['reply-to'] ?? 'no-reply@meditrack.local'),
            'Content-Type: ' . ($headers['content-type'] ?? 'text/plain; charset=UTF-8'),
        ]);
    }

    private function queueEmail(string $to, string $subject, string $body, array $headers): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO email_queue (recipient, subject, body, headers, status, created_at)
            VALUES (:recipient, :subject, :body, :headers, :status, NOW())
        ');

        $stmt->execute([
            ':recipient' => $to,
            ':subject' => $subject,
            ':body' => $body,
            ':headers' => json_encode([
                'from' => $headers['from'],
                'reply_to' => $headers['reply-to'],
                'content_type' => $headers['content-type'],
            ], JSON_UNESCAPED_UNICODE),
            ':status' => 'queued',
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function markQueueStatus(int $queueId, string $status): void
    {
        $stmt = $this->db->prepare('
            UPDATE email_queue
            SET status = :status,
                processed_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':status' => $status,
            ':id' => $queueId,
        ]);
    }
}
