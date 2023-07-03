<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>查看文件夹中的图片</title>
	<style>
		.img-container {
			display: inline-block;
			margin: 10px;
			vertical-align: top;
		}
		
		.img-container img {
			width: 590px;
			height: 300px;
			display: block;
			margin: 0 auto;
		}
	</style>
</head>
<body>
	<h4>查看文件夹中的图片</h4>
	<form method="POST">
		<label for="folder-name">请输入要查看的文件夹名称：</label>
		<input type="text" id="folder-name" name="folder-name" required>
		<button type="submit" name="view">查看</button>
	</form>

	<?php
	if(isset($_POST['view'])) {
		$folder_name = $_POST['folder-name'];
		$folder_path = dirname(__FILE__) . '/01_pic/' . $folder_name;

		if(is_dir($folder_path)) {
			$files = scandir($folder_path);
			foreach($files as $file) {
				if($file != '.' && $file != '..') {
					echo "<div class='img-container'><img src='01_pic/$folder_name/$file'></div>";
				}
			}
		} else {
			echo "文件夹不存在！";
		}
	}
	?>
</body>
</html>
