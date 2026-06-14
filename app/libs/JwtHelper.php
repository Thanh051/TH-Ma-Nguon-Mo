<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper {
    private static $secret_key = "webstore_super_secret_jwt_key_2026_hoai_thanh";
    private static $algorithm = "HS256";
    private static $expiry_seconds = 7200; // 2 hours

    /**
     * Generate JWT Token
     * @param array $user User data (must not contain password)
     * @return string
     */
    public static function generateToken($user) {
        $issuedAt = time();
        $expire = $issuedAt + self::$expiry_seconds;

        // Ensure sensitive info like password is NOT in the token
        unset($user['password'], $user['reset_token'], $user['token_expiry']);

        $payload = [
            'iat'  => $issuedAt,
            'exp'  => $expire,
            'user' => $user
        ];

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    /**
     * Decode JWT Token
     * @param string $jwt
     * @return array|null The decoded user array or null if invalid
     * @throws Exception if expired or invalid
     */
    public static function decodeToken($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key(self::$secret_key, self::$algorithm));
            return (array)$decoded->user;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
