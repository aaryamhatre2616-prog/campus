<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

$sql = "SELECT id, resource_id, title, description, category,
               status, created_at
        FROM requests
        WHERE user_id = ?
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Requests - CampusSwap</title>

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
    padding: 16px 7%;
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

.back-btn {
    text-decoration: none;
    color: #328b55;
    background: #e6f5ea;
    padding: 9px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
}

/* MAIN */

.container {
    width: 88%;
    max-width: 1100px;
    margin: 40px auto 60px;
}

.heading {
    margin-bottom: 30px;
}

.heading h1 {
    font-size: 32px;
    margin-bottom: 8px;
}

.heading p {
    color: #718078;
    font-size: 14px;
}

/* REQUEST CARD */

.request-card {
    background: white;
    border: 1px solid #e1eee5;
    border-radius: 16px;
    padding: 23px;
    margin-bottom: 20px;
    box-shadow: 0 5px 18px rgba(42,89,56,0.06);
}

.request-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.resource-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.icon {
    width: 52px;
    height: 52px;
    background: #e6f5ea;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 25px;
}

.resource-info h2 {
    font-size: 19px;
    margin-bottom: 5px;
}

.category {
    color: #718078;
    font-size: 13px;
}

.description {
    color: #718078;
    font-size: 13px;
    margin: 18px 0;
    line-height: 1.6;
}

.details {
    border-top: 1px solid #edf2ee;
    padding-top: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.date {
    color: #718078;
    font-size: 12px;
}

/* STATUS */

.status {
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
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

/* EMPTY */

.empty {
    background: white;
    border: 1px solid #e1eee5;
    border-radius: 16px;
    padding: 55px;
    text-align: center;
}

.empty-icon {
    font-size: 50px;
    margin-bottom: 15px;
}

.empty h2 {
    margin-bottom: 8px;
}

.empty p {
    color: #718078;
    font-size: 14px;
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

    <a href="dashboard.php" class="back-btn">
        ← Dashboard
    </a>

</nav>


<!-- MAIN -->

<div class="container">

    <div class="heading">

        <h1>
            My Exchange Requests 📋
        </h1>

        <p>
            Track the exchange requests you have sent to other students.
        </p>

    </div>


    <?php if ($result && mysqli_num_rows($result) > 0) { ?>


        <?php while ($row = mysqli_fetch_assoc($result)) { ?>


            <div class="request-card">

                <div class="request-top">

                    <div class="resource-info">

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


                    <?php

                    $status = strtolower($row["status"]);

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

                    <span class="status <?php echo $statusClass; ?>">

                        <?php
                        echo htmlspecialchars($row["status"]);
                        ?>

                    </span>

                </div>


                <p class="description">

                    <?php
                    echo htmlspecialchars($row["description"]);
                    ?>

                </p>


                <div class="details">

                    <span class="date">

                        📅 Requested on:

                        <?php
                        echo date(
                            "d M Y",
                            strtotime($row["created_at"])
                        );
                        ?>

                    </span>

                </div>

            </div>


        <?php } ?>


    <?php } else { ?>


        <div class="empty">

            <div class="empty-icon">
                📋
            </div>

            <h2>
                No Exchange Requests
            </h2>

            <p>
                You have not sent any exchange requests yet.
            </p>

        </div>


    <?php } ?>


</div>


<!-- FOOTER -->

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