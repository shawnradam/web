<?php
// admin/helpers/SecurityHelper.php
// Security helper for rate limiting and protection

class SecurityHelper
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function checkRateLimit($ipAddress, $username = null)
    {
        require_once __DIR__ . '/../config/email.config.php';

        $timeWindow = date('Y-m-d H:i:s', time() - LOGIN_ATTEMPT_WINDOW);

        $query = "SELECT COUNT(*) as attempt_count 
                  FROM login_attempts 
                  WHERE ip_address = :ip 
                  AND attempt_time > :time_window";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':ip' => $ipAddress,
            ':time_window' => $timeWindow
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['attempt_count'] < MAX_LOGIN_ATTEMPTS;
    }

    public function logLoginAttempt($ipAddress, $username, $success = false)
    {
        $query = "INSERT INTO login_attempts (ip_address, username, success) 
                  VALUES (:ip, :username, :success)";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':ip' => $ipAddress,
            ':username' => $username,
            ':success' => $success ? 1 : 0
        ]);
    }

    public function cleanOldAttempts()
    {
        $cutoffTime = date('Y-m-d H:i:s', time() - 86400);
        $query = "DELETE FROM login_attempts WHERE attempt_time < :cutoff";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':cutoff' => $cutoffTime]);
    }

    public function generateSecureToken()
    {
        return bin2hex(random_bytes(32));
    }

    public function createSession($userId, $ipAddress, $userAgent)
    {
        $token = $this->generateSecureToken();
        $expiresAt = date('Y-m-d H:i:s', time() + 86400);

        $query = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
                  VALUES (:user_id, :token, :ip, :user_agent, :expires)";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':token' => $token,
            ':ip' => $ipAddress,
            ':user_agent' => $userAgent,
            ':expires' => $expiresAt
        ]);

        return $token;
    }

    public function validateSession($token)
    {
        $query = "SELECT user_id FROM user_sessions 
                  WHERE session_token = :token 
                  AND expires_at > NOW()";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':token' => $token]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
?>