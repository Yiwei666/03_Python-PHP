<?php
header('Content-Type: text/plain; charset=utf-8');

// 引入数据库配置
require_once '/home/01_html/08_db_config.php';

// 设置字符集，避免中文乱码
$mysqli->set_charset("utf8mb4");

// 目标表名
$tableName = 'Categories';

try {
    // 1. 先读取所有数据进行格式检查
    $sql = "SELECT id, category_name FROM {$tableName} ORDER BY id ASC";
    $result = $mysqli->query($sql);

    if (!$result) {
        throw new Exception("查询失败: " . $mysqli->error);
    }

    $rows = [];
    $invalidRows = [];

    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $categoryName = $row['category_name'];

        // 去掉首尾空白后检查
        $trimmed = trim($categoryName);

        // 要求格式：A + 至少一个空格 + B
        // 例如：1.8 知世
        if (!preg_match('/^(\S+)\s+(.+)$/u', $trimmed, $matches)) {
            $invalidRows[] = [
                'id' => $id,
                'category_name' => $categoryName
            ];
            continue;
        }

        // 第二部分 B
        $secondPart = trim($matches[2]);

        // 再次确保第二部分不是空
        if ($secondPart === '') {
            $invalidRows[] = [
                'id' => $id,
                'category_name' => $categoryName
            ];
            continue;
        }

        $newCategoryName = $id . ' ' . $secondPart;

        $rows[] = [
            'id' => $id,
            'old_category_name' => $categoryName,
            'second_part' => $secondPart,
            'new_category_name' => $newCategoryName
        ];
    }

    $result->free();

    // 2. 如果有不符合格式的记录，则停止
    if (!empty($invalidRows)) {
        echo "发现以下记录不满足“A + 空格 + B”格式，已停止更新：\n\n";
        foreach ($invalidRows as $badRow) {
            echo "id = {$badRow['id']}, category_name = [{$badRow['category_name']}]\n";
        }
        exit(1);
    }

    // 3. 开始事务
    $mysqli->begin_transaction();

    $stmt = $mysqli->prepare("UPDATE {$tableName} SET category_name = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("预处理失败: " . $mysqli->error);
    }

    $updatedCount = 0;

    foreach ($rows as $row) {
        $newCategoryName = $row['new_category_name'];
        $id = $row['id'];

        $stmt->bind_param("si", $newCategoryName, $id);

        if (!$stmt->execute()) {
            throw new Exception("更新失败，id={$id}，错误：" . $stmt->error);
        }

        $updatedCount++;
        echo "已更新: id={$id} | [{$row['old_category_name']}] => [{$newCategoryName}]\n";
    }

    $stmt->close();

    // 4. 提交事务
    $mysqli->commit();

    echo "\n全部更新完成，共更新 {$updatedCount} 条记录。\n";

} catch (Exception $e) {
    // 回滚事务
    if ($mysqli->errno || $mysqli->error || $mysqli->connect_errno === 0) {
        try {
            $mysqli->rollback();
        } catch (Exception $rollbackException) {
            // 忽略回滚异常，优先输出原始错误
        }
    }

    echo "执行失败：" . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
?>
