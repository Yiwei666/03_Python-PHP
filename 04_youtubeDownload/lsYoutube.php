<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>文件列表</title>
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
                        $files[] = $file;
                    }
                }
                // 关闭目录句柄
                closedir($handle);

                // 对文件名数组进行排序
                sort($files);

                // 插入空行
                echo "<br>";

                // 打印排序后的文件名
                foreach ($files as $file) {
                    echo trim($file) . "<br>";
                }
            }
        } else {
            echo "指定的目录不存在！";
        }
        ?>
    </pre>
</body>
</html>
