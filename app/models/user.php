<?php
class User{
    private $conn;
    private $table='users';
    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $motDePasse;
    public $role;
    public $date_creation;
    public function __construct($db){
        $this->conn=$db;
    }
    //fonction CREATE
    public function create(){
        $stmt=$this->conn->prepare("INSERT INTO {$this->table} (nom, prenom, email, motDePasse, role)
        VALUES(:nom, :prenom, :email, :motDePasse, :role)");
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->motDePasse=htmlspecialchars(strip_tags($this->motDePasse));
        $this->role=htmlspecialchars(strip_tags($this->role));
        //la liaison
        $stmt->bindParam(':nom',$this->nom);
        $stmt->bindParam(':prenom',$this->prenom);
        $stmt->bindParam(':email',$this->email);
        $stmt->bindParam(':motDePasse',$this->motDePasse);
        $stmt->bindParam(':role',$this->role);
        return $stmt->execute();
    }
    //fonction readone
    public function readOne(){
        $stmt=$this->conn->prepare("SELECT * FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id',$this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    //fonction readall
    public function readAll(){
        $stmt=$this->conn->prepare("SELECT * FROM " . $this->table);
        $stmt->execute();
        return $stmt;
    }
    //fonction update
    public function update(){
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET nom=:nom, prenom=:prenom, email=:email, motDePasse=:motDePasse, role=:role
        WHERE id=:id");
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->motDePasse=htmlspecialchars(strip_tags($this->motDePasse));
        $this->role=htmlspecialchars(strip_tags($this->role));
        //la liaison
        $stmt->bindParam(':nom',$this->nom);
        $stmt->bindParam(':prenom',$this->prenom);
        $stmt->bindParam(':email',$this->email);
        $stmt->bindParam(':motDePasse',$this->motDePasse);
        $stmt->bindParam(':role',$this->role);
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
    //fonction delete
    public function delete(){
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id',$this->id);
        return $stmt->execute();
    }
}