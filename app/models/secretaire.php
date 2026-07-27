<?php
class Secretaire {
    private $conn;
    private $table='secretaire';
    private $userTable='user';
    //secretaire
    public $id;
    //user
    public $nom;
    public $prenom;
    public $email;
    public $motDePasse;
    public $role;
    public function __construct($db) {
        $this->conn = $db;
    }
    //fonctuion create
    public function create(){
        //user
        $userStmt=$this->conn->prepare(
            "INSERT INTO".$this->userTable."(nom, prenom, email, motDePasse, role)
            VALUES(:nom, :prenom, :email, :motDePasse, :role)"
        );
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->motDePasse=htmlspecialchars(strip_tags($this->motDePasse));
        $this->role='secretaire';
        $userStmt->bindParam(':nom',$this->nom);
        $userStmt->bindParam(':prenom',$this->prenom);
        $userStmt->bindParam(':email',$this->email);
        $userStmt->bindParam(':motDePasse',$this->motDePasse);
        $userStmt->bindParam(':role',$this->role);
        $userStmt->execute();
        $lastId = $this->conn->lastInsertId();
        //secretaire
        $secStmt=$this->conn->prepare(
            "INSERT INTO".$this->table."(id) VALUES(:id)"
        );
        $this->id=$lastId;
        $secStmt->bindParam(':id',$this->id);
        return $secStmt->execute();
    }
    //fonction read one
    public function readOne(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role
            FROM {$this->userTable} u
            INNER JOIN {$this->table} s ON u.id=s.id
            WHERE u.id=:id"
        );
        $stmt->bindParam(':id',$this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    //fonction read all
    public function readAll(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role
            FROM {$this->userTable} u
            INNER JOIN {$this->table} s ON u.id=s.id"
        );
        $stmt->execute();
        return $stmt;
    }
    //fonction update
    public function update(){
        $userStmt=$this->conn->prepare(
            "UPDATE".$this->userTable."(nom, prenom, email, motDePasse)
            VALUES(:nom,:prenom,:email,:motDePasse)
            WHERE id=:id"
        );
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->motDePasse=htmlspecialchars(strip_tags($this->motDePasse));
        $userStmt->bindParam(':nom',$this->nom);
        $userStmt->bindParam(':prenom',$this->prenom);
        $userStmt->bindParam(':email',$this->email);
        $userStmt->bindParam(':motDePasse',$this->motDePasse);
        $userStmt->bindParam(':id',$this->id);
        return $userStmt->execute();
    }
    //fonction delete
    public function delete(){
        //secretaite
        $secStmt=$this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $secStmt->bindParam(':id', $this->id);
        $secStmt->execute();
        //user
        $userStmt = $this->conn->prepare("DELETE FROM {$this->userTable} WHERE id = :id");
        $userStmt->bindParam(':id', $this->id);
        return $userStmt->execute();
    }
}