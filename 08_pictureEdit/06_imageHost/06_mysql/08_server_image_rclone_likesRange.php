<?php
/**
 * 08_download_images.php
 *
 * 根据用户输入的 likes 范围或具体值，筛选出需要下载的图片并执行下载操作。
 * 依赖外部:
 *   - 08_db_config.php 数据库连接
 *   - 08_db_sync_images.php 用于同步更新数据库记录
 *   - 08_db_image_status.php 检查并更新图片状态
 *   - 08_pic_url_check.js Node脚本
 *
 * 使用方法:
 *   php 08_download_images.php
 *   (脚本运行后，会提示您输入 likes 条件及确认信息)
 */

// 1. 引入数据库配置和同步模块
include '08_db_config.php';               // 数据库连接
include '08_db_sync_images.php';          // 用于将新下载的图片名写入数据库

// 2. 同步更新数据库(确保数据库是最新的)
syncImages("/home/01_html/08_x/image/01_imageHost");

// 3. 提示用户输入 likes 数范围或具体值
echo "请输入 likes 的范围或具体值（例如: 3-5 或 3,5 或 3）: ";
$handle = fopen("php://stdin", "r");
$userInput = trim(fgets($handle));
fclose($handle);

if (empty($userInput)) {
    echo "未输入任何内容，程序结束。\n";
    exit;
}

// 解析用户输入的 likes 条件
// 支持以下几种格式：
//   1) 3-5   （整数范围，包含3,4,5）
//   2) 3,5   （多个整数，英文逗号分隔）
//   3) 3     （单个整数）

$likesList = [];  // 用于存储最终需要查询的likes值
$rangeMode = false;
$rangeStart = 0;
$rangeEnd = 0;

// 判断是否为 range 模式 (带 '-')
if (strpos($userInput, '-') !== false) {
    $rangeMode = true;
    list($start, $end) = explode('-', $userInput, 2);
    $start = trim($start);
    $end   = trim($end);

    // 校验是否为整数以及 start < end
    if (!ctype_digit($start) || !ctype_digit($end)) {
        echo "输入的范围不是有效的整数范围，请重新运行。\n";
        exit;
    }
    $start = (int)$start;
    $end   = (int)$end;

    if ($start > $end) {
        echo "输入的范围不正确，起始值应小于或等于结束值。\n";
        exit;
    }

    // 保存范围
    $rangeStart = $start;
    $rangeEnd   = $end;

} else {
    // 否则可能是多个 likes 值(逗号分隔)或单个值
    $tempVals = explode(',', $userInput);
    foreach ($tempVals as $val) {
        $val = trim($val);
        if (!ctype_digit($val)) {
            echo "检测到无效的数字：{$val}，请重新运行。\n";
            exit;
        }
        $likesList[] = (int)$val;
    }
    // 去重
    $likesList = array_unique($likesList);
}

// 4. 根据解析结果去数据库中查询
//    先从数据库中筛选出符合 likes 条件的 id, image_name，
//    再从其中筛选出 image_exists = 0 的记录
//    （也可以直接在SQL里加上 image_exists=0 的条件）

try {
    if ($rangeMode) {
        // 范围查询
        $sql = "SELECT id, image_name FROM images 
                WHERE likes BETWEEN ? AND ? 
                  AND image_exists = 0";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $rangeStart, $rangeEnd);
    } else {
        // 多值或单值 in 查询
        // 需要先构造带问号的 in(...) 占位符
        // 例如 likes in (?, ?, ?)
        if (empty($likesList)) {
            echo "没有可用的 likes 条件。\n";
            exit;
        }
        $placeHolders = implode(',', array_fill(0, count($likesList), '?'));
        $sql = "SELECT id, image_name FROM images 
                WHERE likes IN ($placeHolders)
                  AND image_exists = 0";
        $stmt = $mysqli->prepare($sql);

        // 动态绑定参数
        // 生成字符串类型，如 "iii..." 
        // 这里 likes 为 int，所以全部是 "i"
        $bindTypes = str_repeat('i', count($likesList));
        $stmt->bind_param($bindTypes, ...$likesList);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} catch (Exception $e) {
    echo "数据库查询出错: " . $e->getMessage() . "\n";
    exit;
}

$diffBD = [];  // 存储需要下载的文件名
while ($row = $result->fetch_assoc()) {
    $diffBD[$row['id']] = $row['image_name'];
}

$count = count($diffBD);
if ($count === 0) {
    echo "没有符合条件且尚未下载的图片（image_exists=0）。\n";
    exit;
}

// 打印出符合要求的图片数量，让用户确认
echo "符合条件的 likes 值:";
if ($rangeMode) {
    echo " [{$rangeStart}-{$rangeEnd}]";
} else {
    echo " [" . implode(',', $likesList) . "]";
}
echo " 的图片有 {$count} 张。\n";

// 提示用户确认
echo "确认下载吗？输入 'y' 继续，或按其它键退出: ";
$handle = fopen("php://stdin", "r");
$confirm = trim(fgets($handle));
fclose($handle);

if (strtolower($confirm) !== 'y') {
    echo "用户取消操作。\n";
    exit;
}

// 5. 进行下载(rclone copy)
//    远程目录 $remote_dir
//    本地目录 $local_dir
// 将需要下载的文件名提取成一个数组(去掉 id，只保留文件名)
$fileList = array_values($diffBD);

// 生成一个临时文件，列出所有要下载的文件名（每行一个）
$tmpFile = '/tmp/files_to_download.txt';
file_put_contents($tmpFile, implode("\n", $fileList));

// 准备 rclone 命令
// 注意：使用 --files-from 时，rclone 从 $remote_dir 下的这些文件名一并下载到 $local_dir
$remote_dir = 'rc6:cc1-1/01_html/08_x/image/01_imageHost'; // 根据实际情况修改
$local_dir  = '/home/01_html/08_x/image/01_imageHost';

$copy_command = "rclone copy '$remote_dir' '$local_dir' --files-from '$tmpFile' --transfers=16";

// 执行批量下载
exec($copy_command, $copy_output, $copy_return_var);

if ($copy_return_var !== 0) {
    echo "Failed to copy files.\n";
} else {
    echo "Copied all files successfully.\n";
}

// 如果临时文件无需保留，可以在这里删除
unlink($tmpFile);


// 关闭数据库连接
$mysqli->close();

// 6. 完成后执行后续脚本，更新数据库图片状态，重启 Node 服务等
exec('php /home/01_html/08_db_image_status.php');
exec('pm2 restart /home/01_html/08_x_nodejs/08_pic_url_check.js');
echo "Process completed.\n";
