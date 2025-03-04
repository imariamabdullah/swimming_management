<?php
// models/Performance.php

class Performance {
    private $conn;
    private $table_name = "performance";

    // Object properties
    public $id;
    public $trainee_id;
    public $time;
    public $speed;
    public $distance;
    public $date;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // READ all performances
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ one performance
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->trainee_id = $row['trainee_id'];
            $this->time = $row['time'];
            $this->speed = $row['speed'];
            $this->distance = $row['distance'];
            $this->date = $row['date'];
            return true;
        }
        return false;
    }

    // READ all performances for a trainee
    public function readByTrainee() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE trainee_id = ? ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->trainee_id);
        $stmt->execute();
        return $stmt;
    }

    // Get trainee performance metrics
    public function getTraineeMetrics() {
        $query = "SELECT 
                    AVG(time) as avg_time,
                    AVG(speed) as avg_speed,
                    AVG(distance) as avg_distance,
                    MAX(distance) as max_distance,
                    MIN(time) as best_time
                FROM " . $this->table_name . "
                WHERE trainee_id = ?";
                
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->trainee_id);
        $stmt->execute();
        return $stmt;
    }

    // CREATE performance
    public function create() {
        // If speed is not provided, calculate it
        if(empty($this->speed) && !empty($this->distance) && !empty($this->time)) {
            $this->speed = $this->distance / $this->time;
        }
        
        $query = "INSERT INTO " . $this->table_name . " (trainee_id, time, speed, distance, date) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->trainee_id = htmlspecialchars(strip_tags($this->trainee_id));
        $this->time = htmlspecialchars(strip_tags($this->time));
        $this->speed = htmlspecialchars(strip_tags($this->speed));
        $this->distance = htmlspecialchars(strip_tags($this->distance));
        
        // Use current date if not specified
        if(empty($this->date)) {
            $this->date = date('Y-m-d H:i:s');
        }
        
        // Bind parameters
        $stmt->bindParam(1, $this->trainee_id);
        $stmt->bindParam(2, $this->time);
        $stmt->bindParam(3, $this->speed);
        $stmt->bindParam(4, $this->distance);
        $stmt->bindParam(5, $this->date);
        
        // Execute query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // UPDATE performance
    public function update() {
        // If speed is not provided, calculate it
        if(empty($this->speed) && !empty($this->distance) && !empty($this->time)) {
            $this->speed = $this->distance / $this->time;
        }
        
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    trainee_id = ?, 
                    time = ?, 
                    speed = ?, 
                    distance = ?, 
                    date = ?
                WHERE 
                    id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->trainee_id = htmlspecialchars(strip_tags($this->trainee_id));
        $this->time = htmlspecialchars(strip_tags($this->time));
        $this->speed = htmlspecialchars(strip_tags($this->speed));
        $this->distance = htmlspecialchars(strip_tags($this->distance));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(1, $this->trainee_id);
        $stmt->bindParam(2, $this->time);
        $stmt->bindParam(3, $this->speed);
        $stmt->bindParam(4, $this->distance);
        $stmt->bindParam(5, $this->date);
        $stmt->bindParam(6, $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE performance
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
    
    // Get performance improvement over time
    public function getImprovementOverTime() {
        $query = "SELECT 
                    DATE_FORMAT(date, '%Y-%m') as month,
                    AVG(speed) as avg_speed,
                    AVG(time) as avg_time
                FROM " . $this->table_name . "
                WHERE trainee_id = ?
                GROUP BY DATE_FORMAT(date, '%Y-%m')
                ORDER BY month";
                
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->trainee_id);
        $stmt->execute();
        return $stmt;
    }
}
?>