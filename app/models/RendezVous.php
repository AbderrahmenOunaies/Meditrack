<?php
class RendezVous{
    private $conn;
    private $table='rendezvous';
    public $id;
    public $idPatient;
    public $idMedecin;
    public $date;
    public $statut;
    public function __construct($db){
        $this->conn=$db;
    }
    //lire
    public function readAll(){
        $query= " SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt=$this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function create(){
        $stmt=$this->conn->prepare("Insert Into ".$this->table."(idPatient, idMedecin, date, statut) Values(:idPatient, :idMedecin, :date, :statut)");
        $this->idPatient=htmlspecialchars(strip_tags($this->idPatient));
        $this->idMedecin=htmlspecialchars(strip_tags($this->idMedecin));
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->statut=htmlspecialchars(strip_tags($this->statut));
        $stmt->bindParam(':idPatient', $this->idPatient);
        $stmt->bindParam(':idMedecin', $this->idMedecin);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':statut', $this->statut);
        return $stmt->execute();
    }
    //supprimer un produit
    public function delete(){
        $stmt=$this->conn->prepare("Delete From ".$this->table." Where id=:id");
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
}