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

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 獲取 Airbnb ID
$id = $_GET['id'] ?? null;  //retransmit the id
if (!$id) {
    header("Location: home_page.php");
    exit();
}

// 查詢 Airbnb 資料
// 1. Listing Location
$query_location = "
    SELECT neighbourhood_group, neighbourhood, host_id
    FROM listings_location
    WHERE id = :id
";
$stmt = $pdo->prepare($query_location);
$stmt->execute(['id' => $id]);
$location = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Listing Detail
$query_detail = "
    SELECT property_type, room_type, accommodates, bedrooms, beds, amenities, name_ AS airbnb_name
    FROM listings_detail
    WHERE id = :id
";
$stmt = $pdo->prepare($query_detail);
$stmt->execute(['id' => $id]);
$detail = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Listing Reviews
$query_reviews = "
    SELECT comments
    FROM review_detail as r
    WHERE listing_id = :id
    ORDER BY r.date_ DESC
";
$stmt = $pdo->prepare($query_reviews);
$stmt->execute(['id' => $id]);
$reviews = $stmt->fetchAll(PDO::FETCH_COLUMN);

$query_review_scores = "
    SELECT number_of_reviews, review_scores_rating, review_scores_accuracy, review_scores_cleanliness,
           review_scores_checkin, review_scores_communication, review_scores_location
    FROM listings_review_score
    WHERE id = :id
";
$stmt = $pdo->prepare($query_review_scores);
$stmt->execute(['id' => $id]);
$review_scores = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Listing Host
$host_id = $location['host_id'] ?? null;
$query_host = "
    SELECT host_name, host_about, host_response_rate, host_identity_verified, host_is_superhost
    FROM host_detail
    WHERE host_id = :host_id
";
$stmt = $pdo->prepare($query_host);
$stmt->execute(['host_id' => $host_id]);
$host = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details about <?= htmlspecialchars($detail['airbnb_name'] ?? 'Airbnb') ?></title>
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
        .section {
            margin-top: 20px;
        }
        .section h2 {
            font-size: 18px;
            margin-bottom: 10px;
            cursor: pointer;
            color: #007AFF;
        }
        .section-content {
            display: none;
            padding-left: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            margin-top: 20px;
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
            color: #78B142;
            font-weight: bold;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.section h2').forEach(section => {
                section.addEventListener('click', () => {
                    const content = section.nextElementSibling;
                    content.style.display = content.style.display === 'block' ? 'none' : 'block';
                });
            });
        });
    </script>
</head>
<body>
    <div class="header">
        <a href="home_page.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>
            Details about Airbnb:
            <br>
            <span style="font-size: inherit; color: inherit;">
                <?= htmlspecialchars($detail['airbnb_name'] ?? 'Airbnb') ?>
            </span>
        </h1>
        <?php if (!empty($_SESSION['message'])): ?>
            <p class="success-message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
        <?php endif; ?>
        <div class="section">
            <h2>Listing Location(click for more details)</h2>
            <div class="section-content">
                <p><strong>Neighborhood Group:</strong> <?= htmlspecialchars($location['neighbourhood_group'] ?? 'N/A') ?></p>
                <p><strong>Neighborhood:</strong> <?= htmlspecialchars($location['neighbourhood'] ?? 'N/A') ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Listing Detail (click for more details)</h2>
            <div class="section-content">
                <p><strong>Property Type:</strong> <?= htmlspecialchars($detail['property_type'] ?? 'N/A') ?></p>
                <p><strong>Room Type:</strong> <?= htmlspecialchars($detail['room_type'] ?? 'N/A') ?></p>
                <p><strong>Accommodates:</strong> <?= htmlspecialchars($detail['accommodates'] ?? 'N/A') ?></p>
                <p><strong>Bedrooms:</strong> <?= htmlspecialchars($detail['bedrooms'] ?? 'N/A') ?></p>
                <p><strong>Beds:</strong> <?= htmlspecialchars($detail['beds'] ?? 'N/A') ?></p>
                <p><strong>Amenities:</strong></p>
                <ul>
                    <?php 
                    // 將字串轉為陣列，移除多餘的符號
                    $amenities_raw = $detail['amenities'] ?? '[]';
                    $amenities_clean = preg_replace(['/\[|\]/', '/"/'], '', $amenities_raw); // 移除 "["、"]" 和引號
                    $amenities_list = explode(',', $amenities_clean); // 分割成陣列

                    foreach ($amenities_list as $amenity): ?>
                        <li><?= htmlspecialchars(trim($amenity)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="section">
            <h2>Listing Reviews(click for more details)</h2>
            <div class="section-content">
                <p><strong>Number of Reviews:</strong> <?= htmlspecialchars($review_scores['number_of_reviews'] ?? 'N/A') ?></p>
                <p><strong>Rating:</strong> <?= htmlspecialchars($review_scores['review_scores_rating'] ?? 'N/A') ?></p>
                <p><strong>Accuracy:</strong> <?= htmlspecialchars($review_scores['review_scores_accuracy'] ?? 'N/A') ?></p>
                <p><strong>Cleanliness:</strong> <?= htmlspecialchars($review_scores['review_scores_cleanliness'] ?? 'N/A') ?></p>
                <p><strong>Check-in:</strong> <?= htmlspecialchars($review_scores['review_scores_checkin'] ?? 'N/A') ?></p>
                <p><strong>Communication:</strong> <?= htmlspecialchars($review_scores['review_scores_communication'] ?? 'N/A') ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($review_scores['review_scores_location'] ?? 'N/A') ?></p>
                <h3>Comments:</h3>
                <?php 
                $counter = 1; // 初始化計數器
                foreach ($reviews as $comment): ?>
                    <p><strong>#<?= $counter++ ?>:</strong> <?= htmlspecialchars($comment) ?></p>
                <?php endforeach; ?>
            </div>
        </div>


        <div class="section">
            <h2>Listing Host(click for more details)</h2>
            <div class="section-content">
                <p><strong>Host Name:</strong> <?= htmlspecialchars($host['host_name'] ?? 'N/A') ?></p>
                <p><strong>About Host:</strong> <?= htmlspecialchars($host['host_about'] ?? 'N/A') ?></p>
                <p><strong>Response Rate:</strong> <?= htmlspecialchars($host['host_response_rate'] ?? 'N/A') ?></p>
                <p><strong>Identity Verified:</strong> <?= htmlspecialchars($host['host_identity_verified'] ?? 'N/A') ?></p>
                <p><strong>Superhost:</strong> <?= htmlspecialchars($host['host_is_superhost'] ?? 'N/A') ?></p>
            </div>
        </div>

        <a href="add_comment.php?id=<?= htmlspecialchars($id) ?>" class="btn">Add Review</a>
    </div>
</body>
</html>
