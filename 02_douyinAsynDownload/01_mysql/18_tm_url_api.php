<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// 引入数据库配置文件
include '18_db_config.php';

// 设置返回 JSON
header('Content-Type: application/json; charset=utf-8');

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 判断是否为 POST 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取剪贴板文本
    // 如果你在前端 fetch 中使用的键是 "clipboardContent"，此处要对应
    $clipboardContent = isset($_POST['clipboardContent']) ? $_POST['clipboardContent'] : '';

    // 准备返回结果
    $response = [
        'status' => 'error',
        'message' => '',
        'detail' => []
    ];

    // 如果提交内容为空，直接返回
    if (trim($clipboardContent) === '') {
        $response['message'] = '提交内容为空或未获取到剪贴板内容';
        echo json_encode($response);
        exit;
    }

    // 利用正则从文本中提取所有链接
    // 这里与 18_url_get.php 中的逻辑保持一致
    preg_match_all('/https:\/\/[^ ]+/', $clipboardContent, $matches);
    $links = $matches[0];

    if (empty($links)) {
        $response['message'] = '未找到有效的链接，请重新输入';
        echo json_encode($response);
        exit;
    }

    // 遍历处理每一个链接，插入数据库
    $insertCount = 0;
    foreach ($links as $link) {
        // 检查是否已存在
        $checkQuery = "SELECT 1 FROM douyin_videos WHERE video_url = ?";
        $stmt = $mysqli->prepare($checkQuery);
        $stmt->bind_param("s", $link);
        $stmt->execute();
        $result = $stmt->get_result();

        // 如果不存在，则插入
        if (!$result->fetch_assoc()) {
            $timestamp = date('Y-m-d H:i:s');
            $insertQuery = "INSERT INTO douyin_videos (video_url, url_write_time) VALUES (?, ?)";
            $insertStmt = $mysqli->prepare($insertQuery);
            $insertStmt->bind_param("ss", $link, $timestamp);
            $insertStmt->execute();

            $insertCount++;
            $response['detail'][] = [
                'link' => $link,
                'status' => 'success',
                'message' => "链接{$link} 已成功保存到数据库！"
            ];
        } else {
            // 已存在则提示
            $response['detail'][] = [
                'link' => $link,
                'status' => 'exists',
                'message' => "链接{$link} 已存在，不进行写入。"
            ];
        }
    }

    // 根据写入结果设置整体状态和消息
    if ($insertCount > 0) {
        $response['status'] = 'success';
        $response['message'] = "共插入 {$insertCount} 条新记录。";
    } else {
        $response['status'] = 'warning';
        $response['message'] = "没有新的链接写入到数据库。";
    }

    // 输出 JSON
    echo json_encode($response);
    exit;
} else {
    // 非 POST 请求
    echo json_encode([
        'status' => 'error',
        'message' => '请使用 POST 方法提交数据'
    ]);
    exit;
}
