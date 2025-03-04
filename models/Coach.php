<?php
// models/Coach.php

class Coach {
    private $conn;
    private $table_name = "coach";

    // Object properties
    public $id;
    public $name;
    public $email;
    public $age;
    public $level;
    public $performance_id;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // READ all coaches
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ one coach
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->age = $row['age'];
            $this->level = $row['level'];
            $this->performance_id = $row['performance_id'];
            return true;
        }
        return false;
    }

    // READ coach with performance details
    public function readWithPerformance() {
        $query = "SELECT c.*, p.time, p.speed, p.distance, p.date 
                FROM " . $this->table_name . " c
                LEFT JOIN performance p ON c.performance_id = p.id
                WHERE c.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt;
    }

    // CREATE coach
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, email, age, level, performance_id) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->level = htmlspecialchars(strip_tags($this->level));
        
        // Bind parameters
        $stmt->bindParam(1, $this->name);
        $stmt->bindParam(2, $this->email);
        $stmt->bindParam(3, $this->age);
        $stmt->bindParam(4, $this->level);
        $stmt->bindParam(5, $this->performance_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // UPDATE coach
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    name = ?, 
                    email = ?, 
                    age = ?, 
                    level = ?, 
                    performance_id = ?
                WHERE 
                    id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->level = htmlspecialchars(strip_tags($this->level));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(1, $this->name);
        $stmt->bindParam(2, $this->email);
        $stmt->bindParam(3, $this->age);
        $stmt->bindParam(4, $this->level);
        $stmt->bindParam(5, $this->performance_id);
        $stmt->bindParam(6, $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE coach
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
}
?>