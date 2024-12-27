<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 確保使用者已登錄
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

// 排序參數
$valid_sort_options = [
    'price_asc' => 'c.adjusted_price ASC',
    'price_desc' => 'c.adjusted_price DESC',
    'rating_asc' => 'lr.review_scores_rating ASC',
    'rating_desc' => 'lr.review_scores_rating DESC',
    'value_asc' => '(lr.review_scores_rating / c.adjusted_price) ASC',
    'value_desc' => '(lr.review_scores_rating / c.adjusted_price) DESC',
];

// 默認排序方式
$sort_by = $_GET['sort_by'] ?? 'rating_desc';
$order_clause = $valid_sort_options[$sort_by] ?? $valid_sort_options['rating_desc'];

// 分頁參數
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 30;
$offset = ($page - 1) * $items_per_page;

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
$calendar_ids = [];
while ($row = $result->fetch_assoc()){
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
    WHERE property_type = ? AND room_type = ? AND accommodates >= ?
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

// 獲取符合條件的 Airbnb 資訊
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
    ORDER BY $order_clause
    LIMIT ? OFFSET ?
";

$params = array_merge($s, [$items_per_page, $offset]);
$stmt = $conn->prepare($query_airbnb);
$stmt->bind_param(str_repeat('i', count($s)) . "ii", ...$params);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 計算總數以生成分頁
$total_count = count($s);
$total_pages = ceil($total_count / $items_per_page);

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
    <title>AirBnB Search Results</title>
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
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007AFF;
        }
        .pagination a.active {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="home_page.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>AirBnBs That Meet Your Requirements</h1>
        <form method="GET" action="">
            <label for="sort_by">Sort By:</label>
            <select name="sort_by" id="sort_by">
                <option value="price_asc" <?= $sort_by === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                <option value="price_desc" <?= $sort_by === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                <option value="rating_asc" <?= $sort_by === 'rating_asc' ? 'selected' : '' ?>>Rating (Low to High)</option>
                <option value="rating_desc" <?= $sort_by === 'rating_desc' ? 'selected' : '' ?>>Rating (High to Low)</option>
                <option value="value_asc" <?= $sort_by === 'value_asc' ? 'selected' : '' ?>>Value (Low to High)</option>
                <option value="value_desc" <?= $sort_by === 'value_desc' ? 'selected' : '' ?>>Value (High to Low)</option>
            </select>
            <button type="submit">Apply</button>
        </form>
        <?php if (empty($results)) : ?>
            <p class="error-message">No results found. Please adjust your search criteria.</p>
        <?php else : ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Rating</th>
                        <th>Price</th>
                        <th>Check Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['review_scores_rating'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['adjusted_price'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="details.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn">Check Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <a href="?page=<?php echo $page - 1; ?>&sort_by=<?php echo $sort_by; ?>">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <a href="?page=<?php echo $i; ?>&sort_by=<?php echo $sort_by; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages) : ?>
                    <a href="?page=<?php echo $page + 1; ?>&sort_by=<?php echo $sort_by; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <a href="search_hotel.php" class="btn">New Search</a>
    </div>
</body>
</html>
