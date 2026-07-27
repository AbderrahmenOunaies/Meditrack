<?php
class Facture{
    private $conn;
    private $table='facture';
    public $id;
    public $idPatient;
    public $idRendezVous;
    public $montant;
    public $date;
    public $date_echeance;
    public $statutPaiement;
    public $details;
    public $modePaiement;
    public function __construct($db){
        $this->conn=$db;
    }
    //fonction create
    public function create(){
        $stmt="INSERT INTO".$this->table."(idPatient, idRendezVous, montant, date_echeance, statutPaiement, details, modePaiement)
        Values(:idPatient, :idRendezVous, :montant, :date_echeance, :statutPaiement, :details, :modePaiement)";
        $safeMontant=htmlspecialchars(strip_tags($this->montant));
        $safeDate=htmlspecialchars(strip_tags($this->date_echeance));
        $safeStatut=htmlspecialchars(strip_tags($this->statutPaiement));
        $safeDetails=htmlspecialchars(strip_tags($this->details));
        $safeMode=htmlspecialchars(strip_tags($this->modePaiement));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':idRendezVous',$this->idRendezVous);
        $stmt->bindParam(':montant',$safeMontant);
        $stmt->bindParam(':date_echeance',$safeDate);
        $stmt->bindParam(':statutPaiement',$safeStatut);
        $stmt->bindParam(':details',$safeDetails);
        $stmt->bindParam(':modePaiement',$safeMode);
        return $stmt->execute();
    }
    //fonction read one
    public function readOne(){
        $stmt=$this->conn->prepare("SELECT f.*, p.id AS id_patient, rv.id AS id_rendezvous
        FROM facture f
        INNER JOIN patient p ON f.idPatient=p.id
        INNER JOIN rendezvous rv ON f.idRendezVous=rv.id
        WHERE f.id=:id"
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
        $stmt="UPDATE".$this->table."(montant, date_echeance, statutPaiement, details, modePaiement)
        VALUES(:montant, :date_echeance, :statutPaiement, :details, :modePaiement) WHERE id=:id";
        $safeMontant=htmlspecialchars(strip_tags($this->montant));
        $safeDate=htmlspecialchars(strip_tags($this->date_echeance));
        $safeStatut=htmlspecialchars(strip_tags($this->statutPaiement));
        $safeDetails=htmlspecialchars(strip_tags($this->details));
        $safeMode=htmlspecialchars(strip_tags($this->modePaiement));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':idRendezVous',$this->idRendezVous);
        $stmt->bindParam(':montant',$safeMontant);
        $stmt->bindParam(':date_echeance',$safeDate);
        $stmt->bindParam(':statutPaiement',$safeStatut);
        $stmt->bindParam(':details',$safeDetails);
        $stmt->bindParam(':modePaiement',$safeMode);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    //fonction delete
    public function delete(){
        $stmt=$this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
}  