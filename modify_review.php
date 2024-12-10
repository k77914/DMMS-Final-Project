<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

// 確認使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 獲取要修改的評論 ID
if (!isset($_GET['review_id'])) {
    header("Location: edit_review.php");
    exit();
}

$review_id = $_GET['review_id'];

// 獲取評論的詳細資料
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM review_detail WHERE id = :id AND reviewer_id = :reviewer_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $review_id, 'reviewer_id' => $user_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        header("Location: edit_review.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comments = $_POST['comments'];
    $current_date = date('Y-m-d H:i:s'); // 獲取當前時間

    try {
        $update_query = "UPDATE review_detail SET comments = :comments, date_ = :date_ WHERE id = :id";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([
            'comments' => $comments,
            'date_' => $current_date,
            'id' => $review_id,
        ]);

        // 設置成功訊息並跳轉回 edit_review.php
        $_SESSION['message'] = "Review updated successfully!";
        header("Location: edit_review.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Failed to update the review: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Review</title>
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
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007AFF;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        button:hover {
            background-color: #005BB5;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="home_page.php">Back to Home</a>
        <a href="edit_review.php">Back to Reviews</a>
    </div>
    <div class="container">
        <h1>Modify Your Review</h1>
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="comments">Modify Your Comment:</label>
            <textarea id="comments" name="comments" rows="5" required><?= htmlspecialchars($review['comments']); ?></textarea>
            <button type="submit">Modify</button>
        </form>
    </div>
</body>
</html>
