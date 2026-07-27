<?php
class Ordonnance{
    private $conn;
    private $table='ordonnance';
    public $id;
    public $idPatient;
    public $idMedecin;
    public $date;
    public $contenu;
    public $medicaments;
    public $posologie;
    public $duree_traitement;
    // Constructeur avec connexion à la bd
    public function __construct($db){
        $this->conn=$db;
    }
    //fonction create
    public function create(){
        $stmt="INSERT INTO".$this->table."(idPatient, idMedecin, contenu, medicaments, posologie, duree_traitement)
        VALUES(:idPatient, :idMedecin, :contenu, :medicaments, :posologie, :duree_traitement)";
        $safeContenu=$this->cleanText($this->contenu);
        $safeMedicaments=$this->cleanText($this->medicaments);
        $safePosologie=$this->cleanText($this->posologie);
        $safeDuree=htmlspecialchars(strip_tags($this->duree_traitement));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':idMedecin',$this->idMedecin);
        $stmt->bindParam(':contenu',$safeContenu);
        $stmt->bindParamWithNull(':medicaments',$safeMedicaments);
        $stmt->bindParamWithNull(':posologie',$safePosologie);
        $stmt->bindParamWithNull(':duree_traitement',$safeDuree);
        return $stmt->execute();
    }
    //fonction read one
    public function readOne(){
        $stmt=$this->conn->prepare("SELECT o.*, p.id AS id_patient, m.id AS id_medecin
        FROM ordonnance o
        INNER JOIN patient p ON o.idPatient=p.id
        INNER JOIN medecin m ON o.idMedecin=m.id
        WHERE o.id=:id"
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
        $stmt="UPDATE".$this->table."(contenu, medicaments, posologie, duree_traitement)
        VALUES(:contenu, :medicaments, :posologie, :duree_traitement) WHERE id=:id";
        $safeContenu=$this->cleanText($this->contenu);
        $safeMedicaments=$this->cleanText($this->medicaments);
        $safePosologie=$this->cleanText($this->posologie);
        $safeDuree=htmlspecialchars(strip_tags($this->duree_traitement));
        $stmt->bindParam(':idPatient',$this->idPatient);
        $stmt->bindParam(':idMedecin',$this->idMedecin);
        $stmt->bindParam(':contenu',$safeContenu);
        $stmt->bindParamWithNull(':medicaments',$safeMedicaments);
        $stmt->bindParamWithNull(':posologie',$safePosologie);
        $stmt->bindParamWithNull(':duree_traitement',$safeDuree);
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