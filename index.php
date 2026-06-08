<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$rawUrl = $_GET['url'] ?? '';
$rawUrl = trim($rawUrl, '/');
$url = $rawUrl === '' ? [] : explode('/', $rawUrl);

// ===============================
// WEB API: mọi endpoint bắt đầu bằng /api và luôn trả JSON
// ===============================
if (isset($url[0]) && strtolower($url[0]) === 'api') {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        echo json_encode(["status" => true, "message" => "OK"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    array_shift($url); // bỏ api
    $resource = strtolower($url[0] ?? 'product');
    $aliases = [
        'products' => 'product',
        'categories' => 'category',
        'carts' => 'cart',
        'orders' => 'order',
        'payments' => 'payment',
        'accounts' => 'account',
        'users' => 'account'
    ];
    $resource = $aliases[$resource] ?? $resource;

    $controllerName = ucfirst($resource) . 'ApiController';
    $controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';
    $action = $url[1] ?? 'index';

    if (is_numeric($action)) {
        // Cho phép dạng /api/product/5 tương đương /api/product/detail/5
        $id = $action;
        $action = 'detail';
    } else {
        $id = $url[2] ?? null;
    }

    if (strpos($action, '_') !== false) {
        $action = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $action))));
    }

    if (!file_exists($controllerFile)) {
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "message" => "Không tìm thấy API controller: $controllerName"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    require_once $controllerFile;
    $controller = new $controllerName();

    if (!method_exists($controller, $action)) {
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "message" => "Endpoint /api/$resource/$action không tồn tại"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id !== null ? $controller->$action($id) : $controller->$action();
    exit;
}

// ===============================
// FRONTEND: chỉ trả HTML, mọi thao tác dữ liệu gọi API bằng fetch()
// ===============================
header("Content-Type: text/html; charset=UTF-8");
require_once __DIR__ . '/app/views/index.html';
