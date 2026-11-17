<?php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/08_api_auth.php';
require_once __DIR__ . '/08_category_operations.php';
require_once __DIR__ . '/08_web_Base32.php';

$categoryIDs = $_POST['categoryIDs'] ?? '';
$sort = isset($_POST['sort']) && is_string($_POST['sort']) ? $_POST['sort'] : 'paperID_desc';
$items = [];
$seenDois = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['message' => '请使用 POST 请求复制所选分类。']);
    exit;
}

checkApiKey();

if (!is_string($categoryIDs) || !preg_match('/\A[1-9][0-9]*(?:,[1-9][0-9]*)*\z/', $categoryIDs)) {
    http_response_code(400);
    echo json_encode(['message' => '请勾选至少一个有效分类。']);
    exit;
}

if (!function_exists('getPapersByCategories')) {
    http_response_code(500);
    echo json_encode(['message' => '复制多类需要同步更新服务器上的 08_category_operations.php。']);
    exit;
}

try {
    // 配置模块会立即连接数据库，因此在鉴权和校验通过后加载。
    require_once __DIR__ . '/08_db_config.php';
    $result = getPapersByCategories($mysqli, array_unique(explode(',', $categoryIDs)), $sort);
    if (!$result) {
        throw new RuntimeException('Failed to query papers by category: ' . $mysqli->error);
    }

    while ($paper = $result->fetch_assoc()) {
        $doi = $paper['doi'];
        $doiKey = strtolower(trim($doi ?? ''));
        if ($doiKey === '' || isset($seenDois[$doiKey])) {
            continue;
        }
        $seenDois[$doiKey] = true;
        $items[] = [
            'paperID' => (int)$paper['paperID'],
            'doi' => $doi,
            'title' => $paper['title'],
            'encodedDOI' => Base32::encode($doi),
            'gdURL' => !empty($paper['gdFileID']) ? 'https://drive.google.com/file/d/' . rawurlencode($paper['gdFileID']) . '/view' : null
        ];
    }
    echo json_encode(['data' => $items], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('[copy_categories] ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['message' => '无法获取所选分类的论文数据。']);
}
