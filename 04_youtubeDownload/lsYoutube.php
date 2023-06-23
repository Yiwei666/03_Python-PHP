<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="https://mctea.one/00_logo/fileList.png">
    <title>文件列表</title>
    <style>
        pre {
            font-family: Calibri, Arial, sans-serif;
        }

        table {
            margin-left: auto;
            margin-right: auto;
            border-collapse: collapse;
            table-layout: fixed;
            width: 60%;
        }

        th, td {
            text-align: left;
            padding: 5px;
        }

        th.filename {
            width: 50%;
        }

        th.date, th.size {
            width: 30%;
        }

        th.size {
            width: 20%;
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

                        $files[] = [
                            'filename' => $file,
                            'date' => $creationDate,
                            'size' => $fileSize,
                            'unit' => $sizeUnit
                        ];
                    }
                }
                // 关闭目录句柄
                closedir($handle);

                // 对文件名数组进行排序
                sort($files);

                // 插入空行
                echo "<br>";

                // 打印排序后的文件信息
                echo "<table>";
                echo "<tr><th class='filename'>文件名</th><th class='date'>日期</th><th class='size'>大小</th></tr>";
                foreach ($files as $file) {
                    echo "<tr>";
                    echo "<td>" . $file['filename'] . "</td>";
                    echo "<td>" . $file['date'] . "</td>";
                    echo "<td>" . $file['size'] . " " . $file['unit'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "指定的目录不存在！";
        }
        ?>
    </pre>
</body>
</html>
