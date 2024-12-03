<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Check if the user has left any comments
$query = "SELECT COUNT(*) FROM review_detail WHERE reviewer_id = :reviewer_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['reviewer_id' => $user_id]);
$comment_count = $stmt->fetchColumn();

if ($comment_count == 0) {
    // If no comments exist, redirect to no_comments.php
    header("Location: no_comments.php");
    exit();
}

// Process form submission for adding new comments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewer_name = $_POST['reviewer_name'] ?? '';
    $comments_text = $_POST['comments'] ?? '';

    if (!empty($reviewer_name) && !empty($comments_text)) {
        $random_id = bin2hex(random_bytes(4)); // Unique ID for the new review
        $date_ = date('Y-m-d H:i:s');

        $insert_query = "
            INSERT INTO review_detail (id, listing_id, date_, reviewer_id, reviewer_name, comments)
            VALUES (:id, :listing_id, :date_, :reviewer_id, :reviewer_name, :comments)
        ";
        $stmt = $pdo->prepare($insert_query);

        try {
            $stmt->execute([
                'id' => $random_id,
                'listing_id' => $user_id, // Assuming the user's ID corresponds to the listing ID
                'date_' => $date_,
                'reviewer_id' => $user_id,
                'reviewer_name' => $reviewer_name,
                'comments' => $comments_text,
            ]);
            $_SESSION['message'] = 'Review added successfully!';
            header("Location: main.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Failed to add review: " . $e->getMessage();
        }
    } else {
        $error_message = "All fields are required!";
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
        }
        .header a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            margin-left: 20px;
        }
        .container {
            margin: 40px auto;
            width: 90%;
            max-width: 600px;
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group textarea {
            resize: vertical;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
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
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="main.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Manage Your Reviews</h1>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="reviewer_name">Reviewer Name:</label>
                <input type="text" id="reviewer_name" name="reviewer_name" required>
            </div>
            <div class="form-group">
                <label for="comments">Comment:</label>
                <textarea id="comments" name="comments" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn">Add Review</button>
        </form>
    </div>
</body>
</html>
