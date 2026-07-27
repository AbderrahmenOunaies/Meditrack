<?php
class Medecin{
    private $conn;
    private $table='medecin';
    private $userTable='users';
    //medecin
    public $id;
    public $specialite;
    public $numero_ordre;
    //user
    public $nom;
    public $prenom;
    public $email;
    public $motDePasse;
    public $role;
    public function __construct($db){
        $this->conn = $db;
    }
    //fonction create
    public function create(){
        //user
        $userStmt=$this->conn->prepare(
            "INSERT INTO {$this->userTable} (nom, prenom, email, motDePasse, role)
            VALUES (:nom, :prenom, :email, :motDePasse, :role)"
        );
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->motDePasse=htmlspecialchars(strip_tags($this->motDePasse));
        $this->role='medecin';
        $userStmt->bindParam(':nom',$this->nom);
        $userStmt->bindParam(':prenom',$this->prenom);
        $userStmt->bindParam(':email',$this->email);
        $userStmt->bindParam(':motDePasse',$this->motDePasse);
        $userStmt->bindParam(':role',$this->role);
        $userStmt->execute();
        $lastId=$this->conn->lastInsertId();
        //medecin
        $medStmt=$this->conn->prepare(
            "INSERT INTO {$this->table} (id, specialite, numero_ordre)
            VALUES (:id, :specialite, :numero_ordre)"
        );
        $this->id=$lastId;
        $this->specialite=htmlspecialchars(strip_tags($this->specialite));
        $this->numero_ordre=htmlspecialchars(strip_tags($this->numero_ordre));
        $medStmt->bindParam(':id',$this->id);
        $medStmt->bindParam(':specialite',$this->specialite);
        $medStmt->bindParam(':numero_ordre',$this->numero_ordre);
        return $medStmt->execute();
    }
    //read one
    public function readOne(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role, m.specialite, m.numero_ordre
             FROM {$this->userTable} u
             INNER JOIN {$this->table} m ON u.id=m.id
             WHERE u.id=:id"
        );
        $stmt->bindParam(':id',$this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    //read all
    public function readAll(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role, m.specialite, m.numero_ordre
            FROM {$this->userTable} u
            INNER JOIN {$this->table} m ON u.id=m.id"
        );
        $stmt->execute();
        return $stmt;
    }
    //fonction update
    public function update(){
        //user
        $userStmt=$this->conn->prepare(
            "UPDATE {$this->userTable} 
             SET nom=:nom, prenom=:prenom, email=:email, motDePasse=:motDePasse
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
        $userStmt->execute();
        //medecin
        $medStmt=$this->conn->prepare(
            "UPDATE {$this->table} 
             SET specialite=:specialite, numero_ordre=:numero_ordre
             WHERE id=:id"
        );
        $this->specialite=htmlspecialchars(strip_tags($this->specialite));
        $this->numero_ordre=htmlspecialchars(strip_tags($this->numero_ordre));
        $medStmt->bindParam(':specialite',$this->specialite);
        $medStmt->bindParam(':numero_ordre',$this->numero_ordre);
        $medStmt->bindParam(':id',$this->id);
        return $medStmt->execute();
    }
    //fonction delete
    public function delete(){
        //medecin
        $medStmt=$this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $medStmt->bindParam(':id',$this->id);
        $medStmt->execute();
        //user
        $userStmt = $this->conn->prepare("DELETE FROM {$this->userTable} WHERE id=:id");
        $userStmt->bindParam(':id',$this->id);
        return $userStmt->execute();
    }
}