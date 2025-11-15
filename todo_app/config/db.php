<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      
define('DB_PASS', '');         
define('DB_NAME', 'todo_app_db'); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES   => false, 
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {

    die("L_P_Lỗi kết nối CSDL: " . $e->getMessage());
}
?>