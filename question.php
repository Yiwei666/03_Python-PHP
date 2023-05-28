<?php
session_start();

$filename = "questiondata.txt";
$scriptname = "question.php";

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
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <link rel="shortcut icon" href="http://101.200.215.126/00_logo/question.png">
  <title>Write to Question File</title>
  <style>
    
    h3 {
      text-align: center;
      margin: 20px auto; /* Add 20px margin on top and bottom, and center horizontally */
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    textarea[readonly] {
      display: block;
      margin: 0 auto;
      text-align: center;
    }

    .unshow-container {
      text-align: center;
      margin-top: 20px;
    }
/*下面的main，footer是有关logout的style*/
    main,footer {
      font-family: Arial, sans-serif;
      text-align: center;
    }
    p {
      font-size: 14px;
      margin-top: 20px;
      margin-bottom: 20px;
    }
  </style>
  <script>
    function toggleVisibility() {
      window.location.href = "http://101.200.215.126/<?php echo $scriptname; ?>";
    }
  </script>
</head>
<body>
<h3>Write to Question File</h3>
<form action="<?php echo $scriptname; ?>" method="post">
  <p><label for="questiondata">Enter Question Data:</label></p>
  <textarea rows="16" cols="100" id="questiondata" name="questiondata"></textarea><br><br>
  <input type="submit" value="Submit Question">
</form>

  <br><br>
<form action="<?php echo $scriptname; ?>" method="get">
  <input type="submit" value="Display Latest Content" name="display_content">
</form>
<div class="unshow-container">
  <input type="button" value="Unshow" onclick="toggleVisibility()">
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  date_default_timezone_set("Asia/Shanghai");
  $data = $_POST['questiondata'];
  $data = "[" . date("Y-m-d H:i:s") . "]\n" . $data . "\n\n";
  $file = fopen($filename, "r");
  $content = fread($file, filesize($filename));
  fclose($file);
  $file = fopen($filename, "w");
  fwrite($file, $data . $content);
  fclose($file);
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['display_content'])) {
  $file = fopen($filename, "r");
  $content = fread($file, filesize($filename));
  fclose($file);
  echo "<br><br><textarea rows='24' cols='100' readonly>" . $content . "</textarea>";
}
?>
<!--下面的main，footer是有关logout的style-->
<main>
  <p>You have successfully logged in.</p>
  <p><a href="<?php echo $scriptname; ?>?logout=true">Logout</a></p>
</main>
<footer>
  <p>Copyright &copy; 2023 Your Company Name</p>
</footer>
</body>
</html>
