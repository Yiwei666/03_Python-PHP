<?php
// 设置响应头为纯文本，便于在浏览器或命令行中查看输出
header('Content-Type: text/plain; charset=utf-8');

// 1. 包含数据库配置文件并连接数据库
require_once '08_db_config.php';

echo "Successfully connected to the database '{$dbname}'.\n";

// 定义从最新的多少条记录中进行随机选取
$topN = 300;

// 2. 按照新规则选取候选论文
// 步骤 1: 筛选 (doi_type != 'F' or NULL) AND (citation_count = 0)
// 步骤 2: 排序 (paperID DESC)
// 步骤 3: 限制 (LIMIT N)
$sql = "SELECT paperID, doi FROM papers
        WHERE (doi_type != 'F' OR doi_type IS NULL)
        AND citation_count = 0
        AND doi IS NOT NULL AND doi != ''
        ORDER BY paperID DESC
        LIMIT ?";

$stmt_select = $mysqli->prepare($sql);
if ($stmt_select === false) {
    $mysqli->close();
    die('Prepare failed for SELECT: ' . htmlspecialchars($mysqli->error));
}

$stmt_select->bind_param("i", $topN);
$stmt_select->execute();
$result = $stmt_select->get_result();
$stmt_select->close();

// 将所有候选行读入数组
$candidates = $result->fetch_all(MYSQLI_ASSOC);

if (empty($candidates)) {
    $mysqli->close();
    die("Execution finished: No eligible papers with citation_count = 0 found to update.\n");
}

// 步骤 4: 从候选数组中随机选取一行
$randomIndex = array_rand($candidates);
$paper = $candidates[$randomIndex];

$paperID = $paper['paperID'];
$doi = $paper['doi'];

echo "Found " . count($candidates) . " candidate(s). Randomly selected one to update:\n";
echo "  - Paper ID: {$paperID}\n";
echo "  - DOI: {$doi}\n\n";

// 3. 构建 CrossRef API 请求 URL
// 使用 urlencode() 对 DOI 进行编码，防止 DOI 中的特殊字符导致 URL 格式错误
$mailto = 'fangxy@icha.one'; // 礼貌地在请求中带上邮箱
$apiUrl = "https://api.crossref.org/works/" . urlencode($doi) . "?mailto=" . $mailto;

echo "Requesting API URL:\n{$apiUrl}\n\n";

// 4. 使用 cURL 发送 API 请求
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 将响应作为字符串返回
curl_setopt($ch, CURLOPT_USERAGENT, 'CitationUpdater/1.0 (mailto:'.$mailto.')'); // 设置 User-Agent 是一个好习惯
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置30秒超时
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // 获取 HTTP 状态码

// 检查 cURL 请求是否出错
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    $mysqli->close();
    die("cURL Error: " . $error_msg . "\n");
}

curl_close($ch);

// 5. 处理 API 响应
if ($http_code == 200) {
    echo "API request successful (HTTP 200 OK).\n";
    
    // 解析返回的 JSON 数据
    $data = json_decode($response, true); // 第二个参数为 true，返回关联数组

    if (json_last_error() !== JSON_ERROR_NONE) {
        $mysqli->close();
        die("Error: Failed to parse JSON response.\n");
    }

    // 检查所需字段是否存在且有效
    // CrossRef API 的数据通常在 'message' 键下
    if (isset($data['message']['is-referenced-by-count']) && 
        is_numeric($data['message']['is-referenced-by-count']) && 
        $data['message']['is-referenced-by-count'] >= 0) {
        
        $citationCount = (int) $data['message']['is-referenced-by-count'];
        echo "Found citation count: {$citationCount}\n";
        
        // 6. 更新数据库中的 citation_count 字段
        // 使用预处理语句（Prepared Statements）防止 SQL 注入
        $updateSql = "UPDATE papers SET citation_count = ? WHERE paperID = ?";
        
        $stmt_update = $mysqli->prepare($updateSql);
        
        if ($stmt_update === false) {
            $mysqli->close();
            die('Prepare failed for UPDATE: ' . htmlspecialchars($mysqli->error));
        }
        
        // "ii" 表示两个参数都是整数类型
        $stmt_update->bind_param("ii", $citationCount, $paperID);
        
        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                echo "Successfully updated paperID {$paperID} with citation count {$citationCount}.\n";
            } else {
                echo "Notice: Update query ran, but no rows were changed. The citation count may already be up-to-date.\n";
            }
        } else {
            echo "Error: Failed to update the database. " . htmlspecialchars($stmt_update->error) . "\n";
        }
        
        $stmt_update->close();

    } else {
        echo "Warning: 'is-referenced-by-count' field not found or invalid in the API response.\n";
    }

} else {
    echo "Error: API request failed with HTTP status code: {$http_code}\n";
    echo "Response body:\n" . $response . "\n";
}

// 7. 关闭数据库连接
$mysqli->close();
echo "\nScript finished and database connection closed.\n";
?>
