<?php
// 08_tm_get_paper_metaInfo.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Api-Key");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// 引入 API 认证、数据库与操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 执行 API Key 检查
checkApiKey();

$doi = isset($_GET['doi']) ? trim($_GET['doi']) : '';
if ($doi === '') {
    echo json_encode(['success' => false, 'message' => 'DOI不能为空。'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 查询
$paper = getPaperByDOI($mysqli, $doi);
if ($paper) {
    echo json_encode(['success' => true, 'paper' => $paper], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => '未找到该论文的元信息。'], JSON_UNESCAPED_UNICODE);
}
