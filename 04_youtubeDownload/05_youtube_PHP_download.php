<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['input'])) {
        $userInput = $_POST['input'];
        $filePath = '/home/01_html/06_youtubeDownload/01_name+url.txt';

        file_put_contents($filePath, $userInput);

        echo 'String saved successfully!';
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Youtube Downloader</title>
    <link rel="shortcut icon" href="https://mctea.one/00_logo/download.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        #inputForm {
            text-align: center;
        }
        #inputText {
            width: 400px;
            height: 200px;
        }
        #saveButton {
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <form id="inputForm" method="POST">
        <textarea id="inputText" name="input" rows="5" cols="50" placeholder="请输入字符串"></textarea>
        <br>
        <input id="saveButton" type="submit" value="保存并执行">
    </form>

    <div id="output"></div>

    <script>
        $(document).ready(function() {
            $('#inputForm').submit(function(event) {
                event.preventDefault();
                var userInput = $('#inputText').val();

                $.ajax({
                    url: '05_youtube_PHP_download.php',
                    type: 'POST',
                    data: { input: userInput },
                    success: function(response) {
                        runPythonScript();
                    }
                });
            });

            function runPythonScript() {
                $.ajax({
                    url: 'run_python_youtube.php',
                    type: 'GET',
                    success: function(response) {
                        printLogFile();
                    }
                });
            }

            function printLogFile() {
                $.ajax({
                    url: 'print_log_youtube.php',
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
