<?php
$directory = '/home/01_html/02_douyVideo';

// 检查目录是否存在
if (is_dir($directory)) {
    // 打开目录
    if ($handle = opendir($directory)) {
        // 循环读取目录中的文件和子目录
        while (($file = readdir($handle)) !== false) {
            // 排除当前目录（.）和上级目录（..）
            if ($file != "." && $file != "..") {
                // 打印文件名
                echo $file . "\n";
            }
        }
        // 关闭目录句柄
        closedir($handle);
    }
} else {
    echo "指定的目录不存在！";
}
?>
