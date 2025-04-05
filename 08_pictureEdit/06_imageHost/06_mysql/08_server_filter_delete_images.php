<?php
/**
 * 08_filter_delete_images.php
 * 用于对指定目录下的图片进行多条件筛选并执行删除等操作
 */

// 1. 引入数据库配置与同步脚本
include '08_db_config.php';        // 连接数据库
include '08_db_sync_images.php';   // 同步数据库函数

// 同步数据库
syncImages("/home/01_html/08_x/image/01_imageHost");

// 准备工作：一些通用函数

/**
 * 从标准输入读取用户输入（CLI 环境）。
 * @param string $msg 提示信息
 * @return string 去除首尾空白后的用户输入
 */
function prompt($msg) {
    echo $msg;
    return trim(fgets(STDIN));
}

/**
 * 解析形如 1-5,10,20-50,51 的范围或单个数字输入
 * @param string $inputStr
 * @return array 返回二维数组，形如 [['min'=>1, 'max'=>5], ['value'=>10], ['min'=>20, 'max'=>50], ['value'=>51]]
 *               同时也可以返回空数组表示用户未输入或非法
 */
function parseRanges($inputStr) {
    $result = [];
    if (strlen($inputStr) === 0) {
        return $result;
    }
    // 分割逗号
    $parts = explode(',', $inputStr);
    $checked = [];

    foreach ($parts as $part) {
        $part = trim($part);
        if (strpos($part, '-') !== false) {
            // 形如 x-y
            list($start, $end) = explode('-', $part);
            $start = trim($start);
            $end   = trim($end);
            // 检查是否都是整数
            if (!ctype_digit($start) || !ctype_digit($end)) {
                return []; // 解析失败或非法
            }
            $startVal = intval($start);
            $endVal   = intval($end);
            if ($startVal > $endVal) {
                // 若用户输入 10-5 此类情况，也视为非法
                return [];
            }
            // 检查是否有重叠
            // 这里仅做简单演示，不做复杂的重叠校验，
            // 需要更严格可自行编写逻辑或记录上一次范围
            $result[] = ['min' => $startVal, 'max' => $endVal];
        } else {
            // 单个数字
            if (!ctype_digit($part)) {
                return []; // 非法数字
            }
            $val = intval($part);
            // 同样可以检查是否重复
            if (in_array($val, $checked)) {
                // 若重复可以直接视为非法，也可忽略
                return [];
            }
            $checked[] = $val;
            $result[] = ['value' => $val];
        }
    }

    return $result;
}

/**
 * 根据 parseRanges 返回的数组生成对应 SQL 条件（适用于 likes 或 dislikes 或 (likes-dislikes)）。
 * 例如： ( (likes >= 1 AND likes <= 5) OR (likes = 10) OR (likes >= 20 AND likes <= 50) OR (likes = 51) )
 * @param array  $ranges  parseRanges 函数解析结果
 * @param string $field   用于判断的字段，如 'likes' 或 'dislikes' 或 '(i.likes - i.dislikes)'
 * @return string         拼接好的一段 SQL
 */
function buildRangeConditions($ranges, $field) {
    // 如果没有条件就返回空字符串
    if (empty($ranges)) {
        return '';
    }
    $conds = [];
    foreach ($ranges as $r) {
        if (isset($r['min']) && isset($r['max'])) {
            $conds[] = "($field >= {$r['min']} AND $field <= {$r['max']})";
        } elseif (isset($r['value'])) {
            $conds[] = "($field = {$r['value']})";
        }
    }
    if (empty($conds)) {
        return '';
    }
    $joined = implode(' OR ', $conds);
    return "($joined)";
}

// 2. 初步筛选：image_exists = 1
// 构建查询时将所有条件放在一个数组里，最后再拼接成 WHERE xxx AND xxx 的形式
$whereClauses = [];
$whereClauses[] = "i.image_exists = 1"; // 必选

// 用于记录用户在每一步的选择，方便最后回顾
$userChoices = [
    'filter_star' => 'n',
    'star_value' => '',
    'filter_id_range' => 'n',
    'id_ranges' => '',
    'filter_category' => 'n',
    'category_list' => [],
    'filter_likes' => 'n',
    'likes_value' => '',
    'filter_dislikes' => 'n',
    'dislikes_value' => '',
    'filter_diff' => 'n',
    'diff_value' => '',
    'filter_name' => 'n',
    'name_value' => ''
];

// 3. 询问是否基于 star 进行筛选
while (true) {
    $input = prompt("基于 star 进行筛选? (y/n), 输入q退出: ");
    if ($input === 'q') {
        echo "程序结束.\n";
        exit;
    } elseif ($input === 'y') {
        $userChoices['filter_star'] = 'y';
        // 输入 star 值（0或1）
        while (true) {
            $starVal = prompt("请输入 star 的值(0或1)，输入q退出: ");
            if ($starVal === 'q') {
                echo "程序结束.\n";
                exit;
            }
            if ($starVal === '0' || $starVal === '1') {
                $userChoices['star_value'] = $starVal;
                $whereClauses[] = "i.star = {$starVal}";
                break;
            } else {
                echo "非法 star 值，请重新输入.\n";
            }
        }
        break; // 结束本步骤
    } elseif ($input === 'n') {
        // 不基于 star 进行筛选
        $userChoices['filter_star'] = 'n';
        break;
    } else {
        echo "非法输入，请重新输入.\n";
    }
}

// 4. 是否基于 id 范围进行筛选
while (true) {
    $input = prompt("是否基于 id 范围进行筛选? (y/n), 输入q退出: ");
    if ($input === 'q') {
        echo "程序结束.\n";
        exit;
    } elseif ($input === 'y') {
        $userChoices['filter_id_range'] = 'y';
        // 解析id范围
        while (true) {
            $rangeStr = prompt("请输入 id 范围(如 31-101,1-10,12,20)，输入q退出: ");
            if ($rangeStr === 'q') {
                echo "程序结束.\n";
                exit;
            }
            $parsed = parseRanges($rangeStr);
            if (empty($parsed) && strlen($rangeStr) > 0) {
                echo "id 范围输入非法或为空，请重新输入.\n";
            } else {
                // 构建SQL
                if (!empty($parsed)) {
                    $conds = [];
                    foreach ($parsed as $r) {
                        if (isset($r['min']) && isset($r['max'])) {
                            $conds[] = "(i.id >= {$r['min']} AND i.id <= {$r['max']})";
                        } elseif (isset($r['value'])) {
                            $conds[] = "(i.id = {$r['value']})";
                        }
                    }
                    if (!empty($conds)) {
                        $whereClauses[] = "(".implode(" OR ", $conds).")";
                    }
                    $userChoices['id_ranges'] = $rangeStr;
                }
                break;
            }
        }
        break;
    } elseif ($input === 'n') {
        $userChoices['filter_id_range'] = 'n';
        break;
    } else {
        echo "非法输入，请重新输入.\n";
    }
}

// 5. 是否基于分类进行筛选
$categoryNeeded = false;
$categoryNames = [];
while (true) {
    $input = prompt("是否基于 PicCategories 表的分类进行筛选？(y/n)，输入q退出: ");
    if ($input === 'q') {
        echo "程序结束.\n";
        exit;
    } elseif ($input === 'y') {
        $userChoices['filter_category'] = 'y';
        $categoryNeeded = true;
        // 输入分类列表，例如："1.1 林希威","1.1 IES"
        // 因为类别中可能有空格，所以用户用引号包裹，但这里示例中直接让用户输入带双引号的字符串。
        // 也可以约定: 用户直接输入不用引号，但若有空格则需要另外方式处理。
        while (true) {
            $catInput = prompt("请输入类别名称，可多个，英文逗号分隔（例如：\"1.1 林希威\",\"1.1 IES\" ），输入q退出: ");
            if ($catInput === 'q') {
                echo "程序结束.\n";
                exit;
            }
            // 将输入拆分为多个带引号的部分
            // 比如输入: "1.1 林希威","1.1 IES"
            // 可以用正则或手动解析
            // 这里简单做个演示：假设用户严格输入 `"xxx","yyy"` 这样的格式

            // 去掉首尾空白
            $catInput = trim($catInput);
            if (strlen($catInput) === 0) {
                echo "输入为空，请重新输入.\n";
                continue;
            }

            // 最简单做法：按英文逗号拆分，然后去掉引号
            // 但要保证用户真的给每个类别加上引号
            $matches = [];
            preg_match_all('/"([^"]*)"/', $catInput, $matches);
            if (!empty($matches[1])) {
                $categoryNames = $matches[1]; // 匹配到的所有类别名
                // 去重
                $categoryNames = array_unique($categoryNames);

                // 核查这些类别是否存在
                // 如果有不存在的就提示错误
                $placeholders = implode(',', array_fill(0, count($categoryNames), '?'));
                $sql = "SELECT category_name FROM Categories WHERE category_name IN ($placeholders)";
                $stmt = $mysqli->prepare($sql);
                // 绑定参数
                $types = str_repeat('s', count($categoryNames));
                $stmt->bind_param($types, ...$categoryNames);
                $stmt->execute();
                $res = $stmt->get_result();
                $exists = [];
                while($row = $res->fetch_assoc()) {
                    $exists[] = $row['category_name'];
                }
                $stmt->close();

                $notFound = array_diff($categoryNames, $exists);
                if (!empty($notFound)) {
                    echo "以下类别在数据库中不存在，请重新输入：\n";
                    foreach ($notFound as $nf) {
                        echo "  - $nf\n";
                    }
                    continue; // 继续让用户重新输入
                }

                // 如果都存在，就可以记下来
                $userChoices['category_list'] = $categoryNames;
                break;
            } else {
                echo "未能正确解析，请确保使用英文逗号分隔并使用双引号包裹类别名称.\n";
            }
        }
        break;
    } elseif ($input === 'n') {
        $userChoices['filter_category'] = 'n';
        break;
    } else {
        echo "非法输入，请重新输入.\n";
    }
}

// 6. 是否基于 likes 进行筛选
while (true) {
    $input = prompt("是否基于 likes 进行筛选？(y/n)，输入q退出: ");
    if ($input === 'q') {
        echo "程序结束.\n";
        exit;
    } elseif ($input === 'y') {
        $userChoices['filter_likes'] = 'y';
        while (true) {
            $likeStr = prompt("请输入 likes 的值或范围(如 1-5,10,20-50,51 )，输入q退出: ");
            if ($likeStr === 'q') {
                echo "程序结束.\n";
                exit;
            }
            $parsedLikes = parseRanges($likeStr);
            if (empty($parsedLikes) && strlen($likeStr) > 0) {
                echo "likes 范围输入非法或为空，请重新输入.\n";
            } else {
                if (!empty($parsedLikes)) {
                    $cond = buildRangeConditions($parsedLikes, 'i.likes');
                    if ($cond) {
                        $whereClauses[] = $cond;
                    }
                    $userChoices['likes_value'] = $likeStr;
                }
                break;
            }
        }
        break;
    } elseif ($input === 'n') {
        $userChoices['filter_likes'] = 'n';
        break;
    } else {
        echo "非法输入，请重新输入.\n";
    }
}

// 7. 是否基于 dislikes 进行筛选
while (true) {
    $input = prompt("是否基于 dislikes 进行筛选？(y/n)，输入q退出: ");
    if ($input === 'q') {
        echo "程序结束.\n";
        exit;
    } elseif ($input === 'y') {
        $userChoices['filter_dislikes'] = 'y';
        while (true) {
            $dislikeStr = prompt("请输入 dislikes 的值或范围(如 1-5,10,20-50,51 )，输入q退出: ");
            if ($dislikeStr === 'q') {
                echo "程序结束.\n";
                exit;
            }
            $parsedDislikes = parseRanges($dislikeStr);
            if (empty($parsedDislikes) && strlen($dislikeStr) > 0) {
                echo "dislikes 范围输入非法或为空，请重新输入.\n";
            } else {
                if (!empty($parsedDislikes)) {
                    $cond = buildRangeConditions($parsedDislikes, 'i.dislikes');
                    if ($cond) {
                        $whereClauses[] = $cond;
                    }
                    $userChoices['dislikes_value'] = $dislikeStr;
                }
                break;
            }
        }
        break;
    } elseif ($input === 'n') {
        $userChoices['filter_dislikes'] = 'n';
        break;
    } else {
        echo "非法输入，请重新输入.\n";
    }
}

// 8. 是否基于 (likes - dislikes) 差值进行筛选
while (true) {
    $input = prompt("是否基于 (likes-dislikes) 差值进行筛选？(y/n)，输入q退出: ");
    if ($input === 'q') {
        echo "程序结束.\n";
        exit;
    } elseif ($input === 'y') {
        $userChoices['filter_diff'] = 'y';
        while (true) {
            $diffStr = prompt("请输入差值的范围或具体值(如 1-5,10,20-50,51 )，输入q退出: ");
            if ($diffStr === 'q') {
                echo "程序结束.\n";
                exit;
            }
            $parsedDiff = parseRanges($diffStr);
            if (empty($parsedDiff) && strlen($diffStr) > 0) {
                echo "(likes-dislikes) 范围输入非法或为空，请重新输入.\n";
            } else {
                if (!empty($parsedDiff)) {
                    // 这里字段用 (i.likes - i.dislikes)
                    $cond = buildRangeConditions($parsedDiff, '(i.likes - i.dislikes)');
                    if ($cond) {
                        $whereClauses[] = $cond;
                    }
                    $userChoices['diff_value'] = $diffStr;
                }
                break;
            }
        }
        break;
    } elseif ($input === 'n') {
        $userChoices['filter_diff'] = 'n';
        break;
    } else {
        echo "非法输入，请重新输入.\n";
    }
}

// 9. 是否基于 image_name (模糊包含多个字符串) 进行筛选
while (true) {
    $input = prompt("是否基于 image_name 进行筛选？(y/n)，输入q退出: ");
    if ($input === 'q') {
        echo "程序结束.\n";
        exit;
    } elseif ($input === 'y') {
        $userChoices['filter_name'] = 'y';
        while (true) {
            $nameStr = prompt("请输入要包含的字符串（多个用英文逗号分隔），如 vegoro1,g2w2w4；输入q退出: ");
            if ($nameStr === 'q') {
                echo "程序结束.\n";
                exit;
            }
            $nameStr = trim($nameStr);
            if (strlen($nameStr) === 0) {
                echo "输入为空，请重新输入.\n";
                continue;
            }
            $parts = explode(',', $nameStr);
            $parts = array_map('trim', $parts);
            $parts = array_filter($parts);  // 去除空白
            if (empty($parts)) {
                echo "输入字符串解析后为空，请重新输入.\n";
                continue;
            }
            $userChoices['name_value'] = $nameStr;

            // 构建 sql： name 中需要同时包含每一个关键字
            // 即 image_name LIKE '%xxx%' AND image_name LIKE '%yyy%'
            foreach ($parts as $p) {
                // 使用 mysqli_real_escape_string 防止特殊字符
                $p_escaped = $mysqli->real_escape_string($p);
                $whereClauses[] = "i.image_name LIKE '%{$p_escaped}%'";
            }

            break;
        }
        break;
    } elseif ($input === 'n') {
        $userChoices['filter_name'] = 'n';
        break;
    } else {
        echo "非法输入，请重新输入.\n";
    }
}

// 10. 打印所有用户的选择，以供核对
echo "=== 您的筛选条件如下： ===\n";
echo "1) image_exists = 1（必选条件）\n";
echo "2) 是否基于 star 筛选: {$userChoices['filter_star']}\n";
if ($userChoices['filter_star'] === 'y') {
    echo "   star = {$userChoices['star_value']}\n";
}
echo "3) 是否基于 id 范围筛选: {$userChoices['filter_id_range']}\n";
if ($userChoices['filter_id_range'] === 'y') {
    echo "   id 范围: {$userChoices['id_ranges']}\n";
}
echo "4) 是否基于分类: {$userChoices['filter_category']}\n";
if ($userChoices['filter_category'] === 'y') {
    echo "   分类名称: " . implode(', ', $userChoices['category_list']) . "\n";
}
echo "5) 是否基于 likes: {$userChoices['filter_likes']}\n";
if ($userChoices['filter_likes'] === 'y') {
    echo "   likes 条件: {$userChoices['likes_value']}\n";
}
echo "6) 是否基于 dislikes: {$userChoices['filter_dislikes']}\n";
if ($userChoices['filter_dislikes'] === 'y') {
    echo "   dislikes 条件: {$userChoices['dislikes_value']}\n";
}
echo "7) 是否基于 (likes-dislikes): {$userChoices['filter_diff']}\n";
if ($userChoices['filter_diff'] === 'y') {
    echo "   (likes-dislikes) 条件: {$userChoices['diff_value']}\n";
}
echo "8) 是否基于 image_name: {$userChoices['filter_name']}\n";
if ($userChoices['filter_name'] === 'y') {
    echo "   image_name 需包含: {$userChoices['name_value']}\n";
}
echo "====================================\n";

// 11. 根据所有条件筛选图片ID
// 如果需要分类，则要 JOIN piccategories 和 categories
$baseTable = "images i";
$joinClause = "";
if ($categoryNeeded) {
    // 用户要基于分类进行筛选，说明要 JOIN
    // 我们的需求是“图片属于任意给定分类” 还是“同时属于所有给定分类”？
//  -- 如果是只要符合其中任意一个分类即可：
//        c.category_name IN ( ... ) 即可
//     SELECT DISTINCT i.id FROM images i
//        JOIN PicCategories pc ON i.id = pc.image_id
//        JOIN Categories c ON pc.category_id = c.id
//     WHERE (c.category_name in (...)) AND [其他条件]
//  -- 如果想要所有分类都匹配，需要更复杂的group逻辑
//  这里示例默认“只要属于其中一个分类”就满足

    $joinClause = "JOIN PicCategories pc ON i.id = pc.image_id
                   JOIN Categories c ON pc.category_id = c.id";

    $placeholders = implode(',', array_fill(0, count($categoryNames), '?'));
    // 在最终执行时再绑定参数
    $whereClauses[] = "c.category_name IN ($placeholders)";
}

// 拼接 WHERE 子句
$whereSql = "";
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

// 整体查询语句（注意 DISTINCT，避免多分类导致重复ID）
$sql = "SELECT DISTINCT i.id, i.image_name 
        FROM $baseTable
        $joinClause
        $whereSql";

// 预处理
$stmt = $mysqli->prepare($sql);

// 如果有分类筛选，需要绑定参数
if ($categoryNeeded && !empty($categoryNames)) {
    // 构造类型和绑定变量
    $types = str_repeat('s', count($categoryNames));
    $stmt->bind_param($types, ...$categoryNames);
}

$stmt->execute();
$res = $stmt->get_result();

$finalIds = [];
$imageInfoMap = []; // id => image_name
while ($row = $res->fetch_assoc()) {
    $finalIds[] = $row['id'];
    $imageInfoMap[$row['id']] = $row['image_name'];
}
$stmt->close();

$count = count($finalIds);
echo "符合筛选条件的图片数量: $count\n";

// 11. 如果数量不为0，询问用户是否删除对应目录下的图片
if ($count > 0) {
    while (true) {
        $input = prompt("是否删除目录下对应的图片文件？(y/n)，输入q退出: ");
        if ($input === 'q') {
            echo "程序结束.\n";
            exit;
        } elseif ($input === 'y') {
            // 12. 核对对应ID的图片是否都存在
            $local_dir = '/home/01_html/08_x/image/01_imageHost';
            $missingFiles = [];
            foreach ($finalIds as $fid) {
                $fname = $imageInfoMap[$fid];
                $fullPath = rtrim($local_dir, '/').'/'.$fname;
                if (!file_exists($fullPath)) {
                    $missingFiles[] = $fname;
                }
            }
            if (!empty($missingFiles)) {
                echo "以下文件在本地目录中不存在，无法进行完整删除：\n";
                foreach ($missingFiles as $mf) {
                    echo "  - $mf\n";
                }
                echo "操作中止.\n";
                exit;
            }
            // 文件都存在，则执行删除
            $deletedCount = 0;
            foreach ($finalIds as $fid) {
                $fname = $imageInfoMap[$fid];
                $fullPath = rtrim($local_dir, '/').'/'.$fname;
                if (file_exists($fullPath)) {
                    if (unlink($fullPath)) {
                        $deletedCount++;
                    }
                }
            }
            echo "已删除 {$deletedCount} 个文件.\n";
            // 计算剩余文件数量
            $remainFiles = scandir($local_dir);
            // 去除 . 和 ..
            $remainFiles = array_diff($remainFiles, ['.', '..']);
            echo "删除后，目录 {$local_dir} 下剩余文件数量: ".count($remainFiles)."\n";
            break;
        } elseif ($input === 'n') {
            echo "用户选择不删除任何文件.\n";
            break;
        } else {
            echo "非法输入，请重新输入.\n";
        }
    }
}

// 13. 删除操作完成后，执行下面两个命令
exec('php /home/01_html/08_db_image_status.php');
exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js');
echo "Process completed.\n";
