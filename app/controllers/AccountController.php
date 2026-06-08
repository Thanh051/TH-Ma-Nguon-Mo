<?php
require_once 'app/models/AccountModel.php';
require_once 'app/config/MailConfig.php';

class AccountController {
    private $accountModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->accountModel = new AccountModel();
        
        // 1. TỰ ĐỘNG KIỂM TRA REMEMBER ME KHI KHỞI TẠO
        $this->checkRememberMe();
    }

    // Khối xử lý kiểm tra Cookie ghi nhớ đăng nhập
    private function checkRememberMe() {
        if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];
            $user = $this->accountModel->getUserByRememberToken($token);
            if ($user) {
                // Đảm bảo tài khoản không bị khóa mới cho tự động đăng nhập
                if ($user['status'] == 1) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'] ?? '',
                        'role' => $user['role'] ?? 'user',
                        'avatar' => $user['avatar'] ?? 'default_avatar.png'
                    ];
                }
            }
        }
    }

    // Giao diện và Xử lý Đăng ký (Tích hợp Xác thực qua Email thật)
    public function register() {
        $error = "";
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($email) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ thông tin yêu cầu!";
            } elseif ($this->accountModel->checkUsernameExists($username)) {
                $error = "Tên tài khoản này đã được sử dụng!";
            } else {
                // Đăng ký tạo tài khoản ở trạng thái chờ xác thực (is_verified = 0)
                $token = $this->accountModel->register($username, $email, $password);
                if ($token) {
                    // Tạo đường dẫn link kích hoạt dựa theo HTTP_HOST động để tránh sai thư mục
                    $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . "/WebStore/index.php?url=account/verifyEmail&token=" . $token;
                    
                    $subject = "Xác thực kích hoạt tài khoản - TTG STORE";
                    $content = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; max-width: 500px; margin: 0 auto; border-radius: 8px;'>
                            <h2 style='color: #28a745; text-align: center;'>KÍCH HOẠT TÀI KHOẢN</h2>
                            <p>Chào bạn <strong>{$username}</strong>,</p>
                            <p>Cảm ơn bạn đã đăng ký thành viên tại hệ thống TTG Store. Vui lòng bấm vào liên kết bên dưới để xác thực và kích hoạt tài khoản của mình:</p>
                            <p style='text-align: center; margin: 30px 0;'>
                                <a href='{$verifyLink}' style='background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; font-weight: bold; border-radius: 5px; display: inline-block;'>XÁC THỰC EMAIL NGAY</a>
                            </p>
                            <p style='font-size: 12px; color: #6c757d;'>Nếu nút trên không hoạt động, bạn có thể sao chép liên kết này dán vào trình duyệt: <br>{$verifyLink}</p>
                        </div>
                    ";

                    MailConfig::sendEmail($email, $subject, $content);
                    $success = "Đăng ký thành công! Vui lòng mở hòm thư Gmail để kích hoạt tài khoản trước khi đăng nhập.";
                } else {
                    $error = "Có lỗi xảy ra trong quá trình khởi tạo dữ liệu, vui lòng thử lại!";
                }
            }
        }
        require_once 'app/views/auth/register.php';
    }

    // Xử lý khi click link từ Mail kích hoạt
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        if (!empty($token) && $this->accountModel->verifyEmail($token)) {
            // FIX ĐƯỜNG DẪN: Bỏ dấu gạch chéo ở đầu đường dẫn điều hướng của JS
            echo "<script>
                alert('Xác thực tài khoản thành công! Bạn hiện tại có thể đăng nhập vào hệ thống.'); 
                window.location.href = 'index.php?url=account/login';
            </script>";
        } else {
            echo "<script>
                alert('Mã xác thực không hợp lệ hoặc tài khoản đã được kích hoạt từ trước.'); 
                window.location.href = 'index.php';
            </script>";
        }
        exit;
    }

    // Giao diện và Xử lý Đăng nhập (Tích hợp Remember Me & Kiểm tra Khóa tài khoản)
    public function login() {
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']); 

            $user = $this->accountModel->login($username, $password);
            
            if ($user === 'locked') {
                $error = "Tài khoản này hiện đang bị khóa bởi Ban Quản Trị!";
            } elseif ($user === 'unverified') {
                $error = "Tài khoản của bạn chưa được xác thực Email. Vui lòng kiểm tra hộp thư!";
            } elseif ($user) {
                $userId = $user['id'] ?? $user['user_id'] ?? $user['id_user'] ?? null;
                if (!$userId) { $userId = 1; }

                // Hiển thị thông tin người dùng sau khi đăng nhập thành công thông qua Session
                $_SESSION['user'] = [
                    'id' => $userId,
                    'username' => $user['username'],
                    'email' => $user['email'] ?? '',
                    'role' => $user['role'] ?? 'user',
                    'avatar' => $user['avatar'] ?? 'default_avatar.png'
                ];

                // Xử lý ghi nhớ đăng nhập (Remember Me)
                if ($remember) {
                    $rememberToken = bin2hex(random_bytes(32));
                    $this->accountModel->setRememberToken($userId, $rememberToken);
                    // Đặt Cookie thời hạn 30 ngày áp dụng toàn bộ source code
                    setcookie('remember_me', $rememberToken, time() + (86400 * 30), "/");
                }

                // ==================== KHỐI ĐỒNG BỘ GIỎ HÀNG VÀO DATABASE ====================
                $guest_key = 'cart_guest';
                if (isset($_SESSION[$guest_key]) && !empty($_SESSION[$guest_key])) {
                    require_once 'app/models/ProductModel.php';
                    $productModel = new ProductModel();
                    foreach ($_SESSION[$guest_key] as $product_id => $item) {
                        $productModel->addToCartDB($userId, $product_id, $item['qty']);
                    }
                    unset($_SESSION[$guest_key]);
                }
                // ===========================================================================

                // FIX ĐƯỜNG DẪN: Chuyển hướng không có dấu gạch chéo / ở đầu
                header('Location: index.php');
                exit;
            } else {
                $error = "Tài khoản hoặc mật khẩu nhập vào không chính xác!";
            }
        }
        require_once 'app/views/auth/login.php';
    }

    // Đăng xuất xóa sạch phiên làm việc và gỡ Cookie ghi nhớ
    public function logout() {
        if (isset($_SESSION['user'])) {
            $this->accountModel->setRememberToken($_SESSION['user']['id'], null);
        }
        // Xóa sạch Cookie lưu ở máy khách
        setcookie('remember_me', '', time() - 3600, "/");
        unset($_SESSION['user']);
        session_destroy();
        
        // FIX ĐƯỜNG DẪN: Chuyển hướng tương đối chạy tốt trên Laragon
        header('Location: index.php');
        exit;
    }

    // Giao diện và Thay đổi thông tin cá nhân + Thay đổi mật khẩu + Tải lên Ảnh đại diện
    public function profile() {
        if (!isset($_SESSION['user'])) {
            // FIX ĐƯỜNG DẪN: Tránh lỗi trắng trang khi chưa đăng nhập
            header('Location: index.php?url=account/login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $msg = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $avatar = $_POST['old_avatar'] ?? 'default_avatar.png';

            // Cho phép người dùng tải lên và thay đổi ảnh đại diện
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $targetDir = "public/uploads/avatars/";
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                    $newFileName = "avatar_" . $userId . "_" . time() . "." . $fileExtension;
                    $targetFilePath = $targetDir . $newFileName;

                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFilePath)) {
                        $avatar = $newFileName;
                    }
                }
            }
            
            // Xử lý cập nhật thông tin / Đổi mật khẩu
            if ($this->accountModel->updateProfile($userId, $email, $avatar, !empty($password) ? $password : null)) {
                $msg = "<div class='alert alert-success shadow-sm'>✓ Cập nhật thông tin và thay đổi hồ sơ thành công!</div>";
                
                // Đồng bộ cập nhật ngay lập tức các dữ liệu mới lên thanh điều hướng (Header) thông qua Session
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['avatar'] = $avatar;
            } else {
                $msg = "<div class='alert alert-danger shadow-sm'>❌ Thao tác cập nhật thất bại, vui lòng thử lại!</div>";
            }
        }

        $userInfo = $this->accountModel->getUserById($userId);
        require_once 'app/views/account/profile.php';
    }

    // ==================== QUẢN LÝ THÀNH VIÊN DÀNH CHO ADMIN ====================
    public function manageUsers() {
        // Phân quyền người dùng theo vai trò Admin và User.
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php?url=account/login');
            exit;
        }

        // Lấy thông báo lưu từ session ra hiển thị (nếu có)
        $msg = $_SESSION['user_action_msg'] ?? '';
        unset($_SESSION['user_action_msg']);

        // Lấy toàn bộ danh sách tài khoản hiển thị lên trang quản trị Admin
        $users = $this->accountModel->getAllUsers();
        require_once 'app/views/account/manage_users.php';
    }

    // HÀM MỚI ĐỒNG BỘ: Tiếp nhận dữ liệu POST từ Form giao diện gửi lên Router
    public function toggleUserStatus() {
        // Kiểm tra quyền hạn Admin bảo mật hệ thống
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php?url=account/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
            $currentStatus = isset($_POST['current_status']) ? intval($_POST['current_status']) : 1;

            // Ngăn chặn tối đa việc Admin tự khóa chính tài khoản cá nhân của mình
            if ($id === intval($_SESSION['user']['id'])) {
                $_SESSION['user_action_msg'] = "<div class='alert alert-danger shadow-sm alert-dismissible fade show' role='alert'>❌ Bạn không thể tự khóa tài khoản chính mình!</div>";
            } else {
                // Nếu đang hoạt động (1) -> Khóa (0) và ngược lại
                $newStatus = ($currentStatus === 1) ? 0 : 1;
                
                if ($this->accountModel->toggleUserStatus($id, $newStatus)) {
                    $_SESSION['user_action_msg'] = "<div class='alert alert-success shadow-sm alert-dismissible fade show' role='alert'>✓ Cập nhật trạng thái tài khoản thành công!</div>";
                } else {
                    $_SESSION['user_action_msg'] = "<div class='alert alert-danger shadow-sm alert-dismissible fade show' role='alert'>❌ Cập nhật trạng thái thất bại, vui lòng thử lại!</div>";
                }
            }
        }
        
        // Sau khi xử lý xong, điều hướng quay lại trang danh sách thành viên
        header('Location: index.php?url=account/manageUsers');
        exit;
    }

    // ==================== QUÊN MẬT KHẨU GỬI OTP ====================
    public function forgotPassword() {
        $msg = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $user = $this->accountModel->getUserByEmail($email);

            if ($user) {
                $otp = rand(100000, 999999);
                $this->accountModel->saveResetToken($email, $otp);

                $subject = "Mã xác thực khôi phục mật khẩu - TTG STORE";
                $content = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; max-width: 500px; margin: 0 auto; border-radius: 8px;'>
                        <h2 style='color: #007bff; text-align: center;'>TTG STORE</h2>
                        <p>Mã xác thực OTP cấp lại mật khẩu của bạn là:</p>
                        <p style='text-align: center; margin: 25px 0;'>
                            <span style='font-size: 26px; font-weight: bold; color: #dc3545; letter-spacing: 6px; background: #f8f9fa; padding: 12px 25px; border: 1px dashed #dc3545; border-radius: 5px;'>{$otp}</span>
                        </p>
                        <p style='font-size: 13px; color: #6c757d;'>Mã có giá trị hiệu lực trong vòng 15 phút.</p>
                    </div>
                ";

                if (MailConfig::sendEmail($email, $subject, $content)) {
                    header("Location: index.php?url=account/resetPassword&email=" . urlencode($email));
                    exit;
                } else {
                    $msg = "<div class='alert alert-danger'>❌ Không thể gửi email, kiểm tra lại cấu hình SMTP!</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>❌ Địa chỉ email này không tồn tại trên hệ thống!</div>";
            }
        }
        require_once 'app/views/account/forgot_password.php';
    }

    public function resetPassword() {
        $msg = "";
        $email = $_GET['email'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otp = trim($_POST['otp'] ?? '');
            $newPass = $_POST['password'] ?? '';
            $user = $this->accountModel->checkResetToken($otp);

            if ($user) {
                if ($this->accountModel->resetPassword($otp, $newPass)) {
                    $msg = "<div class='alert alert-success'>✓ Đổi mật khẩu mới thành công! <a href='index.php?url=account/login' class='fw-bold text-decoration-none'>Đăng nhập ngay</a></div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>❌ Mã xác thực OTP không chính xác hoặc đã hết thời gian hiệu lực!</div>";
            }
        }
        require_once 'app/views/account/reset_password.php';
    }
}