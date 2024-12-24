<?php
// 08_tm_update_paper_categories.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // 生产环境请根据需求调整
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 引入数据库配置和操作模块
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => '无效的请求数据。']);
    exit();
}

$doi = isset($data['doi']) ? trim($data['doi']) : '';
$categoryIDs = isset($data['categoryIDs']) ? $data['categoryIDs'] : [];

if (empty($doi)) {
    echo json_encode(['success' => false, 'message' => 'DOI不能为空。']);
    exit();
}

if (!is_array($categoryIDs)) {
    echo json_encode(['success' => false, 'message' => '分类ID必须是数组。']);
    exit();
}

// 获取paperID
$paper = getPaperByDOI($mysqli, $doi);
if (!$paper) {
    echo json_encode(['success' => false, 'message' => '未找到对应的论文。']);
    exit();
}

$paperID = $paper['paperID'];

/**
 * 核心修复：原逻辑里是判断 !in_array(0, $categoryIDs) 就 push 0，
 * 但数据库中 “0 All papers” 对应的 categoryID 是 1 而非 0，
 * 会导致外键冲突。
 *
 * 所以这里改为强制确保 categoryID = 1 在数组中。
 */
if (!in_array(1, $categoryIDs)) {
    $categoryIDs[] = 1;
}

// 更新论文分类
$updateResult = updatePaperCategories($mysqli, $paperID, $categoryIDs);

if ($updateResult['success']) {
    echo json_encode(['success' => true, 'message' => '分类更新成功。']);
} else {
    echo json_encode(['success' => false, 'message' => $updateResult['message']]);
}
?>
