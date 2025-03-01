<?php
// 引入数据库配置文件
include '08_db_config.php';                          // 创建数据库连接对象 $mysqli
include '08_db_sync_images.php';                     // 新下载的图片名写入到数据库中
syncImages("/home/01_html/08_x/image/01_imageHost");    // 调用函数并提供图片存储目录
include '08_db_image_status.php';                    // 判断数据库中所有图片的存在状态

// 提醒用户输入 a、b 和 x，用空格分隔
echo "Enter three integers (a, b, x), separated by spaces: ";
$input = trim(fgets(STDIN));
list($a, $b, $x) = explode(' ', $input);

// 检查 a 是否小于 b
if ($a >= $b) {
    echo "Error: 'a' should be less than 'b'.\n";
    exit(1);
}

// 显示选项
echo "Select an option:\n";
echo "1. Update and count images with likes between [$a, $b]\n";
echo "2. Update and count images with dislikes between [$a, $b]\n";
echo "3. Count images with likes between [$a, $b]\n";
echo "4. Count images with dislikes between [$a, $b] and delete corresponding files\n";
echo "5. Count images with likes and dislikes between [$a, $b] where image_exists is 1\n";
echo "6. Copy images with likes-dislikes in range [$a, $b] to another directory\n";
echo "7. Count images with image_exists = 1 and likes in range [$a, $b]\n";
echo "8. Count images with likes between [$a, $b] and delete corresponding files\n";
echo "Enter option (1/2/3/4/5/6/7/8): ";
$option = trim(fgets(STDIN));

// 获取图片总数
$total_images_result = $mysqli->query("SELECT COUNT(*) AS total FROM images");
$total_images = $total_images_result->fetch_assoc()['total'];

// 获取 image_exists 为 0 和 1 的图片数量
$image_exists_0_result = $mysqli->query("SELECT COUNT(*) AS total FROM images WHERE image_exists = 0");
$image_exists_0_count = $image_exists_0_result->fetch_assoc()['total'];

$image_exists_1_result = $mysqli->query("SELECT COUNT(*) AS total FROM images WHERE image_exists = 1");
$image_exists_1_count = $image_exists_1_result->fetch_assoc()['total'];

// 功能 1：打印 likes 在 [a, b] 之间的图片数量并增加 x 个
if ($option == '1') {
    $count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE likes BETWEEN $a AND $b");
    $count = $count_result->fetch_assoc()['count'];
    $mysqli->query("UPDATE images SET likes = likes + $x WHERE likes BETWEEN $a AND $b");
    echo "Number of images with likes between [$a, $b]: $count\n";
    echo "Total images in the database: $total_images\n";
    echo "Updated likes by adding $x to all matching images.\n";
}

// 功能 2：打印 dislikes 在 [a, b] 之间的图片数量并增加 x 个
elseif ($option == '2') {
    $count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE dislikes BETWEEN $a AND $b");
    $count = $count_result->fetch_assoc()['count'];
    $mysqli->query("UPDATE images SET dislikes = dislikes + $x WHERE dislikes BETWEEN $a AND $b");
    echo "Number of images with dislikes between [$a, $b]: $count\n";
    echo "Total images in the database: $total_images\n";
    echo "Updated dislikes by adding $x to all matching images.\n";
}

// 功能 3：打印 likes 在 [a, b] 之间的图片数量
elseif ($option == '3') {
    $count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE likes BETWEEN $a AND $b");
    $count = $count_result->fetch_assoc()['count'];
    echo "Number of images with likes between [$a, $b]: $count\n";
    echo "Total images in the database: $total_images\n";
}

// 功能 4：打印 dislikes 在 [a, b] 之间的图片数量，删除相关文件
elseif ($option == '4') {
    // 获取 dislikes 在 [a, b] 之间的所有图片文件名称
    $files_result = $mysqli->query("SELECT image_name FROM images WHERE dislikes BETWEEN $a AND $b");
    $project_folder = '/home/01_html/08_x/image/01_imageHost/'; // 替换为项目文件夹的路径
    $files_to_delete = [];
    
    // 检查每个文件是否存在
    while ($row = $files_result->fetch_assoc()) {
        $file_path = $project_folder . $row['image_name'];
        if (file_exists($file_path)) {
            $files_to_delete[] = $row['image_name'];
        }
    }

    // 打印待删除文件的数量和名称
    $num_files_to_delete = count($files_to_delete);
    if ($num_files_to_delete > 0) {
        echo "Number of files to be deleted: $num_files_to_delete\n";
        echo "Files to be deleted:\n";
        foreach ($files_to_delete as $file) {
            echo $file . "\n";
        }

        // 确认是否删除文件，只输入 y 或 n
        echo "Do you want to delete these files? (y/n): ";
        $confirmation = strtolower(trim(fgets(STDIN)));

        if ($confirmation == 'y') {
            foreach ($files_to_delete as $file) {
                $file_path = $project_folder . $file;

                // 删除文件
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // 获取删除后的项目文件夹中剩余文件
            $remaining_files = array_diff(scandir($project_folder), ['..', '.']);
            $remaining_files_count = count($remaining_files);

            // 打印删除后的文件总数，并计算与数据库记录的差值
            echo "Remaining images in the project folder: " . $remaining_files_count . "\n";
            echo "Total images in the database: " . $total_images . "\n";
            echo "Difference between database and project folder: " . ($total_images - $remaining_files_count) . "\n";
        } else {
            echo "Deletion cancelled.\n";
        }
    } else {
        echo "No files to delete with dislikes between [$a, $b].\n";
    }
}

// 功能 5：打印 likes 和 dislikes 在 [a, b] 之间且 image_exists 为 1 的图片数量
elseif ($option == '5') {
    $likes_count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE likes BETWEEN $a AND $b AND image_exists = 1");
    $likes_count = $likes_count_result->fetch_assoc()['count'];

    $dislikes_count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE dislikes BETWEEN $a AND $b AND image_exists = 1");
    $dislikes_count = $dislikes_count_result->fetch_assoc()['count'];

    echo "Number of images with likes between [$a, $b] where image_exists is 1: $likes_count\n";
    echo "Number of images with dislikes between [$a, $b] where image_exists is 1: $dislikes_count\n";
    echo "Total images in the database: $total_images\n";
    echo "Number of images where image_exists is 0: $image_exists_0_count\n";
    echo "Number of images where image_exists is 1: $image_exists_1_count\n";
}

// 功能 6：复制 likes - dislikes 在 [a, b] 之间的图片到另一个目录
elseif ($option == '6') {
    $query = "SELECT image_name FROM images WHERE (likes - dislikes) BETWEEN $a AND $b";
    $result = $mysqli->query($query);
    $destination_folder = '/home/01_html/08_x/image/06_picVideo/';
    if (!file_exists($destination_folder)) {
        mkdir($destination_folder, 0777, true);
    }
    $num_copied = 0;
    while ($row = $result->fetch_assoc()) {
        $source_file = "/home/01_html/08_x/image/01_imageHost/" . $row['image_name'];
        $destination_file = $destination_folder . $row['image_name'];
        if (file_exists($source_file)) {
            if (copy($source_file, $destination_file)) {
                $num_copied++;
            }
        }
    }
    echo "Number of images copied: $num_copied\n";
}

// 功能 7：统计 image_exists = 1 并且 likes 在 [a, b] 范围内的每一个值的图片数量
elseif ($option == '7') {
    echo "Counting images with image_exists = 1 and likes in range [$a, $b]:\n";
    for ($i = $a; $i <= $b; $i++) {
        $count_result = $mysqli->query("SELECT COUNT(*) AS count FROM images WHERE likes = $i AND image_exists = 1");
        $count = $count_result->fetch_assoc()['count'];
        echo "Likes = $i: $count images\n";
    }
    echo "Total images in the database: $total_images\n";
}

// 功能 8：打印 likes 在 [a, b] 之间的图片数量，删除相关文件
elseif ($option == '8') {
    // 获取 likes 在 [a, b] 之间的所有图片文件名称
    $files_result = $mysqli->query("SELECT image_name FROM images WHERE likes BETWEEN $a AND $b");
    $project_folder = '/home/01_html/08_x/image/01_imageHost/'; // 替换为项目文件夹的路径
    $files_to_delete = [];
    
    // 检查每个文件是否存在
    while ($row = $files_result->fetch_assoc()) {
        $file_path = $project_folder . $row['image_name'];
        if (file_exists($file_path)) {
            $files_to_delete[] = $row['image_name'];
        }
    }

    // 打印待删除文件的数量和名称
    $num_files_to_delete = count($files_to_delete);
    if ($num_files_to_delete > 0) {
        echo "Number of files to be deleted: $num_files_to_delete\n";
        echo "Files to be deleted:\n";
        foreach ($files_to_delete as $file) {
            echo $file . "\n";
        }

        // 确认是否删除文件，只输入 y 或 n
        echo "Do you want to delete these files? (y/n): ";
        $confirmation = strtolower(trim(fgets(STDIN)));

        if ($confirmation == 'y') {
            foreach ($files_to_delete as $file) {
                $file_path = $project_folder . $file;

                // 删除文件
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // 获取删除后的项目文件夹中剩余文件
            $remaining_files = array_diff(scandir($project_folder), ['..', '.']);
            $remaining_files_count = count($remaining_files);

            // 打印删除后的文件总数，并计算与数据库记录的差值
            echo "Remaining images in the project folder: " . $remaining_files_count . "\n";
            echo "Total images in the database: " . $total_images . "\n";
            echo "Difference between database and project folder: " . ($total_images - $remaining_files_count) . "\n";
        } else {
            echo "Deletion cancelled.\n";
        }
    } else {
        echo "No files to delete with likes between [$a, $b].\n";
    }
}

// 无效选项处理
else {
    echo "Invalid option selected.\n";
}
