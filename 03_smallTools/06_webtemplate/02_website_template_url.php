<?php
$directory = '/home/01_html/04_webtemplate/webtemplate/'; // 指定目录路径

// 获取目录下的所有子文件夹
$subfolders = array_filter(glob($directory . '*'), 'is_dir');

foreach ($subfolders as $folder) {
    $htmlFiles = glob($folder . '/*.html'); // 获取当前子文件夹下的 HTML 文件
    
    if (!empty($htmlFiles)) {
        $folderName = basename($folder); // 子文件夹名

        foreach ($htmlFiles as $htmlFile) {
            $htmlFileName = basename($htmlFile); // HTML 文件名
            $link = "https://domain.com/04_webtemplate/webtemplate/{$folderName}/{$htmlFileName}"; // 构造链接

            echo "<a href=\"{$link}\">{$link}</a><br>"; // 打印链接到网页上
        }
    }
}
?>
