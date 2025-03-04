<?php
// api/index.php

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get the URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $request_uri);

// Routes handling
if (isset($uri_segments[2])) {
    switch ($uri_segments[2]) {
        case 'coach':
            require_once 'coach.php';
            break;
        case 'trainee':
            require_once 'trainee.php';
            break;
        case 'performance':
            require_once 'performance.php';
            break;
        case 'auth':
            require_once 'auth.php';
            break;
        default:
            // Invalid endpoint
            http_response_code(404);
            echo json_encode(array("message" => "Endpoint not found."));
            break;
    }
} else {
    // Welcome message
    http_response_code(200);
    echo json_encode(array("message" => "Welcome to Swimming Management API"));
}
?>