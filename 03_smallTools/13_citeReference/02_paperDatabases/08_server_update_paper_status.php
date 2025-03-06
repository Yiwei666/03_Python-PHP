<?php
/**
 * update_paper_status.php
 *
 * 功能：
 * 1. 连接 MySQL 数据库，读取 papers 表中的 paperID, doi, status
 * 2. 扫描本地目录(A)和 OneDrive 远程目录(B)，获取已有的 .pdf 文件名（Base32 编码）
 * 3. 针对每一条数据，根据文件在本地和远程的存在情况，按指定逻辑更新 status
 * 4. 如果 status = DW，则执行下载操作；如果 status = DL，则执行本地删除操作
 * 5. 下载/删除成功后，更新 status
 *
 * 注意：请根据实际环境修改：
 * - 数据库连接参数
 * - Base32 编码函数
 * - rclone 命令获取远程文件列表方式(此处改为递归获取)
 * - rclone copy 命令路径
 * - 错误处理和日志记录方式
 */

/**
 * Base32 编码和解码类 (符合 RFC 4648 标准)
 */
class Base32
{
    private static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * 将输入字符串进行 Base32 编码
     * @param string $input
     * @return string
     */
    public static function encode($input)
    {
        if (empty($input)) return '';

        $binary = '';
        // 将每个字符转换为其二进制表示
        for ($i = 0; $i < strlen($input); $i++) {
            $binary .= str_pad(decbin(ord($input[$i])), 8, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串填充到5的倍数
        $binary = str_pad($binary, ceil(strlen($binary) / 5) * 5, '0', STR_PAD_RIGHT);

        $base32 = '';
        for ($i = 0; $i < strlen($binary); $i += 5) {
            $chunk = substr($binary, $i, 5);
            // 如果最后一组不足5位，则补0
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $index = bindec($chunk);
            $base32 .= self::$alphabet[$index];
        }

        // 添加填充
        $padding = 8 - (strlen($base32) % 8);
        if ($padding !== 8) {
            $base32 .= str_repeat('=', $padding);
        }

        return $base32;
    }

    /**
     * 将 Base32 编码的字符串解码回原始字符串
     * @param string $input
     * @return string|false
     */
    public static function decode($input)
    {
        if (empty($input)) return '';

        // 移除填充字符
        $input = strtoupper($input);
        $input = rtrim($input, '=');

        $binary = '';
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            $index = strpos(self::$alphabet, $char);
            if ($index === false) {
                // 无效的Base32字符
                return false;
            }
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串转换回原始字符串
        $decoded = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            if (strlen($byte) < 8) {
                // 忽略不完整的字节
                break;
            }
            $decoded .= chr(bindec($byte));
        }

        return $decoded;
    }
}

// ============ 配置部分 =============
$db_host     = 'localhost';          // 数据库主机
$db_name     = 'paper_db';           // 数据库名称
$db_user     = 'root';               // 数据库用户名
$db_password = '123456';             // 数据库密码

// 本地目录(A)
$local_dir   = '/home/01_html/08_paperLocalStorage';
// 远程目录(B)
// 注意：此处的远程目录可能包含若干子目录
$remote_dir  = 'rc4:/3图书/13_paperRemoteStorage';

// 1. 连接数据库
try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_password, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 2. 获取本地目录(A)下所有PDF文件名（Base32）
$local_files = getLocalPdfFiles($local_dir); // 返回不含路径的文件名数组，如 ["ABCD....pdf", "XYZ...pdf", ...]

// 3. 获取远程目录(B)及其子目录下所有PDF文件
//    注意这里返回的是 关联数组：['ABCD.pdf' => 'subdir1/ABCD.pdf', 'XYZ.pdf' => 'subdir2/XYZ.pdf', ...]
$remote_files = getRemotePdfFiles($remote_dir);

// 4. 从数据库获取所有 paperID, doi, status
$sql = "SELECT paperID, doi, status FROM papers";
$stmt = $pdo->query($sql);
$papers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. 逐条处理
foreach ($papers as $paper) {
    $paperID = $paper['paperID'];
    $doi     = $paper['doi'];
    $status  = $paper['status'];

    // 将 DOI 做 Base32 编码 + ".pdf"
    $base32Filename = Base32::encode($doi) . '.pdf';

    // 判断文件是否存在于本地/远程
    $inLocal  = in_array($base32Filename, $local_files, true);
    // 注意这里改用 array_key_exists 来判断是否在远程存在
    $inRemote = array_key_exists($base32Filename, $remote_files);

    // 如果状态是 DW => 执行 rclone 下载，然后设置为 CL（若成功）
    if ($status === 'DW') {
        echo "[paperID={$paperID}, doi={$doi}] status=DW => 准备从远程下载\n";
        if ($inRemote) {
            // 远程文件相对路径
            $remoteRelativePath = $remote_files[$base32Filename];
            $download_ok = rcloneDownload($remote_dir, $local_dir, $remoteRelativePath);
            if ($download_ok) {
                updateStatus($pdo, $paperID, 'CL');
                echo "[paperID={$paperID}, doi={$doi}] 下载成功 => status 改为 CL\n";
                // 下载成功后，本地也就存在了该文件，可根据需要更新 $local_files 数组
                $local_files[] = $base32Filename;
            } else {
                echo "[paperID={$paperID}, doi={$doi}] 下载失败，status 不变\n";
            }
        } else {
            echo "[paperID={$paperID}, doi={$doi}] 远程不存在该文件，无法下载\n";
        }
        continue;
    }

    // 如果状态是 DL => 执行本地删除，然后设置为 C（若成功）
    if ($status === 'DL') {
        echo "[paperID={$paperID}, doi={$doi}] status=DL => 准备删除本地文件\n";
        $delete_ok = deleteLocalFile($local_dir, $base32Filename);
        if ($delete_ok) {
            updateStatus($pdo, $paperID, 'C');
            echo "[paperID={$paperID}, doi={$doi}] 删除成功 => status 改为 C\n";
            // 删除成功后，本地自然不存在了该文件，可根据需要更新 $local_files 数组
            $local_files = array_diff($local_files, [$base32Filename]);
        } else {
            echo "[paperID={$paperID}, doi={$doi}] 删除失败，status 不变\n";
        }
        continue;
    }

    // 普通情况，根据文件是否在本地和远程进行判断
    if ($inLocal && $inRemote) {
        // 如果同时存在，若 status 不是 CL 或 DL，则改为 CL
        if ($status !== 'CL' && $status !== 'DL') {
            updateStatus($pdo, $paperID, 'CL');
            echo "[paperID={$paperID}, doi={$doi}] 同时存在本地和远程 => status 改为 CL\n";
        }
    } elseif ($inRemote && !$inLocal) {
        // 如果只在远程存在，若 status 不是 C 或 DW，则改为 C
        if ($status !== 'C' && $status !== 'DW') {
            updateStatus($pdo, $paperID, 'C');
            echo "[paperID={$paperID}, doi={$doi}] 只在远程存在 => status 改为 C\n";
        }
    } elseif ($inLocal && !$inRemote) {
        // 如果只在本地存在，若 status 不是 L，则改为 L
        if ($status !== 'L') {
            updateStatus($pdo, $paperID, 'L');
            echo "[paperID={$paperID}, doi={$doi}] 只在本地存在 => status 改为 L\n";
        }
    } else {
        // 本地远程都不存在，若 status 不是 N，则改为 N
        if ($status !== 'N') {
            updateStatus($pdo, $paperID, 'N');
            echo "[paperID={$paperID}, doi={$doi}] 本地&远程都不存在 => status 改为 N\n";
        }
    }
}

// ============ 相关函数定义 ============

/**
 * 从本地目录获取所有 PDF 文件名（不含子目录）
 * @param string $dir
 * @return array
 */
function getLocalPdfFiles(string $dir): array
{
    $files = [];
    // 获取目录下所有 .pdf
    $globPattern = rtrim($dir, '/') . '/*.pdf';
    foreach (glob($globPattern) as $filePath) {
        $files[] = basename($filePath);
    }
    return $files;
}

/**
 * 从远程目录获取所有 PDF 文件名（包含子目录），使用 rclone
 * 这里示例使用 `rclone lsf --recursive --files-only`, 输出就直接是相对于 remote_dir 的子路径
 * 如 "subdirA/SomeFile.pdf", "subdirB/ABC.pdf" 等
 *
 * 返回格式：['SomeFile.pdf' => 'subdirA/SomeFile.pdf', 'ABC.pdf' => 'subdirB/ABC.pdf', ...]
 *
 * @param string $remote_dir
 * @return array
 */
function getRemotePdfFiles(string $remote_dir): array
{
    $files = [];
    // -R 或 --recursive 可以递归列出子目录
    $command = "rclone lsf --recursive --files-only \"{$remote_dir}\"";
    exec($command, $output, $return_var);
    if ($return_var !== 0) {
        echo "[Error] 获取远程目录文件列表失败\n";
        return $files; // 空数组
    }

    // $output 数组每行可能是 "subdir/xxxxx.pdf"
    foreach ($output as $line) {
        $line = trim($line);
        // 跳过空行
        if ($line === '') {
            continue;
        }
        $filename = basename($line);
        // 判断是否是 .pdf 文件
        if (substr($filename, -4) === '.pdf') {
            // 将 basename 作为 key，完整子路径作为 value
            // 若同名文件在多个子目录出现，这里后出现的会覆盖前一个（如有需要可自行处理冲突）
            $files[$filename] = $line;
        }
    }

    return $files;
}

/**
 * 调用 rclone 进行下载
 * @param string $remoteDir       远程主目录 (如 "rc4:/3图书/13_paperRemoteStorage")
 * @param string $localDir        本地目录
 * @param string $remoteSubPath   远程文件相对路径 (如 "subdirA/XXXX.pdf")
 * @return bool                   是否下载成功
 */
function rcloneDownload(string $remoteDir, string $localDir, string $remoteSubPath): bool
{
    // 组合出完整的远程文件路径，例如 "rc4:/3图书/13_paperRemoteStorage/subdirA/XXXX.pdf"
    $remoteFilePath = rtrim($remoteDir, '/') . '/' . $remoteSubPath;

    // 这里只指定到本地目录，rclone 会将文件复制到该目录下，文件名不变
    $copyCommand = "rclone copy \"{$remoteFilePath}\" \"{$localDir}\"";
    exec($copyCommand, $copyOutput, $copyReturnVar);

    if ($copyReturnVar !== 0) {
        echo "[Error] Failed to copy {$remoteSubPath} from remote. Error code = {$copyReturnVar}\n";
        return false;
    } else {
        echo "[Info] Copied {$remoteSubPath} successfully\n";
        return true;
    }
}

/**
 * 删除本地文件
 * @param string $localDir  本地目录
 * @param string $fileName  文件名（含 .pdf）
 * @return bool             是否删除成功
 */
function deleteLocalFile(string $localDir, string $fileName): bool
{
    $filePath = rtrim($localDir, '/') . '/' . $fileName;
    if (!file_exists($filePath)) {
        // 没有这个文件，说明已经算是“删除成功”
        return true;
    }
    if (unlink($filePath)) {
        echo "[Info] Deleted {$fileName} successfully\n";
        return true;
    } else {
        echo "[Error] Failed to delete {$fileName}\n";
        return false;
    }
}

/**
 * 更新数据库中某条记录的 status
 * @param PDO $pdo
 * @param int $paperID
 * @param string $newStatus
 * @return void
 */
function updateStatus(PDO $pdo, int $paperID, string $newStatus): void
{
    $sql = "UPDATE papers SET status = :status WHERE paperID = :paperID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':status'  => $newStatus,
        ':paperID' => $paperID
    ]);
}

echo "=== All Done ===\n";
?>
