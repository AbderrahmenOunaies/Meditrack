<?php
class DossierMedical {
    private $conn;
    private $table='dossiermedical';
    public $id;
    public $idPatient;
    public $observations;
    public $traitements;
    public $allergies;
    public $antecedents;
    public $date_creation;
    public $date_modification;
    public function __construct($db){
        $this->conn = $db;
    }
    //fonction crate
    public function create(){
        $stmt=$this->conn->prepare("INSERT INTO".$this->table."(idPatient, observations, traitements, allergies, antecedents)
        VALUES(:idPatient, :observations, :traitements, :allergies, :antecedents)"
        );
        $safeObs=htmlspecialchars(strip_tags($this->observations));
        $safeTrt=htmlspecialchars(strip_tags($this->traitements));
        $safeAll=htmlspecialchars(strip_tags($this->allergies));
        $safeAnt=htmlspecialchars(strip_tags($this->antecedents));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':observations',$safeObs);
        $stmt->bindParam(':traitements',$safeTrt);
        $stmt->bindParam(':allergies',$safeAll);
        $stmt->bindParam(':antecedents',$safeAnt);
        return $stmt->execute();
    }
    //fonction readone
    public function readOne(){
        $stmt=$this->conn->prepare("SELECT dm.*, p.id AS id_patient
        FROM dossiermedical dm
        INNER JOIN patient p ON dm.idPatient=p.id
        WHERE dm.id=:id"
        );
        $stmt->bindParam(':id',$this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    //fonction reall
    public function readAll(){
        $stmt=$this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt;
    }
    //fonction update
    public function update(){
        $stmt=$this->conn->prepare("UPDATE".$this->table."(idPatient, observations, traitements, allergies, antecedents)
        VALUES(:idPatient, :observations, :traitements, :allergies, :antecedents) WHERE id=:id"
        );
        $safeObs=htmlspecialchars(strip_tags($this->observations));
        $safeTrt=htmlspecialchars(strip_tags($this->traitements));
        $safeAll=htmlspecialchars(strip_tags($this->allergies));
        $safeAnt=htmlspecialchars(strip_tags($this->antecedents));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':observations',$safeObs);
        $stmt->bindParam(':traitements',$safeTrt);
        $stmt->bindParam(':allergies',$safeAll);
        $stmt->bindParam(':antecedents',$safeAnt);
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
    //fonction delete
    public function delete(){
        $stmt=$this->conn->prepare("DELETE FROM {$this->table}WHERE id=:id");
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
}