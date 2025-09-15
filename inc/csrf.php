<?php
class CSRF {
    public static function generateToken($formName) {
        if (empty($_SESSION['csrf_tokens'][$formName])) {
            $_SESSION['csrf_tokens'][$formName] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_tokens'][$formName];
    }
    
    public static function validateToken($formName, $token) {
        if (!empty($_SESSION['csrf_tokens'][$formName]) && 
            hash_equals($_SESSION['csrf_tokens'][$formName], $token)) {
            unset($_SESSION['csrf_tokens'][$formName]);
            return true;
        }
        return false;
    }
}
?>