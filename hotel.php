<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 確保使用者已登錄
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // 未登錄則重定向至登入頁面
    exit();
}

// 連接資料庫
include 'db.php';

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 從 session 獲取搜尋條件
$search_criteria = $_SESSION['search_criteria'] ?? [];

// 獲取搜尋條件
$location = $search_criteria['location'] ?? '';
$number_of_people = $search_criteria['num_people'] ?? 0;
$number_of_days_stay = $search_criteria['num_days'] ?? 0;
$room_type = $search_criteria['room_type'] ?? '';
$property_type = $search_criteria['property_type'] ?? '';

// 1. 從 calendar 表中找到符合 minimum_nights 和 maximum_nights 的 listing_id
$query_calendar = "
    SELECT DISTINCT listing_id
    FROM calendar
    WHERE minimum_nights <= ? AND maximum_nights >= ?
";
$stmt = $conn->prepare($query_calendar);
$stmt->bind_param("ii", $number_of_days_stay, $number_of_days_stay);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $calendar_ids[] = $row['listing_id'];
}

// 2. 從 listing_location 表中找到符合 neighborhood_group 的 id
$query_location = "
    SELECT DISTINCT id
    FROM listings_location
    WHERE neighbourhood_group = ?
";
$stmt = $conn->prepare($query_location);
$stmt->bind_param("s", $location);
$stmt->execute();
$result = $stmt->get_result();
$location_ids = [];
while ($row = $result->fetch_assoc()) {
    $location_ids[] = $row['id'];
}

// 3. 從 listing_detail 表中找到符合條件的 id
$query_detail = "
    SELECT DISTINCT id
    FROM listings_detail
    WHERE property_type = ?
      AND room_type = ?
      AND accommodates >= ?
";
$stmt = $conn->prepare($query_detail);
$stmt->bind_param("ssi", $property_type, $room_type, $number_of_people);
$stmt->execute();
$result = $stmt->get_result();
$detail_ids = [];
while ($row = $result->fetch_assoc()) {
    $detail_ids[] = $row['id'];
}

// 取三個集合的交集
$s = array_intersect($calendar_ids, $location_ids, $detail_ids);
if (empty($s)) {
    header("Location: no_meets.php");
    exit();
}

// 獲取符合條件的 Airbnb 資訊 /
$in_placeholders = implode(',', array_fill(0, count($s), '?'));
$query_airbnb = "
    SELECT 
        ld.id, 
        ld.name_ AS name, 
        lr.review_scores_rating, 
        c.adjusted_price
    FROM listings_detail ld
    LEFT JOIN listings_review_score lr ON ld.id = lr.id
    LEFT JOIN (
        SELECT 
            listing_id, 
            adjusted_price
        FROM calendar
        WHERE (listing_id, date_) IN (
            SELECT 
                listing_id, 
                MAX(date_) AS latest_date
            FROM calendar
            GROUP BY listing_id
        )
    ) c ON ld.id = c.listing_id
    WHERE ld.id IN ($in_placeholders)
    ORDER BY lr.review_scores_rating DESC
    LIMIT 50
";

$stmt = $conn->prepare($query_airbnb);
$stmt->bind_param(str_repeat('i', count($s)), ...$s);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($search_criteria)) {
    header("Location: search_hotel.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Search Results</title>
    <!-- CSS Style -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #007AFF;
            color: white;
            padding: 15px;
            text-align: right;
        }
        .header a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            margin-left: 20px;
        }
        .container {
            margin: 20px auto;
            width: 90%;
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table th {
            background-color: #007AFF;
            color: white;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            margin-top: 10px;
            color: white;
            background-color: #007AFF;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #005BB5;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="home_page.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Airbnbs That Meet Your Requirements</h1>
        <?php if (!empty($_SESSION['message'])): ?>
            <p class="success-message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Airbnb Name</th>
                    <th>Rating</th>
                    <th>Price</th>
                    <th>Check Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['name']) ?></td>
                            <td><?= htmlspecialchars($result['review_scores_rating']) ?></td>
                            <td><?= htmlspecialchars($result['adjusted_price']) ?></td>
                            <td><a href="details.php?id=<?= $result['id'] ?>" class="btn">Check Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No results found for your criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
