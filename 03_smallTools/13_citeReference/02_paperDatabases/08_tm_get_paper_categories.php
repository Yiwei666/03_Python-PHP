<?php
// 08_tm_get_paper_categories.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // 允许跨域请求，生产环境请根据需求调整
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, X-Api-Key");

// 引入数据库配置、API认证和操作模块
require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 执行 API Key 检查
checkApiKey();

// 获取GET参数
$doi = isset($_GET['doi']) ? trim($_GET['doi']) : '';

if (empty($doi)) {
    echo json_encode(['success' => false, 'message' => 'DOI不能为空。']);
    exit();
}

// 获取paperID
$paper = getPaperByDOI($mysqli, $doi);
if (!$paper) {
    echo json_encode(['success' => false, 'message' => '未找到对应的论文。']);
    exit();
}

$paperID = $paper['paperID'];

// 获取论文的分类
$categoryIDs = getCategoriesByPaperID($mysqli, $paperID);

if ($categoryIDs !== false) {
    echo json_encode(['success' => true, 'categoryIDs' => $categoryIDs]);
} else {
    echo json_encode(['success' => false, 'message' => '获取分类失败。']);
}
?>
