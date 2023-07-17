<html>
  <head>
    <meta charset="UTF-8">
    <title>Input Form</title>
    <style>
      form {
        display: flex;
        flex-direction: column;
        align-items: center;
      }

      code {
        display: block;
        margin: 0 auto;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
      Name: <input type="text" name="name" size="50" style="font-size:14px;"><br><br>
      URL: <input type="text" name="url" size="50" style="font-size:14px;"><br><br>
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
