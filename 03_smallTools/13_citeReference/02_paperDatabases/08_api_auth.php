<?php
// 08_api_auth.php

/**
 * 检查请求头中的 API Key 是否有效。
 * 如果无效，则返回 401 并终止执行。
 */
function checkApiKey() {
    // 从请求头获取全部 Header
    $headers = getallheaders();

    // 这里设置服务器端预设的有效 API Key （生产环境建议更安全的存储方式）
    $validKey = 'YOUR_API_KEY_HERE';

    // 判断是否存在 X-Api-Key 且是否与预设的 validKey 匹配
    if (!isset($headers['X-Api-Key']) || $headers['X-Api-Key'] !== $validKey) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401); // 未授权
        echo json_encode(['success' => false, 'message' => 'Invalid or missing API key']);
        exit();
    }
}
