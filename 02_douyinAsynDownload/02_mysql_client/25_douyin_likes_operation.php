<?php
// 设置响应头为 JSON 格式
header('Content-Type: application/json');

// 引入数据库配置文件
require_once '/home/01_html/03_mysql_douyin/03_db_config.php';

// 初始化响应数组
$response = ['success' => false, 'message' => '无效的请求.'];

// 检查是否通过 POST 方法传递了 video_name 和 action 参数
if (isset($_POST['video_name']) && isset($_POST['action'])) {
    $videoName = $_POST['video_name'];
    $action = $_POST['action'];

    // 根据 action 参数准备 SQL 语句
    $sql = '';
    if ($action === 'increment') {
        // 增加 likes
        $sql = "UPDATE tk_videos SET likes = likes + 1 WHERE video_name = ?";
    } elseif ($action === 'decrement') {
        // 减少 likes，并使用 GREATEST 函数防止其小于 0
        $sql = "UPDATE tk_videos SET likes = GREATEST(0, likes - 1) WHERE video_name = ?";
    }

    if (!empty($sql)) {
        // 使用预处理语句防止 SQL 注入
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $videoName);

        // 执行更新操作
        if ($stmt->execute()) {
            // 更新成功后，获取最新的 likes 值
            $select_stmt = $mysqli->prepare("SELECT likes FROM tk_videos WHERE video_name = ?");
            $select_stmt->bind_param("s", $videoName);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            $row = $result->fetch_assoc();
            
            // 构建成功的响应
            $response = [
                'success' => true,
                'likes' => $row['likes']
            ];
            $select_stmt->close();
        } else {
            $response['message'] = '数据库更新失败: ' . $mysqli->error;
        }
        $stmt->close();
    } else {
        $response['message'] = '无效的操作类型.';
    }
}

// 关闭数据库连接
$mysqli->close();

// 以 JSON 格式输出响应结果
echo json_encode($response);
?>
