<?php
require_once '08_db_config.php'; // 引入数据库连接配置

echo "=======================================================\n";
echo "  papers 表管理脚本（终端交互版）\n";
echo "=======================================================\n";
echo "请选择要执行的功能编号：\n";
echo "1. 打印出 papers 表中的所有数据\n";
echo "2. 打印出最后写入的 10 条完整数据\n";
echo "3. 查找 doi 重复的行\n";
echo "4. 提示用户输入 doi，然后打印包含该 doi 的所有行\n";
echo "5. 打印出表格中所有 status = 'N' 行对应的 doi\n";
echo "6. 打印出数据库中 status 为 'CL','C','L','N','DW','DL' 各个值的数据条数，以及数据总条数\n";
echo "7. 列出所有字段名称，用户通过输入字段序号（可输入多个，用空格分隔）来打印对应字段的值\n";
echo "8. 打印出 papers 的表结构 (DESCRIBE papers)\n";
echo "9. 修改表中 title 的 varchar 最大存储长度\n";
echo "10. 根据指定 doi 更新 title 的值\n";
echo "11. 根据指定 doi 更新 journal_name 的值\n";
echo "请输入序号并回车：";

$choice = trim(fgets(STDIN));

// 根据选择执行对应操作
switch ($choice) {
    case '1':
        // 1. 打印出 papers 表中的所有数据
        $sql = "SELECT * FROM papers";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            echo "==== papers 表所有数据 ====\n";
            // 获取字段名
            $fieldsInfo = $result->fetch_fields();
            // 输出表头
            $headers = array_map(function($f){return $f->name;}, $fieldsInfo);
            echo implode(" | ", $headers) . "\n";
            echo str_repeat("-", 80) . "\n";

            $result->data_seek(0); // 重置游标
            while ($row = $result->fetch_assoc()) {
                $line = [];
                foreach ($headers as $h) {
                    $line[] = $row[$h];
                }
                echo implode(" | ", $line) . "\n";
            }
        } else {
            echo "papers 表中没有数据或查询失败。\n";
        }
        break;

    case '2':
        // 2. 打印出最后写入的 10 条完整数据
        $sql = "SELECT * FROM papers ORDER BY paperID DESC LIMIT 10";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            echo "==== 最后写入的 10 条数据 ====\n";
            $fieldsInfo = $result->fetch_fields();
            $headers = array_map(function($f){return $f->name;}, $fieldsInfo);
            echo implode(" | ", $headers) . "\n";
            echo str_repeat("-", 80) . "\n";

            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                $line = [];
                foreach ($headers as $h) {
                    $line[] = $row[$h];
                }
                echo implode(" | ", $line) . "\n";
            }
        } else {
            echo "查询失败或无数据。\n";
        }
        break;

    case '3':
        // 3. 查找 doi 重复的行
        // 方式一：只列出重复 doi 及其出现次数
        $sql = "SELECT doi, COUNT(*) as cnt
                FROM papers
                GROUP BY doi
                HAVING cnt > 1 AND doi <> ''";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            echo "==== 重复的 DOI 及出现次数 ====\n";
            while ($row = $result->fetch_assoc()) {
                echo "DOI: {$row['doi']} - 重复次数: {$row['cnt']}\n";
            }
        } else {
            echo "没有检测到重复的 doi 或查询失败。\n";
        }

        // 方式二：打印出所有重复行的完整信息
        echo "\n==== 重复 doi 的行详细信息 ====\n";
        $sqlDup = "
            SELECT *
            FROM papers
            WHERE doi IN (
                SELECT doi
                FROM papers
                GROUP BY doi
                HAVING COUNT(*) > 1 
                AND doi <> ''
            )";
        $resDup = $mysqli->query($sqlDup);
        if ($resDup && $resDup->num_rows > 0) {
            $fieldsInfo = $resDup->fetch_fields();
            $headers = array_map(function($f){return $f->name;}, $fieldsInfo);
            echo implode(" | ", $headers) . "\n";
            echo str_repeat("-", 80) . "\n";

            $resDup->data_seek(0);
            while ($row = $resDup->fetch_assoc()) {
                $line = [];
                foreach ($headers as $h) {
                    $line[] = $row[$h];
                }
                echo implode(" | ", $line) . "\n";
            }
        } else {
            echo "无重复行或查询失败。\n";
        }
        break;

    case '4':
        // 4. 提示用户输入 doi，然后打印包含该 doi 的所有行
        echo "请输入要查找的 DOI: ";
        $searchDoi = trim(fgets(STDIN));
        if ($searchDoi === '') {
            echo "未输入任何 doi。\n";
            break;
        }
        $searchDoiEsc = $mysqli->real_escape_string($searchDoi);
        $sql = "SELECT * FROM papers WHERE doi LIKE '%$searchDoiEsc%'";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            echo "==== 包含 doi '{$searchDoi}' 的行 ====\n";
            $fieldsInfo = $result->fetch_fields();
            $headers = array_map(function($f){return $f->name;}, $fieldsInfo);
            echo implode(" | ", $headers) . "\n";
            echo str_repeat("-", 80) . "\n";

            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                $line = [];
                foreach ($headers as $h) {
                    $line[] = $row[$h];
                }
                echo implode(" | ", $line) . "\n";
            }
        } else {
            echo "找不到包含该 doi 的记录。\n";
        }
        break;

    case '5':
        // 5. 打印出表格中所有 status = "N" 行对应的 doi
        $sql = "SELECT doi FROM papers WHERE status = 'N'";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            echo "==== status = 'N' 的所有行对应的 doi ====\n";
            while ($row = $result->fetch_assoc()) {
                echo $row['doi'] . "\n";
            }
        } else {
            echo "找不到 status = 'N' 的记录或查询失败。\n";
        }
        break;

    case '6':
        // 6. 打印出数据库中 status 为 'CL','C','L','N','DW','DL' 各个值的数据条数，以及数据总条数
        $statuses = ['CL','C','L','N','DW','DL'];
        echo "==== 各 status 值对应的条数 ====\n";
        $totalCount = 0;

        foreach ($statuses as $st) {
            $sql = "SELECT COUNT(*) as cnt FROM papers WHERE status = '$st'";
            $res = $mysqli->query($sql);
            $count = 0;
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $count = $row['cnt'];
            }
            echo "Status = $st : $count 条\n";
            $totalCount += $count;
        }

        // 总条数（不区分 status）
        $sqlTotal = "SELECT COUNT(*) as total FROM papers";
        $resTotal = $mysqli->query($sqlTotal);
        if ($resTotal && $resTotal->num_rows > 0) {
            $row = $resTotal->fetch_assoc();
            $dbTotal = $row['total'];
            echo "\n数据库中全部条数：{$dbTotal}\n";
        } else {
            echo "\n无法查询数据库总条数。\n";
        }
        break;

    case '7':
        // 7. 列出所有字段名称，用户通过输入字段序号（可输入多个，用空格分隔）来打印对应字段的值
        $sqlDesc = "DESCRIBE papers";
        $resDesc = $mysqli->query($sqlDesc);
        $fields = [];
        if ($resDesc && $resDesc->num_rows > 0) {
            echo "==== papers 表字段列表 ====\n";
            $i = 1;
            while ($row = $resDesc->fetch_assoc()) {
                $fields[$i] = $row['Field'];
                echo $i . ". " . $row['Field'] . "\n";
                $i++;
            }
        } else {
            echo "查询 papers 表结构失败。\n";
            break;
        }

        echo "\n请选择字段序号（可输入多个，用空格分隔）：";
        $input = trim(fgets(STDIN));
        if ($input === '') {
            echo "未输入任何序号。\n";
            break;
        }

        // 将用户输入拆分为多个序号
        $indexes = explode(' ', $input);
        $selectedFields = [];
        foreach ($indexes as $idx) {
            $idx = intval($idx);
            if (isset($fields[$idx])) {
                $selectedFields[] = $mysqli->real_escape_string($fields[$idx]);
            }
        }

        // 如果没有有效的字段被选中，退出
        if (empty($selectedFields)) {
            echo "无效的字段序号。\n";
            break;
        }

        // 构建 SELECT 查询：选中多个字段
        $sql = "SELECT `" . implode("`, `", $selectedFields) . "` FROM papers";
        $result = $mysqli->query($sql);

        if ($result && $result->num_rows > 0) {
            // 输出选中的字段名作为表头
            echo "==== 选择的字段的所有值 ====\n";
            echo implode(" | ", $selectedFields) . "\n";
            echo str_repeat("-", 80) . "\n";

            while ($row = $result->fetch_assoc()) {
                $line = [];
                foreach ($selectedFields as $f) {
                    $line[] = $row[$f];
                }
                echo implode(" | ", $line) . "\n";
            }
        } else {
            echo "无数据或查询失败。\n";
        }
        break;

    case '8':
        // 8. 打印出 papers 的表结构 (DESCRIBE papers)
        $sql = "DESCRIBE papers";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            echo "==== papers 表结构 ====\n";
            echo "Field | Type | Null | Key | Default | Extra\n";
            echo str_repeat("-", 80) . "\n";
            while ($row = $result->fetch_assoc()) {
                echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']} | {$row['Extra']}\n";
            }
        } else {
            echo "查询失败。\n";
        }
        break;

    case '9':
        // 9. 修改表中 title 的 varchar 最大存储长度
        // 先获取当前长度
        $sqlCheckTitle = "SHOW COLUMNS FROM papers LIKE 'title'";
        $resCheckTitle = $mysqli->query($sqlCheckTitle);
        $currentMaxLen = '';
        if ($resCheckTitle && $resCheckTitle->num_rows > 0) {
            $row = $resCheckTitle->fetch_assoc();
            if (preg_match('/varchar\((\d+)\)/i', $row['Type'], $matches)) {
                $currentMaxLen = $matches[1];
            }
        }
        echo "当前 title 字段最大长度: {$currentMaxLen}\n(推荐值为 355)\n";

        echo "请输入新的最大长度：";
        $newLen = trim(fgets(STDIN));
        echo "确认是否用新的最大长度替换旧值？输入 y 确认，n 或其它表示取消：";
        $confirm = trim(fgets(STDIN));

        if (strtolower($confirm) === 'y') {
            if (!is_numeric($newLen) || intval($newLen) <= 0) {
                echo "新的最大长度必须是正整数。\n";
            } else {
                $newLen = intval($newLen);
                $sqlAlter = "ALTER TABLE papers MODIFY title VARCHAR($newLen)";
                if ($mysqli->query($sqlAlter)) {
                    echo "成功将 title 的最大长度修改为：$newLen\n";
                } else {
                    echo "修改失败，错误信息：{$mysqli->error}\n";
                }
            }
        } else {
            echo "已取消操作。\n";
        }
        break;

    case '10':
        // 10. 根据指定 doi 更新 title 的值
        echo "请输入要更新的 doi: ";
        $updateDoi = trim(fgets(STDIN));
        if ($updateDoi === '') {
            echo "未输入 doi。\n";
            break;
        }

        // 查询原有 title
        $doiEsc = $mysqli->real_escape_string($updateDoi);
        $sqlFind = "SELECT title FROM papers WHERE doi = '$doiEsc' LIMIT 1";
        $resFind = $mysqli->query($sqlFind);
        $oldTitle = null;
        if ($resFind && $resFind->num_rows > 0) {
            $row = $resFind->fetch_assoc();
            $oldTitle = $row['title'];
            echo "当前数据库中，该 doi = '{$updateDoi}' 的 title 为：{$oldTitle}\n";
        } else {
            echo "未找到该 doi 对应的记录。\n";
        }

        echo "请输入新的 title: ";
        $newTitle = trim(fgets(STDIN));
        echo "你输入的新 title 为：{$newTitle}\n";
        echo "确认是否将其更新到数据库？输入 y 确认，n 或其它表示取消：";
        $confirmTitle = trim(fgets(STDIN));

        if (strtolower($confirmTitle) === 'y') {
            $titleEsc = $mysqli->real_escape_string($newTitle);
            $sqlUpdate = "UPDATE papers SET title = '$titleEsc' WHERE doi = '$doiEsc'";
            if ($mysqli->query($sqlUpdate)) {
                echo "成功更新。新的 title 值为：{$newTitle}\n";
            } else {
                echo "更新失败，错误信息：{$mysqli->error}\n";
            }
        } else {
            echo "已取消更新操作。\n";
        }
        break;

    case '11':
        // 11. 根据指定 doi 更新 journal_name 的值
        echo "请输入要更新的 doi: ";
        $updateDoi = trim(fgets(STDIN));
        if ($updateDoi === '') {
            echo "未输入 doi。\n";
            break;
        }

        // 查询原有 journal_name
        $doiEsc = $mysqli->real_escape_string($updateDoi);
        $sqlFind = "SELECT journal_name FROM papers WHERE doi = '$doiEsc' LIMIT 1";
        $resFind = $mysqli->query($sqlFind);
        $oldJournal = null;
        if ($resFind && $resFind->num_rows > 0) {
            $row = $resFind->fetch_assoc();
            $oldJournal = $row['journal_name'];
            echo "当前数据库中，该 doi = '{$updateDoi}' 的 journal_name 为：{$oldJournal}\n";
        } else {
            echo "未找到该 doi 对应的记录。\n";
        }

        echo "请输入新的 journal_name: ";
        $newJournal = trim(fgets(STDIN));
        echo "你输入的新 journal_name 为：{$newJournal}\n";
        echo "确认是否将其更新到数据库？输入 y 确认，n 或其它表示取消：";
        $confirmJournal = trim(fgets(STDIN));

        if (strtolower($confirmJournal) === 'y') {
            $journalEsc = $mysqli->real_escape_string($newJournal);
            $sqlUpdate = "UPDATE papers SET journal_name = '$journalEsc' WHERE doi = '$doiEsc'";
            if ($mysqli->query($sqlUpdate)) {
                echo "成功更新。新的 journal_name 值为：{$newJournal}\n";
            } else {
                echo "更新失败，错误信息：{$mysqli->error}\n";
            }
        } else {
            echo "已取消更新操作。\n";
        }
        break;

    default:
        echo "无效的功能编号或未输入序号。\n";
        break;
}

// 关闭数据库连接
$mysqli->close();
exit(0);
