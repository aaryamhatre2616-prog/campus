<?php
session_start();
include "db.php";

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Check whether wishlist item ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: wishlist.php");
    exit();
}

$wishlist_id = (int) $_GET['id'];

// Remove only the current user's wishlist item
$sql = "DELETE FROM wishlist WHERE id = ? AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $wishlist_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        header("Location: wishlist.php?message=removed");
        exit();
    } else {
        header("Location: wishlist.php");
        exit();
    }
} else {
    echo "Unable to remove the resource. Please try again.";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>