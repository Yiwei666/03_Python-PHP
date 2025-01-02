<?php
// 08_web_update_paper_status.php
header('Content-Type: application/json');

// 启用错误报告（调试用，生产环境请关闭）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入数据库及操作模块
require_once '08_db_config.php';           // 数据库连接
require_once '08_category_operations.php'; // 内含 getPaperByDOI() 和 updatePaperStatus()

// 从请求体中获取 JSON 数据
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (isset($data['doi']) && isset($data['status'])) {
    $doi = trim($data['doi']);
    $newStatus = trim($data['status']);

    // 根据 doi 获取论文
    $paper = getPaperByDOI($mysqli, $doi);

    if (!$paper) {
        echo json_encode([
            'success' => false,
            'message' => '未找到对应的论文或数据库查询错误。'
        ]);
        exit;
    }

    // 执行更新
    $paperID = $paper['paperID'];
    $updateResult = updatePaperStatus($mysqli, $paperID, $newStatus);

    if ($updateResult['success']) {
        echo json_encode([
            'success' => true,
            'message' => '论文状态更新成功'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $updateResult['message'] ?? '更新状态时出现未知错误'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => '请求参数不完整，需要传递 doi 和 status'
    ]);
}
