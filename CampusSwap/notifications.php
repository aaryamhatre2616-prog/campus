<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = (int) $_SESSION['user_id'];

/* Get notifications for current user */
$sql = "SELECT id, message, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

/* Count unread notifications */
$count_sql = "SELECT COUNT(*) AS unread_count
              FROM notifications
              WHERE user_id = ? AND is_read = 0";

$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $user_id);
mysqli_stmt_execute($count_stmt);

$count_result = mysqli_stmt_get_result($count_stmt);
$count_data = mysqli_fetch_assoc($count_result);

$unread_count = (int) $count_data['unread_count'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Notifications - CampusSwap</title>

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
    font-size: 24px;
    font-weight: 800;
    color: #328b55;
}

.nav-links {
    display: flex;
    gap: 15px;
    align-items: center;
}

.nav-links a {
    text-decoration: none;
    color: #328b55;
    font-weight: 600;
    font-size: 14px;
}

.dashboard-btn {
    background: #e5f5e9;
    padding: 9px 15px;
    border-radius: 8px;
}

/* MAIN */

.container {
    width: 88%;
    max-width: 900px;
    margin: 45px auto 70px;
}

/* HEADER */

.heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}

.heading h1 {
    font-size: 31px;
    color: #294735;
    margin-bottom: 7px;
}

.heading p {
    color: #718078;
    font-size: 14px;
}

.unread-count {
    background: #328b55;
    color: white;
    padding: 9px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

/* NOTIFICATION LIST */

.notification-list {
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.notification {
    background: white;
    border: 1px solid #e2eee5;
    border-radius: 14px;
    padding: 19px 20px;
    display: flex;
    gap: 15px;
    align-items: flex-start;
    box-shadow: 0 4px 15px rgba(42,89,56,0.05);
}

.notification.unread {
    border-left: 4px solid #328b55;
    background: #f8fcf9;
}

.notification-icon {
    width: 43px;
    height: 43px;
    min-width: 43px;
    background: #e6f5ea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
}

.notification-content {
    flex: 1;
}

.notification-message {
    color: #294735;
    font-size: 14px;
    line-height: 1.55;
    margin-bottom: 7px;
}

.notification-time {
    color: #8a968e;
    font-size: 12px;
}

.unread-label {
    display: inline-block;
    background: #e4f5e8;
    color: #328b55;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    margin-left: 7px;
}

/* EMPTY */

.empty {
    background: white;
    border: 1px solid #e3eee6;
    border-radius: 16px;
    padding: 65px 25px;
    text-align: center;
}

.empty-icon {
    font-size: 50px;
    margin-bottom: 15px;
}

.empty h2 {
    color: #315b38;
    margin-bottom: 9px;
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

/* RESPONSIVE */

@media(max-width:650px) {

    .navbar {
        padding: 14px 5%;
    }

    .container {
        width: 92%;
    }

    .heading {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .heading h1 {
        font-size: 27px;
    }

}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        🔄 CampusSwap
    </div>

    <div class="nav-links">

        <a href="dashboard.php" class="dashboard-btn">
            ← Dashboard
        </a>

        <a href="wishlist.php">
            ❤️ Wishlist
        </a>

    </div>

</nav>


<!-- MAIN -->

<div class="container">

    <div class="heading">

        <div>

            <h1>
                Notifications 🔔
            </h1>

            <p>
                Stay updated about your CampusSwap activities.
            </p>

        </div>

        <?php if ($unread_count > 0) { ?>

            <div class="unread-count">
                <?php echo $unread_count; ?> Unread
            </div>

        <?php } ?>

    </div>


    <!-- NOTIFICATIONS -->

    <?php if ($result && mysqli_num_rows($result) > 0) { ?>

        <div class="notification-list">

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <div class="notification
                    <?php
                    if ((int)$row['is_read'] === 0) {
                        echo 'unread';
                    }
                    ?>
                ">

                    <div class="notification-icon">
                        🔔
                    </div>

                    <div class="notification-content">

                        <div class="notification-message">

                            <?php
                            echo htmlspecialchars($row['message']);
                            ?>

                            <?php if ((int)$row['is_read'] === 0) { ?>

                                <span class="unread-label">
                                    NEW
                                </span>

                            <?php } ?>

                        </div>

                        <div class="notification-time">

                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime($row['created_at'])
                            );
                            ?>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty">

            <div class="empty-icon">
                🔔
            </div>

            <h2>
                No Notifications
            </h2>

            <p>
                You don't have any notifications yet.
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