<?php
// Include database connection
include('../db_connect.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the password
        $password_hash = hash('sha256', $password);

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password_hash);

        if ($stmt->execute()) {
            header("Location: /authentication/login.php?success=1");
            exit;
        } else {
            $error_message = "Error: Could not register user.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
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
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
      }
      .btn:hover {
        background-color: #0056b3;
      }
      .link {
        text-align: center;
        display: block;
        margin-top: 15px;
      }
      .link a {
        color: #007bff;
        text-decoration: none;
      }
      .link a:hover {
        text-decoration: underline;
      }
      .error {
        color: red;
        font-size: 0.9em;
        margin-bottom: 10px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h2>Register</h2>
      <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p>
      <?php endif; ?>
      <form action="register.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
        </div>
        <button type="submit" class="btn">Register</button>
        <div class="link">
          <a href="/authentication/login.php">Already have an account? Sign In</a>
        </div>
      </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  </body>
</html>
