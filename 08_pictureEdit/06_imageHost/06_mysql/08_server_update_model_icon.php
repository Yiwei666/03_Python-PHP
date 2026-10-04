#!/usr/bin/env php
<?php
/**
 * 为每个图片分类选择一张头像照片，并同步到“181 模特头像”分类。
 *
 * 选择规则：
 * 1. 只考虑 images.image_exists = 1 的图片；
 * 2. 每个分类选择 likes 最大的图片；
 * 3. likes 相同时选择 id 最大的图片；
 * 4. 同一张图片即使被多个分类选中，也只建立一次“181 模特头像”关联；
 * 5. “181 模特头像”自身不参与头像候选计算。
 *
 * 脚本采用增量同步：保留仍然有效的头像关联，删除已经失效的头像关联，
 * 并补充新选出的头像关联。图片已有的其他分类不会被修改。
 */

require_once __DIR__ . '/08_db_config.php';

$targetCategoryName = '181 模特头像';
$transactionStarted = false;

if (!$mysqli->set_charset('utf8mb4')) {
    echo "设置数据库字符集失败：" . $mysqli->error . PHP_EOL;
    $mysqli->close();
    exit(1);
}

try {
    // 1. 确认目标分类已经存在，并避免同名分类导致关联目标不明确。
    $stmt = $mysqli->prepare(
        'SELECT id FROM Categories WHERE category_name = ? ORDER BY id ASC'
    );
    if (!$stmt) {
        throw new RuntimeException('准备目标分类查询失败：' . $mysqli->error);
    }

    $stmt->bind_param('s', $targetCategoryName);
    if (!$stmt->execute()) {
        throw new RuntimeException('查询目标分类失败：' . $stmt->error);
    }

    $result = $stmt->get_result();
    $targetCategoryIds = [];
    while ($row = $result->fetch_assoc()) {
        $targetCategoryIds[] = (int)$row['id'];
    }
    $stmt->close();

    if (count($targetCategoryIds) === 0) {
        echo "分类 '{$targetCategoryName}' 在 Categories 表中不存在，请先创建！脚本终止。" . PHP_EOL;
        $mysqli->close();
        exit(1);
    }

    if (count($targetCategoryIds) > 1) {
        echo "Categories 表中存在多个同名分类 '{$targetCategoryName}'，无法确定目标分类。脚本终止。" . PHP_EOL;
        $mysqli->close();
        exit(1);
    }

    $targetCategoryId = $targetCategoryIds[0];

    if (!$mysqli->begin_transaction()) {
        throw new RuntimeException('开启数据库事务失败：' . $mysqli->error);
    }
    $transactionStarted = true;

    /*
     * 2-3. 一次性选出每个分类的头像。
     *
     * NOT EXISTS 用于判断同一分类下是否还存在更优图片：
     * likes 更大，或 likes 相同但 id 更大。这样无需依赖 MySQL 8 的窗口函数。
     * COALESCE 将极少见的 NULL likes 按 0 处理。
     */
    $sqlSelectWinners = <<<'SQL'
        SELECT
            pc.category_id,
            i.id AS image_id,
            COALESCE(i.likes, 0) AS likes
        FROM PicCategories AS pc
        INNER JOIN images AS i ON i.id = pc.image_id
        WHERE i.image_exists = 1
          AND pc.category_id <> ?
          AND NOT EXISTS (
              SELECT 1
              FROM PicCategories AS pc_better
              INNER JOIN images AS i_better ON i_better.id = pc_better.image_id
              WHERE pc_better.category_id = pc.category_id
                AND i_better.image_exists = 1
                AND (
                    COALESCE(i_better.likes, 0) > COALESCE(i.likes, 0)
                    OR (
                        COALESCE(i_better.likes, 0) = COALESCE(i.likes, 0)
                        AND i_better.id > i.id
                    )
                )
          )
        ORDER BY pc.category_id ASC
        SQL;

    $stmt = $mysqli->prepare($sqlSelectWinners);
    if (!$stmt) {
        throw new RuntimeException('准备头像筛选查询失败：' . $mysqli->error);
    }

    $stmt->bind_param('i', $targetCategoryId);
    if (!$stmt->execute()) {
        throw new RuntimeException('筛选各分类头像失败：' . $stmt->error);
    }

    $result = $stmt->get_result();
    $winnerByCategory = [];
    $desiredImageIds = [];

    while ($row = $result->fetch_assoc()) {
        $categoryId = (int)$row['category_id'];
        $imageId = (int)$row['image_id'];

        $winnerByCategory[$categoryId] = $imageId;
        // 以图片 id 为数组键去重，处理同一合影被多个分类同时选中的情况。
        $desiredImageIds[$imageId] = true;
    }
    $stmt->close();

    // 4-5. 读取当前头像集合，与新集合比较后仅同步发生变化的关联。
    $stmt = $mysqli->prepare(
        'SELECT image_id FROM PicCategories WHERE category_id = ?'
    );
    if (!$stmt) {
        throw new RuntimeException('准备现有头像查询失败：' . $mysqli->error);
    }

    $stmt->bind_param('i', $targetCategoryId);
    if (!$stmt->execute()) {
        throw new RuntimeException('查询现有头像关联失败：' . $stmt->error);
    }

    $result = $stmt->get_result();
    $existingImageIds = [];
    while ($row = $result->fetch_assoc()) {
        $existingImageIds[(int)$row['image_id']] = true;
    }
    $stmt->close();

    $imageIdsToDelete = array_keys(array_diff_key($existingImageIds, $desiredImageIds));
    $imageIdsToInsert = array_keys(array_diff_key($desiredImageIds, $existingImageIds));

    if (!empty($imageIdsToDelete)) {
        $deleteStmt = $mysqli->prepare(
            'DELETE FROM PicCategories WHERE image_id = ? AND category_id = ?'
        );
        if (!$deleteStmt) {
            throw new RuntimeException('准备删除旧头像关联失败：' . $mysqli->error);
        }

        foreach ($imageIdsToDelete as $imageId) {
            $deleteStmt->bind_param('ii', $imageId, $targetCategoryId);
            if (!$deleteStmt->execute()) {
                throw new RuntimeException(
                    "删除旧头像关联失败（图片 ID：{$imageId}）：" . $deleteStmt->error
                );
            }
        }
        $deleteStmt->close();
    }

    if (!empty($imageIdsToInsert)) {
        $insertStmt = $mysqli->prepare(
            'INSERT INTO PicCategories (image_id, category_id) VALUES (?, ?)'
        );
        if (!$insertStmt) {
            throw new RuntimeException('准备新增头像关联失败：' . $mysqli->error);
        }

        foreach ($imageIdsToInsert as $imageId) {
            $insertStmt->bind_param('ii', $imageId, $targetCategoryId);
            if (!$insertStmt->execute()) {
                throw new RuntimeException(
                    "新增头像关联失败（图片 ID：{$imageId}）：" . $insertStmt->error
                );
            }
        }
        $insertStmt->close();
    }

    if (!$mysqli->commit()) {
        throw new RuntimeException('提交数据库事务失败：' . $mysqli->error);
    }
    $transactionStarted = false;

    $selectedCategoryCount = count($winnerByCategory);
    $uniqueAvatarCount = count($desiredImageIds);
    $deletedCount = count($imageIdsToDelete);
    $insertedCount = count($imageIdsToInsert);
    $unchangedCount = $uniqueAvatarCount - $insertedCount;

    echo "'{$targetCategoryName}' 分类同步完成。" . PHP_EOL;
    echo "已选出头像的分类数：{$selectedCategoryCount}" . PHP_EOL;
    echo "去重后的头像图片数：{$uniqueAvatarCount}" . PHP_EOL;
    echo "新增关联数：{$insertedCount}" . PHP_EOL;
    echo "删除失效关联数：{$deletedCount}" . PHP_EOL;
    echo "保持不变的关联数：{$unchangedCount}" . PHP_EOL;
} catch (Throwable $e) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }

    fwrite(STDERR, '脚本执行失败：' . $e->getMessage() . PHP_EOL);
    $mysqli->close();
    exit(1);
}

$mysqli->close();
