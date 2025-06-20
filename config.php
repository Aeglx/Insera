<?php
// config.php - 数据库配置文件

define('DB_HOST', 'localhost');
define('DB_NAME', 'juyi');
define('DB_USER', 'root');
define('DB_PASS', '');

// 尝试建立数据库连接
$pdo = null;
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // echo "Database connection successful!"; // 调试用，生产环境请删除
} catch (PDOException $e) {
    // 生产环境应记录错误日志而非直接输出到页面
    error_log("Database connection failed: " . $e->getMessage());
    die("抱歉，数据库连接失败。请稍后再试或联系管理员。");
}
?>