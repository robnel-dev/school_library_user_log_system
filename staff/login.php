<?php
session_start();
require_once '../inc/db.php';
require_once '../inc/config.php';

// Redirect if already logged in
if (isset($_SESSION['staff_id']) && !empty($_SESSION['staff_id'])) {
    header('Location: dashboard.php');
    exit();
}


$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
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
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Library System</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="login-header">
            <h1>SAN PEDRO COLLEGE OF BUSINESS ADMINISTRATION</h1>
            <h2>Library Staff Login</h2>
        </div>
        
        <main>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['timeout'])): ?>
                <div class="error">Your session has timed out. Please login again.</div>
            <?php endif; ?>
            
            <form method="POST" class="card">
                <h3>Staff Login</h3>
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Login</button>
                 <a href="../public/index.php" class="button">Back</a>
            </form>
            
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> San Pedro College of Business Administration</p>
        </footer>
    </div>
</body>
</html>