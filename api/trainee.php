<?php


// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and trainee model
include_once '../config/database.php';
include_once '../models/Trainee.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate trainee object
$trainee = new Trainee($db);

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
        // Get trainee ID from URL
        $trainee_id = isset($_GET['id']) ? $_GET['id'] : null;
        
        // Get 'with_performance' parameter
        $with_performance = isset($_GET['with_performance']) ? true : false;
        
        // If ID is provided, read one trainee
        if ($trainee_id) {
            $trainee->id = $trainee_id;
            
            if ($with_performance) {
                $stmt = $trainee->readWithPerformance();
                $num = $stmt->rowCount();
                
                if ($num > 0) {
                    $trainees_arr = array();
                    $trainees_arr["records"] = array();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        
                        $trainee_item = array(
                            "id" => $id,
                            "name" => $name,
                            "email" => $email,
                            "phone" => $phone,
                            "age" => $age,
                            "level" => $level,
                            "performance" => array(
                                "time" => $time,
                                "speed" => $speed,
                                "distance" => $distance,
                                "date" => $date
                            )
                        );
                        
                        array_push($trainees_arr["records"], $trainee_item);
                    }
                    
                    http_response_code(200);
                    echo json_encode($trainees_arr);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Trainee not found."));
                }
            } else {
                $result = $trainee->readOne();
                
                if ($result) {
                    $trainee_arr = array(
                        "id" => $trainee->id,
                        "name" => $trainee->name,
                        "email" => $trainee->email,
                        "phone" => $trainee->phone,
                        "age" => $trainee->age,
                        "level" => $trainee->level
                    );
                    
                    http_response_code(200);
                    echo json_encode($trainee_arr);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Trainee not found."));
                }
            }
        } 
        // Read all trainees
        else {
            $stmt = $trainee->read();
            $num = $stmt->rowCount();
            
            if ($num > 0) {
                $trainees_arr = array();
                $trainees_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $trainee_item = array(
                        "id" => $id,
                        "name" => $name,
                        "email" => $email,
                        "phone" => $phone,
                        "age" => $age,
                        "level" => $level
                    );
                    
                    array_push($trainees_arr["records"], $trainee_item);
                }
                
                http_response_code(200);
                echo json_encode($trainees_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No trainees found."));
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
            !empty($data->password)
        ) {
            // Set trainee property values
            $trainee->name = $data->name;
            $trainee->email = $data->email;
            $trainee->password = $data->password;
            $trainee->phone = isset($data->phone) ? $data->phone : "";
            $trainee->age = isset($data->age) ? $data->age : null;
            $trainee->level = isset($data->level) ? $data->level : "Beginner";
            
            // Create trainee
            if ($trainee->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Trainee was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create trainee."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create trainee. Data is incomplete."));
        }
        break;
    
    case 'PUT':
        // Get trainee ID from URL
        $trainee_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Trainee ID is required.")));
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Set ID to update
        $trainee->id = $trainee_id;
        
        // Check if trainee exists
        if ($trainee->readOne()) {
            // Set trainee property values
            if(isset($data->name)) $trainee->name = $data->name;
            if(isset($data->email)) $trainee->email = $data->email;
            if(isset($data->password)) $trainee->password = $data->password;
            if(isset($data->phone)) $trainee->phone = $data->phone;
            if(isset($data->age)) $trainee->age = $data->age;
            if(isset($data->level)) $trainee->level = $data->level;
            
            // Update trainee
            if ($trainee->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Trainee was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update trainee."));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Trainee not found."));
        }
        break;
    
    case 'DELETE':
        // Get trainee ID from URL
        $trainee_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Trainee ID is required.")));
        
        // Set trainee ID to be deleted
        $trainee->id = $trainee_id;
        
        // Delete trainee
        if ($trainee->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Trainee was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete trainee."));
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>