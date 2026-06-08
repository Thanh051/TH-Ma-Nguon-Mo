<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/config/database.php';

class AccountApiController extends BaseApiController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    private function publicUser($user) {
        if (!$user) return null;
        unset($user['password']);
        return $user;
    }

    // POST /api/account/register
    public function register() {
        if (!$this->requireMethod('POST')) return;
        $data = $this->body();
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập username, email và password'], 400);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['status' => false, 'message' => 'Email không hợp lệ'], 400);
            return;
        }
        if (strlen($password) < 6) {
            $this->json(['status' => false, 'message' => 'Mật khẩu tối thiểu 6 ký tự'], 400);
            return;
        }

        $check = $this->conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
        $check->execute([':username' => $username, ':email' => $email]);
        if ($check->fetch()) {
            $this->json(['status' => false, 'message' => 'Username hoặc email đã tồn tại'], 400);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, role, status)
                                      VALUES (:username, :email, :password, 'user', 1)");
        $ok = $stmt->execute([':username' => $username, ':email' => $email, ':password' => $hash]);

        $this->json([
            'status' => $ok,
            'message' => $ok ? 'Đăng ký tài khoản bằng API thành công' : 'Không thể đăng ký tài khoản'
        ], $ok ? 201 : 500);
    }

    // POST /api/account/login
    public function login() {
        if (!$this->requireMethod('POST')) return;
        $data = $this->body();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập username và password'], 400);
            return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username OR email = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $this->json(['status' => false, 'message' => 'Sai tài khoản hoặc mật khẩu'], 401);
            return;
        }
        if ((int)$user['status'] !== 1) {
            $this->json(['status' => false, 'message' => 'Tài khoản đang bị khóa'], 403);
            return;
        }

        $_SESSION['user'] = $this->publicUser($user);
        $this->json([
            'status' => true,
            'message' => 'Đăng nhập bằng API thành công',
            'data' => $_SESSION['user']
        ]);
    }

    // GET /api/account/me
    public function me() {
        if (!$this->requireMethod('GET')) return;
        if (empty($_SESSION['user'])) {
            $this->json(['status' => false, 'message' => 'Chưa đăng nhập'], 401);
            return;
        }
        $this->json(['status' => true, 'data' => $_SESSION['user']]);
    }

    // POST /api/account/logout
    public function logout() {
        if (!$this->requireMethod(['POST', 'DELETE'])) return;
        unset($_SESSION['user']);
        $this->json(['status' => true, 'message' => 'Đăng xuất bằng API thành công']);
    }

    // PUT /api/account/profile
    public function profile() {
        if (!$this->requireMethod('PUT')) return;
        if (empty($_SESSION['user'])) {
            $this->json(['status' => false, 'message' => 'Chưa đăng nhập'], 401);
            return;
        }

        $data = $this->body();
        $email = trim($data['email'] ?? ($_SESSION['user']['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['status' => false, 'message' => 'Email không hợp lệ'], 400);
            return;
        }

        $stmt = $this->conn->prepare("UPDATE users SET email = :email WHERE id = :id");
        $ok = $stmt->execute([':email' => $email, ':id' => (int)$_SESSION['user']['id']]);
        if ($ok) {
            $_SESSION['user']['email'] = $email;
        }
        $this->json([
            'status' => $ok,
            'message' => $ok ? 'Cập nhật hồ sơ bằng API thành công' : 'Không thể cập nhật hồ sơ',
            'data' => $_SESSION['user']
        ]);
    }
}
