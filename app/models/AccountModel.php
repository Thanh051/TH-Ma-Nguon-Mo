<?php
class AccountModel {
    private $conn;

    public function __construct() {
        // Kết nối cơ sở dữ liệu thông qua lớp Database của dự án của bạn
        require_once 'app/config/Database.php'; 
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // 1. Kiểm tra Tên tài khoản đã tồn tại chưa (Dùng khi Đăng ký)
    public function checkUsernameExists($username) {
        $sql = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // 2. Đăng ký tài khoản (is_verified mặc định = 0, trả về token kích hoạt)
    public function register($username, $email, $password) {
        $sql = "INSERT INTO users (username, email, password, role, status, is_verified, email_verification_token) 
                VALUES (:username, :email, :password, 'user', 1, 0, :token)";
        
        // Băm mật khẩu bằng Bcrypt bảo mật cao
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        // Tạo token ngẫu nhiên độ dài 32 ký tự để gửi mail kích hoạt
        $token = bin2hex(random_bytes(16));

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':token', $token);

        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    // 3. Xác thực kích hoạt tài khoản qua link Email công cộng
    public function verifyEmail($token) {
        // Kiểm tra xem token có hợp lệ trong DB không
        $sql = "SELECT id FROM users WHERE email_verification_token = :token AND is_verified = 0 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Cập nhật trạng thái kích hoạt lên 1 và xóa token đi
            $updateSql = "UPDATE users SET is_verified = 1, email_verification_token = NULL WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindParam(':id', $user['id']);
            return $updateStmt->execute();
        }
        return false;
    }

    // 4. Xử lý logic Đăng nhập (Kiểm tra mật khẩu, Trạng thái khóa, Trạng thái kích hoạt)
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Xác thực mã hash mật khẩu Bcrypt
            if (password_verify($password, $user['password'])) {
                // Kiểm tra xem tài khoản bị khóa hay không (status = 0)
                if (isset($user['status']) && $user['status'] == 0) {
                    return 'locked';
                }
                // Kiểm tra xem tài khoản đã kích hoạt email chưa (is_verified = 0)
                if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                    return 'unverified';
                }
                return $user; // Hợp lệ, trả về toàn bộ mảng thông tin user
            }
        }
        return false;
    }

    // 5. Lưu Remember Token vào Database phục vụ "Ghi nhớ đăng nhập"
    public function setRememberToken($userId, $token) {
        $sql = "UPDATE users SET remember_token = :token WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    // 6. Tìm kiếm tài khoản bằng Remember Token (Dùng khi người dùng mở lại trình duyệt)
    public function getUserByRememberToken($token) {
        $sql = "SELECT * FROM users WHERE remember_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 7. Lấy thông tin chi tiết của 1 user theo ID
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 8. Cập nhật Hồ sơ cá nhân (Email, Avatar, Đổi mật khẩu nếu có nhập mới)
    public function updateProfile($userId, $email, $avatar, $password = null) {
        if ($password !== null) {
            // Trường hợp người dùng có điền mật khẩu mới muốn thay đổi
            $sql = "UPDATE users SET email = :email, avatar = :avatar, password = :password WHERE id = :id";
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':password', $hashed_password);
        } else {
            // Trường hợp giữ nguyên mật khẩu cũ, chỉ đổi email hoặc avatar
            $sql = "UPDATE users SET email = :email, avatar = :avatar WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
        }

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    // 9. Lấy dữ liệu tài khoản bằng Email (Phục vụ chức năng Quên mật khẩu)
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 10. Lưu mã OTP quên mật khẩu kèm thời gian hết hạn (15 phút sau)
    public function saveResetToken($email, $otp) {
        $sql = "UPDATE users SET reset_token = :otp, token_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':otp', $otp);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }

    // 11. Kiểm tra tính hợp lệ và thời hạn của mã số OTP
    public function checkResetToken($otp) {
        $sql = "SELECT * FROM users WHERE reset_token = :otp AND token_expiry > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':otp', $otp);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 12. Cập nhật mật khẩu mới sau khi xác thực OTP thành công
    public function resetPassword($otp, $newPassword) {
        $sql = "UPDATE users SET password = :password, reset_token = NULL, token_expiry = NULL WHERE reset_token = :otp";
        $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':otp', $otp);
        return $stmt->execute();
    }

    // ==================== [ADMIN] KHỐI QUẢN LÝ THÀNH VIÊN ====================

    // 13. Lấy toàn bộ danh sách thành viên trong hệ thống
    public function getAllUsers() {
        $sql = "SELECT id, username, email, role, status, avatar FROM users ORDER BY id DESC";
        // FIX LỖI: Chuyển đổi $this->db thành $this->conn khớp với cấu trúc tệp kết nối
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 14. Thay đổi tên hàm từ updateUserStatus thành toggleUserStatus để đồng bộ tuyệt đối với Controller
    public function toggleUserStatus($userId, $status) {
        $sql = "UPDATE users SET status = :status WHERE id = :id";
        // FIX LỖI: Chuyển đổi $this->db thành $this->conn
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $userId
        ]);
    }
}