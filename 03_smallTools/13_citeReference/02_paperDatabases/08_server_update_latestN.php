<?php
require_once __DIR__ . '/08_db_config.php';

$latestN = 200;
$targetCategoryName = '0 latestN';

$mysqli->set_charset('utf8mb4');

try {
    $mysqli->begin_transaction();

    // 1. 检查 categories 表中是否存在 "0 latestN"
    $stmt = $mysqli->prepare("SELECT categoryID FROM categories WHERE category_name = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("准备查询分类失败: " . $mysqli->error);
    }

    $stmt->bind_param('s', $targetCategoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();

    if (!$category) {
        $mysqli->rollback();
        echo "分类标签 '{$targetCategoryName}' 不存在，程序结束。\n";
        exit;
    }

    $latestCategoryID = (int)$category['categoryID'];

    // 2. 获取最新写入的 N 条论文，按 paperID 降序判断“最新”
    $stmt = $mysqli->prepare("SELECT paperID FROM papers ORDER BY paperID DESC LIMIT ?");
    if (!$stmt) {
        throw new Exception("准备查询最新论文失败: " . $mysqli->error);
    }

    $stmt->bind_param('i', $latestN);
    $stmt->execute();
    $result = $stmt->get_result();

    $latestPaperIDs = [];
    while ($row = $result->fetch_assoc()) {
        $latestPaperIDs[] = (int)$row['paperID'];
    }
    $stmt->close();

    // 3. 清除 "0 latestN" 分类下不属于最新 N 条的论文
    if (!empty($latestPaperIDs)) {
        $placeholders = implode(',', array_fill(0, count($latestPaperIDs), '?'));
        $types = 'i' . str_repeat('i', count($latestPaperIDs));
        $params = array_merge([$latestCategoryID], $latestPaperIDs);

        $sql = "DELETE FROM paperCategories 
                WHERE categoryID = ? 
                AND paperID NOT IN ($placeholders)";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("准备清理旧分类关系失败: " . $mysqli->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $deletedRows = $stmt->affected_rows;
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare("DELETE FROM paperCategories WHERE categoryID = ?");
        if (!$stmt) {
            throw new Exception("准备清空分类关系失败: " . $mysqli->error);
        }

        $stmt->bind_param('i', $latestCategoryID);
        $stmt->execute();
        $deletedRows = $stmt->affected_rows;
        $stmt->close();
    }

    // 4. 将最新 N 条论文加入 "0 latestN"，不改变其原有其他分类
    $stmt = $mysqli->prepare(
        "INSERT IGNORE INTO paperCategories (paperID, categoryID) VALUES (?, ?)"
    );
    if (!$stmt) {
        throw new Exception("准备写入最新分类关系失败: " . $mysqli->error);
    }

    $insertedRows = 0;
    foreach ($latestPaperIDs as $paperID) {
        $stmt->bind_param('ii', $paperID, $latestCategoryID);
        $stmt->execute();
        $insertedRows += $stmt->affected_rows;
    }
    $stmt->close();

    $mysqli->commit();

    echo "更新完成。\n";
    echo "分类标签: {$targetCategoryName}，categoryID={$latestCategoryID}\n";
    echo "最新论文数量: " . count($latestPaperIDs) . "\n";
    echo "新增分类关系: {$insertedRows}\n";
    echo "移除旧分类关系: {$deletedRows}\n";
} catch (Exception $e) {
    $mysqli->rollback();
    echo "更新失败: " . $e->getMessage() . "\n";
    exit(1);
}
?>
