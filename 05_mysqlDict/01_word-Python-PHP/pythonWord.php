<!DOCTYPE html>
<html>
<head>
    <title>运行 09N_背单词.py</title>
    <style>
        body {
            background-color: #222;
            color: white;
        }

        .output-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: left;
            height: 60vh;
            margin-top: 50px; /* Replace with your desired margin-top value */
        }

        pre {
            font-size: 18px;
            margin: 0;
        }

        .form-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="output-container">
        <?php
            if(isset($_POST['submit'])){
                $output = shell_exec('/home/00_software/01_Anaconda/bin/python /home/01_html/15_pythonword/09N_背单词.py /home/01_html/15_pythonword/09N_单词数据库.json 2>&1');
                echo "<pre>$output</pre>";
            }
        ?>
    </div>

    <div class="form-container">
        <form method="post">
            <input type="submit" name="submit" value="运行 09N_背单词.py">
        </form>
    </div>
</body>
</html>
