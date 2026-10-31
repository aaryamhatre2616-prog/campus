<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

$borrow_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($borrow_id <= 0) {
    die("Invalid borrow record.");
}

/* Check that this borrow record belongs to the logged-in user */
$sql = "SELECT resource_id, status
        FROM borrow_records
        WHERE id = ?
        AND borrower_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $borrow_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Borrow record not found or you are not authorized.");
}

$row = mysqli_fetch_assoc($result);

$resource_id = (int)$row["resource_id"];
$current_status = $row["status"];

mysqli_stmt_close($stmt);

/* Check if already returned */
if ($current_status == "Returned") {
    die("This resource has already been returned.");
}

/* Update borrow record */
$sql = "UPDATE borrow_records
        SET status = 'Returned',
            return_date = CURDATE()
        WHERE id = ?
        AND borrower_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Update query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $borrow_id, $user_id);

if (!mysqli_stmt_execute($stmt)) {
    die("Failed to update borrow record: " . mysqli_error($conn));
}

mysqli_stmt_close($stmt);

/* Make the resource available again */
$sql = "UPDATE resources
        SET status = 'Available'
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Resource update failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $resource_id);

if (!mysqli_stmt_execute($stmt)) {
    die("Failed to update resource: " . mysqli_error($conn));
}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Resource Returned - CampusSwap</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4fbf6;
    color: #243b2a;
}

.container {
    width: 90%;
    max-width: 650px;
    margin: 100px auto;
}

.card {
    background: white;
    padding: 40px;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(40, 90, 50, 0.10);
    border: 1px solid #e1f0e4;
}

.icon {
    font-size: 55px;
    margin-bottom: 15px;
}

h1 {
    color: #2e7d32;
    margin-bottom: 12px;
}

p {
    color: #66756a;
    line-height: 1.6;
}

.btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 22px;
    background: #3f8f4a;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
}

.btn:hover {
    background: #2f7338;
}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="icon">✓</div>

        <h1>Resource Returned Successfully!</h1>

        <p>
            The borrowed resource has been marked as returned.
        </p>

        <p>
            The resource is now available again for other students.
        </p>

        <a href="my_borrowed_resources.php" class="btn">
            My Borrowed Resources
        </a>

        <a href="browse_resources.php" class="btn">
            Browse Resources
        </a>

    </div>

</div>

</body>

</html>