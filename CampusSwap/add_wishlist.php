<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['resource_id'])) {
    die("Resource not found.");
}

$resource_id = intval($_GET['resource_id']);

/* Check whether resource exists */
$sql = "SELECT id, user_id, title FROM resources WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $resource_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$resource = mysqli_fetch_assoc($result);

if (!$resource) {
    die("Resource not found.");
}

/* User cannot add own resource */
if ($resource['user_id'] == $user_id) {
    header("Location: browse_resources.php?message=own_resource");
    exit();
}

/* Check if already in wishlist */
$check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND resource_id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $resource_id);
mysqli_stmt_execute($check_stmt);

$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) > 0) {
    header("Location: wishlist.php?message=already_added");
    exit();
}

/* Add resource to wishlist */
$insert_sql = "INSERT INTO wishlist (user_id, resource_id) VALUES (?, ?)";
$insert_stmt = mysqli_prepare($conn, $insert_sql);
mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $resource_id);

if (mysqli_stmt_execute($insert_stmt)) {
    header("Location: wishlist.php?message=added");
    exit();
} else {
    die("Error adding resource to wishlist.");
}
?>