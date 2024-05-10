<?php
// 引入数据库配置文件
include '05_db_config.php';

// 确保是 POST 请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取 POST 数据
    $videoId = isset($_POST['videoId']) ? intval($_POST['videoId']) : null;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // 根据 action 更新数据库
    if ($videoId && ($action === 'like' || $action === 'dislike')) {
        if ($action === 'like') {
            $query = "UPDATE videos SET likes = likes + 1 WHERE id = ?";
        } elseif ($action === 'dislike') {
            $query = "UPDATE videos SET dislikes = dislikes + 1 WHERE id = ?";  // 注意此处也改为加一
        }

        // 准备和执行 SQL 语句
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("i", $videoId);
            $stmt->execute();
            $stmt->close();

            // 获取更新后的值
            $result = $mysqli->query("SELECT likes, dislikes FROM videos WHERE id = $videoId");
            $row = $result->fetch_assoc();

            // 返回 JSON 数据
            echo json_encode($row);
        } else {
            echo json_encode(['error' => 'Failed to prepare statement']);
        }
    } else {
        echo json_encode(['error' => 'Invalid input']);
    }
} else {
    // 非 POST 请求处理
    echo json_encode(['error' => 'Invalid request method']);
}

?>
