<?php
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

// If the user clicked the logout link, log them out and redirect to the login page
if (isset($_GET['logout'])) {
  session_destroy(); // destroy all session data
  header('Location: login.php');
  exit;
}
?>
<?php
date_default_timezone_set('Asia/Shanghai');
// 创建以当天日期命名的文件夹
$today_folder = date("Ymd");
$target_dir = "/home/01_html/01_pic/" . $today_folder;
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// 如果上传按钮被点击
if(isset($_POST["upload"])) {
    $target_file = $target_dir . "/" . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // 检查图片是否为真实的图片文件
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
    // 检查图片是否已存在
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }
    // 检查文件大小
    if ($_FILES["fileToUpload"]["size"] > 20000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // 允许上传的文件格式
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // 如果存在错误，则提示用户
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // 否则，将文件上传到服务器
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// 如果查看按钮被点击
if(isset($_POST["view"])) {
    // 查找当天文件夹下的所有图片
    $images = glob($target_dir . "/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
    // 输出图片
    if (!empty($images)) {
        foreach($images as $image) {
            echo "<img src='./01_pic/".$today_folder."/".basename($image)."' width='600' height='300'>";
        }
    } else {
        echo "No pictures found.";
    }
}

if(isset($_POST["viewpast"])) {
// 重定向到图片页面
header("Location: http://domian.com/picture_pastls.php");
exit();
}

?>

<!DOCTYPE html>
<html>
  <head>
    <style>
      form {
        display: flex;
        flex-direction: column; 
        align-items: center;
      }

      /* Add margin to the bottom of the input elements */
      input[type="file"],
      input[type="submit"] {
        margin-bottom: 10px;
      }
    </style>
  </head>
  <body>
    <form action="" method="post" enctype="multipart/form-data">
      <label for="fileToUpload">Select image to upload:</label>
      <input type="file" name="fileToUpload" id="fileToUpload" />

      <input type="submit" value="Upload Image" name="upload" />
    </form>

    <form action="" method="post">
      <input type="submit" value="View Images" name="view" />
    </form>

    <form action="" method="post">
      <input type="submit" value="View Past" name="viewpast" />
    </form>
  </body>
</html>
