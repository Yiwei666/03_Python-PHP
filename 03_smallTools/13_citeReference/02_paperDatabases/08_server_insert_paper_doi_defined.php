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
    echo "请输入论文元数据（单篇为 JSON 对象，多篇为 JSON 数组），输入完毕后按两次回车：\n";
    $json = '';
    while (true) {
        $line = fgets(STDIN);
        if ($line === false) {
            if ($json === '') {
                echo "输入结束，程序退出。\n";
                exit;
            }
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
        $decoded = json_decode($jsonStr);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON 格式错误：", json_last_error_msg(), "，请重新输入。\n\n";
            continue;
        }
        $isBatch = is_array($decoded);
        if (!($decoded instanceof stdClass) && !$isBatch) {
            echo "输入必须是单篇 JSON 对象或论文对象数组，请重新输入。\n\n";
            continue;
        }
        $papers = $isBatch ? $decoded : [$decoded];
        if (empty($papers)) {
            echo "论文数组不能为空，请重新输入。\n\n";
            continue;
        }
        $errors = [];
        foreach ($papers as $index => $paper) {
            $number = $index + 1;
            if (!($paper instanceof stdClass)) {
                $errors[] = "第 {$number} 篇必须是 JSON 对象。";
                continue;
            }
            $data = (array)$paper;
            if (!isset($data['doi']) || !is_string($data['doi']) || trim($data['doi']) === '') {
                $errors[] = "第 {$number} 篇缺少 doi 项、值为空或不是字符串。";
            }
            foreach ($data as $field => $value) {
                if ($value !== null && !is_string($value) && !is_int($value) && !is_float($value)) {
                    $errors[] = "第 {$number} 篇字段 {$field} 必须是字符串、数字或 null。";
                }
            }
            $papers[$index] = $data;
        }
        if (!empty($errors)) {
            echo implode("\n", $errors), "\n本次输入未写入数据库，请重新输入。\n\n";
            continue;
        }
        break;
    }

    // 4. 重复检查：doi 或 title（忽略大小写）
    $errors = [];
    foreach ($papers as $index => $data) {
        while (true) {
            $doi   = $data['doi'];
            $title = isset($data['title']) ? $data['title'] : '';
            $stmtDup = $mysqli->prepare(
                "SELECT COUNT(*) FROM papers WHERE LOWER(doi)=LOWER(?) OR LOWER(title)=LOWER(?)"
            );
            if (!$stmtDup) {
                die("准备查重语句失败：{$mysqli->error}\n");
            }
            $stmtDup->bind_param('ss', $doi, $title);
            if (!$stmtDup->execute()) {
                die("执行查重失败：{$stmtDup->error}\n");
            }
            $stmtDup->bind_result($count);
            if (!$stmtDup->fetch()) {
                die("读取查重结果失败：{$stmtDup->error}\n");
            }
            $stmtDup->close();

            if ($count > 0) {
                if ($isBatch) {
                    $number = $index + 1;
                    $errors[] = "第 {$number} 篇已有相同 DOI 或 Title 的记录 (count={$count})。";
                    break;
                }
                echo "检测到已有相同 DOI 或 Title 的记录 (count={$count})。\n";
                echo "是否重新输入？(y=重新输入, n=继续插入重复, q=取消): ";
                $dupOp = trim(fgets(STDIN) ?: 'q');
                if ($dupOp === 'y') {
                    echo "重新输入论文信息。\n\n";
                    continue 3;  // 跳回最外层重新输入
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
        if ($isBatch) {
            for ($previous = 0; $previous < $index; $previous++) {
                if (preg_match('/\A' . preg_quote($papers[$previous]['doi'], '/') . '\z/iu', $doi)
                    || preg_match('/\A' . preg_quote((string)($papers[$previous]['title'] ?? ''), '/') . '\z/iu', (string)$title)) {
                    $number = $index + 1;
                    $previousNumber = $previous + 1;
                    $errors[] = "第 {$number} 篇与第 {$previousNumber} 篇的 DOI 或 Title 重复。";
                    break;
                }
            }
        }
    }
    if (!empty($errors)) {
        echo implode("\n", $errors), "\n整批未写入数据库，请重新输入。\n\n";
        continue;
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
    $insertRows = [];
    foreach ($papers as $data) {
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

        $insertRows[] = $insertData;
    }

    // 5. 打印待插入信息并让用户确认
    echo "\n待插入的论文信息：\n";
    echo json_encode($isBatch ? $insertRows : $insertRows[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n\n";

    while (true) {
        echo "确认插入？(y=是, n=重新输入, q=取消): ";
        $op = trim(fgets(STDIN) ?: 'q');
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

// 整批仅选择一次分类，写入前完成选择
$row = $mysqli->query("SELECT category_name FROM categories WHERE categoryID = 1")->fetch_assoc();
echo "分类 1 对应 name：", $row['category_name'] ?? '不存在', "\n";
if (!$row) {
    die("默认分类 1 不存在，本次输入未写入数据库。\n");
}
$addCategory123 = false;
$row123 = $mysqli->query("SELECT category_name FROM categories WHERE categoryID = 123")->fetch_assoc();
if ($row123) {
    echo "分类 123 对应 name：", $row123['category_name'], "\n";
    while (true) {
        echo "是否为本次全部论文添加 categoryID=123 关联？(y/n): ";
        $c = fgets(STDIN);
        if ($c === false) {
            die("输入结束，本次输入未写入数据库。\n");
        }
        $c = trim($c);
        if ($c === 'y') {
            $addCategory123 = true;
            break;
        } elseif ($c === 'n') {
            echo "未选择该关联。\n";
            break;
        } else {
            echo "非法输入，请输入 y 或 n。\n";
        }
    }
} else {
    echo "分类 123 不存在，跳过该关联。\n";
}

$paperIDs = [];
try {
    if (!$mysqli->begin_transaction()) {
        throw new RuntimeException("无法开始事务：{$mysqli->error}");
    }
    foreach ($insertRows as $insertData) {
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
            throw new RuntimeException("准备插入语句失败：{$mysqli->error}");
        }
        if (! empty($bindValues)) {
            $stmt->bind_param($bindTypes, ...$bindValues);
        }
        if (! $stmt->execute()) {
            throw new RuntimeException("执行插入失败：{$stmt->error}");
        }
        $paperID = $mysqli->insert_id;
        $stmt->close();
        $paperIDs[] = $paperID;

        // 插入 categoryID = 1 的关联
        $stmt2 = $mysqli->prepare("INSERT INTO paperCategories (paperID, categoryID) VALUES (?, 1)");
        if (!$stmt2) {
            throw new RuntimeException("准备默认分类关联失败：{$mysqli->error}");
        }
        $stmt2->bind_param('i', $paperID);
        if (!$stmt2->execute()) {
            throw new RuntimeException("添加默认分类关联失败：{$stmt2->error}");
        }
        $stmt2->close();

        // 可选插入 categoryID = 123 的关联
        if ($addCategory123) {
            $stmt3 = $mysqli->prepare("INSERT INTO paperCategories (paperID, categoryID) VALUES (?, 123)");
            if (!$stmt3) {
                throw new RuntimeException("准备可选分类关联失败：{$mysqli->error}");
            }
            $stmt3->bind_param('i', $paperID);
            if (!$stmt3->execute()) {
                throw new RuntimeException("添加可选分类关联失败：{$stmt3->error}");
            }
            $stmt3->close();
        }
    }
    if (!$mysqli->commit()) {
        throw new RuntimeException("提交事务失败：{$mysqli->error}");
    }
} catch (Throwable $e) {
    $mysqli->rollback();
    echo "写入失败，事务已回滚：", $e->getMessage(), "\n";
    $mysqli->close();
    exit(1);
}
foreach ($paperIDs as $paperID) {
    echo "\n✅ 插入成功，新的 paperID = {$paperID}\n";
    echo "已添加关联 (paperID={$paperID}, categoryID=1)\n";
    if ($addCategory123) {
        echo "已添加关联 (paperID={$paperID}, categoryID=123)\n";
    }
}

$mysqli->close();
exit;
?>
