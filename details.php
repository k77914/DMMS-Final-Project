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
            color: #007AFF;
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
        .hidden {
            display: none;
        }
        ul {
            padding-left: 20px;
            list-style-type: disc;
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
            document.querySelectorAll('.toggle-more').forEach(button => {
                button.addEventListener('click', () => {
                    const moreContent = button.nextElementSibling;
                    moreContent.style.display = moreContent.style.display === 'block' ? 'none' : 'block';
                    button.textContent = moreContent.style.display === 'block' ? 'Show Less' : 'Click for More Details';
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
            Details about AirBnB:
            <br>
            <span style="font-size: inherit; color: inherit;">
                <?= htmlspecialchars($detail['airbnb_name'] ?? 'Airbnb') ?>
            </span>
        </h1>
        <?php if (!empty($_SESSION['message'])): ?>
            <p class="success-message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
        <?php endif; ?>
        <!-- Listing Location -->
        <div class="section">
            <h2>Listing Location:</h2>
            <p><strong>Neighborhood Group:</strong> <?= htmlspecialchars($location['neighbourhood_group'] ?? 'N/A') ?></p>
            <p><strong>Neighborhood:</strong> <?= htmlspecialchars($location['neighbourhood'] ?? 'N/A') ?></p>
        </div>

        <!-- Listing Detail -->
        <div class="section">
            <h2>Listing Detail:</h2>
            <p><strong>Property Type:</strong> <?= htmlspecialchars($detail['property_type'] ?? 'N/A') ?></p>
            <p><strong>Room Type:</strong> <?= htmlspecialchars($detail['room_type'] ?? 'N/A') ?></p>
            <p><strong>Accommodates:</strong> <?= htmlspecialchars($detail['accommodates'] ?? 'N/A') ?></p>
            <p><strong>Bedrooms:</strong> <?= htmlspecialchars($detail['bedrooms'] ?? 'N/A') ?></p>
            <p><strong>Beds:</strong> <?= htmlspecialchars($detail['beds'] ?? 'N/A') ?></p>
            <h3>Amenities:</h3>
            <ul>
                <?php 
                $amenities_raw = $detail['amenities'] ?? '[]';
                $amenities_clean = preg_replace(['/\[|\]/', '/"/'], '', $amenities_raw);
                $amenities_list = array_filter(array_map('trim', explode(',', $amenities_clean)));
                $show_more_amenities = count($amenities_list) > 5;
                foreach (array_slice($amenities_list, 0, 5) as $amenity): ?>
                    <li><?= htmlspecialchars($amenity) ?></li>
                <?php endforeach; ?>
                <?php if ($show_more_amenities): ?>
                    <button class="toggle-more">Click for More Details</button>
                    <div class="hidden">
                        <?php foreach (array_slice($amenities_list, 5) as $amenity): ?>
                            <li><?= htmlspecialchars($amenity) ?></li>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Listing Reviews -->
        <div class="section">
            <h2>Listing Reviews:</h2>
            <p><strong>Number of Reviews:</strong> <?= htmlspecialchars($review_scores['number_of_reviews'] ?? 'N/A') ?></p>
            <p><strong>Rating:</strong> <?= htmlspecialchars($review_scores['review_scores_rating'] ?? 'N/A') ?> / 5</p>
            <p><strong>Accuracy:</strong> <?= htmlspecialchars($review_scores['review_scores_accuracy'] ?? 'N/A') ?> / 5</p>
            <p><strong>Cleanliness:</strong> <?= htmlspecialchars($review_scores['review_scores_cleanliness'] ?? 'N/A') ?> / 5</p>
            <p><strong>Check-in:</strong> <?= htmlspecialchars($review_scores['review_scores_checkin'] ?? 'N/A') ?> / 5</p>
            <p><strong>Communication:</strong> <?= htmlspecialchars($review_scores['review_scores_communication'] ?? 'N/A') ?> / 5</p>
            <p><strong>Location:</strong> <?= htmlspecialchars($review_scores['review_scores_location'] ?? 'N/A') ?> / 5</p>
            <h3>Comments:</h3>
            <?php 
            $show_more_comments = count($reviews) > 5;
            foreach (array_slice($reviews, 0, 5) as $index => $comment): ?>
                <p><strong>#<?= $index + 1 ?>:</strong> <?= htmlspecialchars($comment) ?></p>
            <?php endforeach; ?>
            <?php if ($show_more_comments): ?>
                <button class="toggle-more">Click for More Details</button>
                <div class="hidden">
                    <?php foreach (array_slice($reviews, 5) as $index => $comment): ?>
                        <p><strong>#<?= $index + 6 ?>:</strong> <?= htmlspecialchars($comment) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Listing Host -->
        <div class="section">
            <h2>Listing Host:</h2>
            <p><strong>Host Name:</strong> <?= htmlspecialchars($host['host_name'] ?? 'N/A') ?></p>
            <p><strong>About Host:</strong> <?= htmlspecialchars($host['host_about'] ?? 'N/A') ?></p>
            <p><strong>Response Rate:</strong> <?= htmlspecialchars($host['host_response_rate'] ?? 'N/A') ?> %</p>
            <p>
                <strong>Identity Verified:</strong>
                <?php if (($host['host_identity_verified'] ?? 'f') === 't'): ?>
                    <span style="color: green; font-weight: bold;">True</span>
                <?php elseif (($host['host_identity_verified'] ?? 'f') === 'f'): ?>
                    <span style="color: red; font-weight: bold;">False</span>
                <?php else: ?>
                    <?= htmlspecialchars($host['host_identity_verified'] ?? 'N/A') ?>
                <?php endif; ?>
            </p>
            <p>
                <strong>Superhost:</strong>
                <?php if (($host['host_is_superhost'] ?? 'f') === 't'): ?>
                    <span style="color: green; font-weight: bold;">True</span>
                <?php elseif (($host['host_is_superhost'] ?? 'f') === 'f'): ?>
                    <span style="color: red; font-weight: bold;">False</span>
                <?php else: ?>
                    <?= htmlspecialchars($host['host_is_superhost'] ?? 'N/A') ?>
                <?php endif; ?>
            </p>
        </div>
        <a href="add_comment.php?id=<?= htmlspecialchars($id) ?>" class="btn">Add Review</a>
        <a href="hotel.php" class="btn">Back to search results</a>
    </div>
</body>
</html>
