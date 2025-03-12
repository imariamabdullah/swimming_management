<?php


class Trainee {
    private $conn;
    private $table_name = "trainee";

    // Object properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $age;
    public $level;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // READ all trainees
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ one trainee
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->age = $row['age'];
            $this->level = $row['level'];
            return true;
        }
        return false;
    }

    // READ trainee with performance details
    public function readWithPerformance() {
        $query = "SELECT t.*, p.time, p.speed, p.distance, p.date 
                FROM " . $this->table_name . " t
                LEFT JOIN performance p ON p.trainee_id = t.id
                WHERE t.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt;
    }

    // CREATE trainee
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, email, password, phone, age, level) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and hash password
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->level = htmlspecialchars(strip_tags($this->level));
        
        // Bind parameters
        $stmt->bindParam(1, $this->name);
        $stmt->bindParam(2, $this->email);
        $stmt->bindParam(3, $hashed_password);
        $stmt->bindParam(4, $this->phone);
        $stmt->bindParam(5, $this->age);
        $stmt->bindParam(6, $this->level);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // UPDATE trainee
    public function update() {
        // If password is included in update
        if(!empty($this->password)) {
            $query = "UPDATE " . $this->table_name . " 
                    SET 
                        name = ?, 
                        email = ?, 
                        password = ?,
                        phone = ?,
                        age = ?, 
                        level = ?
                    WHERE 
                        id = ?";
                        
            $stmt = $this->conn->prepare($query);
            
            // Sanitize and hash password
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $this->phone = htmlspecialchars(strip_tags($this->phone));
            $this->age = htmlspecialchars(strip_tags($this->age));
            $this->level = htmlspecialchars(strip_tags($this->level));
            $this->id = htmlspecialchars(strip_tags($this->id));
            
            // Bind parameters
            $stmt->bindParam(1, $this->name);
            $stmt->bindParam(2, $this->email);
            $stmt->bindParam(3, $hashed_password);
            $stmt->bindParam(4, $this->phone);
            $stmt->bindParam(5, $this->age);
            $stmt->bindParam(6, $this->level);
            $stmt->bindParam(7, $this->id);
        }
        // If password is not included in update
        else {
            $query = "UPDATE " . $this->table_name . " 
                    SET 
                        name = ?, 
                        email = ?, 
                        phone = ?,
                        age = ?, 
                        level = ?
                    WHERE 
                        id = ?";
                        
            $stmt = $this->conn->prepare($query);
            
            // Sanitize input
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->phone = htmlspecialchars(strip_tags($this->phone));
            $this->age = htmlspecialchars(strip_tags($this->age));
            $this->level = htmlspecialchars(strip_tags($this->level));
            $this->id = htmlspecialchars(strip_tags($this->id));
            
            // Bind parameters
            $stmt->bindParam(1, $this->name);
            $stmt->bindParam(2, $this->email);
            $stmt->bindParam(3, $this->phone);
            $stmt->bindParam(4, $this->age);
            $stmt->bindParam(5, $this->level);
            $stmt->bindParam(6, $this->id);
        }
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE trainee
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize id
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameter
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login trainee
    public function login() {
        $query = "SELECT id, name, email, password FROM " . $this->table_name . " WHERE email = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize email
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind parameter
        $stmt->bindParam(1, $this->email);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            // Verify password
            if(password_verify($this->password, $row['password'])) {
                // Set object properties
                $this->id = $row['id'];
                $this->name = $row['name'];
                return true;
            }
        }
        return false;
    }
}
?>