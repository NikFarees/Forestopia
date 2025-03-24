<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color:#D1F7F3;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      .container {
        max-width: 400px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
      }
      .btn {
        width: 100%;
        padding: 10px;
        background-color:#34b7a7;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
      }
      .btn:hover {
        background-color: #34b7a7;
      }
      .link {
        text-align: center;
        display: block;
        margin-top: 15px;
      }
      .link a {
        color: #34b7a7;
        text-decoration: none;
      }
      .link a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h2>Login</h2>
      <form action="login.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn">Sign In</button>
        <div class="link">
          <a href="/authentication/register.php">Not a member? Sign Up</a>
        </div>
      </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  </body>
</html>


<?php
session_start();

// Include the database connection
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the username and password from the form
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Hash the password using SHA-256
    $password_hash = hash('sha256', $password);

    // Query to find the user in the database
    $query = "SELECT * FROM users WHERE username = '$username' AND password_hash = '$password_hash' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        // User found, login successful
        $_SESSION['username'] = $username; // Store the username in the session

        // Redirect to the home page (index.php)
        header('Location: ../index.php');
        exit;
    } else {
        // Invalid credentials, display an error message
        echo "<script>alert('Invalid username or password');</script>";
    }
}

mysqli_close($conn);
?>
