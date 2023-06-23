<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hello World</title>
    <style>
        .top-button,
        .bottom-button {
            position: fixed;
            padding: 10px;
            background-color: #ccc;
            color: #fff;
            text-decoration: none;
        }

        .top-button {
            top: 20px;
            right: 20px;
        }

        .bottom-button {
            bottom: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <h1>Hello World</h1>

    <a href="#top" class="top-button">返回顶部</a>
    <a href="#bottom" class="bottom-button">返回底部</a>

    <?php
    // PHP代码部分，可以在这里添加其他的PHP逻辑或内容
    ?>

    <script>
        var topButton = document.querySelector('.top-button');
        topButton.addEventListener('click', function(event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        var bottomButton = document.querySelector('.bottom-button');
        bottomButton.addEventListener('click', function(event) {
            event.preventDefault();
            var windowHeight = window.innerHeight;
            var documentHeight = document.documentElement.scrollHeight;
            window.scrollTo({ top: documentHeight - windowHeight, behavior: 'smooth' });
        });
    </script>
</body>
</html>
