<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chuẩn hóa đường dẫn tuyệt đối tự động tương thích với cả Windows và Linux
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'Exception.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'PHPMailer.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'SMTP.php';

class MailConfig {
    public static function sendEmail($toEmail, $subject, $content) {
        $mail = new PHPMailer(true);

        try {
            // Cấu hình Máy chủ SMTP của Google
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // ĐIỀN THÔNG TIN GMAIL CỦA BẠN VÀO ĐÂY:
            $mail->Username   = 'maihoaithanh461@gmail.com'; 
            $mail->Password   = 'blvh iqqj pmcg yxke'; // Mật khẩu ứng dụng 16 ký tự của Google
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Người gửi & Người nhận
            $mail->setFrom('email_cua_ban@gmail.com', 'TTG STORE');
            $mail->addAddress($toEmail);

            // Nội dung Email
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $content;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Xem chi tiết lỗi bằng cách ghi log hoặc echo nếu gửi thất bại: $mail->ErrorInfo;
            return false;
        }
    }
}