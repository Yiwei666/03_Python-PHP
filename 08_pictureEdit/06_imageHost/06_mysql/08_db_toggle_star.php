<?php
include '08_db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageId = intval($_POST['imageId']);

    // 获取当前图片的star值
    $query = "SELECT star FROM images WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // 切换star值：如果是1则变成0，如果是0则变成1
    $newStarValue = ($row['star'] == 1) ? 0 : 1;

    // 更新数据库中的star值
    $updateQuery = "UPDATE images SET star = ? WHERE id = ?";
    $updateStmt = $mysqli->prepare($updateQuery);
    $updateStmt->bind_param('ii', $newStarValue, $imageId);
    $updateStmt->execute();

    // 返回新的star值
    echo json_encode(['star' => $newStarValue]);
}
?>
