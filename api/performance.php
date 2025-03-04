<?php
// api/performance.php

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and performance model
include_once '../config/database.php';
include_once '../models/Performance.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate performance object
$performance = new Performance($db);

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
        // Get performance ID from URL
        $performance_id = isset($_GET['id']) ? $_GET['id'] : null;
        
        // Get trainee ID from URL
        $trainee_id = isset($_GET['trainee_id']) ? $_GET['trainee_id'] : null;
        
        // Get metrics parameter
        $metrics = isset($_GET['metrics']) ? true : false;
        
        // Get improvement parameter
        $improvement = isset($_GET['improvement']) ? true : false;
        
        // If performance ID is provided, read one performance
        if ($performance_id) {
            $performance->id = $performance_id;
            
            if ($performance->readOne()) {
                $performance_arr = array(
                    "id" => $performance->id,
                    "trainee_id" => $performance->trainee_id,
                    "time" => $performance->time,
                    "speed" => $performance->speed,
                    "distance" => $performance->distance,
                    "date" => $performance->date
                );
                
                http_response_code(200);
                echo json_encode($performance_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Performance not found."));
            }
        }
        // If trainee ID is provided, read performances by trainee
        else if ($trainee_id) {
            $performance->trainee_id = $trainee_id;
            
            // If metrics parameter is set, get trainee metrics
            if ($metrics) {
                $stmt = $performance->getTraineeMetrics();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    http_response_code(200);
                    echo json_encode($row);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "No metrics found for this trainee."));
                }
            }
            // If improvement parameter is set, get improvement over time
            else if ($improvement) {
                $stmt = $performance->getImprovementOverTime();
                $num = $stmt->rowCount();
                
                if ($num > 0) {
                    $performance_arr = array();
                    $performance_arr["records"] = array();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        array_push($performance_arr["records"], $row);
                    }
                    
                    http_response_code(200);
                    echo json_encode($performance_arr);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "No improvement data found for this trainee."));
                }
            }
            // Otherwise, get all performances for the trainee
            else {
                $stmt = $performance->readByTrainee();
                $num = $stmt->rowCount();
                
                if ($num > 0) {
                    $performance_arr = array();
                    $performance_arr["records"] = array();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        
                        $performance_item = array(
                            "id" => $id,
                            "trainee_id" => $trainee_id,
                            "time" => $time,
                            "speed" => $speed,
                            "distance" => $distance,
                            "date" => $date
                        );
                        
                        array_push($performance_arr["records"], $performance_item);
                    }
                    
                    http_response_code(200);
                    echo json_encode($performance_arr);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "No performances found for this trainee."));
                }
            }
        }
        // Read all performances
        else {
            $stmt = $performance->read();
            $num = $stmt->rowCount();
            
            if ($num > 0) {
                $performance_arr = array();
                $performance_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $performance_item = array(
                        "id" => $id,
                        "trainee_id" => $trainee_id,
                        "time" => $time,
                        "speed" => $speed,
                        "distance" => $distance,
                        "date" => $date
                    );
                    
                    array_push($performance_arr["records"], $performance_item);
                }
                
                http_response_code(200);
                echo json_encode($performance_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No performances found."));
            }
        }
        break;
    
    case 'POST':
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Make sure data is not empty
        if (
            !empty($data->trainee_id) &&
            (!empty($data->time) || !empty($data->speed)) &&
            !empty($data->distance)
        ) {
            // Set performance property values
            $performance->trainee_id = $data->trainee_id;
            $performance->time = isset($data->time) ? $data->time : null;
            $performance->speed = isset($data->speed) ? $data->speed : null;
            $performance->distance = $data->distance;
            $performance->date = isset($data->date) ? $data->date : date('Y-m-d H:i:s');
            
            // Create performance
            $new_id = $performance->create();
            if ($new_id) {
                http_response_code(201);
                echo json_encode(array("message" => "Performance was created.", "id" => $new_id));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create performance."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create performance. Data is incomplete."));
        }
        break;
    
    case 'PUT':
        // Get performance ID from URL
        $performance_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Performance ID is required.")));
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Set ID to update
        $performance->id = $performance_id;
        
        // Check if performance exists
        if ($performance->readOne()) {
            // Set performance property values
            if(isset($data->trainee_id)) $performance->trainee_id = $data->trainee_id;
            if(isset($data->time)) $performance->time = $data->time;
            if(isset($data->speed)) $performance->speed = $data->speed;
            if(isset($data->distance)) $performance->distance = $data->distance;
            if(isset($data->date)) $performance->date = $data->date;
            
            // Update performance
            if ($performance->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Performance was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update performance."));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Performance not found."));
        }
        break;
    
    case 'DELETE':
        // Get performance ID from URL
        $performance_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Performance ID is required.")));
        
        // Set performance ID to be deleted
        $performance->id = $performance_id;
        
        // Delete performance
        if ($performance->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Performance was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete performance."));
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>