<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

$resource_id = isset($_GET["resource_id"]) ? (int)$_GET["resource_id"] : 0;

if ($resource_id <= 0) {
    die("Invalid resource ID.");
}

/* Get donation resource */
$sql = "SELECT id, user_id, title, category, description, condition_item, exchange_type, status
        FROM resources
        WHERE id = ?
        AND status = 'Available'";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $resource_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Donation resource not found or is no longer available.");
}

$resource = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* Prevent owner from requesting their own donation */
if ((int)$resource["user_id"] === $user_id) {
    die("You cannot request your own donated resource.");
}

/* Make sure this is actually a donation */
if ($resource["exchange_type"] !== "Donate") {
    die("This resource is not available for donation.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* Check for existing pending request */
    $sql = "SELECT id
            FROM requests
            WHERE user_id = ?
            AND resource_id = ?
            AND status = 'Pending'";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Database query failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ii", $user_id, $resource_id);
    mysqli_stmt_execute($stmt);

    $check_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_result) > 0) {

        $message = "You have already requested this donated resource.";

    } else {

        mysqli_stmt_close($stmt);

        $sql = "INSERT INTO requests
                (user_id, resource_id, title, description, category, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'Pending', NOW())";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            die("Insert query failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "iisss",
            $user_id,
            $resource_id,
            $resource["title"],
            $resource["description"],
            $resource["category"]
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Donation request sent successfully!";

        } else {

            $message = "Error: " . mysqli_error($conn);
        }
    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Request Donation - CampusSwap</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4fbf6;
    color: #294735;
}

.navbar {
    background: white;
    padding: 18px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.06);
}

.logo {
    font-size: 24px;
    font-weight: 800;
    color: #328b55;
}

.navbar a {
    text-decoration: none;
    color: #328b55;
    font-weight: 600;
}

.container {
    width: 90%;
    max-width: 700px;
    margin: 50px auto;
}

.card {
    background: white;
    padding: 35px;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(40,80,50,0.08);
    border: 1px solid #e3eee6;
}

h1 {
    color: #294735;
    margin-bottom: 10px;
}

.subtitle {
    color: #748078;
    margin-bottom: 25px;
}

.resource-box {
    background: #f3faf5;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.resource-box p {
    margin: 8px 0;
}

.message {
    background: #e5f6e9;
    color: #287345;
    padding: 14px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-weight: 600;
}

button {
    width: 100%;
    border: none;
    background: #328b55;
    color: white;
    padding: 14px;
    border-radius: 9px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
}

button:hover {
    background: #267343;
}

.back {
    display: inline-block;
    margin-top: 18px;
    color: #328b55;
    text-decoration: none;
    font-weight: 600;
}

</style>

</head>

<body>

<nav class="navbar">

    <div class="logo">
        🔄 CampusSwap
    </div>

    <a href="dashboard.php">
        Dashboard
    </a>

</nav>

<div class="container">

    <div class="card">

        <h1>Request Donated Resource 🎁</h1>

        <p class="subtitle">
            Send a request to receive this resource from another student.
        </p>

        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <div class="resource-box">

            <p>
                <strong>Resource:</strong>
                <?php echo htmlspecialchars($resource["title"]); ?>
            </p>

            <p>
                <strong>Category:</strong>
                <?php echo htmlspecialchars($resource["category"]); ?>
            </p>

            <p>
                <strong>Condition:</strong>
                <?php echo htmlspecialchars($resource["condition_item"]); ?>
            </p>

            <p>
                <strong>Description:</strong>
                <?php echo htmlspecialchars($resource["description"]); ?>
            </p>

        </div>

        <?php if ($message == "") { ?>

            <form method="POST">

                <button type="submit">
                    Send Donation Request
                </button>

            </form>

        <?php } ?>

        <a href="browse_resources.php" class="back">
            ← Back to Browse Resources
        </a>

    </div>

</div>

</body>

</html>