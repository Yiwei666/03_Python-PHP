<?php
/**
 * manage_images.php
 *
 * 根据题目需求，在命令行环境与用户交互，对数据库中的图片进行多条件筛选并执行相应操作。
 */

// 1. 首先引入数据库连接配置，以及需要的同步函数
include '08_db_config.php';         // 创建 $mysqli 数据库连接对象
include '08_db_sync_images.php';    // syncImages() 函数

// 在脚本执行的开头先调用同步函数，确保数据库中已经包含最新的图片信息
// 请根据你的图片存储目录实际路径修改
$local_dir = '/home/01_html/08_x/image/01_imageHost';
syncImages($local_dir);

// -----------------------------------------------------------------------------
// 为了在命令行环境中与用户交互，我们需要一些辅助函数。

/**
 * 从 STDIN 获取一行输入（去掉首尾空白）。
 * @param string $prompt 给用户的提示文本
 * @return string 用户输入的内容
 */
function getUserInput($prompt = '')
{
    echo $prompt;
    $handle = fopen('php://stdin', 'r');
    $line   = fgets($handle);
    if ($line === false) {
        // 若无法读取到输入，则直接退出
        exit("无法读取输入，程序结束。\n");
    }
    return trim($line);
}

/**
 * 解析逗号分隔的范围或单值列表，如 "1-5,10,20-50,51"
 * 返回一个数组，每个元素形如：
 *   ['type' => 'range', 'start' => X, 'end' => Y]
 * 或
 *   ['type' => 'value', 'value' => X]
 * 并检查是否有负数、重叠等非法情况。
 *
 * @param string $input 用户输入的字符串
 * @param bool $allowZero 是否允许0
 * @return array
 */
function parseRanges($input, $allowZero = true)
{
    // 以逗号拆分
    $parts = explode(',', $input);
    $result = [];

    // 临时用于检测重叠的数组（或许可以进一步优化）
    $allValues = [];

    foreach ($parts as $part) {
        $part = trim($part);
        // 匹配形如 "A-B"
        if (preg_match('/^(\d+)\-(\d+)$/', $part, $matches)) {
            $start = (int)$matches[1];
            $end   = (int)$matches[2];

            // 检查顺序
            if ($start > $end) {
                throw new Exception("范围 {$part} 不合法：起始值大于结束值。");
            }
            if (!$allowZero && ($start < 1 || $end < 1)) {
                throw new Exception("范围 {$part} 不合法：包含非正整数。");
            }

            // 检查重叠
            for ($i = $start; $i <= $end; $i++) {
                if (in_array($i, $allValues)) {
                    throw new Exception("范围/值中存在重叠或重复值：{$i}");
                }
                $allValues[] = $i;
            }

            $result[] = [
                'type'  => 'range',
                'start' => $start,
                'end'   => $end,
            ];
        }
        // 匹配单个整数
        elseif (preg_match('/^\d+$/', $part)) {
            $val = (int)$part;
            if (!$allowZero && $val < 1) {
                throw new Exception("单值 {$part} 不合法：必须为正整数。");
            }
            if (in_array($val, $allValues)) {
                throw new Exception("范围/值中存在重复值：{$val}");
            }
            $allValues[] = $val;
            $result[] = [
                'type'  => 'value',
                'value' => $val,
            ];
        } else {
            throw new Exception("输入 {$part} 不合法，必须是整数或形如 A-B 的范围。");
        }
    }

    return $result;
}

/**
 * 根据 parseRanges() 返回的数组构造一段 SQL 条件，如：
 *   ( (col BETWEEN start AND end) OR (col = value) )
 * 对 likes/dislikes 等整型列可用此函数。
 *
 * @param string $column 数据库列名(或表达式)
 * @param array  $ranges parseRanges() 的返回结果
 * @return string 构造好的 SQL 条件
 */
function buildRangeCondition($column, array $ranges)
{
    $pieces = [];
    foreach ($ranges as $rng) {
        if ($rng['type'] === 'range') {
            $pieces[] = "({$column} BETWEEN {$rng['start']} AND {$rng['end']})";
        } else { // 'value'
            $val = $rng['value'];
            $pieces[] = "({$column} = {$val})";
        }
    }
    return '( ' . implode(' OR ', $pieces) . ' )';
}

/**
 * 根据字符串列表构造模糊搜索条件（image_name LIKE %xxx%）。
 * 如用户输入 "vegoro1,GXYMRico" => ( image_name LIKE '%vegoro1%' OR image_name LIKE '%GXYMRico%' )
 *
 * @param string $column 列名，如 image_name
 * @param array $keywords 字符串数组
 * @return string SQL片段
 */
function buildLikeCondition($column, array $keywords, $mysqli)
{
    $orParts = [];
    foreach ($keywords as $kw) {
        $kw   = trim($kw);
        // 转义以防SQL注入
        $safe = $mysqli->real_escape_string($kw);
        $orParts[] = "({$column} LIKE '%{$safe}%')";
    }
    return '( ' . implode(' OR ', $orParts) . ' )';
}


// -----------------------------------------------------------------------------
// 下面开始逐步询问用户，构建筛选条件

$filtersSummary = []; // 用于记录用户的所有选择，供最终核对
$conditions     = []; // 最终要拼到 WHERE 后面的各个条件
$categoryFilter = false; // 标识是否选择了分类筛选
$excludeHasCategory = false; // 标识是否筛选“没有任何分类”的图片

// 2. 询问是否基于 image_exists 筛选
while (true) {
    $input = getUserInput("[2] 是否基于 image_exists 筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['image_exists'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        // 提示用户输入具体值
        while (true) {
            $val = getUserInput("  请输入 image_exists 的值(0 或 1)：");
            if ($val === 'q') {
                exit("用户选择退出程序。\n");
            }
            if ($val !== '0' && $val !== '1') {
                echo "非法值，请重新输入。\n";
                continue;
            }
            $filtersSummary['image_exists'] = "筛选 image_exists = {$val}";
            $conditions[] = "image_exists = {$val}";
            break;
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 3. 询问是否基于 star 筛选
while (true) {
    $input = getUserInput("[3] 是否基于 star 筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['star'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        // 提示用户输入具体值
        while (true) {
            $val = getUserInput("  请输入 star 的值(0 或 1)：");
            if ($val === 'q') {
                exit("用户选择退出程序。\n");
            }
            if ($val !== '0' && $val !== '1') {
                echo "非法值，请重新输入。\n";
                continue;
            }
            $filtersSummary['star'] = "筛选 star = {$val}";
            $conditions[] = "star = {$val}";
            break;
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 4. 询问是否需要基于 id 范围筛选
while (true) {
    $input = getUserInput("[4] 是否需要基于 id 范围筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['id'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        while (true) {
            $rangeInput = getUserInput("  请输入 id 范围或值(例如: 1-10,12-15,18,20)：");
            if ($rangeInput === 'q') {
                exit("用户选择退出程序。\n");
            }
            try {
                $ranges = parseRanges($rangeInput, true);
                // 构造 SQL 条件
                $idCondition = buildRangeCondition('id', $ranges);
                $filtersSummary['id'] = "筛选 id 满足：{$rangeInput}";
                $conditions[] = $idCondition;
                break;
            } catch (Exception $e) {
                echo "输入错误：{$e->getMessage()}，请重新输入。\n";
                continue;
            }
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 5. 询问是否需要基于分类进行筛选
while (true) {
    $input = getUserInput("[5] 是否基于分类 (Categories 表) 进行筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['categories'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        // 需要用户输入类别名称列表
        // 由于可能包含空格，因此题目示例用引号包裹，但我们可以直接让用户输入引号内的文字
        // 为简化，这里直接提示用户按题目格式输入: "1.1 林希威","1.1 IES"
        while (true) {
            $catInput = getUserInput('  请输入分类名称（可多个，英文逗号分隔），示例："1.1 林希威","1.1 IES"：');
            if ($catInput === 'q') {
                exit("用户选择退出程序。\n");
            }
            // 用户可能输入："A","B","C"
            // 简单处理思路：按英文逗号拆分 => 去掉首尾空格和引号 => 得到分类名称数组
            // 当然要小心分类中含有逗号的情况，这里按题意仅示例。
            $temp = explode(',', $catInput);
            $categories = [];
            foreach ($temp as $t) {
                $t = trim($t);
                // 去掉首尾的双引号
                $t = trim($t, '"');
                if ($t !== '') {
                    $categories[] = $t;
                }
            }
            if (empty($categories)) {
                echo "  输入的分类名称为空，请重新输入。\n";
                continue;
            }
            // 检查是否存在重复
            if (count($categories) !== count(array_unique($categories))) {
                echo "  输入的多个分类中有重复，请重新输入。\n";
                continue;
            }

            // 检查分类是否在数据库中存在
            // 构造带引号的字符串列表供 SQL 查询
            $quotedList = implode("','", array_map([$mysqli, 'real_escape_string'], $categories));
            $sqlCheck = "SELECT category_name FROM Categories WHERE category_name IN ('{$quotedList}')";
            $resCheck = $mysqli->query($sqlCheck);
            if (!$resCheck) {
                echo "  查询分类时出错：" . $mysqli->error . "\n";
                continue;
            }
            $foundNames = [];
            while ($row = $resCheck->fetch_assoc()) {
                $foundNames[] = $row['category_name'];
            }
            // 比对找到的与用户输入的
            $notFound = array_diff($categories, $foundNames);
            if (!empty($notFound)) {
                echo "  以下分类在数据库中不存在，请重新输入：\n";
                echo "    " . implode(", ", $notFound) . "\n";
                continue;
            }
            // 至此，所有分类都存在
            // 构造一个 condition： images.id IN (SELECT pc.image_id FROM PicCategories pc JOIN Categories c ON pc.category_id = c.id WHERE c.category_name IN (...))
            $categoryFilter = true;
            $filtersSummary['categories'] = '需要分类筛选：' . implode(', ', $categories);
            $cond = "id IN (
                SELECT pc.image_id
                FROM PicCategories pc
                JOIN Categories c ON pc.category_id = c.id
                WHERE c.category_name IN ('{$quotedList}')
            )";
            $conditions[] = $cond;
            break;
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 6. 如果没有进行分类筛选，则询问是否要筛选没有被分类过的图片
if (!$categoryFilter) {
    while (true) {
        $input = getUserInput("[6] 是否筛选“没有被分类”的图片？(y/n，或 q 退出): ");
        if ($input === 'q') {
            exit("用户选择退出程序。\n");
        } elseif ($input === 'n') {
            $filtersSummary['noCategory'] = '不筛选';
            break;
        } elseif ($input === 'y') {
            $filtersSummary['noCategory'] = '筛选：没有分类的图片';
            // 条件： id NOT IN (SELECT image_id FROM PicCategories)
            $conditions[] = "id NOT IN (SELECT image_id FROM PicCategories)";
            $excludeHasCategory = true;
            break;
        } else {
            echo "输入无效，请重新输入。\n";
        }
    }
}

// 7. 询问是否基于 likes 进行筛选
while (true) {
    $input = getUserInput("[7] 是否基于 likes 进行筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['likes'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        while (true) {
            $rangeInput = getUserInput("  请输入 likes 的值或范围(如: 1-5,10,20-50,51)：");
            if ($rangeInput === 'q') {
                exit("用户选择退出程序。\n");
            }
            try {
                $ranges = parseRanges($rangeInput, true);
                $likesCondition = buildRangeCondition('likes', $ranges);
                $conditions[] = $likesCondition;
                $filtersSummary['likes'] = "筛选 likes 满足: {$rangeInput}";
                break;
            } catch (Exception $e) {
                echo "输入错误：{$e->getMessage()}，请重新输入。\n";
                continue;
            }
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 8. 询问是否基于 dislikes 进行筛选
while (true) {
    $input = getUserInput("[8] 是否基于 dislikes 进行筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['dislikes'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        while (true) {
            $rangeInput = getUserInput("  请输入 dislikes 的值或范围(如: 1-5,10,20-50,51)：");
            if ($rangeInput === 'q') {
                exit("用户选择退出程序。\n");
            }
            try {
                $ranges = parseRanges($rangeInput, true);
                $dislikesCondition = buildRangeCondition('dislikes', $ranges);
                $conditions[] = $dislikesCondition;
                $filtersSummary['dislikes'] = "筛选 dislikes 满足: {$rangeInput}";
                break;
            } catch (Exception $e) {
                echo "输入错误：{$e->getMessage()}，请重新输入。\n";
                continue;
            }
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 9. 询问是否基于 (likes-dislikes) 差值进行筛选
while (true) {
    $input = getUserInput("[9] 是否基于 (likes - dislikes) 差值进行筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['likes-dislikes'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        while (true) {
            $rangeInput = getUserInput("  请输入 (likes-dislikes) 的值或范围(如: 1-5,10,20-50,51)：");
            if ($rangeInput === 'q') {
                exit("用户选择退出程序。\n");
            }
            try {
                $ranges = parseRanges($rangeInput, true);
                // 这里的列名用表达式 (likes - dislikes)
                $diffCondition = buildRangeCondition('(likes - dislikes)', $ranges);
                $conditions[] = $diffCondition;
                $filtersSummary['likes-dislikes'] = "筛选 (likes-dislikes) 满足: {$rangeInput}";
                break;
            } catch (Exception $e) {
                echo "输入错误：{$e->getMessage()}，请重新输入。\n";
                continue;
            }
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 10. 询问是否基于 image_name 进行筛选
while (true) {
    $input = getUserInput("[10] 是否基于 image_name 进行筛选？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        $filtersSummary['image_name'] = '不筛选';
        break;
    } elseif ($input === 'y') {
        while (true) {
            $nameInput = getUserInput("  请输入要匹配的字符串(多个以英文逗号分隔，如：vegoro1,GXYMRico)：");
            if ($nameInput === 'q') {
                exit("用户选择退出程序。\n");
            }
            $temp    = explode(',', $nameInput);
            $keywords = [];
            foreach ($temp as $t) {
                $t = trim($t);
                if ($t !== '') {
                    $keywords[] = $t;
                }
            }
            if (empty($keywords)) {
                echo "  输入字符串为空，请重新输入。\n";
                continue;
            }
            $filtersSummary['image_name'] = "筛选 image_name 包含任意：{$nameInput}";
            $likeCondition = buildLikeCondition('image_name', $keywords, $mysqli);
            $conditions[] = $likeCondition;
            break;
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 11. 总结并打印用户所有筛选项，然后执行查询
echo "\n[11] 以下是您选择的所有筛选项：\n";
foreach ($filtersSummary as $k => $v) {
    echo "  - {$k} : {$v}\n";
}

// 构造最终SQL
$sql = "SELECT id, image_name, likes, dislikes, star, image_exists FROM images";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
// 执行查询
$result = $mysqli->query($sql);
if (!$result) {
    exit("执行查询出错：{$mysqli->error}\n");
}
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$count = count($rows);

echo "\n符合要求的图片数量：{$count}\n";
if ($count === 0) {
    exit("没有符合条件的图片，程序结束。\n");
}

// 如果有结果，询问是否打印 id 和图片名
while (true) {
    $input = getUserInput("是否打印这些图片的 id 和 image_name？(y/n，或 q 退出): ");
    if ($input === 'q') {
        exit("用户选择退出程序。\n");
    } elseif ($input === 'n') {
        break;
    } elseif ($input === 'y') {
        foreach ($rows as $r) {
            echo "  id={$r['id']}, image_name={$r['image_name']}, likes={$r['likes']}, dislikes={$r['dislikes']}, star={$r['star']}, image_exists={$r['image_exists']}\n";
        }
        break;
    } else {
        echo "输入无效，请重新输入。\n";
    }
}

// 12. 核对所选 id 的图片在 $local_dir 是否存在
//     给出提示并询问用户是否要打印
$existing = [];
$notExisting = [];
foreach ($rows as $r) {
    $filename = $r['image_name'];
    $fullpath = rtrim($local_dir, '/').'/'.$filename;
    if (file_exists($fullpath)) {
        $existing[] = $r;
    } else {
        $notExisting[] = $r;
    }
}
$existCount = count($existing);
if ($existCount > 0) {
    while (true) {
        $input = getUserInput("发现目录下已存在 {$existCount} 张同名图片，是否打印它们的 id 和名称？(y/n，或 q 退出): ");
        if ($input === 'q') {
            exit("用户选择退出程序。\n");
        } elseif ($input === 'n') {
            break;
        } elseif ($input === 'y') {
            foreach ($existing as $ex) {
                echo "  [已存在] id={$ex['id']}, image_name={$ex['image_name']}\n";
            }
            echo "总计已存在的文件数：{$existCount}\n";
            break;
        } else {
            echo "输入无效，请重新输入。\n";
        }
    }
}

// 13. 询问用户是否需要使用 rclone 下载筛选出来的图片
//     仅对那些本地目录下不存在的图片执行下载
if (!empty($notExisting)) {
    // 提取这些文件名
    $diffBD = [];
    foreach ($notExisting as $ne) {
        $diffBD[] = $ne['image_name'];
    }

    while (true) {
        $input = getUserInput("[13] 是否需要使用 rclone 下载这批图片？(y/n，或 q 退出): ");
        if ($input === 'q') {
            exit("用户选择退出程序。\n");
        } elseif ($input === 'n') {
            break;
        } elseif ($input === 'y') {
            // 将需要下载的文件名写入临时文件
            $fileList = array_values($diffBD);
            $tmpFile  = '/tmp/files_to_download.txt';
            file_put_contents($tmpFile, implode("\n", $fileList));

            // 准备 rclone 命令（根据实际情况修改 remote_dir）
            $remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost';
            $copy_command = "rclone copy --ignore-existing '$remote_dir' '$local_dir' --files-from '$tmpFile' --transfers=16";

            echo "执行命令：{$copy_command}\n";
            exec($copy_command, $copy_output, $copy_return_var);

            if ($copy_return_var !== 0) {
                echo "下载失败。\n";
            } else {
                echo "下载成功。\n";
            }
            // 删除临时文件
            unlink($tmpFile);

            break;
        } else {
            echo "输入无效，请重新输入。\n";
        }
    }
} else {
    echo "所有筛选图片均已存在于本地目录，无需下载。\n";
}

// 14. 执行后续命令
//    需要说明：这些命令的可执行路径、pm2 的配置等需要与你的实际部署环境相匹配。
exec('php /home/01_html/08_db_image_status.php', $out1, $ret1);
if ($ret1 !== 0) {
    echo "执行 08_db_image_status.php 时出现错误。\n";
} else {
    echo "已执行 08_db_image_status.php。\n";
}

exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js', $out2, $ret2);
if ($ret2 !== 0) {
    echo "执行 pm2 restart 时出现错误。\n";
} else {
    echo "已重启 /home/01_html/08_x_nodejs/08_pic_url_check.js。\n";
}

echo "Process completed.\n";
