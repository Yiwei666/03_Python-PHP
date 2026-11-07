<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once '08_api_auth.php';
require_once '08_db_config.php';
require_once '08_category_operations.php';
checkApiKey();

function batchCategoryStatement($mysqli, $sql, $types = '', $params = []) {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) throw new Exception($mysqli->error);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    return $stmt;
}

function batchCategoryIDs($values) {
    if (!is_array($values)) throw new Exception('分类ID必须是数组。');
    foreach ($values as $id) {
        if (!is_int($id) || $id < 1) throw new Exception('分类ID必须是正整数。');
    }
    return array_values(array_unique($values));
}

function batchCategoryDOI($value) {
    if (!is_string($value) || !preg_match('~^10\.\d{4,}/\S+$~D', trim($value))) {
        throw new Exception('无效的 DOI。');
    }
    return trim($value);
}

$inTransaction = false;
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('请使用 POST 请求。');
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) throw new Exception('无效的请求数据。');
    $action = $data['action'] ?? '';

    if ($action === 'prepare') {
        if (empty($data['papers']) || !is_array($data['papers'])) throw new Exception('论文列表不能为空。');
        $papers = [];
        foreach ($data['papers'] as $paper) {
            if (!is_array($paper)) throw new Exception('无效的论文数据。');
            $doi = batchCategoryDOI($paper['doi'] ?? null);
            $paper['doi'] = $doi;
            $papers[strtolower($doi)] = $paper;
        }
        $paperIDs = [];
        foreach ($papers as $paper) {
            $doi = $paper['doi'];
            // 与单篇添加接口使用相同的 DOI 锁，避免同时添加同一篇论文。
            $lockName = 'paper_doi_' . sha1(strtolower($doi));
            $stmt = batchCategoryStatement($mysqli, 'SELECT GET_LOCK(?, 10) AS acquired', 's', [$lockName]);
            $acquired = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (intval($acquired['acquired']) !== 1) throw new Exception($doi . '：DOI写入锁获取失败，请稍后重试。');
            try {
                if (!$mysqli->begin_transaction()) throw new Exception($mysqli->error);
                $inTransaction = true;
                $existing = getPaperByDOI($mysqli, $doi);
                $citationCount = intval($paper['citationCount'] ?? 0);
                if ($existing) {
                    $paperID = intval($existing['paperID']);
                    $stmt = batchCategoryStatement($mysqli, 'UPDATE papers SET citation_count = ? WHERE paperID = ?', 'ii', [$citationCount, $paperID]);
                    $stmt->close();
                } else {
                    $insert = insertPaper($mysqli, $paper['title'] ?? null, $paper['fullAuthors'] ?? null,
                        $paper['journal'] ?? null, intval($paper['publicationYear'] ?? 0), $paper['volume'] ?? null,
                        $paper['issue'] ?? null, $paper['pages'] ?? null, $paper['articleNumber'] ?? null,
                        $doi, $paper['issnPrint'] ?? null, $paper['publisher'] ?? null, $citationCount);
                    if (!$insert['success']) throw new Exception($doi . '：' . $insert['message']);
                    $paperID = intval($insert['paperID']);
                    $assigned = assignAllPapersCategory($mysqli, $paperID);
                    if (!$assigned['success']) throw new Exception($doi . '：' . $assigned['message']);
                }
                if (!$mysqli->commit()) throw new Exception($mysqli->error);
                $inTransaction = false;
                $paperIDs[] = $paperID;
            } finally {
                if ($inTransaction) {
                    $mysqli->rollback();
                    $inTransaction = false;
                }
                $stmt = batchCategoryStatement($mysqli, 'SELECT RELEASE_LOCK(?)', 's', [$lockName]);
                $stmt->close();
            }
        }
        $paperIDs = array_values(array_unique($paperIDs));
        $in = implode(',', $paperIDs);
        $result = $mysqli->query('SELECT categoryID FROM paperCategories WHERE paperID IN (' . $in . ') GROUP BY categoryID HAVING COUNT(DISTINCT paperID) = ' . count($paperIDs));
        if (!$result) throw new Exception($mysqli->error);
        $commonIDs = [];
        while ($row = $result->fetch_assoc()) $commonIDs[] = intval($row['categoryID']);
        $result->close();
        $categories = getCategoriesByRecentPaperUsage($mysqli);
        if (!is_array($categories)) throw new Exception('获取分类失败。');
        echo json_encode(['success' => true, 'categories' => $categories, 'commonIDs' => $commonIDs], JSON_UNESCAPED_UNICODE);
    } elseif ($action === 'save') {
        if (empty($data['dois']) || !is_array($data['dois'])) throw new Exception('DOI列表不能为空。');
        $dois = [];
        foreach ($data['dois'] as $value) {
            $doi = batchCategoryDOI($value);
            $dois[strtolower($doi)] = $doi;
        }
        $dois = array_values($dois);
        $addedIDs = batchCategoryIDs($data['addedIDs'] ?? []);
        $removedIDs = array_values(array_diff(batchCategoryIDs($data['removedIDs'] ?? []), [1], $addedIDs));
        $addedIDs = array_values(array_unique(array_merge($addedIDs, [1])));
        if (!$mysqli->begin_transaction()) throw new Exception($mysqli->error);
        $inTransaction = true;
        $placeholders = implode(',', array_fill(0, count($dois), '?'));
        $stmt = batchCategoryStatement($mysqli, 'SELECT paperID FROM papers WHERE doi IN (' . $placeholders . ') ORDER BY paperID FOR UPDATE', str_repeat('s', count($dois)), $dois);
        $result = $stmt->get_result();
        $paperIDs = [];
        while ($row = $result->fetch_assoc()) $paperIDs[] = intval($row['paperID']);
        $stmt->close();
        if (count($paperIDs) !== count($dois)) throw new Exception('部分论文已不存在，请重新打开批量分类。');
        $in = implode(',', $paperIDs);
        // 仅删除本次取消的共有分类，保留每篇论文的其他分类。
        if ($removedIDs) {
            if (!$mysqli->query('DELETE FROM paperCategories WHERE paperID IN (' . $in . ') AND categoryID IN (' . implode(',', $removedIDs) . ')')) {
                throw new Exception($mysqli->error);
            }
        }
        $categoryRows = implode(' UNION ALL ', array_map(function($id) { return 'SELECT ' . $id . ' AS categoryID'; }, $addedIDs));
        $sql = 'INSERT INTO paperCategories (paperID, categoryID) '
            . 'SELECT p.paperID, c.categoryID FROM papers p CROSS JOIN (' . $categoryRows . ') c '
            . 'LEFT JOIN paperCategories pc ON pc.paperID = p.paperID AND pc.categoryID = c.categoryID '
            . 'WHERE p.paperID IN (' . $in . ') AND pc.paperID IS NULL';
        if (!$mysqli->query($sql)) throw new Exception($mysqli->error);
        if (!$mysqli->commit()) throw new Exception($mysqli->error);
        $inTransaction = false;
        echo json_encode(['success' => true, 'updatedCount' => count($paperIDs)]);
    } else {
        throw new Exception('无效的批量分类操作。');
    }
} catch (Throwable $error) {
    if ($inTransaction) $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
