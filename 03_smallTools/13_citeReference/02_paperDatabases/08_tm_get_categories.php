<?php
// 08_tm_get_categories.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, X-Api-Key");

// 引入数据库配置、API认证和操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 执行 API Key 检查
checkApiKey();

// 获取所有分类
$categories = getCategories($mysqli);

if (is_array($categories)) {
    echo json_encode(['success' => true, 'categories' => $categories]);
} else {
    echo json_encode(['success' => false, 'message' => '获取分类失败。']);
}
?>
