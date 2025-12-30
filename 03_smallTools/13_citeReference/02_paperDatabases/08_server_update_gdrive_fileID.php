<?php
/**
 * 08_server_update_gdrive_fileID.php
 *
 * 需求实现：
 * 1) 引入 08_db_config.php 连接 paper_db；读取 papers / gdfile
 * 2) rclone 扫描 gd1:/13_paperRemoteStorage 递归获取每个 PDF 的 Name / ID
 * 3) 文件名(去掉 .pdf) Base32 解码得到 doi；在 papers 中查 MIN(paperID)
 * 4) 若 papers 存在该 doi，且 gdfile 中不存在该 doi（确保 filename/doi 不重复），则写入 gdfile
 * 5) 打印：gdfile 行数、papers 行数、远程 PDF 数、本次新增行数
 *
 * 运行：php 08_server_update_gdrive_fileID.php
 */

require_once __DIR__ . '/08_db_config.php';
require_once __DIR__ . '/08_web_Base32.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli->set_charset('utf8mb4');

$remote = 'gd1:/13_paperRemoteStorage';

/** 统一输出错误并退出 */
function fatal($msg, $exitCode = 1) {
    fwrite(STDERR, "[ERROR] {$msg}\n");
    exit($exitCode);
}

/** 统计表行数 */
function tableCount(mysqli $db, string $table): int {
    $row = $db->query("SELECT COUNT(*) AS c FROM {$table}")->fetch_assoc();
    return (int)$row['c'];
}

try {
    // ------------------------
    // 统计：papers / gdfile 行数（before）
    // ------------------------
    $papersCount = tableCount($mysqli, 'papers');
    $gdfileCountBefore = tableCount($mysqli, 'gdfile');

    // ------------------------
    // rclone 拉取远程文件列表（递归）
    // --fast-list 对大量文件更快（需要足够内存；不想用可删掉）
    // ------------------------
    $cmd = 'rclone lsjson ' . escapeshellarg($remote) . ' --recursive --fast-list';
    $json = shell_exec($cmd);

    if ($json === null || trim($json) === '') {
        fatal("rclone 输出为空，请检查 rclone/remote/权限。\n命令：{$cmd}");
    }

    $items = json_decode($json, true);
    if (!is_array($items)) {
        fatal("rclone lsjson 返回 JSON 解析失败。");
    }

    // ------------------------
    // 预编译语句
    // papers：doi 可能重复 → MIN(paperID)
    // gdfile：以 doi 去重（filename/doi 不重复即可）
    // ------------------------
    $stmtFindPaperIDExact = $mysqli->prepare("SELECT MIN(paperID) AS paperID FROM papers WHERE doi = ?");
    $stmtFindPaperIDLower = $mysqli->prepare("SELECT MIN(paperID) AS paperID FROM papers WHERE LOWER(doi) = ?");

    $stmtExistsDoiInGdfile = $mysqli->prepare("SELECT ID, fileID FROM gdfile WHERE doi = ? LIMIT 1");
    $stmtExistsFilenameInGdfile = $mysqli->prepare("SELECT ID, fileID FROM gdfile WHERE filename = ? LIMIT 1");
    $stmtUpdateFileID = $mysqli->prepare("UPDATE gdfile SET fileID = ? WHERE ID = ?");

    $stmtInsert = $mysqli->prepare("INSERT INTO gdfile (filename, fileID, paperID, doi) VALUES (?, ?, ?, ?)");

    // doi -> paperID 缓存，减少重复查库
    $doi2paperID = [];

    $pdfCount = 0;
    $inserted = 0;
    $updated = 0;

    foreach ($items as $it) {
        // 跳过目录
        if (!isset($it['IsDir']) || $it['IsDir'] === true) continue;
        if (!isset($it['Name'], $it['ID'])) continue;

        $name = (string)$it['Name'];
        $fileID = (string)$it['ID'];

        // 只处理 PDF（大小写不敏感）
        if (!preg_match('/\.pdf$/i', $name)) continue;
        $pdfCount++;

        // base32(doi).pdf → base32(doi)
        $base32 = preg_replace('/\.pdf$/i', '', $name);

        // Base32 解码得到 doi
        $decoded = Base32::decode($base32);
        if ($decoded === false) {
            // base32 不合法直接跳过
            continue;
        }
        $doi = trim($decoded);
        if ($doi === '') continue;

        // doi 大小写不敏感，统一用小写做缓存 key
        $doiKey = strtolower($doi);

        // ------------------------
        // 查 paperID（缓存优先）
        // ------------------------
        if (!array_key_exists($doiKey, $doi2paperID)) {
            $paperID = 0;

            // 先精确匹配
            $stmtFindPaperIDExact->bind_param('s', $doi);
            $stmtFindPaperIDExact->execute();
            $row = $stmtFindPaperIDExact->get_result()->fetch_assoc();
            if (!empty($row['paperID'])) {
                $paperID = (int)$row['paperID'];
            } else {
                // 再 lower 匹配（防止 papers 里 doi 大小写不一致）
                $stmtFindPaperIDLower->bind_param('s', $doiKey);
                $stmtFindPaperIDLower->execute();
                $row2 = $stmtFindPaperIDLower->get_result()->fetch_assoc();
                if (!empty($row2['paperID'])) {
                    $paperID = (int)$row2['paperID'];
                }
            }

            // 缓存（没找到则缓存 0）
            $doi2paperID[$doiKey] = $paperID;
        }

        $paperID = (int)$doi2paperID[$doiKey];
        if ($paperID <= 0) {
            // papers 不存在该 doi → 不写入
            continue;
        }

        // ------------------------
        // gdfile 去重：以 doi/filename 均可
        // 你要求“确保 filename 不重复，也就是 doi 不重复即可”
        // 这里同时检查 doi 和 filename，任一存在都跳过（更稳）
        // ------------------------
        $foundID = 0;
        $oldFileID = '';

        $stmtExistsDoiInGdfile->bind_param('s', $doi);
        $stmtExistsDoiInGdfile->execute();
        $r1 = $stmtExistsDoiInGdfile->get_result();
        if ($r1 && ($rowExist = $r1->fetch_assoc())) {
            $foundID = (int)$rowExist['ID'];
            $oldFileID = (string)$rowExist['fileID'];
        }

        if ($foundID === 0) {
            $stmtExistsFilenameInGdfile->bind_param('s', $name);
            $stmtExistsFilenameInGdfile->execute();
            $r2 = $stmtExistsFilenameInGdfile->get_result();
            if ($r2 && ($rowExist2 = $r2->fetch_assoc())) {
                $foundID = (int)$rowExist2['ID'];
                $oldFileID = (string)$rowExist2['fileID'];
            }
        }

        if ($foundID > 0) {
            if ($oldFileID !== $fileID) {
                $stmtUpdateFileID->bind_param('si', $fileID, $foundID);
                $stmtUpdateFileID->execute();
                if ($stmtUpdateFileID->affected_rows === 1) {
                    $updated++;
                }
            }
            continue;
        }

        // ------------------------
        // 插入 gdfile
        // ------------------------
        $stmtInsert->bind_param('ssis', $name, $fileID, $paperID, $doi);
        $stmtInsert->execute();
        if ($stmtInsert->affected_rows === 1) {
            $inserted++;
        }
    }

    // ------------------------
    // 统计：gdfile 行数（after）
    // ------------------------
    $gdfileCountAfter = tableCount($mysqli, 'gdfile');

    // ------------------------
    // 输出汇总
    // ------------------------
    echo "==== 08_server_update_gdrive_fileID.php 运行结果 ====\n";
    echo "papers 表数据行数:                 {$papersCount}\n";
    echo "gdfile 表数据行数(运行前):         {$gdfileCountBefore}\n";
    echo "gdfile 表数据行数(运行后):         {$gdfileCountAfter}\n";
    echo "gd1:/13_paperRemoteStorage PDF数:   {$pdfCount}\n";
    echo "gdfile 本次新增数据行数:           {$inserted}\n";
    echo "gdfile 本次更新fileID数据行数:     {$updated}\n";
    echo "====================================================\n";

    // 释放资源
    $stmtFindPaperIDExact->close();
    $stmtFindPaperIDLower->close();
    $stmtExistsDoiInGdfile->close();
    $stmtExistsFilenameInGdfile->close();
    $stmtUpdateFileID->close();
    $stmtInsert->close();
    $mysqli->close();

} catch (Throwable $e) {
    fatal("运行异常：" . $e->getMessage());
}
