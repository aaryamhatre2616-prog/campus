<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

/*
   Get exchange requests received for resources
   uploaded by the logged-in user.
*/

$sql = "SELECT 
            r.id AS request_id,
            r.resource_id,
            r.title,
            r.description,
            r.category,
            r.status,
            r.created_at,
            u.full_name AS requester_name,
            u.email AS requester_email
        FROM requests r
        INNER JOIN resources res
            ON r.resource_id = res.id
        INNER JOIN users u
            ON r.user_id = u.id
        WHERE res.user_id = ?
        ORDER BY r.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Received Requests - CampusSwap</title>

<style>

/* =========================
   GENERAL
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Arial, sans-serif;
}

html {
    min-height: 100%;
    overflow-y: auto;
}

body {
    margin: 0;
    min-height: 100vh;
    background: #f5fbf7;
    color: #294735;
    overflow-y: auto;
}


/* =========================
   NAVBAR
========================= */

.navbar {
    background: white;
    padding: 16px 7%;

    display: flex;
    justify-content: space-between;
    align-items: center;

    box-shadow: 0 3px 15px rgba(0,0,0,0.06);
}

.logo {
    font-size: 25px;
    font-weight: 800;
    color: #328b55;
}

.back-btn {
    text-decoration: none;
    color: #328b55;

    background: #e6f5ea;

    padding: 10px 17px;

    border-radius: 9px;

    font-weight: 600;
    font-size: 14px;
}

.back-btn:hover {
    background: #d8efdf;
}


/* =========================
   MAIN CONTAINER
========================= */

.container {
    width: 88%;
    max-width: 1100px;

    margin: 45px auto 70px;

    padding-bottom: 40px;
}

.heading {
    margin-bottom: 30px;
}

.heading h1 {
    font-size: 34px;
    margin-bottom: 8px;
    color: #294735;
}

.heading p {
    color: #718078;
    font-size: 14px;
}


/* =========================
   REQUEST CARD
========================= */

.request-card {
    background: white;

    border: 1px solid #e1eee5;

    border-radius: 17px;

    padding: 25px;

    margin-bottom: 22px;

    box-shadow: 0 5px 18px rgba(42,89,56,0.06);

    transition: 0.25s;
}

.request-card:hover {
    box-shadow: 0 10px 25px rgba(42,89,56,0.10);
}


/* =========================
   TOP SECTION
========================= */

.top-section {
    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;
}

.resource-section {
    display: flex;

    gap: 15px;

    align-items: center;
}

.icon {
    width: 56px;
    height: 56px;

    background: #e6f5ea;

    border-radius: 13px;

    display: flex;
    justify-content: center;
    align-items: center;

    font-size: 26px;
}

.resource-section h2 {
    font-size: 20px;

    margin-bottom: 5px;

    color: #294735;
}

.category {
    color: #718078;

    font-size: 13px;
}


/* =========================
   STATUS
========================= */

.status {
    padding: 8px 15px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;
}

.pending {
    background: #fff4d6;
    color: #9a7000;
}

.accepted {
    background: #e1f5e7;
    color: #287744;
}

.rejected {
    background: #fde5e5;
    color: #b33a3a;
}


/* =========================
   REQUESTER
========================= */

.requester {
    margin-top: 22px;

    padding: 16px;

    background: #f7fbf8;

    border-radius: 11px;
}

.requester-title {
    font-size: 12px;

    color: #718078;

    margin-bottom: 7px;
}

.requester-name {
    font-size: 15px;

    font-weight: 700;

    color: #294735;
}

.requester-email {
    color: #718078;

    font-size: 12px;

    margin-top: 4px;
}


/* =========================
   DESCRIPTION
========================= */

.description {
    color: #718078;

    font-size: 13px;

    line-height: 1.6;

    margin: 19px 0;
}


/* =========================
   DATE
========================= */

.date {
    border-top: 1px solid #edf2ee;

    padding-top: 15px;

    color: #718078;

    font-size: 12px;
}


/* =========================
   ACTION BUTTONS
========================= */

.actions {
    display: flex;

    gap: 12px;

    margin-top: 20px;

    padding-top: 5px;
}

.action-btn {
    flex: 1;

    display: block;

    padding: 13px;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 700;

    text-align: center;

    text-decoration: none;

    cursor: pointer;

    transition: 0.2s;
}


/* =========================
   ACCEPT BUTTON
========================= */

.accept-btn {
    background: #328b55;

    color: white;
}

.accept-btn:hover {
    background: #267343;
}


/* =========================
   REJECT BUTTON
========================= */

.reject-btn {
    background: #f8e7e7;

    color: #b33a3a;
}

.reject-btn:hover {
    background: #f3d7d7;
}


/* =========================
   EMPTY
========================= */

.empty {
    background: white;

    border: 1px solid #e1eee5;

    border-radius: 17px;

    padding: 60px 30px;

    text-align: center;
}

.empty-icon {
    font-size: 50px;

    margin-bottom: 15px;
}

.empty h2 {
    margin-bottom: 8px;

    color: #294735;
}

.empty p {
    color: #718078;

    font-size: 14px;
}


/* =========================
   FOOTER
========================= */

footer {
    background: #244c32;

    color: white;

    text-align: center;

    padding: 30px;
}

footer h3 {
    margin-bottom: 5px;
}

footer p {
    color: #d5e8db;

    font-size: 13px;
}


/* =========================
   MOBILE
========================= */

@media(max-width:650px) {

    .container {
        width: 92%;
    }

    .top-section {
        flex-direction: column;
    }

    .actions {
        flex-direction: column;
    }

    .heading h1 {
        font-size: 27px;
    }

}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <div class="logo">
        🔄 CampusSwap
    </div>

    <a href="dashboard.php" class="back-btn">
        ← Dashboard
    </a>

</nav>


<!-- =========================
     MAIN
========================= -->

<div class="container">

    <div class="heading">

        <h1>
            Received Exchange Requests 📩
        </h1>

        <p>
            Manage requests received from students for your shared resources.
        </p>

    </div>


    <?php

    if ($result && mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_assoc($result)) {

            /*
               Convert status to lowercase
               for CSS and button checking.
            */

            $status = strtolower(trim($row["status"]));

            if ($status == "accepted") {

                $statusClass = "accepted";

            }
            elseif ($status == "rejected") {

                $statusClass = "rejected";

            }
            else {

                $statusClass = "pending";

            }

    ?>

    <!-- =========================
         REQUEST CARD
    ========================= -->

    <div class="request-card">

        <!-- TOP -->

        <div class="top-section">

            <div class="resource-section">

                <div class="icon">
                    📚
                </div>

                <div>

                    <h2>

                        <?php
                        echo htmlspecialchars($row["title"]);
                        ?>

                    </h2>

                    <span class="category">

                        <?php
                        echo htmlspecialchars($row["category"]);
                        ?>

                    </span>

                </div>

            </div>


            <!-- STATUS -->

            <span class="status <?php echo $statusClass; ?>">

                <?php
                echo htmlspecialchars($row["status"]);
                ?>

            </span>

        </div>


        <!-- REQUESTER -->

        <div class="requester">

            <div class="requester-title">
                Exchange request from
            </div>

            <div class="requester-name">

                👤

                <?php
                echo htmlspecialchars($row["requester_name"]);
                ?>

            </div>

            <div class="requester-email">

                <?php
                echo htmlspecialchars($row["requester_email"]);
                ?>

            </div>

        </div>


        <!-- DESCRIPTION -->

        <p class="description">

            <?php
            echo htmlspecialchars($row["description"]);
            ?>

        </p>


        <!-- DATE -->

        <div class="date">

            📅 Requested on:

            <?php
            echo date(
                "d M Y",
                strtotime($row["created_at"])
            );
            ?>

        </div>


        <!-- =========================
             ACCEPT / REJECT BUTTONS
        ========================= -->

        <?php

        if ($status === "pending") {

        ?>

            <div class="actions">

                <a
                    href="update_request.php?id=<?php echo (int)$row['request_id']; ?>&status=Accepted"
                    class="action-btn accept-btn"
                >
                    ✓ Accept Request
                </a>

                <a
                    href="update_request.php?id=<?php echo (int)$row['request_id']; ?>&status=Rejected"
                    class="action-btn reject-btn"
                >
                    ✕ Reject Request
                </a>

            </div>

        <?php

        }

        ?>

    </div>


    <?php

        }

    }

    else {

    ?>

    <!-- =========================
         EMPTY
    ========================= -->

    <div class="empty">

        <div class="empty-icon">
            📩
        </div>

        <h2>
            No Requests Received
        </h2>

        <p>
            No students have requested your resources yet.
        </p>

    </div>

    <?php

    }

    ?>

</div>


<!-- =========================
     FOOTER
========================= -->

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