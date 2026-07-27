<?php
class Notification{
    private $conn;
    private $table='notification';
    public $id;
    public $idUser;
    public $titre;
    public $contenu;
    public $type;
    public $lien;
    public $lu;
    public $date_creation;
    public $date_lu;
    public function __construct($db){
        $this->conn=$db;
    }
    //fonction create
    public function create(){
        $stmt="INSERT INTO".$this->table."(idUser, titre, contenu, type, lien, lu, date_lu)
        Values(:idUser, :titre, :contenu, :type, :lien, :lu, :date_lu)";
        $safeTitre=htmlspecialchars(strip_tags($this->titre));
        $safeContenu=htmlspecialchars(strip_tags($this->contenu));
        $safeType=htmlspecialchars(strip_tags($this->type));
        $safeLien=htmlspecialchars(strip_tags($this->lien));
        $safeLu=htmlspecialchars(strip_tags($this->lu));
        $safeDate=htmlspecialchars(strip_tags($this->date_lu));
        $stmt->bindParam(':idUser',$this->idUser);
        $stmt->bindParam(':titre',$safeTitre);
        $stmt->bindParam(':contenu',$safeContenu);
        $stmt->bindParam(':type',$safeType);
        $stmt->bindParam(':lien',$safeLien);
        $stmt->bindParam(':lu',$safeLu);
        $stmt->bindParam(':date_lu',$safeDate);
        return $stmt->execute();
    }
    //fonction read one
    public function readOne(){
        $stmt=$this->conn->prepare("SELECT n.*, u.id AS id_user
        FROM notification n
        INNER JOIN user u ON n.idUser=u.id
        WHERE n.id=:id"
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
    public function update(){
        $stmt="UPDATE".$this->table."(titre, contenu, type, lien, lu, date_lu)
        Values(:titre, :contenu, :type, :lien, :lu, :date_lu)";
        $safeTitre=htmlspecialchars(strip_tags($this->titre));
        $safeContenu=htmlspecialchars(strip_tags($this->contenu));
        $safeType=htmlspecialchars(strip_tags($this->type));
        $safeLien=htmlspecialchars(strip_tags($this->lien));
        $safeLu=htmlspecialchars(strip_tags($this->lu));
        $safeDate=htmlspecialchars(strip_tags($this->date_lu));
        $stmt->bindParam(':idUser',$this->idUser);
        $stmt->bindParam(':titre',$safeTitre);
        $stmt->bindParam(':contenu',$safeContenu);
        $stmt->bindParam(':type',$safeType);
        $stmt->bindParam(':lien',$safeLien);
        $stmt->bindParam(':lu',$safeLu);
        $stmt->bindParam(':date_lu',$safeDate);
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