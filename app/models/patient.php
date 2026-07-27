<?php
class Patient{
    public $conn;
    private $table='patient';
    private $userTable='users';
    //patient
    public $id;
    public $date_naissance;
    public $adresse;
    public $telephone;
    public $assurance_maladie;
    //user
    public $nom;
    public $prenom;
    public $email;
    public $motDePasse;
    public $role;
    public function __construct($db){
        $this->conn = $db;
    }
    //create
    public function create(){
        //user
        $userStmt=$this->conn->prepare(
            "INSERT INTO {$this->userTable}(nom, prenom, email, motDePasse, role)
            VALUES(:nom, :prenom, :email, :motDePasse, :role)"
        );
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->motDePasse=htmlspecialchars(strip_tags($this->motDePasse));
        $this->role='patient';
        $userStmt->bindParam(':nom',$this->nom);
        $userStmt->bindParam(':prenom',$this->prenom);
        $userStmt->bindParam(':email',$this->email);
        $userStmt->bindParam(':motDePasse',$this->motDePasse);
        $userStmt->bindParam(':role',$this->role);
        $userStmt->execute();
        $lastId = $this->conn->lastInsertId();
        //patient
        $patStmt=$this->conn->prepare(
            "INSERT INTO {$this->table}(id, date_naissance, adresse, telephone, assurance_maladie)
            VALUES(:id, :date_naissance, :adresse, :telephone, :assurance_maladie)"
        );
        $this->id=$lastId;
        $this->date_naissance=htmlspecialchars(strip_tags($this->date_naissance));
        $this->adresse=htmlspecialchars(strip_tags($this->adresse));
        $this->telephone=htmlspecialchars(strip_tags($this->telephone));
        $this->assurance_maladie=htmlspecialchars(strip_tags($this->assurance_maladie));
        $patStmt->bindParam(':id',$this->id);
        $patStmt->bindParam(':date_naissance',$this->date_naissance);
        $patStmt->bindParam(':adresse',$this->adresse);
        $patStmt->bindParam(':telephone',$this->telephone);
        $patStmt->bindParam(':assurance_maladie',$this->assurance_maladie);
        return $patStmt->execute();
    }
    //read all
    public function readAll(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role,
            p.date_naissance, p.adresse, p.telephone, p.assurance_maladie 
            FROM {$this->userTable} u
            INNER JOIN {$this->table} p ON u.id=p.id");
        $stmt->execute();
        return $stmt;
    }
    //read one
    public function readOne(){
        $stmt=$this->conn->prepare(
            "SELECT u.id, u.nom, u.prenom, u.email, u.motDePasse, u.role,
            p.date_naissance, p.adresse, p.telephone, p.assurance_maladie
            FROM {$this->userTable} u
            INNER JOIN {$this->table} p ON u.id=p.id
            WHERE u.id=:id"
        );
        $stmt->bindParam(':id',$this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    //fonction update
    public function update(){
        //user
        $userStmt=$this->conn->prepare(
            "UPDATE {$this->userTable} SET nom=:nom, prenom=:prenom, email=:email, motDePasse=:motDePasse WHERE id=:id"
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
        //patient
        $patStmt=$this->conn->prepare(
            "UPDATE {$this->table} SET date_naissance=:date_naissance, adresse=:adresse, telephone=:telephone, assurance_maladie=:assurance_maladie WHERE id=:id"
        );
        $this->date_naissance=htmlspecialchars(strip_tags($this->date_naissance));
        $this->adresse=htmlspecialchars(strip_tags($this->adresse));
        $this->telephone=htmlspecialchars(strip_tags($this->telephone));
        $this->assurance_maladie=htmlspecialchars(strip_tags($this->assurance_maladie));
        $patStmt->bindParam(':date_naissance',$this->date_naissance);
        $patStmt->bindParam(':adresse',$this->adresse);
        $patStmt->bindParam(':telephone',$this->telephone);
        $patStmt->bindParam(':assurance_maladie',$this->assurance_maladie);
        $patStmt->bindParam(':id',$this->id);
        return $patStmt->execute();
    }
    //fonction delete
    public function delete(){
        //patient
        $patStmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $patStmt->bindParam(':id',$this->id);
        $patStmt->execute();
        //user
        $userStmt=$this->conn->prepare("DELETE FROM {$this->userTable} WHERE id=:id");
        $userStmt->bindParam(':id',$this->id);
        return $userStmt->execute();
    }
}