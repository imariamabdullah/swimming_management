<?php
// api/coach.php

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and coach model
include_once '../config/database.php';
include_once '../models/Coach.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate coach object
$coach = new Coach($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight OPTIONS request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get coach ID from URL
        $coach_id = isset($_GET['id']) ? $_GET['id'] : null;
        
        // Get 'with_performance' parameter
        $with_performance = isset($_GET['with_performance']) ? true : false;
        
        // If ID is provided, read one coach
        if ($coach_id) {
            $coach->id = $coach_id;
            
            if ($with_performance) {
                $stmt = $coach->readWithPerformance();
                $num = $stmt->rowCount();
                
                if ($num > 0) {
                    $coaches_arr = array();
                    $coaches_arr["records"] = array();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        
                        $coach_item = array(
                            "id" => $id,
                            "name" => $name,
                            "email" => $email,
                            "age" => $age,
                            "level" => $level,
                            "performance" => array(
                                "id" => $performance_id,
                                "time" => $time,
                                "speed" => $speed,
                                "distance" => $distance,
                                "date" => $date
                            )
                        );
                        
                        array_push($coaches_arr["records"], $coach_item);
                    }
                    
                    http_response_code(200);
                    echo json_encode($coaches_arr);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Coach not found."));
                }
            } else {
                $result = $coach->readOne();
                
                if ($result) {
                    $coach_arr = array(
                        "id" => $coach->id,
                        "name" => $coach->name,
                        "email" => $coach->email,
                        "age" => $coach->age,
                        "level" => $coach->level,
                        "performance_id" => $coach->performance_id
                    );
                    
                    http_response_code(200);
                    echo json_encode($coach_arr);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Coach not found."));
                }
            }
        } 
        // Read all coaches
        else {
            $stmt = $coach->read();
            $num = $stmt->rowCount();
            
            if ($num > 0) {
                
                                $coaches_arr = array();
                                $coaches_arr["records"] = array();
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    extract($row);
                                    
                                    $coach_item = array(
                                        "id" => $id,
                                        "name" => $name,
                                        "email" => $email,
                                        "age" => $age,
                                        "level" => $level,
                                        "performance_id" => $performance_id
                                    );
                                    
                                    array_push($coaches_arr["records"], $coach_item);
                                }
                                
                                http_response_code(200);
                                echo json_encode($coaches_arr);
                            } else {
                                http_response_code(404);
                                echo json_encode(array("message" => "No coaches found."));
                            }
                        }
                        break;
                    
                    case 'POST':
                        // Get posted data
                        $data = json_decode(file_get_contents("php://input"));
                        
                        // Make sure data is not empty
                        if (
                            !empty($data->name) &&
                            !empty($data->email) &&
                            !empty($data->age) &&
                            !empty($data->level)
                        ) {
                            // Set coach property values
                            $coach->name = $data->name;
                            $coach->email = $data->email;
                            $coach->age = $data->age;
                            $coach->level = $data->level;
                            $coach->performance_id = isset($data->performance_id) ? $data->performance_id : null;
                            
                            // Create coach
                            if ($coach->create()) {
                                http_response_code(201);
                                echo json_encode(array("message" => "Coach was created."));
                            } else {
                                http_response_code(503);
                                echo json_encode(array("message" => "Unable to create coach."));
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode(array("message" => "Unable to create coach. Data is incomplete."));
                        }
                        break;
                    
                    case 'PUT':
                        // Get coach ID from URL
                        $coach_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Coach ID is required.")));
                        
                        // Get posted data
                        $data = json_decode(file_get_contents("php://input"));
                        
                        // Set ID to update
                        $coach->id = $coach_id;
                        
                        // Make sure data is not empty
                        if (
                            !empty($data->name) &&
                            !empty($data->email) &&
                            !empty($data->age) &&
                            !empty($data->level)
                        ) {
                            // Set coach property values
                            $coach->name = $data->name;
                            $coach->email = $data->email;
                            $coach->age = $data->age;
                            $coach->level = $data->level;
                            $coach->performance_id = isset($data->performance_id) ? $data->performance_id : null;
                            
                            // Update coach
                            if ($coach->update()) {
                                http_response_code(200);
                                echo json_encode(array("message" => "Coach was updated."));
                            } else {
                                http_response_code(503);
                                echo json_encode(array("message" => "Unable to update coach."));
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode(array("message" => "Unable to update coach. Data is incomplete."));
                        }
                        break;
                    
                    case 'DELETE':
                        // Get coach ID from URL
                        $coach_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Coach ID is required.")));
                        
                        // Set coach ID to be deleted
                        $coach->id = $coach_id;
                        
                        // Delete coach
                        if ($coach->delete()) {
                            http_response_code(200);
                            echo json_encode(array("message" => "Coach was deleted."));
                        } else {
                            http_response_code(503);
                            echo json_encode(array("message" => "Unable to delete coach."));
                        }
                        break;
                    
                    default:
                        http_response_code(405);
                        echo json_encode(array("message" => "Method not allowed."));
                        break;
                }
                ?>