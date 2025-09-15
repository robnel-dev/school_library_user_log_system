<?php
session_start();

class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['staff_id']) && !empty($_SESSION['staff_id']);
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /staff/login.php');
            exit();
        }
    }
    
    public static function login($username, $password) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, username, password_hash, full_name, role FROM staff WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['staff_id'] = $row['id'];
                $_SESSION['staff_username'] = $row['username'];
                $_SESSION['staff_fullname'] = $row['full_name'];
                $_SESSION['staff_role'] = $row['role'];
                $_SESSION['last_activity'] = time();
                return true;
            }
        }
        return false;
    }
    
    public static function logout() {
        session_unset();
        session_destroy();
    }
    
    public static function checkTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            self::logout();
            header('Location: /staff/login.php?timeout=1');
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
}
?>