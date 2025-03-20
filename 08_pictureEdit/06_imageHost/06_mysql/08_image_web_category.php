<?php
// 引入数据库配置
include '08_db_config.php';

/**
 * 根据图片 id 查询 images 表中的相关信息
 *
 * @param int $imageId
 * @return array|null
 */
function getImageInfo($imageId) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();

    if ($image) {
        // 强制转换相关字段为数值类型，以避免前端比较类型不一致
        $image['id']           = (int)$image['id'];
        $image['likes']        = (int)$image['likes'];
        $image['dislikes']     = (int)$image['dislikes'];
        $image['image_exists'] = (int)$image['image_exists'];
        $image['star']         = (int)$image['star'];
    }

    return $image ?: null;
}

/**
 * 查询 Categories 表中的所有分类（并强制将 id 转为整型）
 *
 * @return array
 */
function getAllCategories() {
    global $mysqli;
    $query = "SELECT id, category_name FROM Categories ORDER BY id";
    $result = $mysqli->query($query);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id'            => (int)$row['id'],
            'category_name' => $row['category_name']
        ];
    }
    return $categories;
}

/**
 * 查询某一图片在 PicCategories 表里所属的所有分类（并强制将 id 转为整型）
 * 返回该图片对应的分类ID和名称
 *
 * @param int $imageId
 * @return array
 */
function getCategoriesOfImage($imageId) {
    global $mysqli;
    $stmt = $mysqli->prepare("
        SELECT c.id, c.category_name
        FROM PicCategories pc
        JOIN Categories c ON pc.category_id = c.id
        WHERE pc.image_id = ?
    ");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id'            => (int)$row['id'],
            'category_name' => $row['category_name']
        ];
    }
    $stmt->close();

    return $categories;
}

/**
 * 查询 PicCategories 中，某一分类下的所有图片 id（并强制将 image_id 转为整型）
 *
 * @param int $categoryId
 * @return array
 */
function getImagesOfCategory($categoryId) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT image_id FROM PicCategories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = (int)$row['image_id'];
    }
    $stmt->close();

    return $images;
}

/**
 * 根据输入的图片id和【一组】分类名，更新 PicCategories 中该图片的所属分类
 * 做法：先删除该图片在 PicCategories 的旧记录，再插入新的分类关系
 *
 * @param int   $imageId
 * @param array $categoryNames
 * @return void
 */
function setImageCategories($imageId, $categoryNames) {
    global $mysqli;

    // 先删除该图片已存在的所有分类关联
    $stmt = $mysqli->prepare("DELETE FROM PicCategories WHERE image_id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $stmt->close();

    // 再依据传入的分类名数组，插入新的关联
    foreach ($categoryNames as $catName) {
        // 检查该分类名在 Categories 中是否存在
        $stmt2 = $mysqli->prepare("SELECT id FROM Categories WHERE category_name = ?");
        $stmt2->bind_param("s", $catName);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($row2 = $result2->fetch_assoc()) {
            $catId = (int)$row2['id'];
            // 插入到 PicCategories
            $stmt3 = $mysqli->prepare("
                INSERT INTO PicCategories (image_id, category_id)
                VALUES (?, ?)
            ");
            $stmt3->bind_param("ii", $imageId, $catId);
            $stmt3->execute();
            $stmt3->close();
        }
        $stmt2->close();
    }
}

// -------------------------------------------------------------------------
// 下面是简易 AJAX 接口，根据 POST 的 action 来调用不同函数并返回 JSON
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {

        // 获取某张图片的全部分类 + 该图片所属的分类
        case 'getCategoriesForImage':
            $imageId = isset($_POST['imageId']) ? intval($_POST['imageId']) : 0;
            $allCategories   = getAllCategories();
            $imageCategories = getCategoriesOfImage($imageId);

            // 返回 JSON 给前端
            echo json_encode([
                'allCategories'   => $allCategories,
                'imageCategories' => $imageCategories
            ]);
            break;

        // 设置某张图片的分类
        case 'setImageCategories':
            $imageId = isset($_POST['imageId']) ? intval($_POST['imageId']) : 0;
            $raw     = $_POST['categories'] ?? '[]';  // 传过来的 JSON 字符串
            $catArr  = json_decode($raw, true);

            if (!is_array($catArr)) {
                echo json_encode(['success' => false, 'error' => 'Invalid categories format']);
                break;
            }

            setImageCategories($imageId, $catArr);
            echo json_encode(['success' => true]);
            break;

        default:
            // 未知 action
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }

    exit; // 处理完后终止
}
?>
