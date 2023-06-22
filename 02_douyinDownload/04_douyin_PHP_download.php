<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['input'])) {
        $userInput = $_POST['input'];
        $filePath = '/home/01_html/05_douyinDownload/douyin_url.txt';

        file_put_contents($filePath, $userInput);

        echo 'String saved successfully!';
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Douyin Downloader</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <form id="inputForm" method="POST">
        <textarea id="inputText" rows="5" cols="50" placeholder="请输入字符串"></textarea>
        <br>
        <input type="submit" value="保存并执行">
    </form>

    <div id="output"></div>

    <script>
        $(document).ready(function() {
            $('#inputForm').submit(function(event) {
                event.preventDefault();
                var userInput = $('#inputText').val();

                $.ajax({
                    url: '04_douyin_PHP_download.php',
                    type: 'POST',
                    data: { input: userInput },
                    success: function(response) {
                        runPythonScript();
                    }
                });
            });

            function runPythonScript() {
                $.ajax({
                    url: 'run_python_script.php',
                    type: 'GET',
                    success: function(response) {
                        printLogFile();
                    }
                });
            }

            function printLogFile() {
                $.ajax({
                    url: 'print_log_file.php',
                    type: 'GET',
                    success: function(response) {
                        $('#output').text(response);
                    }
                });
            }
        });
    </script>
</body>
</html>
