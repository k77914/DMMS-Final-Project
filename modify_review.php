<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// 獲取要修改的評論 ID
if (!isset($_GET['id'])) {
    header("Location: edit_review.php");
    exit();
}

$review_id = $_GET['id'];

// 獲取評論的詳細資料
$sql = "SELECT * FROM review_detail WHERE id = ? AND reviewer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $review_id, $user['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: edit_review.php");
    exit();
}

$review = $result->fetch_assoc();

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comments = $_POST['comments'];

    $update_sql = "UPDATE review_detail SET comments = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $comments, $review_id);

    if ($update_stmt->execute()) {
        header("Location: edit_review.php");
        exit();
    } else {
        $error = "Failed to update the review. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modify Review</title>
</head>
<body>
    <a href="edit_review.php">Back to Reviews</a>
    <h1>Modify Your Review</h1>
    <form method="post">
        <label for="comments">Comment:</label><br>
        <textarea id="comments" name="comments" rows="5" cols="50" required><?php echo htmlspecialchars($review['comments']); ?></textarea><br>
        <button type="submit">Update Review</button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
