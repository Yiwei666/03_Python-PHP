<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="https://mctea.one/00_logo/fileList.png">
    <title>Youtube文件列表</title>
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

        .top-button,
        .bottom-button {
            position: fixed;
            padding: 10px;
            background-color: #ccc;
            color: #fff;
            text-decoration: none;
        }

        .top-button {
            top: 20px;
            right: 20px;
        }

        .bottom-button {
            bottom: 20px;
            right: 20px;
        }

    </style>
</head>
<body>
    <pre>
        <?php
        date_default_timezone_set('Asia/Shanghai');

        $directory = '/home/01_html/05_twitter_video';

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
                        $creationDate = date("Y-m-d H:i:s", filemtime($filePath));
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

                // 计算文件大小总和
                $totalSize = 0;
                foreach ($files as $file) {
                    if ($file['unit'] === 'MB') {
                        $totalSize += $file['size'] * 1024; // Convert MB to KB
                    } elseif ($file['unit'] === 'B') {
                        $totalSize += $file['size'] / 1024; // Convert B to KB
                    } else {
                        $totalSize += $file['size']; // Keep size in KB or already in KB
                    }
                }

                // 转换文件大小的单位
                $totalUnit = 'KB';
                if ($totalSize >= 1024) {
                    $totalSize /= 1024;
                    $totalUnit = 'MB';
                    if ($totalSize >= 1024) {
                        $totalSize /= 1024;
                        $totalUnit = 'GB';
                    }
                }

                // 打印文件总数和所有文件大小总和
                echo "<br>";
                echo "<div style='text-align: center;'>";
                echo "文件总数: " . count($files) . "，";
                echo "所有文件大小总和: " . round($totalSize, 2) . " " . $totalUnit;
                echo "</div>";
                
            }
        } else {
            echo "指定的目录不存在！";
        }
        ?>
    </pre>

    <a href="#top" class="top-button">返回顶部</a>
    <a href="#bottom" class="bottom-button">返回底部</a>

    <script>
        var topButton = document.querySelector('.top-button');
        topButton.addEventListener('click', function(event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        var bottomButton = document.querySelector('.bottom-button');
        bottomButton.addEventListener('click', function(event) {
            event.preventDefault();
            var windowHeight = window.innerHeight;
            var documentHeight = document.documentElement.scrollHeight;
            window.scrollTo({ top: documentHeight - windowHeight, behavior: 'smooth' });
        });
    </script>
</body>
</html>
