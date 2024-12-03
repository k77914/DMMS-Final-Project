<?php
session_start();

// 確保使用者已登錄
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 連接資料庫
include 'db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 獲取 Airbnb ID
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: main.php");
    exit();
}

// 查詢 Airbnb 資料
// 1. Listing Location
$query_location = "
    SELECT neighborhood_group, neighborhood, host_id
    FROM listing_location
    WHERE id = :id
";
$stmt = $pdo->prepare($query_location);
$stmt->execute(['id' => $id]);
$location = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Listing Detail
$query_detail = "
    SELECT property_type, room_type, accommodates, bedrooms, beds, amenities, name_ AS airbnb_name
    FROM listing_detail
    WHERE id = :id
";
$stmt = $pdo->prepare($query_detail);
$stmt->execute(['id' => $id]);
$detail = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Listing Reviews
$query_reviews = "
    SELECT comments
    FROM review_detail
    WHERE listing_id = :id
";
$stmt = $pdo->prepare($query_reviews);
$stmt->execute(['id' => $id]);
$reviews = $stmt->fetchAll(PDO::FETCH_COLUMN);

$query_review_scores = "
    SELECT number_of_reviews, review_score_rating, review_score_accuracy, review_score_cleanliness,
           review_score_checkin, review_score_communication, review_score_location
    FROM listing_review_score
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
        <a href="main.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Details about <?= htmlspecialchars($detail['airbnb_name'] ?? 'Airbnb') ?></h1>

        <div class="section">
            <h2>Listing Location</h2>
            <div class="section-content">
                <p><strong>Neighborhood Group:</strong> <?= htmlspecialchars($location['neighborhood_group'] ?? 'N/A') ?></p>
                <p><strong>Neighborhood:</strong> <?= htmlspecialchars($location['neighborhood'] ?? 'N/A') ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Listing Detail</h2>
            <div class="section-content">
                <p><strong>Property Type:</strong> <?= htmlspecialchars($detail['property_type'] ?? 'N/A') ?></p>
                <p><strong>Room Type:</strong> <?= htmlspecialchars($detail['room_type'] ?? 'N/A') ?></p>
                <p><strong>Accommodates:</strong> <?= htmlspecialchars($detail['accommodates'] ?? 'N/A') ?></p>
                <p><strong>Bedrooms:</strong> <?= htmlspecialchars($detail['bedrooms'] ?? 'N/A') ?></p>
                <p><strong>Beds:</strong> <?= htmlspecialchars($detail['beds'] ?? 'N/A') ?></p>
                <p><strong>Amenities:</strong> <?= htmlspecialchars($detail['amenities'] ?? 'N/A') ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Listing Reviews</h2>
            <div class="section-content">
                <p><strong>Number of Reviews:</strong> <?= htmlspecialchars($review_scores['number_of_reviews'] ?? 'N/A') ?></p>
                <p><strong>Rating:</strong> <?= htmlspecialchars($review_scores['review_score_rating'] ?? 'N/A') ?></p>
                <p><strong>Accuracy:</strong> <?= htmlspecialchars($review_scores['review_score_accuracy'] ?? 'N/A') ?></p>
                <p><strong>Cleanliness:</strong> <?= htmlspecialchars($review_scores['review_score_cleanliness'] ?? 'N/A') ?></p>
                <p><strong>Check-in:</strong> <?= htmlspecialchars($review_scores['review_score_checkin'] ?? 'N/A') ?></p>
                <p><strong>Communication:</strong> <?= htmlspecialchars($review_scores['review_score_communication'] ?? 'N/A') ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($review_scores['review_score_location'] ?? 'N/A') ?></p>
                <h3>Comments:</h3>
                <?php foreach ($reviews as $comment): ?>
                    <p><?= htmlspecialchars($comment) ?></p>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <h2>Listing Host</h2>
            <div class="section-content">
                <p><strong>Host Name:</strong> <?= htmlspecialchars($host['host_name'] ?? 'N/A') ?></p>
                <p><strong>About Host:</strong> <?= htmlspecialchars($host['host_about'] ?? 'N/A') ?></p>
                <p><strong>Response Rate:</strong> <?= htmlspecialchars($host['host_response_rate'] ?? 'N/A') ?></p>
                <p><strong>Identity Verified:</strong> <?= htmlspecialchars($host['host_identity_verified'] ?? 'N/A') ?></p>
                <p><strong>Superhost:</strong> <?= htmlspecialchars($host['host_is_superhost'] ?? 'N/A') ?></p>
            </div>
        </div>

        <a href="add_review.php?id=<?= htmlspecialchars($id) ?>" class="btn">Add Review</a>
    </div>
</body>
</html>
