<!DOCTYPE html>
<html>
<head>
    <title>域名替换</title>
    <style>
        code {
            display: block;
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ccc;
            white-space: pre-wrap;
            font-family: Consolas, Courier New, monospace;
            line-height: 2.0;
            margin-top: 40px;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
            width: 50%;
            margin: 50px auto;
        }
        label[for="name"] {
            margin-top: 60px; /* Adjust this value as needed */
            margin-bottom: 10px;
            display: block;
        }
        label[for="domain"] {
            margin-top: 60px; /* Adjust this value as needed */
            margin-bottom: 0px;
            display: block;
        }
        input[type="text"] {
            width: 80%;
            height: 25px; /* Adjust the pixel value as needed */
        }
        input[type="submit"] {
            margin-top: 3px;      /* 控制域名输入框以及提交框与Domain:的垂直距离 */
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #3e8e41;
        }
        form {
            margin-bottom: 15px;
            width: 90%;
        }
        .button-row {
            clear: both;
        }
    </style>
</head>
<body>

<div class="container">
    <form method="post">
        <label for="name">Name: (Enter a name without spaces, e.g., use HackerNews instead of Hacker News.)</label>
        <input type="text" name="name" id="name" placeholder="a,b,c">
        <label for="domain">Domain:</label>
        <input type="text" name="domain" id="domain" placeholder="X,Y,Z">
        <input type="submit" value="生成代码">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $domain = $_POST['domain'];

        $nameArray = explode(',', $name);
        $domainArray = explode(',', $domain);

        $website = implode('、', $nameArray);
        $vname = 'query_' . implode('', $nameArray);
        $search_Url = implode('', $nameArray) . '_Url';

        $domainString = '';
        foreach ($domainArray as $d) {
            $domainString .= 'site%3A' . $d . '+OR+';
        }
        $domainString = rtrim($domainString, '+OR+');

        $code = <<<CODE
if(isset(\$_GET['$vname'])) {
    \$$vname = \$_GET['$vname'];
    \$$search_Url = 'https://www.google.com/search?q=$domainString+' . \$$vname;
    echo "<script>window.location.replace('\$$search_Url');</script>";
    exit;
}

<form method="GET">
    <label for="$vname">在 $website 中搜索:</label>
    <input type="text" name="$vname" id="$vname" placeholder=" $website 搜索">
    <input type="submit" value="搜索">
</form>
CODE;

        echo '<code>' . htmlentities($code) . '</code>';

    }
    ?>
</div>

</body>
</html>
