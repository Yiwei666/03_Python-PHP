<?php
// 08_web_user_select_tmp.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Api-Key");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once '08_api_auth.php';
require_once '08_db_config.php';

// 认证
checkApiKey();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => '缺少 action 参数']);
    exit();
}

$action = $data['action'];

if ($action === 'insert') {
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'items 为空']);
        exit();
    }

    // 先在内存中按 paperID 去重
    $byPid = [];
    foreach ($items as $it) {
        if (!isset($it['paperID']) || !isset($it['doi'])) continue;
        $pid = (int)$it['paperID'];
        $doi = trim($it['doi']);
        if ($pid <= 0 || $doi === '') continue;
        $byPid[$pid] = $doi;
    }

    // 再按 doi 去重（同一 doi 仅保留一个 paperID）
    $seenDoi = [];
    $final = [];
    foreach ($byPid as $pid => $doi) {
        if (isset($seenDoi[$doi])) continue;
        $seenDoi[$doi] = true;
        $final[] = ['paperID' => $pid, 'doi' => $doi];
    }

    if (empty($final)) {
        echo json_encode(['success' => false, 'message' => '无有效数据']);
        exit();
    }

    // 使用 INSERT IGNORE，依赖于表上的 PRIMARY KEY(paperID) 与 UNIQUE(doi) 来避免重复
    $stmt = $mysqli->prepare("INSERT IGNORE INTO select_paper (paperID, doi) VALUES (?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => '预处理失败：' . $mysqli->error]);
        exit();
    }

    $inserted = 0;
    $skipped = 0;
    foreach ($final as $row) {
        $pid = (int)$row['paperID'];
        $doi = $row['doi'];
        $stmt->bind_param('is', $pid, $doi);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $inserted++;
            } else {
                $skipped++; // 因唯一键冲突被 IGNORE
            }
        } else {
            // 单条失败也计入 skipped
            $skipped++;
        }
    }
    $stmt->close();

    echo json_encode(['success' => true, 'inserted' => $inserted, 'skipped' => $skipped], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($action === 'clear') {
    // 清空表
    $ok = $mysqli->query("TRUNCATE TABLE select_paper");
    if ($ok) {
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => $mysqli->error], JSON_UNESCAPED_UNICODE);
    }
    exit();
}

if ($action === 'copy') {
    $res = $mysqli->query("SELECT paperID, doi FROM select_paper ORDER BY paperID ASC");
    if (!$res) {
        echo json_encode(['success' => false, 'message' => $mysqli->error], JSON_UNESCAPED_UNICODE);
        exit();
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = ['paperID' => (int)$row['paperID'], 'doi' => $row['doi']];
    }
    $res->close();
    echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
    exit();
}

echo json_encode(['success' => false, 'message' => '未知 action'], JSON_UNESCAPED_UNICODE);
