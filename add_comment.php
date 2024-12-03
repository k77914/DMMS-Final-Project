<?php
session_start();

// 確保使用者已登錄
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 連接資料庫
$host = "localhost";
$dbname = "final_test";
$user = "root";
$password = "";

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

// 查詢 Airbnb 名稱
$query = "
    SELECT name_ AS airbnb_name
    FROM listing_detail
    WHERE id = :id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $id]);
$airbnb = $stmt->fetch(PDO::FETCH_ASSOC);
$airbnb_name = $airbnb['airbnb_name'] ?? 'Airbnb';

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewer_id = $_POST['reviewer_id'] ?? '';
    $reviewer_name = $_POST['reviewer_name'] ?? '';
    $comments = $_POST['comments'] ?? '';

    if ($reviewer_id && $reviewer_name && $comments) {
        $random_id = bin2hex(random_bytes(4)); // 生成亂數 ID
        $date_ = date('Y-m-d H:i:s'); // 當前時間

        $insert_query = "
            INSERT INTO review_detail (id, listing_id, date_, reviewer_id, reviewer_name, comments)
            VALUES (:id, :listing_id, :date_, :reviewer_id, :reviewer_name, :comments)
        ";
        $stmt = $pdo->prepare($insert_query);

        try {
            $stmt->execute([
                'id' => $random_id,
                'listing_id' => $id,
                'date_' => $date_,
                'reviewer_id' => $reviewer_id,
                'reviewer_name' => $reviewer_name,
                'comments' => $comments
            ]);
            // 成功後跳轉至首頁並顯示成功訊息
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
    <title>Enter the comment about <?= htmlspecialchars($airbnb_name) ?></title>
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
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group textarea {
            resize: vertical;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            color: white;
            background-color: #007AFF;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #005BB5;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="main.php">Back to Home</a>
    </div>
    <div class="container">
        <h1>Enter the comment about <?= htmlspecialchars($airbnb_name) ?></h1>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="reviewer_id">Reviewer ID (Password):</label>
                <input type="text" id="reviewer_id" name="reviewer_id" required>
            </div>
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
