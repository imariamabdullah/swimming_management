<?php

// Headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get and sanitize the request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = array_values(array_filter(explode('/', trim($request_uri, '/'))));

// Optional debugging (comment out in production)
// echo json_encode([
//     "request_uri" => $request_uri,
//     "segments" => $uri_segments
// ]);
// exit;

// Determine the target endpoint (assumes 'api' is the first segment)
$endpoint = null;
if (isset($uri_segments[0]) && $uri_segments[0] === 'api') {
    $endpoint = $uri_segments[1] ?? null;
} else {
    $endpoint = $uri_segments[0] ?? null;
}

// Route to the appropriate file
if ($endpoint) {
    $filename = _DIR_ . '/' . $endpoint . '.php';
    if (file_exists($filename)) {
        require_once $filename;
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Endpoint '$endpoint' not found."]);
    }
} else {
    // Default response for base URL
    http_response_code(200);
    echo json_encode(["message" => "Welcome to Swimming Management API"]);
}
