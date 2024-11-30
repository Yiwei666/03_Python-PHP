<?php
// 文件路径
$file_path = "/home/01_html/06_journal_Abbreviation.txt";

// 读取文件内容并解析为数组
function getJournalData($file_path)
{
    $journals = [];
    if (file_exists($file_path)) {
        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            list($full, $abbreviation) = explode('/', $line);
            $journals[trim($full)] = trim($abbreviation);
        }
    }
    return $journals;
}

// 写入文件并排序
function saveJournalData($file_path, $journals)
{
    uksort($journals, function ($a, $b) {
        return strcasecmp($a, $b); // 忽略大小写排序
    });
    $lines = [];
    foreach ($journals as $key => $value) {
        $lines[] = "$key/$value";
    }
    file_put_contents($file_path, implode("\n", $lines));
}

// 处理表单提交
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $abbreviation = trim($_POST['abbreviation']);
    if (!empty($full_name) && !empty($abbreviation)) {
        $journals = getJournalData($file_path);
        if (array_key_exists($full_name, $journals)) {
            $message = "键（期刊全称）已存在，提交失败。";
        } else {
            $journals[$full_name] = $abbreviation;
            saveJournalData($file_path, $journals);
            $message = "键值对已成功保存。";
        }
    } else {
        $message = "期刊全称和简写不能为空。";
    }
}

// 获取文件数据以供查看
$journals = getJournalData($file_path);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>期刊全称与简写管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            background-color: #e9ecef;
            border-left: 4px solid #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>期刊全称与简写管理</h1>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="full_name">期刊全称</label>
            <input type="text" id="full_name" name="full_name" required>
            <label for="abbreviation">简写</label>
            <input type="text" id="abbreviation" name="abbreviation" required>
            <button type="submit">提交</button>
        </form>
        <button onclick="document.getElementById('view_table').style.display='block'">查看所有期刊</button>
        <div id="view_table" style="display: none;">
            <table>
                <thead>
                    <tr>
                        <th>序号</th>
                        <th>期刊全称</th>
                        <th>简写</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 1; foreach ($journals as $key => $value): ?>
                        <tr>
                            <td><?= $index++ ?></td>
                            <td><?= htmlspecialchars($key) ?></td>
                            <td><?= htmlspecialchars($value) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
