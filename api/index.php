<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROUTE', realpath(dirname(__FILE__)) . DS); 

require 'vendor/autoload.php';

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Exception;

use Firebase\JWT\JWT;

// JWT secret key 
$jwtSecret = 'your_secret_key';

// Routes protected by JWT
$protectedRoutes = ['/products', '/customers'];

// Create an instance of the HTTP server
$http = new Server("0.0.0.0", 5000, SWOOLE_BASE);

$http->on("start", function ($server) {
  echo "Swoole http server is started at http://127.0.0.1:5000\n";   
});

// Define a route for handling GET requests API
$http->on('request', function (Request $request, Response $response) 
  use ($jwtSecret, $protectedRoutes) 
{
  $path = $request->server['request_uri'];
 
  // Checks if the route is protected
  if (in_array($path, $protectedRoutes)) {
    // Checks if the JWT token is present in the Authorization header
    $authorizationHeader = $request->header['authorization'] ?? null;
    if (!$authorizationHeader) {
      $response->status(401);
      $response->end(json_encode(['error' => 'JWT token not provided']));
      return;
    }

    // Checks if the JWT token is valid
    [$tokenType, $token] = explode(' ', $authorizationHeader);
    if ($tokenType !== 'Bearer') {
      $response->status(401);
      $response->end(json_encode(['error' => 'Invalid JWT token']));
      return;
    }

    try {
      // Decodes the JWT token
      $decodedToken = JWT::decode($token, $jwtSecret, ['HS256']);
    } catch (Exception $e) {
      $response->status(401);
      $response->end(json_encode(['error' => 'Invalid JWT token']));
      return;
    }
  }
  
  // Logic to handle requests for routes
  switch ($request->server['request_uri']) {
    case '/products':
      $response->header("Content-Type", "application/json");
      $response->end(json_encode(['message' => 'Product list']));
      break;
    case '/customers':
      $response->header("Content-Type", "application/json");
      $response->end(json_encode(['message' => 'Customer list']));
      break;
    default:
      $response->status(404);
      $response->end(json_encode(['error' => 'Route not found']));
      break;
  }
});

// Start the server
$http->start();