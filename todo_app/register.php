<?php
require_once 'config/db.php'; 
$error = ''; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    if (empty($username) || empty($password)) {
        $error = "Username và Password là bắt buộc.";
    } else {
        try {
            
            $sql_check = "SELECT id FROM users WHERE username = ? OR (email = ? AND email != '')";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$username, $email]);

            if ($stmt_check->rowCount() > 0) {
                
                $error = "Username hoặc Email đã tồn tại.";
            } else {
               
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $sql_insert = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([$username, $hashed_password, $email]);
                header("Location: login.php?register=success");
                exit; 
            }
        } catch (PDOException $e) {
            
            $error = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="text-center mb-0">Tạo tài khoản mới</h3>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email (Tùy chọn)</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                        </form>
                        
                        <p class="text-center mt-3 mb-0">
                            Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>