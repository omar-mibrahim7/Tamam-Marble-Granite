<?php
require_once(__DIR__ . "/../config/security.php");

class LoginSecurity {
    const CAPTCHA_THRESHOLD = 3;
    const LOCK_THRESHOLD = 5;
    const LOCK_MINUTES = 5;

    public static function getClientIp(){
        $candidates = [
            $_SERVER['HTTP_CLIENT_IP'] ?? '',
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            $ip = trim(explode(',', $candidate)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '0.0.0.0';
    }

    public static function getAttempt($conn, $email, $ip){
        $stmt = mysqli_prepare(
            $conn,
            "SELECT attempt_id, email, ip_address, attempts_count, last_attempt_at, locked_until
             FROM login_attempts
             WHERE email = ? AND ip_address = ?
             ORDER BY attempt_id DESC
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $email, $ip);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $attempt = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $attempt ?: null;
    }

    public static function isLocked($conn, $email, $ip){
        $stmt = mysqli_prepare(
            $conn,
            "SELECT attempt_id
             FROM login_attempts
             WHERE email = ?
               AND ip_address = ?
               AND locked_until IS NOT NULL
               AND locked_until > NOW()
             ORDER BY attempt_id DESC
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $email, $ip);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $locked = mysqli_fetch_assoc($result) !== null;
        mysqli_stmt_close($stmt);

        return $locked;
    }

    public static function shouldShowCaptcha($conn, $email, $ip){
        $attempt = self::getAttempt($conn, $email, $ip);
        return $attempt && (int)$attempt['attempts_count'] >= self::CAPTCHA_THRESHOLD;
    }

    public static function recordFailedAttempt($conn, $email, $ip){
        $attempt = self::getAttempt($conn, $email, $ip);

        if ($attempt) {
            $attemptId = (int)$attempt['attempt_id'];
            $attemptsCount = (int)$attempt['attempts_count'] + 1;

            if ($attemptsCount >= self::LOCK_THRESHOLD) {
                $lockMinutes = self::LOCK_MINUTES;
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE login_attempts
                     SET attempts_count = ?,
                         last_attempt_at = NOW(),
                         locked_until = DATE_ADD(NOW(), INTERVAL {$lockMinutes} MINUTE)
                     WHERE attempt_id = ?"
                );
                mysqli_stmt_bind_param($stmt, "ii", $attemptsCount, $attemptId);
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE login_attempts
                     SET attempts_count = ?,
                         last_attempt_at = NOW(),
                         locked_until = NULL
                     WHERE attempt_id = ?"
                );
                mysqli_stmt_bind_param($stmt, "ii", $attemptsCount, $attemptId);
            }

            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return $success;
        }

        $attemptsCount = 1;
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO login_attempts (email, ip_address, attempts_count, last_attempt_at)
             VALUES (?, ?, ?, NOW())"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $email, $ip, $attemptsCount);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    public static function clearAttempts($conn, $email, $ip){
        $stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE email = ? AND ip_address = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email, $ip);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    public static function isRecaptchaConfigured(){
        return defined('RECAPTCHA_SECRET_KEY')
            && defined('RECAPTCHA_SITE_KEY')
            && RECAPTCHA_SECRET_KEY !== 'PUT_YOUR_SECRET_KEY_HERE'
            && RECAPTCHA_SITE_KEY !== 'PUT_YOUR_SITE_KEY_HERE'
            && RECAPTCHA_SECRET_KEY !== ''
            && RECAPTCHA_SITE_KEY !== '';
    }

    public static function verifyRecaptcha($token, $remoteIp){
        // Replace placeholder reCAPTCHA keys in config/security.php before production.
        if (!self::isRecaptchaConfigured()) {
            return true;
        }

        if (trim((string)$token) === '') {
            return false;
        }

        $payload = http_build_query([
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $remoteIp
        ]);

        $response = false;

        if (function_exists('curl_init')) {
            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 8
                ]
            ]);
            $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        }

        if ($response === false) {
            return false;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) && !empty($decoded['success']);
    }
}
?>
