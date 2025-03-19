#!/usr/bin/env php
<?php
/**
 * manage_categories.php
 * 
 * 说明：在命令行中执行本脚本，与用户交互以对 Categories 表进行增、改、删操作。
 * 执行完后会显示当前 Categories 表的所有记录。
 */

// 引入数据库配置文件（确保 08_db_config.php 与本脚本在同一目录下）
require_once '08_db_config.php';

/**
 * 显示当前 Categories 表的所有记录
 */
function showAllCategories($mysqli) {
    echo "\n当前 Categories 表内容如下：\n";
    $result = $mysqli->query("SELECT * FROM Categories");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Category Name: " . $row['category_name'] . "\n";
        }
        $result->free();
    } else {
        echo "查询 Categories 表失败: " . $mysqli->error . "\n";
    }
}

/**
 * 读取用户在命令行输入的信息
 *
 * @param string $prompt 提示信息
 * @return string 用户输入的字符串(去除首尾空白)
 */
function readlineCLI($prompt = "") {
    if (!empty($prompt)) {
        echo $prompt;
    }
    return trim(fgets(STDIN));
}

// 显示操作选项
echo "请选择操作（输入序号并回车）：\n";
echo "1. 添加新的图片分类\n";
echo "2. 修改已有分类名\n";
echo "3. 删除已有分类名\n";
$choice = readlineCLI("请输入操作序号：");

// 根据用户选择执行相应操作
switch ($choice) {
    case "1":
        // 添加新的分类
        $newCategory = readlineCLI("\n请输入要添加的分类名称：");

        // 检查是否已存在同名分类
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM Categories WHERE category_name = ?");
        $stmt->bind_param("s", $newCategory);
        $stmt->execute();
        $result = $stmt->get_result();
        $countRow = $result->fetch_assoc();
        $exists = $countRow['c'] > 0;
        $stmt->close();

        if ($exists) {
            echo "分类 '{$newCategory}' 已存在，无法添加。\n";
        } else {
            // 确认执行
            $confirm = readlineCLI("是否确认添加分类 '{$newCategory}'? (y/n): ");
            if (strtolower($confirm) === 'y') {
                // 执行插入操作
                $stmt = $mysqli->prepare("INSERT INTO Categories (category_name) VALUES (?)");
                $stmt->bind_param("s", $newCategory);
                if ($stmt->execute()) {
                    echo "分类 '{$newCategory}' 添加成功！\n";
                } else {
                    echo "添加分类失败: " . $mysqli->error . "\n";
                }
                $stmt->close();
            } else {
                echo "已取消添加分类。\n";
            }
        }
        break;

    case "2":
        // 修改已有分类名
        $oldName = readlineCLI("\n请输入要修改的原分类名称：");

        // 检查原分类名是否存在
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM Categories WHERE category_name = ?");
        $stmt->bind_param("s", $oldName);
        $stmt->execute();
        $result = $stmt->get_result();
        $countRow = $result->fetch_assoc();
        $oldExists = $countRow['c'] > 0;
        $stmt->close();

        if (!$oldExists) {
            echo "分类 '{$oldName}' 不存在，无法进行修改。\n";
            break;
        }

        $newName = readlineCLI("请输入新的分类名称：");

        // 检查新分类名是否已存在
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM Categories WHERE category_name = ?");
        $stmt->bind_param("s", $newName);
        $stmt->execute();
        $result = $stmt->get_result();
        $countRow = $result->fetch_assoc();
        $newExists = $countRow['c'] > 0;
        $stmt->close();

        if ($newExists) {
            echo "新的分类名称 '{$newName}' 已存在，无法使用。\n";
            break;
        }

        // 确认执行
        $confirm = readlineCLI("是否确认将分类名 '{$oldName}' 修改为 '{$newName}'? (y/n): ");
        if (strtolower($confirm) === 'y') {
            $stmt = $mysqli->prepare("UPDATE Categories SET category_name = ? WHERE category_name = ?");
            $stmt->bind_param("ss", $newName, $oldName);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "分类 '{$oldName}' 已成功修改为 '{$newName}'。\n";
                } else {
                    echo "未能修改分类，可能分类名未变化或出错。\n";
                }
            } else {
                echo "修改分类名失败: " . $mysqli->error . "\n";
            }
            $stmt->close();
        } else {
            echo "已取消修改分类。\n";
        }
        break;

    case "3":
        // 删除已有分类名
        $delName = readlineCLI("\n请输入要删除的分类名称：");

        // 检查该分类名是否存在
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM Categories WHERE category_name = ?");
        $stmt->bind_param("s", $delName);
        $stmt->execute();
        $result = $stmt->get_result();
        $countRow = $result->fetch_assoc();
        $delExists = $countRow['c'] > 0;
        $stmt->close();

        if (!$delExists) {
            echo "分类 '{$delName}' 不存在，无法删除。\n";
            break;
        }

        // 确认执行
        $confirm = readlineCLI("是否确认删除分类 '{$delName}'? (y/n): ");
        if (strtolower($confirm) === 'y') {
            $stmt = $mysqli->prepare("DELETE FROM Categories WHERE category_name = ?");
            $stmt->bind_param("s", $delName);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "分类 '{$delName}' 已成功删除。\n";
                } else {
                    echo "分类删除失败，可能名称不匹配或其他原因。\n";
                }
            } else {
                echo "删除分类失败: " . $mysqli->error . "\n";
            }
            $stmt->close();
        } else {
            echo "已取消删除分类。\n";
        }
        break;

    default:
        echo "\n无效的选项，请重新运行脚本并选择 1/2/3。\n";
        break;
}

// 操作结束后，显示当前 Categories 表内容
showAllCategories($mysqli);

// 关闭连接
$mysqli->close();
