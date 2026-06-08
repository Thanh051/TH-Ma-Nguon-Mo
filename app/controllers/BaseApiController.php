<?php
class BaseApiController {
    protected function json($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    protected function body() {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
        return $_POST ?: [];
    }

    protected function requireMethod($methods) {
        $methods = (array)$methods;
        if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
            $this->json([
                'status' => false,
                'message' => 'Method không hợp lệ. Endpoint này chỉ nhận: ' . implode(', ', $methods)
            ], 405);
            return false;
        }
        return true;
    }

    protected function currentUser() {
        return $_SESSION['user'] ?? null;
    }
}
