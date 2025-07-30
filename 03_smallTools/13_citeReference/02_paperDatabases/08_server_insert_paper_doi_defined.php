#!/usr/bin/env php
<?php
/**
 * 08_server_insert_paper_doi_defined.php
 * 用于手动录入论文信息，并强制要求 doi 项存在且非空
 * 在命令行下运行：php 08_server_insert_paper_doi_defined.php
 */

require '08_db_config.php';

// 读取多行 JSON 输入，空行结束
function readJsonInput() {
    echo "请输入论文元数据（JSON 格式），输入完毕后按两次回车：\n";
    $json = '';
    while (true) {
        $line = fgets(STDIN);
        if ($line === false) {
            break;
        }
        // 空行且已有内容时结束
        if (trim($line) === '' && $json !== '') {
            break;
        }
        // 跳过前导空行
        if (trim($line) === '') {
            continue;
        }
        $json .= $line;
    }
    return trim($json);
}

echo "=== 论文信息录入脚本 ===\n";

while (true) {
    // 1-2. JSON 格式检查 & 强制 doi 存在
    while (true) {
        $jsonStr = readJsonInput();
        $data = json_decode($jsonStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON 格式错误：", json_last_error_msg(), "，请重新输入。\n\n";
            continue;
        }
        if (!isset($data['doi']) || trim($data['doi']) === '') {
            echo "缺少 doi 项或值为空，请重新输入。\n\n";
            continue;
        }
        break;
    }

    // 4. 重复检查：doi 或 title（忽略大小写）
    while (true) {
        $doi   = $data['doi'];
        $title = isset($data['title']) ? $data['title'] : '';
        $stmtDup = $mysqli->prepare(
            "SELECT COUNT(*) FROM papers WHERE LOWER(doi)=LOWER(?) OR LOWER(title)=LOWER(?)"
        );
        $stmtDup->bind_param('ss', $doi, $title);
        $stmtDup->execute();
        $stmtDup->bind_result($count);
        $stmtDup->fetch();
        $stmtDup->close();

        if ($count > 0) {
            echo "检测到已有相同 DOI 或 Title 的记录 (count={$count})。\n";
            echo "是否重新输入？(y=重新输入, n=继续插入重复, q=取消): ";
            $dupOp = trim(fgets(STDIN));
            if ($dupOp === 'y') {
                echo "重新输入论文信息。\n\n";
                continue 2;  // 跳回最外层重新输入
            } elseif ($dupOp === 'n') {
                // 继续执行，插入重复
                break;
            } elseif ($dupOp === 'q') {
                echo "已取消，程序退出。\n";
                exit;
            } else {
                echo "非法输入，请输入 y、n 或 q。\n";
                continue;  // 重新询问重复选项
            }
        }
        break;
    }

    // 3. 获取 papers 表结构及默认值
    $columns = [];
    $res = $mysqli->query("SHOW COLUMNS FROM papers");
    if (! $res) {
        die("无法获取 papers 表结构：{$mysqli->error}\n");
    }
    while ($col = $res->fetch_assoc()) {
        $columns[$col['Field']] = [
            'Type'    => $col['Type'],
            'Null'    => $col['Null'],
            'Default' => $col['Default']
        ];
    }

    // 构建待插入的字段及值
    $insertData = [];
    foreach ($columns as $field => $meta) {
        if ($field === 'paperID') continue;
        if (array_key_exists($field, $data) && trim((string)$data[$field]) !== '') {
            $insertData[$field] = $data[$field];
        } elseif ($meta['Default'] !== null) {
            $insertData[$field] = $meta['Default'];
        } elseif ($meta['Null'] === 'YES') {
            $insertData[$field] = null;
        } else {
            $insertData[$field] = '';
        }
    }

    // 5. 打印待插入信息并让用户确认
    echo "\n待插入的论文信息：\n";
    echo json_encode($insertData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n\n";

    while (true) {
        echo "确认插入？(y=是, n=重新输入, q=取消): ";
        $op = trim(fgets(STDIN));
        if ($op === 'y') {
            break 2;
        } elseif ($op === 'n') {
            echo "\n重新输入论文信息。\n\n";
            continue 2;
        } elseif ($op === 'q') {
            echo "已取消，程序退出。\n";
            exit;
        } else {
            echo "非法输入，请输入 y、n 或 q。\n";
        }
    }
}

// --- 插入数据到 papers ---
$fields = array_keys($insertData);
$placeholders = [];
$bindTypes    = '';
$bindValues   = [];
foreach ($fields as $f) {
    if ($insertData[$f] === null) {
        $placeholders[] = 'NULL';
    } else {
        $placeholders[] = '?';
        $t = $columns[$f]['Type'];
        if (preg_match('/^(tinyint|smallint|mediumint|int|bigint)/i', $t)) {
            $bindTypes .= 'i';
            $bindValues[] = (int)$insertData[$f];
        } elseif (preg_match('/^(float|double|decimal)/i', $t)) {
            $bindTypes .= 'd';
            $bindValues[] = (float)$insertData[$f];
        } else {
            $bindTypes .= 's';
            $bindValues[] = $insertData[$f];
        }
    }
}
$sql = "INSERT INTO papers (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
$stmt = $mysqli->prepare($sql);
if (! $stmt) {
    die("准备插入语句失败：{$mysqli->error}\n");
}
if (! empty($bindValues)) {
    $stmt->bind_param($bindTypes, ...$bindValues);
}
if (! $stmt->execute()) {
    die("执行插入失败：{$stmt->error}\n");
}
$paperID = $mysqli->insert_id;
echo "\n✅ 插入成功，新的 paperID = {$paperID}\n";

// 插入 categoryID = 1 的关联
$row = $mysqli->query("SELECT category_name FROM categories WHERE categoryID = 1")->fetch_assoc();
echo "分类 1 对应 name：", $row['category_name'] ?? '不存在', "\n";
$stmt2 = $mysqli->prepare("INSERT INTO paperCategories (paperID, categoryID) VALUES (?, 1)");
$stmt2->bind_param('i', $paperID);
$stmt2->execute();
echo "已添加关联 (paperID={$paperID}, categoryID=1)\n";

// 可选插入 categoryID = 123 的关联
$row123 = $mysqli->query("SELECT category_name FROM categories WHERE categoryID = 123")->fetch_assoc();
if ($row123) {
    echo "分类 123 对应 name：", $row123['category_name'], "\n";
    while (true) {
        echo "是否添加 (paperID={$paperID}, categoryID=123) 关联？(y/n): ";
        $c = trim(fgets(STDIN));
        if ($c === 'y') {
            $stmt3 = $mysqli->prepare("INSERT INTO paperCategories (paperID, categoryID) VALUES (?, 123)");
            $stmt3->bind_param('i', $paperID);
            $stmt3->execute();
            echo "已添加关联 (paperID={$paperID}, categoryID=123)\n";
            break;
        } elseif ($c === 'n') {
            echo "未添加该关联，程序结束。\n";
            break;
        } else {
            echo "非法输入，请输入 y 或 n。\n";
        }
    }
} else {
    echo "分类 123 不存在，程序结束。\n";
}

$mysqli->close();
exit;
?>
