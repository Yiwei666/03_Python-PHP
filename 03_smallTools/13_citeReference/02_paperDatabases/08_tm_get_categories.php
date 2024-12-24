<?php
// 08_tm_get_categories.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// 引入数据库配置和操作模块
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 获取所有分类
$categories = getCategories($mysqli);

if (is_array($categories)) {
    echo json_encode(['success' => true, 'categories' => $categories]);
} else {
    echo json_encode(['success' => false, 'message' => '获取分类失败。']);
}
?>
