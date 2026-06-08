public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveResetToken($email, $token) {
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Mã có hiệu lực 15 phút
        $sql = "UPDATE users SET reset_token = :token, token_expiry = :expiry WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['token' => $token, 'expiry' => $expiry, 'email' => $email]);
    }

    public function checkResetToken($token) {
        $sql = "SELECT * FROM users WHERE reset_token = :token AND token_expiry > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function resetPassword($token, $newPassword) {
        $sql = "UPDATE users SET password = :password, reset_token = NULL, token_expiry = NULL WHERE reset_token = :token";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'token' => $token
        ]);
    }