<?php
/**
 * 08_server_update_paper_selection.php
 * 
 * 功能：
 * 1) 若 select_paper 表为空，则检测 gd1:/13_paperUserSelect/ 是否有文件；有则清空，无则退出。
 * 2) 若不为空，则读取不重复 DOI，Base32 编码后生成 “ENCODE=.pdf” 文件名；
 *    检查这些文件是否存在于 gd1:/13_paperUserSelect/；
 *    若缺失，则在 gd1:/13_paperRemoteStorage/（多层目录）中查找并复制到 gd1:/13_paperUserSelect/；
 *    remoteStorage 不存在的则跳过。
 * 3) rclone 操作带上 --transfers=8。
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
set_time_limit(0);

// ---- 路径/常量（如 rclone 路径有差异，自行调整）----
$RCLONE_BIN = '/usr/bin/rclone';     // 若系统 PATH 中已配置，可改成 'rclone'
$REMOTE_USER_SELECT   = 'gd1:/13_paperUserSelect';
$REMOTE_STORAGE_ROOT  = 'gd1:/13_paperRemoteStorage';

// ---- 引入已有模块 ----
require_once __DIR__ . '/08_db_config.php';   // 提供 $mysqli
require_once __DIR__ . '/08_web_Base32.php';  // 提供 Base32::encode()

// ---- 简单的 rclone 调用包装 ----
function run_cmd($cmd) {
    // 返回 [exit_code, stdout]
    $output = [];
    $ret = 0;
    exec($cmd . ' 2>&1', $output, $ret);
    return [$ret, implode("\n", $output)];
}

function rclone_lsf_files($rclone, $remotePath, $recursive = false) {
    // 列出文件（不包括目录），返回文件列表（相对 remotePath 的路径或文件名）
    $cmd = sprintf(
        '%s lsf %s --files-only %s',
        escapeshellcmd($rclone),
        $recursive ? '-R' : '',
        escapeshellarg($remotePath)
    );
    list($code, $out) = run_cmd($cmd);
    if ($code !== 0) return [];
    $lines = preg_split('/\r\n|\r|\n/', $out);
    $files = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $files[] = $line;
        }
    }
    return $files;
}

function rclone_delete_all_files($rclone, $remoteDir) {
    // 仅删除目录下文件（不删除目录）
    $cmd = sprintf(
        '%s delete %s --transfers=8',
        escapeshellcmd($rclone),
        escapeshellarg($remoteDir)
    );
    return run_cmd($cmd);
}

function rclone_copyto($rclone, $srcFull, $dstFull) {
    // 单文件复制（扁平到目标目录）
    $cmd = sprintf(
        '%s copyto %s %s --transfers=8',
        escapeshellcmd($rclone),
        escapeshellarg($srcFull),
        escapeshellarg($dstFull)
    );
    return run_cmd($cmd);
}

// ---- 1) 判断 select_paper 是否为空 ----
$count = 0;
$res = $mysqli->query("SELECT COUNT(*) AS c FROM select_paper");
if ($res) {
    $row = $res->fetch_assoc();
    $count = (int)$row['c'];
    $res->close();
} else {
    // 数据库异常时安全退出
    echo "[" . date('Y-m-d H:i:s') . "] DB error: " . $mysqli->error . "\n";
    exit(1);
}

if ($count === 0) {
    // 表空：若 gd1:/13_paperUserSelect/ 有文件，就清空；否则直接结束
    $userFiles = rclone_lsf_files($RCLONE_BIN, $REMOTE_USER_SELECT, false);
    if (!empty($userFiles)) {
        list($delCode, $delOut) = rclone_delete_all_files($RCLONE_BIN, $REMOTE_USER_SELECT);
        echo "[" . date('Y-m-d H:i:s') . "] select_paper empty. userSelect had files, delete result code={$delCode}. Output:\n{$delOut}\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] select_paper empty. userSelect already empty. Nothing to do.\n";
    }
    exit(0);
}

// ---- 2) 表不为空：取不重复 DOI，编码为 Base32，形成目标文件名 ----
$dois = [];
$res = $mysqli->query("SELECT DISTINCT doi FROM select_paper WHERE doi IS NOT NULL AND doi<>''");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $dois[] = trim($row['doi']);
    }
    $res->close();
}

if (empty($dois)) {
    echo "[" . date('Y-m-d H:i:s') . "] No valid DOI found, nothing to do.\n";
    exit(0);
}

$targetFilenames = [];  // filename => true
foreach ($dois as $doi) {
    $encoded = Base32::encode($doi);         // 带 '=' 的 Base32
    $filename = $encoded . '.pdf';
    $targetFilenames[$filename] = true;
}

// ---- 2.5) 获取 userSelect 目录已有文件，找出缺失 ----
$userFiles = rclone_lsf_files($RCLONE_BIN, $REMOTE_USER_SELECT, false); // 该目录无子目录
$userSet = array_fill_keys($userFiles, true);

$missing = [];
foreach ($targetFilenames as $fn => $_) {
    if (!isset($userSet[$fn])) {
        $missing[] = $fn;
    }
}

if (empty($missing)) {
    // ---- 4) 全部已存在，结束 ----
    echo "[" . date('Y-m-d H:i:s') . "] All target PDFs already present in userSelect. Done.\n";
    exit(0);
}

// ---- 3) 在 remoteStorage 递归列出全部文件，建立 filename => path 映射 ----
// 注意：remoteStorage 下可能很大，若担心性能，可改为按缺失文件逐一 --include 搜索。
// 这里一次性扫描，减少多次遍历。
$allStorageFiles = rclone_lsf_files($RCLONE_BIN, $REMOTE_STORAGE_ROOT, true); // 递归
$lookup = []; // baseName => array of full relative path(s)
foreach ($allStorageFiles as $rel) {
    $base = basename($rel);
    // 一个文件名可能在多个子目录重复，先保留首个
    if (!isset($lookup[$base])) {
        $lookup[$base] = $rel; // 相对路径 e.g. sub/GEY...=.pdf
    }
}

// ---- 3.5) 逐个复制缺失文件 ----
$copied = 0;
$skipped = 0;
$notFound = 0;

foreach ($missing as $fn) {
    if (!isset($lookup[$fn])) {
        // remoteStorage 也不存在
        $notFound++;
        echo "[" . date('Y-m-d H:i:s') . "] Not found in remoteStorage: {$fn}\n";
        continue;
    }
    $srcRel = $lookup[$fn];
    $srcFull = $REMOTE_STORAGE_ROOT . '/' . $srcRel;   // 完整源路径
    $dstFull = $REMOTE_USER_SELECT . '/' . $fn;        // 目标文件（扁平到 userSelect 根目录）

    list($cpCode, $cpOut) = rclone_copyto($RCLONE_BIN, $srcFull, $dstFull);
    if ($cpCode === 0) {
        $copied++;
        echo "[" . date('Y-m-d H:i:s') . "] Copied: {$srcFull} -> {$dstFull}\n";
    } else {
        $skipped++;
        echo "[" . date('Y-m-d H:i:s') . "] Copy failed(code={$cpCode}): {$srcFull} -> {$dstFull}\n{$cpOut}\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Summary: copied={$copied}, failed={$skipped}, not_found_in_storage={$notFound}\n";
exit(0);
