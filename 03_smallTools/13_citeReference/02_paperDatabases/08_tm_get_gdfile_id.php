<?php
// 08_tm_get_gdfile_id.php

require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';

header('Content-Type: application/json; charset=utf-8');

checkApiKey();

$paperID = isset($_GET['paperID']) ? intval($_GET['paperID']) : 0;
$doi = isset($_GET['doi']) ? trim($_GET['doi']) : '';

if ($paperID <= 0 && $doi === '') {
    echo json_encode(['success' => false, 'message' => 'paperID 或 doi 至少提供一个。'], JSON_UNESCAPED_UNICODE);
    exit();
}

$fileID = null;

// 优先按 paperID 查
if ($paperID > 0) {
    $stmt = $mysqli->prepare("SELECT fileID FROM gdfile WHERE paperID = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $paperID);
        $stmt->execute();
        $stmt->bind_result($tmp);
        if ($stmt->fetch()) {
            $fileID = $tmp;
        }
        $stmt->close();
    }
}

// 若 paperID 未查到且 doi 可用，则按 doi 查
if (!$fileID && $doi !== '') {
    $stmt = $mysqli->prepare("SELECT fileID FROM gdfile WHERE doi = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $doi);
        $stmt->execute();
        $stmt->bind_result($tmp);
        if ($stmt->fetch()) {
            $fileID = $tmp;
        }
        $stmt->close();
    }
}

if ($fileID) {
    echo json_encode(['success' => true, 'fileID' => $fileID], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => '未在 gdfile 表中找到对应的 fileID。'], JSON_UNESCAPED_UNICODE);
}
