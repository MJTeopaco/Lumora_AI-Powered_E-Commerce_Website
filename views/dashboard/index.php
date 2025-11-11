<?php
// views/dashboard/index.php
// This replaces frontend/pages/index.php
// $username is passed from DashboardController@index
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
    <h1>Welcome to Lumora, <?php echo htmlspecialchars($username); ?>!</h1>
    
    <!-- Logout link now points to the /logout route -->
    <p><a href="/logout">Logout</a></p>
</body>
</html>