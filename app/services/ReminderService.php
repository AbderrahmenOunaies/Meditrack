<?php

require_once __DIR__ . '/NotificationService.php';

class ReminderService
{
    private PDO $db;
    private NotificationService $notifier;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->notifier = new NotificationService($db);
    }

    public function runAll(): array
    {
        return [
            'appointments' => $this->sendUpcomingAppointmentReminders(),
            'invoices' => $this->sendInvoiceDueReminders(),
        ];
    }

    public function sendUpcomingAppointmentReminders(): array
    {
        $stmt = $this->db->prepare('
            SELECT rv.id,
                   rv.idPatient,
                   rv.idMedecin,
                   rv.date,
                   patient.prenom AS patient_prenom,
                   patient.nom AS patient_nom,
                   doctor.prenom AS doctor_prenom,
                   doctor.nom AS doctor_nom
            FROM rendezvous rv
            JOIN users patient ON patient.id = rv.idPatient
            JOIN users doctor ON doctor.id = rv.idMedecin
            WHERE rv.date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
              AND rv.statut IN ("planifie", "confirme")
        ');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $patientCount = 0;
        $doctorCount = 0;
        $secretaryCount = 0;

        foreach ($rows as $row) {
            $appointmentDate = DateTime::createFromFormat('Y-m-d H:i:s', $row['date'] ?? '') ?: null;
            $formattedDate = $this->formatDate($appointmentDate);
            $doctorName = $this->formatName($row['doctor_prenom'] ?? '', $row['doctor_nom'] ?? '', 'Dr');
            $patientName = $this->formatName($row['patient_prenom'] ?? '', $row['patient_nom'] ?? '');

            $patientLink = '/Project/public/index.php?page=patient_appointments&appointment=' . $row['id'];
            if (!$this->hasRecentReminder((int) $row['idPatient'], $patientLink, 'rappel')) {
                $this->notifier->notifyUser(
                    (int) $row['idPatient'],
                    'Rappel de rendez-vous',
                    sprintf('Vous avez un rendez-vous avec %s le %s.', $doctorName, $formattedDate),
                    'rappel',
                    $patientLink,
                    ['in_app', 'email']
                );
                $patientCount++;
            }

            $doctorLink = '/Project/public/index.php?page=medecin_agenda&appointment=' . $row['id'];
            if (!$this->hasRecentReminder((int) $row['idMedecin'], $doctorLink, 'rappel')) {
                $this->notifier->notifyUser(
                    (int) $row['idMedecin'],
                    'Consultation à venir',
                    sprintf('Consultation avec %s prévue le %s.', $patientName, $formattedDate),
                    'rappel',
                    $doctorLink,
                    ['in_app']
                );
                $doctorCount++;
            }

            $secretaryId = $this->getSecretaryIdForDoctor((int) $row['idMedecin']);
            if ($secretaryId && !$this->hasRecentReminder($secretaryId, $doctorLink, 'rappel')) {
                $this->notifier->notifyUser(
                    $secretaryId,
                    'Rappel agenda médecin',
                    sprintf('Préparez le rendez-vous de %s avec %s le %s.', $patientName, $doctorName, $formattedDate),
                    'rappel',
                    '/Project/public/index.php?page=secretaire_appointments',
                    ['in_app']
                );
                $secretaryCount++;
            }
        }

        return [
            'patients' => $patientCount,
            'doctors' => $doctorCount,
            'secretaires' => $secretaryCount,
        ];
    }

    public function sendInvoiceDueReminders(): array
    {
        $stmt = $this->db->prepare('
            SELECT f.id,
                   f.idPatient,
                   f.montant,
                   f.statutPaiement,
                   f.date_echeance,
                   patient.prenom,
                   patient.nom
            FROM facture f
            JOIN users patient ON patient.id = f.idPatient
            WHERE f.statutPaiement IN ("en_attente", "retard")
              AND f.date_echeance IS NOT NULL
              AND f.date_echeance <= DATE_ADD(CURDATE(), INTERVAL 2 DAY)
        ');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $reminders = 0;

        foreach ($rows as $row) {
            $dueDate = DateTime::createFromFormat('Y-m-d', $row['date_echeance'] ?? '') ?: null;
            $formattedDue = $dueDate ? $dueDate->format('d/m/Y') : 'bientôt';

            $link = '/Project/public/index.php?page=patient_dashboard#factures&invoice=' . $row['id'];
            if ($this->hasRecentReminder((int) $row['idPatient'], $link, 'facture')) {
                continue;
            }

            $message = $row['statutPaiement'] === 'retard'
                ? sprintf('Votre facture #%d est en retard depuis le %s. Merci de la régler.', $row['id'], $formattedDue)
                : sprintf('Votre facture #%d arrive à échéance le %s.', $row['id'], $formattedDue);

            $this->notifier->notifyUser(
                (int) $row['idPatient'],
                'Rappel de facture',
                $message,
                'facture',
                $link,
                ['in_app', 'email']
            );
            $reminders++;
        }

        return ['factures' => $reminders];
    }

    private function hasRecentReminder(int $userId, string $link, string $type): bool
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM notification
            WHERE iduser = :user
              AND type = :type
              AND lien = :link
              AND date_creation >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
        ');
        $stmt->execute([
            ':user' => $userId,
            ':type' => $type,
            ':link' => $link,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function getSecretaryIdForDoctor(int $doctorId): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM secretaire WHERE medecin_id = :doctor LIMIT 1');
        $stmt->execute([':doctor' => $doctorId]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function formatName(string $prenom, string $nom, string $prefix = ''): string
    {
        $full = trim($prenom . ' ' . $nom);
        if ($full === '') {
            $full = 'Patient';
        }

        if ($prefix !== '') {
            return trim($prefix . ' ' . $full);
        }

        return $full;
    }

    private function formatDate(?DateTime $date): string
    {
        return $date ? $date->format('d/m/Y à H\hi') : 'bientôt';
    }
}
