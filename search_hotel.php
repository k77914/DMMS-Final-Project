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
            color: white;   
            padding: 15px;
            font-size: 15px;
            text-align: right;
        }
        .header a {
            color: white;
            font-weight: bold;
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
        select, input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #CED0D4;
            border-radius: 8px;
            font-size: 16px;
            color: #1D1D1F;
            background-color: #F2F2F2;
        }
        select:focus, input:focus {
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
        <a href="home_page.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Find the Airbnb that meets your requirements in New York City</h1>
        <h2>Please fill in the following fields of your requirements</h2>
        <form method="POST" action="">
            <select name="location" required>
                <option value="" disabled selected>Choose a location</option>
                <option value="Brooklyn">Brooklyn</option>
                <option value="Queens">Queens</option>
                <option value="Manhattan">Manhattan</option>
                <option value="Bronx">Bronx</option>
                <option value="Staten Island">Staten Island</option>
            </select>
            <select name="num_people" required>
                <option value="" disabled selected>Choose the number of people</option>
                <?php
                for ($i = 1; $i <= 100; $i++) {
                    echo "<option value=\"$i\">$i</option>";
                }
                ?>
            </select>
            <select name="num_days" required>
                <option value="" disabled selected>Choose the number of days</option>
                <?php
                for ($i = 1; $i <= 365; $i++) {
                    echo "<option value=\"$i\">$i</option>";
                }
                ?>
            </select>
            <select name="room_type" required>
                <option value="" disabled selected>Choose a room type</option>
                <option value="Private room">Private room</option>
                <option value="Entire home/apt">Entire home/apt</option>
                <option value="Shared room">Shared room</option>
                <option value="Hotel room">Hotel room</option>
            </select>
            <select name="property_type" required>
                <option value="" disabled selected>Choose a property type</option>
                <option value="Private room in rental unit">Private room in rental unit</option>
                <option value="Entire rental unit">Entire rental unit</option>
                <option value="Entire place">Entire place</option>
                <option value="Shared room in rental unit">Shared room in rental unit</option>
                <option value="Entire condo">Entire condo</option>
                <option value="Entire loft">Entire loft</option>
                <option value="Private room in loft">Private room in loft</option>
                <option value="Entire townhouse">Entire townhouse</option>
                <option value="Entire home">Entire home</option>
                <option value="Private room in townhouse">Private room in townhouse</option>
                <option value="Room in hotel">Room in hotel</option>
                <option value="Private room in home">Private room in home</option>
                <option value="Private room in hostel">Private room in hostel</option>
                <option value="Private room in bed and breakfast">Private room in bed and breakfast</option>
                <option value="Entire guest suite">Entire guest suite</option>
                <option value="Shared room in townhouse">Shared room in townhouse</option>
                <option value="Room in boutique hotel">Room in boutique hotel</option>
                <option value="Entire serviced apartment">Entire serviced apartment</option>
                <option value="Private room in condo">Private room in condo</option>
                <option value="Private room in guesthouse">Private room in guesthouse</option>
                <option value="Private room in houseboat">Private room in houseboat</option>
                <option value="Entire guesthouse">Entire guesthouse</option>
                <option value="Private room">Private room</option>
                <option value="Private room in guest suite">Private room in guest suite</option>
                <option value="Floor">Floor</option>
                <option value="Boat">Boat</option>
                <option value="Houseboat">Houseboat</option>
                <option value="Private room in bungalow">Private room in bungalow</option>
                <option value="Shared room in condo">Shared room in condo</option>
                <option value="Shared room in home">Shared room in home</option>
                <option value="Entire bungalow">Entire bungalow</option>
                <option value="Entire villa">Entire villa</option>
                <option value="Room in serviced apartment">Room in serviced apartment</option>
                <option value="Private room in resort">Private room in resort</option>
                <option value="Tiny home">Tiny home</option>
                <option value="Private room in villa">Private room in villa</option>
                <option value="Room in aparthotel">Room in aparthotel</option>
                <option value="Shared room in floor">Shared room in floor</option>
                <option value="Private room in floor">Private room in floor</option>
                <option value="Entire bed and breakfast">Entire bed and breakfast</option>
                <option value="Shared room in loft">Shared room in loft</option>
                <option value="Entire home/apt">Entire home/apt</option>
                <option value="Room in resort">Room in resort</option>
                <option value="Private room in casa particular">Private room in casa particular</option>
                <option value="Private room in tent">Private room in tent</option>
                <option value="Private room in in-law">Private room in in-law</option>
                <option value="Entire cottage">Entire cottage</option>
                <option value="Private room in serviced apartment">Private room in serviced apartment</option>
                <option value="Shared room in guesthouse">Shared room in guesthouse</option>
                <option value="Shared room in bed and breakfast">Shared room in bed and breakfast</option>
                <option value="Private room in farm stay">Private room in farm stay</option>
                <option value="Private room in dorm">Private room in dorm</option>
                <option value="Room in bed and breakfast">Room in bed and breakfast</option>
                <option value="Private room in tiny home">Private room in tiny home</option>
                <option value="Private room in vacation home">Private room in vacation home</option>
                <option value="Shared room in bungalow">Shared room in bungalow</option>
                <option value="Shared room in serviced apartment">Shared room in serviced apartment</option>
                <option value="Private room in earthen home">Private room in earthen home</option>
                <option value="Private room in religious building">Private room in religious building</option>
                <option value="Private room in barn">Private room in barn</option>
                <option value="Private room in cottage">Private room in cottage</option>
                <option value="Lighthouse">Lighthouse</option>
                <option value="Private room in train">Private room in train</option>
                <option value="Barn">Barn</option>
                <option value="Private room in lighthouse">Private room in lighthouse</option>
                <option value="Casa particular">Casa particular</option>
                <option value="Camper/RV">Camper/RV</option>
                <option value="Private room in camper/rv">Private room in camper/rv</option>
                <option value="Private room in kezhan">Private room in kezhan</option>
                <option value="Castle">Castle</option>
                <option value="Tent">Tent</option>
                <option value="Entire vacation home">Entire vacation home</option>
                <option value="Shared room in vacation home">Shared room in vacation home</option>
                <option value="Cave">Cave</option>
                <option value="Shared room">Shared room</option>
                <option value="Private room in tower">Private room in tower</option>
                <option value="Shared room in casa particular">Shared room in casa particular</option>
                <option value="Shared room in shepherd's hut">Shared room in shepherd's hut</option>
                <option value="Private room in cave">Private room in cave</option>
                <option value="Tower">Tower</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>
</body>
</html>

