<?php


// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and trainee model
include_once '../config/database.php';
include_once '../models/Trainee.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight OPTIONS request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handle POST request for login
if ($method === 'POST') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"));
    
    // Check if email and password are set
    if (!empty($data->email) && !empty($data->password)) {
        // Instantiate trainee object
        $trainee = new Trainee($db);
        
        // Set properties
        $trainee->email = $data->email;
        $trainee->password = $data->password;
        
        // Attempt login
        if ($trainee->login()) {
            // Create simple token (in a production environment, use JWT)
            $token = bin2hex(random_bytes(16));
            
            // Return user data and token
            $user_data = array(
                "id" => $trainee->id,
                "name" => $trainee->name,
                "email" => $trainee->email,
                "token" => $token
            );
            
            http_response_code(200);
            echo json_encode($user_data);
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid credentials."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Email and password are required."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}
?>