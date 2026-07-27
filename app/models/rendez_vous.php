<?php
class RendezVous{
    private $conn;
    private $table='rendezvous';
    public $id;
    public $idPatient;
    public $idMedecin;
    public $date;
    public $statut;
    public $motif;
    public $duree;
    public $date_creation;
    public function __construct($db){
        $this->conn=$db;
    }
    //fonction create
    public function create(){
        $stmt=$this->conn->prepare("INSERT INTO".$this->table."(idPatient, idMedecin, date, statut, motif, duree)
        VALUES(:idPatient, :idMedecin, :date, :statut, :motif, :duree)"
        );
        $safeDate=htmlspecialchars(strip_tags($this->date));
        $safeMotif=htmlspecialchars(strip_tags($this->motif));
        $safeStatut=htmlspecialchars(strip_tags($this->statut));
        $safeDuree=htmlspecialchars(strip_tags($this->duree));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':idMedecin',$this->idMedecin);
        $stmt->bindParam(':date',$safeDate);
        $stmt->bindParam(':statut',$safeStatut);
        $stmt->bindParam(':motif',$safeMotif);
        $stmt->bindParam(':duree',$safeDuree);
        return $stmt->execute();
    }
    //fonction read one
    public function readOne(){
        $stmt=$this->conn->prepare("SELECT rv.*, p.id AS id_patient, m.id AS id_medecin
        FROM rendezvous rv
        INNER JOIN patient p ON rv.idPatient=p.id
        INNER JOIN medecin m ON rv.idMedecin=m.id
        WHERE rv.id=:id"
        );
        $stmt->bindParam(':id',$this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    //fonction read all
    public function readAll(){
        $stmt=$this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt;
    }
    //fonction update
    public function update(){
        $stmt=$this->conn->prepare("UPDATE".$this->table."idPatient, idMedecin, date, statut, motif, duree)
        VALUES(:idPatient, :idMedecin, :date, :statut, :motif, :duree) WHERE id=:id"
        );
        $safeMotif=htmlspecialchars(strip_tags($this->motif));
        $safeStatut=htmlspecialchars(strip_tags($this->statut));
        $safeDuree=htmlspecialchars(strip_tags($this->duree));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':idMedecin',$this->idMedecin);
        $stmt->bindParam(':date',$this->date);
        $stmt->bindParam(':statut',$safeStatut);
        $stmt->bindParam(':motif',$safeMotif);
        $stmt->bindParam(':duree',$safeDuree);
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
    //fonction delete
    public function delete(){
        $stmt=$this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
}