<?php
class Message{
    private $conn;
    private $table='message';
    public $id;
    public $idExpediteur;
    public $idRecepteur;
    public $contenu;
    public $date;
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
        $stmt=$this->conn->prepare("Insert Into ".$this->table."(idExpediteur, idRecepteur, contenu, date) Values(:idExpediteur, :idRecepteur, :contenu, :date)");
        $this->idRecepteur=htmlspecialchars(strip_tags($this->idRecepteur));
        $this->idExpediteur=htmlspecialchars(strip_tags($this->idExpediteur));
        $this->contenu=htmlspecialchars(strip_tags($this->contenu));
        $this->date=htmlspecialchars(strip_tags($this->date));
        $stmt->bindParam(':idExpediteur', $this->idExpediteur);
        $stmt->bindParam(':idRecepteur', $this->idRecepteur);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':date', $this->date);
        return $stmt->execute();
    }
    //supprimer un produit
    public function delete(){
        $stmt=$this->conn->prepare("Delete From ".$this->table." Where id=:id");
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
}