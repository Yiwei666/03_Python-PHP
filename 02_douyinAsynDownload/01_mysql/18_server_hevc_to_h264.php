#!/usr/bin/php
<?php
/**
 * 本脚本在终端中运行，提供功能：
 * 1. 检测 /home/01_html/02_douyVideo 下所有 mp4 视频的编码方式，若是 hevc，则移动到 /home/01_html/18_temp_video/1_hevc
 * 2. 将 /home/01_html/18_temp_video/1_hevc 下的所有 mp4 使用 ffmpeg 转换为 h264 编码，输出到 /home/01_html/18_temp_video/2_h264
 * 3. 统计并显示 /home/01_html/02_douyVideo、/home/01_html/18_temp_video/1_hevc 和 /home/01_html/18_temp_video/2_h264 下的 mp4 数量
 * 4. 将 /home/01_html/18_temp_video/2_h264 下的 mp4 文件移动回 /home/01_html/02_douyVideo（需输入 y 确认）
 * 5. 删除 /home/01_html/18_temp_video/1_hevc 下的所有 mp4 文件（需输入 y 确认）
 */

ini_set('memory_limit', '-1');
set_time_limit(0);


// 目录定义
$dirSource = '/home/01_html/02_douyVideo';
$dirHevc   = '/home/01_html/18_temp_video/1_hevc';
$dirH264   = '/home/01_html/18_temp_video/2_h264';

// 打印菜单
function printMenu() {
    echo "=============================================\n";
    echo "请选择要执行的功能：\n";
    echo "1. 检测 hevc 并移动到 1_hevc\n";
    echo "2. 将 1_hevc 下 mp4 转码为 h264 并保存到 2_h264\n";
    echo "3. 查看三个目录下的 mp4 文件数量\n";
    echo "4. 将 2_h264 下所有 mp4 文件移动回 02_douyVideo\n";
    echo "5. 删除 1_hevc 下所有 mp4 文件\n";
    echo "请输入序号(1-5)，或 Ctrl+C 退出：";
}

// 打印并读取用户输入
function getUserChoice() {
    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    return $choice;
}

// 判断是否 mp4 文件（简单判断后缀）
function isMp4File($filename) {
    return preg_match('/\.mp4$/i', $filename);
}

// 获取目录下所有 mp4 文件名（不包含 . 和 ..）
function getMp4Files($dir) {
    if (!is_dir($dir)) {
        return [];
    }
    $files = scandir($dir);
    $mp4Files = [];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }
        if (isMp4File($f)) {
            $mp4Files[] = $f;
        }
    }
    return $mp4Files;
}

// 获取文件编码是否是 hevc
function isHevc($filePath) {
    // ffprobe 命令：仅显示第一路视频流的 codec_name
    // 注意：需要本机可执行 ffprobe 命令
    $cmd = sprintf('ffprobe -v error -select_streams v:0 -show_entries stream=codec_name -of csv=p=0 "%s"',
        addslashes($filePath)
    );
    exec($cmd, $output, $ret);
    if ($ret === 0 && isset($output[0])) {
        $codecName = trim($output[0]);
        return strtolower($codecName) === 'hevc';
    }
    // 若无法探测到或命令出错，默认返回 false
    return false;
}

// 1. 检测 hevc 并移动到 1_hevc
function moveHevcFiles($dirSource, $dirHevc) {
    $mp4Files = getMp4Files($dirSource);
    if (!file_exists($dirHevc)) {
        mkdir($dirHevc, 0777, true);
    }
    foreach ($mp4Files as $mp4) {
        $sourcePath = $dirSource . '/' . $mp4;
        if (isHevc($sourcePath)) {
            $targetPath = $dirHevc . '/' . $mp4;
            rename($sourcePath, $targetPath);
            echo "HEVC 文件移动到: $targetPath\n";
        }
    }
}

// 2. 转码 h264 并保存到 2_h264
function convertHevcToH264($dirHevc, $dirH264) {
    if (!file_exists($dirH264)) {
        mkdir($dirH264, 0777, true);
    }
    $mp4Files = getMp4Files($dirHevc);
    foreach ($mp4Files as $mp4) {
        $sourcePath = $dirHevc . '/' . $mp4;
        $targetPath = $dirH264 . '/' . $mp4;
        // FFmpeg 转码示例：ffmpeg -i input.mp4 -c:v libx264 -c:a copy output.mp4
        // 这里简单示例保留原音频
        $cmd = sprintf(
            // 'ffmpeg -y -i "%s" -c:v libx264 -threads 1 -c:a copy "%s"',
            'ffmpeg -y -i "%s" -c:v libx264 -preset fast -crf 28 -c:a copy "%s"',
            addslashes($sourcePath),
            addslashes($targetPath)
        );
        echo "开始转码：$sourcePath\n";
        exec($cmd, $output, $ret);
        if ($ret === 0) {
            echo "转码完成：$targetPath\n";
        } else {
            echo "转码失败：$sourcePath\n";
        }
    }
}

// 3. 打印三个目录的 mp4 数量
function printMp4Count($dirSource, $dirHevc, $dirH264) {
    $countSource = count(getMp4Files($dirSource));
    $countHevc   = count(getMp4Files($dirHevc));
    $countH264   = count(getMp4Files($dirH264));

    echo "---------------------------------------------\n";
    echo "目录：$dirSource 下 mp4 文件数量：$countSource\n";
    echo "目录：$dirHevc 下 mp4 文件数量：$countHevc\n";
    echo "目录：$dirH264 下 mp4 文件数量：$countH264\n";
    echo "---------------------------------------------\n";
}

// 4. 将 2_h264 下的文件移动回 02_douyVideo
function moveH264Back($dirH264, $dirSource) {
    $mp4Files = getMp4Files($dirH264);
    if (empty($mp4Files)) {
        echo "没有文件需要移动。\n";
        return;
    }
    echo "确认将 $dirH264 下的所有 mp4 文件移动回 $dirSource 目录吗？(y/n)：";
    $choice = getUserChoice();
    if (strtolower($choice) === 'y') {
        foreach ($mp4Files as $mp4) {
            $sourcePath = $dirH264 . '/' . $mp4;
            $targetPath = $dirSource . '/' . $mp4;
            rename($sourcePath, $targetPath);
            echo "已移动：$sourcePath -> $targetPath\n";
        }
    } else {
        echo "操作取消。\n";
    }
}

// 5. 删除 1_hevc 下所有 mp4 文件
function deleteHevcFiles($dirHevc) {
    $mp4Files = getMp4Files($dirHevc);
    if (empty($mp4Files)) {
        echo "没有文件需要删除。\n";
        return;
    }
    echo "确认删除 $dirHevc 下的所有 mp4 文件吗？(y/n)：";
    $choice = getUserChoice();
    if (strtolower($choice) === 'y') {
        foreach ($mp4Files as $mp4) {
            $filePath = $dirHevc . '/' . $mp4;
            unlink($filePath);
            echo "已删除：$filePath\n";
        }
    } else {
        echo "操作取消。\n";
    }
}

// 主流程
while(true) {
    printMenu();
    $choice = getUserChoice();

    switch($choice) {
        case '1':
            moveHevcFiles($dirSource, $dirHevc);
            break;
        case '2':
            convertHevcToH264($dirHevc, $dirH264);
            break;
        case '3':
            printMp4Count($dirSource, $dirHevc, $dirH264);
            break;
        case '4':
            moveH264Back($dirH264, $dirSource);
            break;
        case '5':
            deleteHevcFiles($dirHevc);
            break;
        default:
            echo "无效的输入，请重新选择。\n";
            break;
    }
    
    echo "\n操作完成。如需继续请选择功能编号，否则 Ctrl+C 退出。\n";
}
