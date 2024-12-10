<?php
// Enable error reporting for debugging (disable in production) 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to maintain user login state
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $location = trim($_POST['location']);
    $num_people = trim($_POST['num_people']);
    $num_days = trim($_POST['num_days']);
    $room_type = trim($_POST['room_type']);
    $property_type = trim($_POST['property_type']);

    // Save input to session to pass data to the next page
    $_SESSION['search_criteria'] = [
        'location' => $location,
        'num_people' => $num_people,
        'num_days' => $num_days,
        'room_type' => $room_type,
        'property_type' => $property_type,
    ];

    // Redirect to hotel page
    header("Location: hotel.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Airbnb</title>
    <!-- CSS Styles -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            background-color: #F5F5F7;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #007AFF;
            padding: 15px;
            text-align: right;
        }
        .header a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            color: #1D1D1F;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
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
    </style>
</head>
<body>
    <div class="header">
        <a href="main.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Find the Airbnb that meets your requirements in New York City</h1>
        <h2>Please fill in the following fields of your requirements</h2>
        <form method="POST" action="">
            <input type="text" name="location" placeholder="Location (e.g., Brooklyn)" required>
            <input type="number" name="num_people" placeholder="Number of People" min="1" required>
            <input type="number" name="num_days" placeholder="Number of Days Stay" min="1" required>
            <input type="text" name="room_type" placeholder="Expected Room Type (e.g., Entire home/apt)" required>
            <input type="text" name="property_type" placeholder="Expected Property Type (e.g., Apartment)" required>
            <button type="submit">Search</button>
        </form>
    </div>
</body>
</html>
