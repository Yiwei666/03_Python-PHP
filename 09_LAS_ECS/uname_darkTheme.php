<html>
  <head>
    <meta charset="UTF-8">
    <title>Input Form</title>
    <style>
      body {
        background-color: #222;
        color: #00bcd4;
        font-size: 14px;
      }

      form {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
      }

      input[type="text"], input[type="submit"] {
        font-size: 14px;
        padding: 5px;
        margin-bottom: 10px;
        background-color: #333;
        color: #00bcd4;
        border: 1px solid #00bcd4;
      }

      input[type="submit"] {
        background-color: #008080;
        color: #fff;
        cursor: pointer;
      }

      input[type="submit"]:hover {
        background-color: #006666;
      }

      code {
        display: block;
        margin: 0 auto;
        text-align: center;
        color: #00bcd4;
      }
    </style>
  </head>
  <body>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
      Name: <input type="text" name="name" size="50"><br><br>
      URL: <input type="text" name="url" size="50"><br><br>
      <input type="submit" name="submit" value="Submit">
    </form>
    <?php
      if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $url = $_POST['url'];

        echo '<br><br><code>—-&lt;a target="_blank" rel="noopener" href="'.$url.'"&gt;'.$name.'&lt;/a&gt;</code><br><br>';
        echo '<code>----['.$name.']('.$url.')</code>';
      }
    ?>
  </body>
</html>
