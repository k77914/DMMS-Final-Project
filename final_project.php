<?php
// Include the header file
$pageTitle = "Final Project";
$pageHeader = "Final Project";
include 'header.php';
?>

<h2>Project Execution</h2>
<p>
    <?php
    // Database connection details
    include 'db.php';
    echo "Connected successfully! OwO<br>";

    // Fetch data
    $sql = "SELECT comments FROM review_detail LIMIT 3";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo "Comment: " . $row["comments"] . "<br>";
        }
    } else {
        echo "0 results";
    }

    // Close the connection
    $conn->close();
    ?>
</p>

<button class="start-button" onclick="location.href='home_page.php';">Back to Home</button>

<?php
// Include the footer file
include 'footer.php';
?>
