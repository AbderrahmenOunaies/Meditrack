<?php
class DossierMedical {
    private $conn;
    private $table = 'dossier_medical';
    
    public $id;
    public $id_patient;
    public $groupe_sanguin;
    public $allergies = [];
    public $antecedents = [];
    public $traitements = [];
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Récupérer le dossier médical d'un patient
    public function getByPatientId($patientId) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_patient = :id_patient";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_patient', $patientId);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $row['id'];
            $this->id_patient = $row['id_patient'];
            $this->groupe_sanguin = $row['groupe_sanguin'];
            
            // Récupérer les allergies
            $this->allergies = $this->getAllergies($this->id);
            
            // Récupérer les antécédents
            $this->antecedents = $this->getAntecedents($this->id);
            
            // Récupérer les traitements
            $this->traitements = $this->getTraitements($this->id);
            
            return $this;
        }
        
        return null;
    }
    
    // Récupérer les allergies d'un dossier médical
    private function getAllergies($dossierId) {
        $query = "SELECT * FROM allergies WHERE id_dossier = :id_dossier";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_dossier', $dossierId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupérer les antécédents d'un dossier médical
    private function getAntecedents($dossierId) {
        $query = "SELECT * FROM antecedents WHERE id_dossier = :id_dossier";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_dossier', $dossierId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupérer les traitements d'un dossier médical
    private function getTraitements($dossierId) {
        $query = "SELECT * FROM traitements WHERE id_dossier = :id_dossier AND date_fin >= CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_dossier', $dossierId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>