<?php
session_start();

// 確保使用者已登錄
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // 未登錄則重定向至登入頁面
    exit();
}

// 連接資料庫
$host = 'localhost';
$dbname = 'final_test';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 從 session 獲取搜尋條件
$search_criteria = $_SESSION['search_criteria'] ?? [];

// 獲取搜尋條件
$location = $search_criteria['location'] ?? '';
$property_type = $search_criteria['property_type'] ?? '';
$room_type = $search_criteria['room_type'] ?? '';
$number_of_people = $search_criteria['number_of_people'] ?? 0;
$number_of_days_stay = $search_criteria['number_of_days_stay'] ?? 0;

// 1. 從 calendar 表中找到符合 minimum_nights 和 maximum_nights 的 listing_id
$query_calendar = "
    SELECT DISTINCT listing_id
    FROM calendar
    WHERE minimum_nights <= :days_stay AND maximum_nights >= :days_stay
";
$stmt = $pdo->prepare($query_calendar);
$stmt->execute(['days_stay' => $number_of_days_stay]);
$calendar_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. 從 listing_location 表中找到符合 neighborhood_group 的 id
$query_location = "
    SELECT DISTINCT id
    FROM listing_location
    WHERE neighborhood_group = :location
";
$stmt = $pdo->prepare($query_location);
$stmt->execute(['location' => $location]);
$location_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 3. 從 listing_detail 表中找到符合條件的 id
$query_detail = "
    SELECT DISTINCT id, host_id
    FROM listing_detail
    WHERE property_type = :property_type
      AND room_type = :room_type
      AND accommodates >= :people
";
$stmt = $pdo->prepare($query_detail);
$stmt->execute([
    'property_type' => $property_type,
    'room_type' => $room_type,
    'people' => $number_of_people,
]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 獲取 host_id 和 detail_id
$detail_ids = array_column($details, 'id');

// 取三個集合的交集
$s = array_intersect($calendar_ids, $location_ids, $detail_ids);

// 獲取符合條件的 Airbnb 資訊
$query_airbnb = "
    SELECT ld.id, ld.name_ AS name, lr.review_score_rating, c.adjusted_price
    FROM listing_detail ld
    LEFT JOIN listing_review_score lr ON ld.id = lr.id
    LEFT JOIN calendar c ON ld.id = c.listing_id
    WHERE ld.id IN (" . implode(',', array_fill(0, count($s), '?')) . ")
      AND c.date_ = :days_stay
    ORDER BY lr.review_score_rating ASC
";
$stmt = $pdo->prepare($query_airbnb);
$stmt->execute(array_merge($s, ['days_stay' => $number_of_days_stay]));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    </style>
</head>
<body>
    <div class="header">
        <a href="main.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Airbnbs That Meet Your Requirements</h1>
        <table>
            <thead>
                <tr>
                    <th>Airbnb Name</th>
                    <th>Rating</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['name']) ?></td>
                            <td><?= htmlspecialchars($result['review_score_rating']) ?></td>
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
