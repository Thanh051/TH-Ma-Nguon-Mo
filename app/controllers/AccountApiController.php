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
        unset($user['password'], $user['reset_token'], $user['token_expiry']);
        // Chuẩn hóa avatar URL
        if (!empty($user['avatar'])) {
            $user['avatar_url'] = 'public/uploads/avatars/' . $user['avatar'];
        } else {
            $user['avatar_url'] = null;
        }
        return $user;
    }

    // POST /api/account/register
    public function register() {
        if (!$this->requireMethod('POST')) return;
        $data     = $this->body();
        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập username, email và password'], 400); return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['status' => false, 'message' => 'Email không hợp lệ'], 400); return;
        }
        if (strlen($password) < 6) {
            $this->json(['status' => false, 'message' => 'Mật khẩu tối thiểu 6 ký tự'], 400); return;
        }

        $check = $this->conn->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
        $check->execute([':u' => $username, ':e' => $email]);
        if ($check->fetch()) {
            $this->json(['status' => false, 'message' => 'Username hoặc email đã tồn tại'], 400); return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, role) VALUES (:u, :e, :p, 'user')");
        $ok   = $stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Đăng ký tài khoản thành công' : 'Không thể đăng ký tài khoản'
        ], $ok ? 201 : 500);
    }

    // POST /api/account/login
    public function login() {
        if (!$this->requireMethod('POST')) return;
        $data     = $this->body();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập username và password'], 400); return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :u OR email = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $this->json(['status' => false, 'message' => 'Sai tài khoản hoặc mật khẩu'], 401); return;
        }

        $publicUser = $this->publicUser($user);
        require_once 'app/libs/JwtHelper.php';
        $token = JwtHelper::generateToken($publicUser);

        $_SESSION['user'] = $publicUser;
        $userId = (int)$user['id'];

        // === Load giỏ hàng từ DB vào session ===
        require_once 'app/controllers/CartApiController.php';
        require_once 'app/models/ProductModel.php';
        CartApiController::loadFromDB($this->conn, $userId, new ProductModel());

        $this->json([
            'status'  => true,
            'message' => 'Đăng nhập thành công',
            'token'   => $token,
            'data'    => $publicUser
        ]);
    }

    // GET /api/account/me
    public function me() {
        if (!$this->requireMethod('GET')) return;
        $user = $this->requireAuth();
        $this->json(['status' => true, 'data' => $user]);
    }

    // POST /api/account/logout – chỉ xóa session, GIỮ giỏ hàng trong DB
    public function logout() {
        if (!$this->requireMethod(['POST', 'DELETE'])) return;

        $user = $this->currentUser();
        if ($user && !empty($user['id'])) {
            $userId  = (int)$user['id'];
            $cartKey = 'cart_' . $userId;
            unset($_SESSION[$cartKey]);
        }

        unset($_SESSION['user']);
        $this->json(['status' => true, 'message' => 'Đăng xuất thành công.']);
    }

    // PUT /api/account/profile
    public function profile() {
        if (!$this->requireMethod('PUT')) return;
        $user = $this->requireAuth();
        $data  = $this->body();
        $email = trim($data['email'] ?? ($user['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['status' => false, 'message' => 'Email không hợp lệ'], 400); return;
        }
        $stmt = $this->conn->prepare("UPDATE users SET email = :e WHERE id = :id");
        $ok   = $stmt->execute([':e' => $email, ':id' => (int)$user['id']]);
        if ($ok) {
            // Fetch updated user từ DB để có đủ các trường mới nhất (kể cả avatar)
            $stmt2 = $this->conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt2->execute([':id' => (int)$user['id']]);
            $updatedUser = $this->publicUser($stmt2->fetch());
            $_SESSION['user'] = $updatedUser;
            require_once 'app/libs/JwtHelper.php';
            $newToken = JwtHelper::generateToken($updatedUser);
            $this->json([
                'status'  => true,
                'message' => 'Cập nhật hồ sơ thành công',
                'token'   => $newToken,
                'data'    => $updatedUser
            ]);
        } else {
            $this->json([
                'status'  => false,
                'message' => 'Không thể cập nhật hồ sơ'
            ], 500);
        }
    }

    // POST /api/account/uploadAvatar  (multipart/form-data)
    public function uploadAvatar() {
        if (!$this->requireMethod('POST')) return;
        $user = $this->requireAuth();

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['status' => false, 'message' => 'Vui lòng chọn file ảnh hợp lệ'], 400); return;
        }

        $file     = $_FILES['avatar'];
        $maxSize  = 5 * 1024 * 1024; // 5MB
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($file['size'] > $maxSize) {
            $this->json(['status' => false, 'message' => 'File quá lớn, tối đa 5MB'], 400); return;
        }
        if (!in_array($mimeType, $allowed)) {
            $this->json(['status' => false, 'message' => 'Chỉ chấp nhận file JPG, PNG, GIF, WEBP'], 400); return;
        }

        $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename   = 'avatar_' . (int)$user['id'] . '_' . time() . '.' . strtolower($ext);
        $uploadDir  = 'public/uploads/avatars/';
        $targetPath = $uploadDir . $filename;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->json(['status' => false, 'message' => 'Không thể lưu file ảnh'], 500); return;
        }

        // Xóa avatar cũ
        if (!empty($user['avatar'])) {
            $oldPath = $uploadDir . $user['avatar'];
            if (file_exists($oldPath)) @unlink($oldPath);
        }

        $stmt = $this->conn->prepare("UPDATE users SET avatar = :a WHERE id = :id");
        $ok   = $stmt->execute([':a' => $filename, ':id' => (int)$user['id']]);

        if ($ok) {
            // Fetch updated user
            $stmt2 = $this->conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt2->execute([':id' => (int)$user['id']]);
            $updatedUser = $this->publicUser($stmt2->fetch());
            $_SESSION['user'] = $updatedUser;
            require_once 'app/libs/JwtHelper.php';
            $newToken = JwtHelper::generateToken($updatedUser);
            $this->json([
                'status'    => true,
                'message'   => 'Upload ảnh đại diện thành công',
                'avatar'    => $filename,
                'avatar_url'=> $targetPath,
                'token'     => $newToken,
                'data'      => $updatedUser
            ]);
        } else {
            $this->json(['status' => false, 'message' => 'Không thể cập nhật ảnh đại diện'], 500);
        }
    }

    // POST /api/account/changePassword
    public function changePassword() {
        if (!$this->requireMethod(['POST', 'PUT'])) return;
        $user = $this->requireAuth();
        $data = $this->body();
        $oldPassword = $data['old_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if ($oldPassword === '' || $newPassword === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập đầy đủ mật khẩu cũ và mới'], 400); return;
        }
        if (strlen($newPassword) < 6) {
            $this->json(['status' => false, 'message' => 'Mật khẩu mới tối thiểu 6 ký tự'], 400); return;
        }

        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $user['id']]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($oldPassword, $dbUser['password'])) {
            $this->json(['status' => false, 'message' => 'Mật khẩu cũ không chính xác'], 400); return;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $this->conn->prepare("UPDATE users SET password = :p WHERE id = :id");
        $ok = $update->execute([':p' => $newHash, ':id' => $user['id']]);

        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Đổi mật khẩu thành công' : 'Không thể đổi mật khẩu'
        ]);
    }

    // POST /api/account/forgotPassword
    public function forgotPassword() {
        if (!$this->requireMethod('POST')) return;
        $data = $this->body();
        $email = trim($data['email'] ?? '');

        if ($email === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng cung cấp email'], 400); return;
        }

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->json(['status' => false, 'message' => 'Địa chỉ email không tồn tại trên hệ thống'], 404); return;
        }

        $otp = (string)rand(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $update = $this->conn->prepare("UPDATE users SET reset_token = :otp, token_expiry = :expiry WHERE email = :e");
        $ok = $update->execute([':otp' => $otp, ':expiry' => $expiry, ':e' => $email]);

        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Đã tạo mã OTP khôi phục mật khẩu (mô phỏng)' : 'Không thể tạo yêu cầu khôi phục',
            'otp'     => $otp
        ]);
    }

    // POST /api/account/resetPassword
    public function resetPassword() {
        if (!$this->requireMethod('POST')) return;
        $data = $this->body();
        $otp = trim($data['otp'] ?? '');
        $newPassword = $data['new_password'] ?? '';

        if ($otp === '' || $newPassword === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập đầy đủ OTP và mật khẩu mới'], 400); return;
        }
        if (strlen($newPassword) < 6) {
            $this->json(['status' => false, 'message' => 'Mật khẩu mới tối thiểu 6 ký tự'], 400); return;
        }

        $stmt = $this->conn->prepare("SELECT id, token_expiry FROM users WHERE reset_token = :otp LIMIT 1");
        $stmt->execute([':otp' => $otp]);
        $user = $stmt->fetch();

        if (!$user || strtotime($user['token_expiry']) < time()) {
            $this->json(['status' => false, 'message' => 'Mã OTP không chính xác hoặc đã hết hạn'], 400); return;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $this->conn->prepare("UPDATE users SET password = :p, reset_token = NULL, token_expiry = NULL WHERE id = :id");
        $ok = $update->execute([':p' => $newHash, ':id' => $user['id']]);

        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Khôi phục mật khẩu thành công' : 'Không thể khôi phục mật khẩu'
        ]);
    }
}
