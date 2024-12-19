<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the logged-in user's information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['name'];

// Fetch user reviews
$query = "
    SELECT rd.id AS review_id, rd.date_, rd.comments, ld.name_ AS airbnb_name, rd.listing_id 
    FROM review_detail rd
    JOIN listings_detail ld ON rd.listing_id = ld.id
    WHERE rd.reviewer_id = :reviewer_id AND rd.reviewer_name = :reviewer_name
    ORDER BY rd.date_ DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'reviewer_id' => $user_id,
    'reviewer_name' => $username,
]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    $listing_id = $_POST['listing_id'];

    try {
        // Delete the review
        $delete_query = "DELETE FROM review_detail WHERE id = :review_id";
        $stmt = $pdo->prepare($delete_query);
        $stmt->execute(['review_id' => $review_id]);

        // Decrement the number_of_reviews in listings_review_score
        $update_query = "
            UPDATE listings_review_score 
            SET number_of_reviews = number_of_reviews - 1
            WHERE id = :listing_id
        ";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute(['listing_id' => $listing_id]);

        $_SESSION['message'] = "Review deleted successfully!";
        header("Location: edit_review.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Failed to delete review: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Reviews</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #007AFF;
            color: white;
            padding: 15px 20px;
            text-align: right;
            font-size: 15px;
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
            max-width: 1000px;
            background: white;
            padding: 30px 30px;
            border-radius: 10px;
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
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #007AFF;
            color: white;
        }
        .btn {
            font-size: 15px;
            display: inline-block;
            padding: 10px 10px;
            background-color: #007AFF;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .btn:hover {
            background-color: #005BB5;
        }
        .error-message, .success-message {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: #78B142;
            font-weight: bold;
            
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="home_page.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Manage Your Reviews</h1>

        <?php if (!empty($_SESSION['message'])): ?>
            <p class="success-message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if ($reviews): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Airbnb Name</th>
                        <th>Comment</th>
                        <th>Delete</th>
                        <th>Modify</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?= htmlspecialchars($review['date_']); ?></td>
                            <td><?= htmlspecialchars($review['airbnb_name']); ?></td>
                            <td><?= htmlspecialchars($review['comments']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['review_id']); ?>">
                                    <input type="hidden" name="listing_id" value="<?= htmlspecialchars($review['listing_id']); ?>">
                                    <button type="submit" name="delete_review" class="btn">Delete</button>
                                </form>
                            </td>
                            <td>
                                <a href="modify_review.php?review_id=<?= urlencode($review['review_id']); ?>" class="btn">Modify</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No previous reviews found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

