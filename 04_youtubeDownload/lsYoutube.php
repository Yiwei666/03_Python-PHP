<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>文件列表</title>
    <style>
        pre {
            font-family: monospace;
        }
    </style>
</head>
<body>
    <pre>
        <?php
        $directory = '/home/01_html/01_yiGongZi';

        // 检查目录是否存在
        if (is_dir($directory)) {
            // 打开目录
            if ($handle = opendir($directory)) {
                // 读取目录中的文件和子目录到数组中
                $files = [];
                $maxFileNameLength = 0;
                while (($file = readdir($handle)) !== false) {
                    // 排除当前目录（.）和上级目录（..）
                    if ($file != "." && $file != "..") {
                        // 获取文件的创建日期和大小
                        $filePath = $directory . '/' . $file;
                        $creationDate = date("Y-m-d H:i:s", filectime($filePath));
                        $fileSize = filesize($filePath);

                        // 转换文件大小的单位
                        $sizeUnit = 'B';
                        if ($fileSize >= 1024 * 1024) {
                            $fileSize = round($fileSize / (1024 * 1024), 2);
                            $sizeUnit = 'MB';
                        } elseif ($fileSize >= 1024) {
                            $fileSize = round($fileSize / 1024, 2);
                            $sizeUnit = 'KB';
                        }

                        // 将文件名、创建日期和文件大小拼接为一行
                        $line = sprintf("%-{$maxFileNameLength}s  %s  %6s %s", $file, $creationDate, $fileSize, $sizeUnit);
                        $files[] = $line;

                        // 更新最大文件名长度
                        $maxFileNameLength = max($maxFileNameLength, strlen($file));
                    }
                }
                // 关闭目录句柄
                closedir($handle);

                // 对文件名数组进行排序
                sort($files);

                // 插入空行
                echo "<br>";

                // 打印排序后的文件信息
                foreach ($files as $line) {
                    echo trim($line) . "<br>";
                }
            }
        } else {
            echo "指定的目录不存在！";
        }
        ?>
    </pre>
</body>
</html>
