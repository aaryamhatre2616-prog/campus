<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

$resource_id = isset($_GET["resource_id"])
    ? (int)$_GET["resource_id"]
    : 0;

if ($resource_id <= 0) {
    die("Invalid resource.");
}


/* Get resource details */

$sql = "SELECT id, user_id, title, category, description,
               exchange_type, condition_item, status
        FROM resources
        WHERE id = ? AND status = 'Available'";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $resource_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Resource is not available.");
}

$resource = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* Prevent owner from borrowing own resource */

if ($resource["user_id"] == $user_id) {
    die("You cannot borrow your own resource.");
}


/* Make sure this is a Borrow resource */

if ($resource["exchange_type"] != "Borrow") {
    die("This resource is not available for borrowing.");
}


/* Check whether user already has an active borrow */

$sql = "SELECT id
        FROM borrow_records
        WHERE resource_id = ?
        AND borrower_id = ?
        AND status = 'Borrowed'";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $resource_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    mysqli_stmt_close($stmt);
    die("You have already borrowed this resource.");
}

mysqli_stmt_close($stmt);


/* Create borrow record */

$borrow_date = date("Y-m-d");

$sql = "INSERT INTO borrow_records
        (resource_id, borrower_id, borrow_date, status, created_at)
        VALUES (?, ?, ?, 'Borrowed', NOW())";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "iis",
    $resource_id,
    $user_id,
    $borrow_date
);

if (mysqli_stmt_execute($stmt)) {

    /* Update resource status */

    $update_sql = "UPDATE resources
                   SET status = 'Requested'
                   WHERE id = ?";

    $update_stmt = mysqli_prepare($conn, $update_sql);

    mysqli_stmt_bind_param(
        $update_stmt,
        "i",
        $resource_id
    );

    mysqli_stmt_execute($update_stmt);

    mysqli_stmt_close($update_stmt);

    mysqli_stmt_close($stmt);

} else {

    die("Borrow request failed: " . mysqli_error($conn));

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Borrow Resource - CampusSwap</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Arial, sans-serif;
}

body {
    background: #f5fbf7;
    color: #294735;
}

.navbar {
    background: white;
    padding: 15px 7%;
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

.back {
    text-decoration: none;
    color: #328b55;
    font-weight: 600;
}

.container {
    width: 90%;
    max-width: 650px;
    margin: 70px auto;
}

.card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    border: 1px solid #e3eee6;
    box-shadow: 0 10px 30px rgba(40,80,50,0.08);
}

.success-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    background: #e5f6e9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
}

h1 {
    color: #294735;
    margin-bottom: 12px;
}

.message {
    color: #5e6d64;
    line-height: 1.6;
    margin-bottom: 25px;
}

.resource {
    background: #f5fbf7;
    border: 1px solid #dfece2;
    border-radius: 12px;
    padding: 18px;
    text-align: left;
    margin-bottom: 25px;
}

.resource-title {
    font-size: 18px;
    font-weight: 700;
    color: #294735;
    margin-bottom: 8px;
}

.resource-info {
    color: #718078;
    font-size: 13px;
    margin-bottom: 5px;
}

.btn {
    display: inline-block;
    text-decoration: none;
    background: #328b55;
    color: white;
    padding: 12px 25px;
    border-radius: 9px;
    font-weight: 700;
    font-size: 14px;
}

.btn:hover {
    background: #267343;
}

</style>

</head>

<body>

<nav class="navbar">

    <div class="logo">
        🔄 CampusSwap
    </div>

    <a href="browse_resources.php" class="back">
        ← Back to Resources
    </a>

</nav>


<div class="container">

    <div class="card">

        <div class="success-icon">
            ✓
        </div>

        <h1>
            Borrow Request Successful!
        </h1>

        <p class="message">
            You have successfully borrowed the following resource.
            Please return it after using it.
        </p>


        <div class="resource">

            <div class="resource-title">
                <?php echo htmlspecialchars($resource["title"]); ?>
            </div>

            <div class="resource-info">
                Category:
                <?php echo htmlspecialchars($resource["category"]); ?>
            </div>

            <div class="resource-info">
                Condition:
                <?php echo htmlspecialchars($resource["condition_item"]); ?>
            </div>

            <div class="resource-info">
                Borrow Date:
                <?php echo htmlspecialchars($borrow_date); ?>
            </div>

            <div class="resource-info">
                Status:
                Borrowed
            </div>

        </div>


        <a href="dashboard.php" class="btn">
            Go to Dashboard
        </a>

    </div>

</div>

</body>

</html>