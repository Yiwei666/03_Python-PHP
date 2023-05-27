<?php
ob_start(); // start output buffering
session_start();

// If the user is already logged in, redirect to the protected page
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  header('Location: lsfile.php');
  exit;
}

// Check if the user submitted the login form
if (isset($_POST['username']) && isset($_POST['password'])) {
  // Verify the username and password (replace with your own verification code)
  if ($_POST['username'] === 'example' && $_POST['password'] === 'password123') {
    // Authentication successful, set session variables
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $_POST['username'];

    // Redirect to the protected page
    header('Location: lsfile.php');
    exit;
  } else {
    // Authentication failed, display error message
    $error = 'Incorrect username or password';
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <style>
    body {
      background-color: #f2f2f2;
    }

    #login-form {
      max-width: 400px;
      margin: 0 auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      border-radius: 3px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
    }

    button {
      background-color: #4CAF50;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
    }

    button:hover {
      background-color: #45a049;
    }

    .error-message {
      color: #f00;
      font-weight: bold;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div id="login-form">
    <h1>Login</h1>
    <?php if (isset($error)) { ?>
      <p class="error-message"><?php echo $error; ?></p>
    <?php } ?>
    <form method="post" action="login.php">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username">

      <label for="password">Password:</label>
      <input type="password" id="password" name="password">

      <button type="submit">Log in</button>
    </form>
  </div>
</body>
</html>
<?php
ob_end_flush(); // flush output buffer
?>
