<?php
/**
 * 功能：对指定范围/集合的图片，批量添加分类关联关系
 *
 * 使用方式：
 * 1. 在命令行执行： php manage_image_categories.php
 * 2. 根据提示输入内容
 *
 * 注意：
 * - 脚本需要命令行模式交互，如果需要网页模式，请自行改写输入输出方式（如HTML表单）。
 * - 请先确认数据库连接信息在 08_db_config.php 中已正确配置。
 */

require_once '08_db_config.php'; // 包含数据库连接配置

//------------------------------------------------------
// 函数：从用户输入字符串中解析 ID 范围/单个值，返回去重后的 ID 数组
//------------------------------------------------------
function parseImageIDs($inputStr) {
    // 以逗号分隔输入内容
    $parts = explode(',', $inputStr);

    $idSet = []; // 用于存储不重复的ID

    foreach ($parts as $part) {
        $part = trim($part);
        if (strpos($part, '-') !== false) {
            // 范围格式，如 31-101
            list($start, $end) = explode('-', $part);
            $start = trim($start);
            $end   = trim($end);

            // 检查是否均为正整数
            if (!ctype_digit($start) || !ctype_digit($end)) {
                echo "错误：范围 '{$part}' 中存在非法数值。\n";
                return false;
            }

            $start = (int)$start;
            $end   = (int)$end;

            // 不允许负数
            if ($start < 1 || $end < 1) {
                echo "错误：范围 '{$part}' 中出现负数或零，脚本不支持。\n";
                return false;
            }

            if ($start > $end) {
                // 若出现 start 大于 end，则自动交换
                $temp  = $start;
                $start = $end;
                $end   = $temp;
            }

            // 将范围内所有ID加入到 $idSet 中
            for ($i = $start; $i <= $end; $i++) {
                $idSet[$i] = true;
            }
        } else {
            // 单个值
            if (!ctype_digit($part)) {
                echo "错误：输入值 '{$part}' 不是合法的正整数。\n";
                return false;
            }
            $val = (int)$part;
            if ($val < 1) {
                echo "错误：输入值 '{$part}' 为负数或零，脚本不支持。\n";
                return false;
            }
            $idSet[$val] = true;
        }
    }

    // 返回去重并排序后的ID数组
    $uniqueIDs = array_keys($idSet);
    sort($uniqueIDs);

    return $uniqueIDs;
}

//------------------------------------------------------
// 1. 读取 images 表，提示用户输入图片的 id 范围
//------------------------------------------------------
echo "请输入要处理的图片ID范围（如：31-101；多个用逗号分隔，如：1-10,12-15,18,20），输入后回车：\n";
$handle    = fopen("php://stdin", "r");
$inputStr  = trim(fgets($handle));

// 解析输入
$imageIDs = parseImageIDs($inputStr);
if ($imageIDs === false || empty($imageIDs)) {
    echo "解析图片ID失败或结果为空，程序结束。\n";
    exit;
}

//------------------------------------------------------
// 2. 读取 Categories 表格，提示用户输入分类ID，并获取分类信息
//------------------------------------------------------
$categoryID = null;
$categoryInfo = null;

while (true) {
    echo "\n请输入分类ID(输入 q 退出程序)：";
    $inputCategory = trim(fgets($handle));
    if (strtolower($inputCategory) === 'q') {
        echo "程序结束。\n";
        exit;
    }

    if (!ctype_digit($inputCategory)) {
        echo "错误：分类ID必须为正整数，请重新输入或输入 q 退出。\n";
        continue;
    }

    $categoryID = (int)$inputCategory;
    if ($categoryID <= 0) {
        echo "错误：分类ID不能为负数或0，请重新输入。\n";
        continue;
    }

    // 查询数据库，判断该分类ID是否存在
    $stmt = $mysqli->prepare("SELECT id, category_name, kindID FROM Categories WHERE id = ?");
    $stmt->bind_param("i", $categoryID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $categoryInfo = $row; // 保存分类信息
        echo "已找到分类信息：\n";
        echo "  Category ID: {$row['id']}\n";
        echo "  Category Name: {$row['category_name']}\n";
        echo "  kindID: {$row['kindID']}\n";
        break; // 跳出while循环
    } else {
        echo "未找到该分类ID对应的记录，请重新输入或输入 q 退出。\n";
    }
    $stmt->close();
}

//------------------------------------------------------
// 3. 判断输入ID范围内的图片是否已在 PicCategories 表中存在关联，
//    对于不存在的关联，准备插入数据
//------------------------------------------------------

// 先获取这些图片的实际信息（如果images表不存在某些ID，则可能需要过滤）
$imageIDsStr = implode(',', $imageIDs);
$sqlImages   = "SELECT id, image_name FROM images WHERE id IN ($imageIDsStr)";
$resultImg   = $mysqli->query($sqlImages);

$validImages = []; // 有效的 images 记录
while ($img = $resultImg->fetch_assoc()) {
    $validImages[$img['id']] = $img;
}
$resultImg->close();

// 如果用户输入了不存在于 images 表的 ID，我们可以提示
$notFoundIDs = array_diff($imageIDs, array_keys($validImages));
if (!empty($notFoundIDs)) {
    echo "\n以下图片ID在 images 表中不存在，将被忽略：\n";
    echo implode(', ', $notFoundIDs) . "\n";
}

// 如果没有有效的图片则退出
if (count($validImages) === 0) {
    echo "没有可操作的图片ID，程序结束。\n";
    exit;
}

// 准备查询 PicCategories，找到已经存在的 (image_id, category_id)
$existMap = []; // 用于记录已存在关联，key: image_id

$sqlPC = "SELECT image_id FROM PicCategories 
          WHERE category_id = ? 
            AND image_id IN (" . implode(',', array_keys($validImages)) . ")";

$stmtPC = $mysqli->prepare($sqlPC);
$stmtPC->bind_param("i", $categoryInfo['id']);
$stmtPC->execute();
$resultPC = $stmtPC->get_result();

while ($rowPC = $resultPC->fetch_assoc()) {
    $existMap[$rowPC['image_id']] = true;
}
$stmtPC->close();

// 准备插入的列表
$toInsert = [];
foreach ($validImages as $id => $imgData) {
    if (!isset($existMap[$id])) {
        // 说明 PicCategories 里还没有此关联，需要插入
        $toInsert[] = $id;
    }
}

//------------------------------------------------------
// 4. 在插入前，打印出相关信息并确认
//------------------------------------------------------
echo "\n分类信息：\n";
echo "  Category ID: {$categoryInfo['id']}\n";
echo "  Category Name: {$categoryInfo['category_name']}\n";
echo "  kindID: {$categoryInfo['kindID']}\n";

echo "\n即将关联的图片 (排除已存在关联)：\n";
if (empty($toInsert)) {
    echo "  所有选定的图片都已具有该分类，无需新增关联。\n";
} else {
    foreach ($toInsert as $imageID) {
        $imgName = $validImages[$imageID]['image_name'];
        echo "  Image ID: $imageID, Image Name: $imgName\n";
    }
}

echo "\n共有 " . count($validImages) . " 张有效图片参与检测；\n";
echo "其中需要新增关联的图片数：" . count($toInsert) . "。\n";

if (!empty($toInsert)) {
    // 询问用户是否执行写入操作
    while (true) {
        echo "\n是否写入上述 " . count($toInsert) . " 条新关联关系？(y/n，输入 q 退出)：";
        $confirm = trim(fgets($handle));

        // 判断用户输入
        if (strtolower($confirm) === 'y') {
            // 执行插入
            $insertSQL = "INSERT INTO PicCategories (image_id, category_id) VALUES (?, ?)";
            $stmtIns = $mysqli->prepare($insertSQL);

            $cnt = 0;
            foreach ($toInsert as $imgID) {
                $stmtIns->bind_param("ii", $imgID, $categoryInfo['id']);
                $stmtIns->execute();
                $cnt++;
            }

            $stmtIns->close();
            echo "成功插入 $cnt 条新关联。\n";
            break;
        } elseif (strtolower($confirm) === 'n') {
            echo "已选择不写入新关联，脚本结束。\n";
            break;
        } elseif (strtolower($confirm) === 'q') {
            echo "程序结束。\n";
            exit;
        } else {
            echo "输入非法，请输入 y / n 或 q。\n";
        }
    }
} else {
    echo "无需写入新关联，脚本结束。\n";
}

// 关闭数据库连接
$mysqli->close();

echo "程序执行完毕。\n";
