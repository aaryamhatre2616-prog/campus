<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

$request_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$status = isset($_GET["status"]) ? $_GET["status"] : "";

if ($request_id <= 0) {
    die("Invalid request ID.");
}

if ($status !== "Accepted" && $status !== "Rejected") {
    die("Invalid request status.");
}


/* Check that this request belongs to a resource
   uploaded by the logged-in user */

$sql = "SELECT r.id
        FROM requests r
        INNER JOIN resources res
            ON r.resource_id = res.id
        WHERE r.id = ?
        AND res.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $request_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("You are not authorized to update this request.");
}

mysqli_stmt_close($stmt);


/* Update request status */

$sql = "UPDATE requests
        SET status = ?
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Update query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "si", $status, $request_id);

if (mysqli_stmt_execute($stmt)) {

    header("Location: received_requests.php");
    exit();

} else {

    die("Failed to update request: " . mysqli_error($conn));
}

?>