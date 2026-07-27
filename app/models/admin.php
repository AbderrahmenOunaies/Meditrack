<?php
class Admin{
    private $conn;
    private $table='admin';
    private $userTable='user';
    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $motDePasse;
    public $role;
    public function __construct($db){
        $this->conn=$db;
    }
    public function create(){
        $userStmt=$this->conn->prepare(
            "INSERT INTO".$this->userTable."(nom, prenom, email, motDePasse, role)
            VALUES (:nom, :prenom, :email, :motDePasse, :role)"
        );
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->motDePasse=htmlspecialchars(strip_tags($this->motDePasse));
        $this->role='admin';
        $userStmt->bindParam(':nom',$this->nom);
        $userStmt->bindParam(':prenom',$this->prenom);
        $userStmt->bindParam(':email',$this->email);
        $userStmt->bindParam(':motDePasse',$this->motDePasse);
        $userStmt->bindParam(':role',$this->role);
        $userStmt->execute();
        $lastId=$this->conn->lastInsertId();
        $adminStmt=$this->conn->prepare(
            "INSERT INTO".$this->table."(id) VALUES(:id)"
        );
        $this->id=$lastId;
        $adminStmt->bindParam(':id',$this->id);
        return $adminStmt->execute();
    }
    public function readOne(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role
            FROM {$this->userTable} u
            INNER JOIN {$this->table} a ON u.id=a.id
            WHERE u.id=:id"
        );
        $stmt->bindParam(':id',$this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function readAll(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role
            FROM {$this->userTable} u
            INNER JOIN {$this->table} a ON u.id=a.id"
        );
        $stmt->execute();
        return $stmt;
    }
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
    public function delete(){
        $adminStmt=$this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $adminStmt->bindParam(':id',$this->id);
        $adminStmt->execute();
        $userStmt=$this->conn->prepare("DELETE FROM {$this->userTable} WHERE id=:id");
        $userStmt->bindParam(':id',$this->id);
        return $userStmt->execute();
    }
}