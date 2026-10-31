<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = (int) $_SESSION["user_id"];

if (!isset($_GET["resource_id"]) || !is_numeric($_GET["resource_id"])) {
    die("Invalid resource.");
}

$resource_id = (int) $_GET["resource_id"];

$message = "";
$error = "";


/* Get resource details */

$sql = "SELECT id, user_id, title, description, category, exchange_type,
               condition_item, status
        FROM resources
        WHERE id = ? AND status = 'Available'";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $resource_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$resource = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* Check resource */

if (!$resource) {
    die("Resource not found or no longer available.");
}


/* Prevent requesting own resource */

if ((int)$resource["user_id"] === $user_id) {
    die("You cannot request your own resource.");
}


/* Submit request */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $resource["title"];
    $description = $resource["description"];
    $category = $resource["category"];
    $status = "Pending";

    /* Resource owner ID */
    $owner_id = (int) $resource["user_id"];


    /* Get requester name */

    $name_sql = "SELECT full_name
                 FROM users
                 WHERE id = ?";

    $name_stmt = mysqli_prepare($conn, $name_sql);

    mysqli_stmt_bind_param(
        $name_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($name_stmt);

    $name_result = mysqli_stmt_get_result($name_stmt);

    $user_data = mysqli_fetch_assoc($name_result);

    mysqli_stmt_close($name_stmt);

    $requester_name = $user_data
        ? $user_data["full_name"]
        : "A student";


    /* Check duplicate pending request */

    $check_sql = "SELECT id
                  FROM requests
                  WHERE user_id = ?
                  AND resource_id = ?
                  AND status = 'Pending'";

    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param(
        $check_stmt,
        "ii",
        $user_id,
        $resource_id
    );

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {

        $error = "You have already sent a request for this resource.";

    } else {

        /* Insert exchange request */

        $insert_sql = "INSERT INTO requests
                       (user_id, resource_id, title, description,
                        category, status, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $insert_stmt = mysqli_prepare($conn, $insert_sql);

        mysqli_stmt_bind_param(
            $insert_stmt,
            "iissss",
            $user_id,
            $resource_id,
            $title,
            $description,
            $category,
            $status
        );

        if (mysqli_stmt_execute($insert_stmt)) {

            /*
             * Create notification for resource owner
             */

            $notification_message =
                $requester_name .
                " sent an exchange request for your resource: " .
                $title;

            $notification_sql = "INSERT INTO notifications
                                 (user_id, message, is_read, created_at)
                                 VALUES (?, ?, 0, NOW())";

            $notification_stmt = mysqli_prepare(
                $conn,
                $notification_sql
            );

            mysqli_stmt_bind_param(
                $notification_stmt,
                "is",
                $owner_id,
                $notification_message
            );

            mysqli_stmt_execute($notification_stmt);

            mysqli_stmt_close($notification_stmt);

            $message = "Exchange request sent successfully!";

        } else {

            $error = "Unable to send request.";

        }

        mysqli_stmt_close($insert_stmt);
    }

    mysqli_stmt_close($check_stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Request Exchange - CampusSwap</title>

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


/* NAVBAR */

.navbar {
    background: white;
    padding: 15px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.06);
}

.logo {
    color: #328b55;
    font-size: 24px;
    font-weight: 800;
}

.back {
    text-decoration: none;
    color: #328b55;
    font-weight: 600;
    font-size: 14px;
}


/* CONTAINER */

.container {
    width: 90%;
    max-width: 760px;
    margin: 45px auto 70px;
}


/* CARD */

.card {
    background: white;
    border: 1px solid #e2eee5;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 8px 25px rgba(42,89,56,0.08);
}

.card h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.subtitle {
    color: #718078;
    font-size: 14px;
    margin-bottom: 28px;
}


/* MESSAGE */

.success {
    background: #e4f6e8;
    color: #287345;
    padding: 13px 15px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 14px;
}

.error {
    background: #fff0f0;
    color: #b23b3b;
    padding: 13px 15px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 14px;
}


/* RESOURCE PREVIEW */

.resource-preview {
    border: 1px solid #e2eee5;
    border-radius: 15px;
    padding: 22px;
    background: #fbfdfb;
    margin-bottom: 25px;
}

.resource-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.resource-icon {
    width: 55px;
    height: 55px;
    background: #e5f5e9;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 27px;
}

.category {
    background: #e4f5e8;
    color: #328b55;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.resource-preview h2 {
    font-size: 20px;
    margin-bottom: 9px;
}

.description {
    color: #718078;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 18px;
}

.details {
    border-top: 1px solid #e8eee9;
    padding-top: 15px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 9px;
    font-size: 13px;
}

.label {
    color: #7a877f;
}

.value {
    color: #405448;
    font-weight: 600;
}


/* BUTTON */

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


/* FOOTER */

footer {
    background: #244c32;
    color: white;
    text-align: center;
    padding: 28px;
}

footer p {
    color: #d5e8db;
    font-size: 13px;
    margin-top: 5px;
}

</style>

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        🔄 CampusSwap
    </div>

    <a href="browse_resources.php" class="back">
        ← Back to Resources
    </a>

</nav>


<!-- MAIN -->

<div class="container">

    <div class="card">

        <h1>
            Request Exchange 🔄
        </h1>

        <p class="subtitle">
            Send an exchange request to the resource owner.
        </p>


        <?php if ($message != "") { ?>

            <div class="success">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php } ?>


        <?php if ($error != "") { ?>

            <div class="error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php } ?>


        <!-- RESOURCE -->

        <div class="resource-preview">

            <div class="resource-header">

                <div class="resource-icon">
                    📚
                </div>

                <span class="category">

                    <?php
                    echo htmlspecialchars($resource["category"]);
                    ?>

                </span>

            </div>


            <h2>

                <?php
                echo htmlspecialchars($resource["title"]);
                ?>

            </h2>


            <p class="description">

                <?php
                echo htmlspecialchars($resource["description"]);
                ?>

            </p>


            <div class="details">

                <div class="detail-row">

                    <span class="label">
                        Condition
                    </span>

                    <span class="value">

                        <?php
                        echo htmlspecialchars(
                            $resource["condition_item"]
                        );
                        ?>

                    </span>

                </div>


                <div class="detail-row">

                    <span class="label">
                        Exchange Type
                    </span>

                    <span class="value">

                        <?php
                        echo htmlspecialchars(
                            $resource["exchange_type"]
                        );
                        ?>

                    </span>

                </div>


                <div class="detail-row">

                    <span class="label">
                        Status
                    </span>

                    <span class="value">

                        <?php
                        echo htmlspecialchars(
                            $resource["status"]
                        );
                        ?>

                    </span>

                </div>

            </div>

        </div>


        <?php if ($message == "") { ?>

            <form method="POST">

                <button type="submit">
                    Send Exchange Request
                </button>

            </form>

        <?php } else { ?>

            <a
                href="browse_resources.php"
                style="
                    display:block;
                    text-align:center;
                    background:#328b55;
                    color:white;
                    padding:14px;
                    border-radius:9px;
                    text-decoration:none;
                    font-weight:700;
                "
            >
                Back to Resources
            </a>

        <?php } ?>


    </div>

</div>


<footer>

    <h3>
        CampusSwap
    </h3>

    <p>
        Share. Exchange. Grow Together.
    </p>

</footer>


</body>
</html>