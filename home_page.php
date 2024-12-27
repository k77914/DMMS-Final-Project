<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['name'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get the logged-in username from the session
$username = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            background-color: #F5F5F7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .header, .main {
            text-align: center;
            margin-bottom: 20px;
        }
        h1, h2, h3 {
            color: #1D1D1F;
        }
        .function-cards {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .card {
            flex: 1;
            min-width: 250px;
            padding: 20px;
            background-color: #F2F2F2;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .card h3 {
            margin-bottom: 15px;
        }
        .button {
            padding: 12px 20px;
            background-color: #007AFF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px 0;
        }
        .button:hover {
            background-color: #005BBB;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logout-button {
            padding: 10px 15px;
            background-color: #FF3B30;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-button:hover {
            background-color: #C92A24;
        }
        .welcome-message {
            font-size: 16px;
            font-weight: 600;
            color: #1D1D1F;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header-container">
            <button class="logout-button" onclick="location.href='logout.php';">Logout</button>
            <div class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?></div>
        </div>
        <header class="header">
            <h1>Your Best Guide to Find AirBnBs in New York City!</h1>
        </header>
        <main class="main">
            <h2>Welcome!</h2>
            <p>What would you like to do today?</p>
            <?php if (!empty($_SESSION['message'])): ?>
                <p class="success-message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <div class="function-cards">
                <!-- Card for Accommodation -->
                <div class="card">
                    <h3>Find accommodation in NYC!</h3>
                    <button class="button" onclick="location.href='search_hotel.php';">Start 🔍</button>
                </div>
                <!-- Card for Manage Comments -->
                <div class="card">
                    <h3>Manage Comments:</h3>
                    <button class="button" onclick="location.href='edit_review.php';">Start 🔍</button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
