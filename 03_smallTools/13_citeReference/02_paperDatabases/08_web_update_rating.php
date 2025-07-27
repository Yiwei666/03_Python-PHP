<?php
// 08_web_update_rating.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Api-Key");

// 预检请求直接返回
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

/**
 * 统一输出
 */
function respond($success, $message = '', $rating = null) {
    $resp = ['success' => $success];
    if ($message !== '') $resp['message'] = $message;
    if ($rating !== null) $resp['rating'] = (int)$rating;
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
    exit();
}

// 读取 JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['doi']) || trim($data['doi']) === '') {
    respond(false, '缺少必要参数：doi');
}

$doi = trim($data['doi']);
$ratingProvided = array_key_exists('rating', $data); // 是否显式传入 rating（可为 0）
$rating = $ratingProvided ? $data['rating'] : null;

// 先根据 DOI 找论文
$paper = getPaperByDOI($mysqli, $doi);
if (!$paper) {
    respond(false, '未找到对应的论文。');
}

// 如果未提供 rating，执行查询并返回
if (!$ratingProvided || $rating === '' || $rating === null) {
    respond(true, '查询成功', isset($paper['rating']) ? $paper['rating'] : 0);
}

// 校验 rating（必须是 0-10 的整数）
$validated = filter_var($rating, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0, 'max_range' => 10]
]);
if ($validated === false) {
    respond(false, 'rating 必须是 0-10 的整数');
}

// 更新
$paperID = (int)$paper['paperID'];
$updateSql = "UPDATE papers SET rating = ? WHERE paperID = ?";
$stmt = $mysqli->prepare($updateSql);
if (!$stmt) {
    respond(false, '数据库预处理失败：' . $mysqli->error);
}
$stmt->bind_param('ii', $validated, $paperID);
if ($stmt->execute()) {
    respond(true, '更新成功', $validated);
} else {
    respond(false, '更新失败：' . $stmt->error);
}
