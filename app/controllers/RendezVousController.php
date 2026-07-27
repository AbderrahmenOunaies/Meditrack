<?php
class RendezVousController {
    private $rendezVousModel;
    private $patientModel;
    private $medecinModel;

    public function __construct() {
        require_once __DIR__ . '/../models/rendez_vous.php';
        require_once __DIR__ . '/../models/patient.php';
        require_once __DIR__ . '/../models/medecin.php';
        $this->rendezVousModel = new RendezVous();
        $this->patientModel = new Patient();
        $this->medecinModel = new Medecin();
    }

    public function index() {
        $rendezVous = $this->rendezVousModel->getAll();
        require_once __DIR__ . '/../views/rendezvous/liste.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'patient_id' => $_POST['patient_id'],
                'medecin_id' => $_POST['medecin_id'],
                'date_heure' => $_POST['date_heure'],
                'motif' => $_POST['motif'],
                'statut' => 'planifie' // Par défaut, le rendez-vous est planifié
            ];
            
            if ($this->rendezVousModel->create($data)) {
                // Envoyer une notification au patient
                $this->envoyerNotificationRendezVous($data);
                header('Location: /rendez-vous');
                exit();
            }
        } else {
            $patients = $this->patientModel->getAllPatients();
            $medecins = $this->medecinModel->getAll();
            require_once __DIR__ . '/../views/rendezvous/ajouter.php';
        }
    }

    public function show($id) {
        $rendezVous = $this->rendezVousModel->getById($id);
        require_once __DIR__ . '/../views/rendezvous/detail.php';
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'patient_id' => $_POST['patient_id'],
                'medecin_id' => $_POST['medecin_id'],
                'date_heure' => $_POST['date_heure'],
                'motif' => $_POST['motif'],
                'statut' => $_POST['statut']
            ];
            
            if ($this->rendezVousModel->update($id, $data)) {
                header('Location: /rendez-vous/' . $id);
                exit();
            }
        } else {
            $rendezVous = $this->rendezVousModel->getById($id);
            $patients = $this->patientModel->getAllPatients();
            $medecins = $this->medecinModel->getAll();
            require_once __DIR__ . '/../views/rendezvous/modifier.php';
        }
    }

    public function delete($id) {
        if ($this->rendezVousModel->delete($id)) {
            header('Location: /rendez-vous');
            exit();
        }
    }

    public function calendrier() {
        // Récupérer les rendez-vous au format pour le calendrier
        $rendezVous = $this->rendezVousModel->getAll();
        $events = [];
        
        foreach ($rendezVous as $rdv) {
            $events[] = [
                'title' => 'RDV ' . $rdv['patient_nom'] . ' - ' . $rdv['medecin_nom'],
                'start' => $rdv['date_heure'],
                'url' => '/rendez-vous/' . $rdv['id']
            ];
        }
        
        require_once __DIR__ . '/../views/rendezvous/calendrier.php';
    }

    private function envoyerNotificationRendezVous($data) {
        // Implémentez ici l'envoi de notification (email, SMS, etc.)
        // Exemple avec email :
        /*
        $to = $this->patientModel->getEmailById($data['patient_id']);
        $subject = 'Nouveau rendez-vous médical';
        $message = 'Votre rendez-vous a été programmé pour le ' . $data['date_heure'];
        mail($to, $subject, $message);
        */
    }
}
?>
