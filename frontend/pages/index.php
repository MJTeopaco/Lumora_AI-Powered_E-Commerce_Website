<?php
session_start();
// Adjust paths to be relative FROM 'frontend/pages/index.php'
require_once '../../backend/php/db_connect.php'; 
require_once '../../backend/php/auth.php';     

// If user is not logged in, check for remember-me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    validateRememberMeToken();
}

// *** THIS IS THE CRUCIAL PART ***
// If, after checking session AND cookie, the user is STILL not logged in,
// send them back to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html'); // Since we are in the same /pages/ folder
    exit();
}

// If the script reaches this point, the user IS logged in.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumora - Welcome!</title>
  <style>
    body {
      background-color: #1e4d3d;
      margin: 0;
      height: 100vh;
      color: white;
      font-family: sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }
    h1 {
      font-weight: 300;
    }
    a {
      color: #a7f3d0;
      font-size: 1.2rem;
      text-decoration: none;
      border: 1px solid #a7f3d0;
      padding: 10px 20px;
      border-radius: 8px;
      transition: all 0.3s;
    }
    a:hover {
      background: #a7f3d0;
      color: #1e4d3d;
    }
  </style>
</head>
<body>
    <h1>Welcome to Lumora, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    
    <p><a href="../../backend/php/logout.php">Logout</a></p>
</body>
</html>