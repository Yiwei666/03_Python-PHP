#!/usr/bin/env php
<?php
/**
 * 08_server_batch_categorize_images.php
 * 
 * 功能描述：
 *  1. 从 images 表中筛选出 image_exists = 1 的图片；
 *  2. 询问用户是否基于 star 值（0/1）进一步筛选；
 *  3. 对最终筛选到的图片，检查其 image_name 中是否包含 Categories 表里非空的 kindID；
 *     若包含，则生成 (image_id, category_id) 需要插入到 PicCategories 表的数据（若已存在则跳过）；
 *  4. 最终打印信息，确认后写入。
 */

require_once '08_db_config.php';

/**
 * 命令行读取用户输入的封装函数
 */
function readlineCLI($prompt = "") {
    if (!empty($prompt)) {
        echo $prompt;
    }
    return trim(fgets(STDIN));
}

// 1) 先查询 image_exists = 1 的图片
$baseQuery = "SELECT id, image_name, star FROM images WHERE image_exists = 1";
// 用于记录最后的筛选条件（star 的可选条件）
$starFilter = false;
$starValue = null;

// 2) 询问是否基于 star 值进行进一步筛选
while (true) {
    $input = readlineCLI("是否基于 star 值进行筛选？(y/n)，或输入 q 退出程序：");
    $input = strtolower($input);

    if ($input === 'q') {
        echo "已退出程序。\n";
        exit(0); // 结束脚本
    } elseif ($input === 'n') {
        // 不基于 star 值
        $starFilter = false;
        break;
    } elseif ($input === 'y') {
        // 需进一步询问 star 的值
        $starValInput = readlineCLI("请输入 star 的值(0或1)：");
        if ($starValInput === '0' || $starValInput === '1') {
            $starFilter = true;
            $starValue = (int)$starValInput;
            break;
        } else {
            echo "非法输入：star 只能为 0 或 1。\n";
            // 继续循环重新问
        }
    } else {
        echo "非法输入：只能输入 y / n / q。\n";
        // 继续循环重新问
    }
}

// 如果需要 star 筛选，则拼接到查询
if ($starFilter) {
    $baseQuery .= " AND star = {$starValue}";
}

// 获取最终要处理的图片列表
$result = $mysqli->query($baseQuery);

if (!$result) {
    echo "查询数据库失败：" . $mysqli->error . "\n";
    $mysqli->close();
    exit(1);
}

// 将结果放到数组中
$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$result->free();

// 如果没有筛选出任何图片，则直接结束
if (count($images) === 0) {
    echo "没有符合条件的图片。\n";
    $mysqli->close();
    exit(0);
}

echo "共筛选出 " . count($images) . " 张图片。\n";

// 3) 对比 Categories 表中非空 kindID，检查每张图片的 image_name 是否包含该 kindID
$sqlCategories = "SELECT id, category_name, kindID FROM Categories WHERE kindID IS NOT NULL AND kindID <> ''";
$resCat = $mysqli->query($sqlCategories);

if (!$resCat) {
    echo "查询 Categories 表失败：" . $mysqli->error . "\n";
    $mysqli->close();
    exit(1);
}

// 收集 kindID 及其对应的 category id
$categoriesWithKindID = [];
while ($catRow = $resCat->fetch_assoc()) {
    $categoriesWithKindID[] = $catRow; // [ 'id' => xx, 'category_name' => xx, 'kindID' => xx ]
}
$resCat->free();

// 准备统计数据
$matchedImages = [];      // 所有满足条件、image_name 中包含某个 kindID 的图片信息
$newRelations = [];       // 需要插入的 (image_id, category_id)
$existCount = 0;          // 已存在的关系记录数
$checkCount = 0;          // 总匹配次数（包含已存在和需要新增的）

foreach ($images as $img) {
    $imgId = $img['id'];
    $imgName = $img['image_name'];

    // 对于每一个 kindID，检查 $imgName 是否包含
    foreach ($categoriesWithKindID as $catInfo) {
        $catId = $catInfo['id'];
        $kindID = $catInfo['kindID'];

        if (strpos($imgName, $kindID) !== false) {
            // 命中
            $checkCount++;

            // 记录到 matchedImages
            if (!isset($matchedImages[$imgId])) {
                $matchedImages[$imgId] = [
                    'image_id' => $imgId,
                    'image_name' => $imgName,
                    'matched_categories' => []
                ];
            }
            $matchedImages[$imgId]['matched_categories'][] = $catId;
        }
    }
}

// 如果没有任何匹配，直接结束
if (empty($matchedImages)) {
    echo "没有任何图片的 image_name 匹配到 Categories 表中的 kindID。\n";
    $mysqli->close();
    exit(0);
}

// 这里 matchedImages 是按照 image_id 聚合的
// 统计下总匹配了多少张图片
$matchedImageCount = count($matchedImages);

// 统计并检查哪些 relationships (image_id, category_id) 需要插入
foreach ($matchedImages as $info) {
    $imgId = $info['image_id'];
    $catIds = $info['matched_categories'];
    
    foreach ($catIds as $catId) {
        // 判断 PicCategories 中是否已存在
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM PicCategories WHERE image_id = ? AND category_id = ?");
        $stmt->bind_param("ii", $imgId, $catId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row['c'] > 0) {
            // 已存在
            $existCount++;
        } else {
            // 需要新插入
            $newRelations[] = [
                'image_id'    => $imgId,
                'category_id' => $catId
            ];
        }
    }
}

// 打印匹配到的图片名称
echo "\n匹配到 kindID 的图片名单：\n";
foreach ($matchedImages as $info) {
    echo "image_id: " . $info['image_id'] 
       . " | image_name: " . $info['image_name'] . "\n";
}

// =======================================
// 只修改以下打印部分
// =======================================

// 打印需要新插入的匹配关系对应的图片名称
echo "\n需要新插入匹配关系的图片名单：\n";

// 收集要插入的 image_id（去重）
$imgIdsNeedInsert = [];
foreach ($newRelations as $rel) {
    $imgIdsNeedInsert[] = $rel['image_id'];
}
$imgIdsNeedInsert = array_unique($imgIdsNeedInsert);

// 打印这些图片
foreach ($imgIdsNeedInsert as $imgId) {
    echo "image_id: " . $imgId 
       . " | image_name: " . $matchedImages[$imgId]['image_name'] . "\n";
}

// =======================================
// 以下保持原逻辑不变
// =======================================

echo "\n总共有 {$matchedImageCount} 张图片与至少一个 kindID 匹配。\n";
echo "总匹配次数(图片×匹配分类)为 {$checkCount} 次。\n";
echo "其中已存在于 PicCategories 的匹配关系有 {$existCount} 条。\n";
echo "需要新插入的匹配关系有 " . count($newRelations) . " 条。\n";

// 4) 提示用户是否确认插入
if (count($newRelations) > 0) {
    $confirm = readlineCLI("\n是否确认将以上需要插入的匹配关系写入 PicCategories 表？(y/n): ");
    if (strtolower($confirm) === 'y') {
        // 执行插入
        $insertStmt = $mysqli->prepare("INSERT INTO PicCategories (image_id, category_id) VALUES (?, ?)");
        
        foreach ($newRelations as $rel) {
            $insertStmt->bind_param("ii", $rel['image_id'], $rel['category_id']);
            $insertStmt->execute();
        }
        $insertStmt->close();

        echo "插入完成，新增关系共 " . count($newRelations) . " 条。\n";
    } else {
        echo "已取消插入操作。\n";
    }
} else {
    echo "没有需要插入的关系，无需写入。\n";
}

$mysqli->close();
echo "脚本执行完毕。\n";
