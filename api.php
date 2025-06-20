<?php
// api.php - 数据接口

// 允许跨域请求（如果你的前端和后端不在同一个域名）
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// 引入数据逻辑文件
require_once 'data_logic.php';

// 获取数据
$dashboardData = getDashboardData();

// 将数据以 JSON 格式输出
echo json_encode($dashboardData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>