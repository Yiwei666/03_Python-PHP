#!/usr/bin/env php
<?php
/**
 * 此脚本用于：
 * 1. 确认 "0.0 未知" 分类是否已经在 Categories 表中存在，如不存在则提示并退出。
 * 2. 筛选出 images 表中所有 image_exists=1 并且 star=1 的图片 id 。
 * 3. 对于每个图片 id：
 *    - 如果在 PicCategories 表中没有任何分类关联，则将其关联到 "0.0 未知" 分类；
 *    - 如果在 PicCategories 表中正好只有一个关联分类，则跳过；
 *    - 如果在 PicCategories 表中有 2 个及以上的关联分类，并且其中一个分类是 "0.0 未知"，则删除该图片 id 与 "0.0 未知" 的关联；
 *      如果不存在 "0.0 未知" 关联，则跳过。
 * 4. 最后打印出在 "0.0 未知" 分类下的图片数量。
 */

require_once '08_db_config.php';  // 引用数据库连接配置

// 1. 查询 "0.0 未知" 分类是否已经存在
$unknownCategoryName = "0.0 未知";
$sqlCheckCategory = "SELECT id FROM Categories WHERE category_name = '$unknownCategoryName' LIMIT 1";
$resultCheckCategory = $mysqli->query($sqlCheckCategory);

if (!$resultCheckCategory) {
    echo "查询 Categories 表失败：" . $mysqli->error . PHP_EOL;
    exit;
}

// 如果没有找到该分类，提示并退出
if ($resultCheckCategory->num_rows === 0) {
    echo "分类 '{$unknownCategoryName}' 在 Categories 表中不存在，请先创建！脚本终止。" . PHP_EOL;
    exit;
}

$categoryRow = $resultCheckCategory->fetch_assoc();
$unknownCategoryId = $categoryRow['id'];

// 2. 筛选 images 表中所有 image_exists=1 并且 star=1 的图片 id
$sqlSelectImages = "SELECT id FROM images WHERE image_exists = 1 AND star = 1";
$resultImages = $mysqli->query($sqlSelectImages);

if (!$resultImages) {
    echo "查询 images 表失败：" . $mysqli->error . PHP_EOL;
    exit;
}

// 逐条处理符合条件的图片 id
while ($row = $resultImages->fetch_assoc()) {
    $imageId = $row['id'];

    // 3. 查询该图片在 PicCategories 中的分类数
    $sqlCountCategories = "SELECT COUNT(*) AS cnt FROM PicCategories WHERE image_id = {$imageId}";
    $resultCount = $mysqli->query($sqlCountCategories);

    if (!$resultCount) {
        echo "查询 PicCategories 表失败：" . $mysqli->error . PHP_EOL;
        continue;
    }

    $countRow = $resultCount->fetch_assoc();
    $categoryCount = (int)$countRow['cnt'];

    // 根据分类数进行不同的处理
    if ($categoryCount === 0) {
        // 如果没有任何分类关联，则插入一条关联到 "0.0 未知"
        $sqlInsert = "INSERT INTO PicCategories (image_id, category_id) VALUES ({$imageId}, {$unknownCategoryId})";
        if (!$mysqli->query($sqlInsert)) {
            echo "插入关联失败：Image ID = {$imageId}, Error = " . $mysqli->error . PHP_EOL;
        }
    } elseif ($categoryCount >= 2) {
        // 如果该图片关联了 2 个及以上分类，检查其中是否存在 "0.0 未知"
        $sqlCheckUnknown = "
            SELECT COUNT(*) AS c 
            FROM PicCategories 
            WHERE image_id = {$imageId} 
              AND category_id = {$unknownCategoryId}
        ";
        $resultCheckUnknown = $mysqli->query($sqlCheckUnknown);
        if (!$resultCheckUnknown) {
            echo "查询是否存在 '0.0 未知' 分类失败：" . $mysqli->error . PHP_EOL;
            continue;
        }

        $checkRow = $resultCheckUnknown->fetch_assoc();
        if ((int)$checkRow['c'] > 0) {
            // 如果其中一个分类是 "0.0 未知"，则删除该关联
            $sqlDeleteUnknown = "
                DELETE FROM PicCategories 
                WHERE image_id = {$imageId} 
                  AND category_id = {$unknownCategoryId}
            ";
            if (!$mysqli->query($sqlDeleteUnknown)) {
                echo "删除 '0.0 未知' 分类关联失败：Image ID = {$imageId}, Error = " . $mysqli->error . PHP_EOL;
            }
        }
        // 如果不存在 "0.0 未知" 关联，则跳过不处理
    }
    // 如果是 1 条关联分类，则跳过不处理
}

// 6. 最后打印出在 "0.0 未知" 分类下的图片数量
$sqlCountUnknown = "
    SELECT COUNT(DISTINCT image_id) AS total 
    FROM PicCategories 
    WHERE category_id = {$unknownCategoryId}
";
$resultCountUnknown = $mysqli->query($sqlCountUnknown);

if (!$resultCountUnknown) {
    echo "统计 '0.0 未知' 分类下的图片数量失败：" . $mysqli->error . PHP_EOL;
    exit;
}

$countUnknownRow = $resultCountUnknown->fetch_assoc();
$unknownCount = $countUnknownRow['total'];

echo "在 '0.0 未知' 分类下的图片数量为：{$unknownCount}" . PHP_EOL;

// 关闭数据库连接
$mysqli->close();
