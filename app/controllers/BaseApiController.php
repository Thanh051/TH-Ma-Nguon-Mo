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
        $token = $this->getBearerToken();
        if ($token) {
            try {
                require_once 'app/libs/JwtHelper.php';
                return JwtHelper::decodeToken($token);
            } catch (Exception $e) {
                return null;
            }
        }
        return $_SESSION['user'] ?? null;
    }

    protected function getBearerToken() {
        $authHeader = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } else {
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        
        if (preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function requireAuth() {
        $token = $this->getBearerToken();
        if (!$token) {
            $this->json([
                'status' => false,
                'message' => 'Unauthorized: Thiếu token xác thực'
            ], 401);
            exit;
        }

        try {
            require_once 'app/libs/JwtHelper.php';
            $user = JwtHelper::decodeToken($token);
            // Đồng bộ sang session để tương thích ngược với code cũ
            $_SESSION['user'] = $user;
            return $user;
        } catch (\Firebase\JWT\ExpiredException $e) {
            $this->json([
                'status' => false,
                'message' => 'Unauthorized: Token đã hết hạn'
            ], 401);
            exit;
        } catch (\Exception $e) {
            $this->json([
                'status' => false,
                'message' => 'Unauthorized: Token không hợp lệ'
            ], 401);
            exit;
        }
    }

    protected function requireRole($role) {
        $user = $this->requireAuth();
        if (($user['role'] ?? '') !== $role) {
            $this->json([
                'status' => false,
                'message' => 'Forbidden: Bạn không có quyền truy cập chức năng này'
            ], 403);
            exit;
        }
        return $user;
    }
}
