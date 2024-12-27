<?php
// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection credentials
include 'db.php';
session_start();

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
        // Check if username already exists
        $sql = "SELECT * FROM users WHERE name_ = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL error: " . $conn->error);
        }

        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose another.";
        } else {
            // Hash the password
            $hashed_password = hash('sha256',$password_input);

            // Generate a unique ID for the user
            $user_id = uniqid();

            $insert_sql = "INSERT INTO users (id, name_, password) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);

            if (!$insert_stmt) {
                die("Insert SQL error: " . $conn->error);
            }

            $insert_stmt->bind_param("sss", $user_id, $name, $hashed_password);

            if ($insert_stmt->execute()) {
                // Account created, redirect to login page
                $_SESSION['message'] = 'Account created successfully!';
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Failed to create account. Please try again.";
            }
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
    <title>Sign Up</title>
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
        <h1>Create Your Account Now!</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Enter your name" required>
            <input type="password" name="password" placeholder="Create a password" required>
            <button type="submit">Sign Up</button>
        </form>
        <div class="link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</body>
</html>
