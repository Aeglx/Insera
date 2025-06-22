<?php
// config.php

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'juyi');
define('DB_USER', 'root');
define('DB_PASS', ''); // 您的密码是空

// 表格名称
define('TABLE_XUBAO', 'xubao');         // 续保表
define('TABLE_YUANSHUJU', 'yuanshuju'); // 原始数据表

// 设置时区为北京时间，确保日期计算准确性
date_default_timezone_set('Asia/Shanghai');

// 数据库连接
function getDbConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        // 生产环境中应记录日志而非直接抛出错误
        error_log("Database connection failed: " . $e->getMessage());
        die("数据库连接失败，请检查配置。错误信息：" . $e->getMessage());
    }
}
?>