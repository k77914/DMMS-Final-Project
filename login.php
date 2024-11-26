<?php
// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize error message
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $password_input = trim($_POST['password']);

    if (empty($name) || empty($password_input)) {
        $error_message = "All fields are required.";
    } else {
        // Retrieve the hashed password from the 'password' field
        $sql = "SELECT * FROM users WHERE name_ = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL error: " . $conn->error);
        }

        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, verify password
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];

            if (password_verify($password_input, $hashed_password)) {
                // Start a session and set session variables if needed
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['name'] = $row['name_'];

                // Redirect to home page
                header("Location: home_page.php");
                exit();
            } else {
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $error_message = "Username does not exist. Please sign up.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <!-- CSS Styles -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            background-color: #F5F5F7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 80px auto;
            padding: 40px;
            background-color: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h1 {
            font-weight: 600;
            color: #1D1D1F;
            text-align: center;
            margin-bottom: 30px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #CED0D4;
            border-radius: 8px;
            font-size: 16px;
            color: #1D1D1F;
            background-color: #F2F2F2;
        }
        input:focus {
            border-color: #007AFF;
            background-color: #FFFFFF;
            outline: none;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #007AFF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background-color: #005BBB;
        }
        .error {
            color: #FF3B30;
            font-size: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .link {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
        }
        .link a {
            color: #007AFF;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sign In with your Account!</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Enter your name" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Sign In</button>
        </form>
        <div class="link">
            Don't have an account? <a href="signup.php">Create yours now</a>
        </div>
    </div>
</body>
</html>
