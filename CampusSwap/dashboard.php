<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = (int) $_SESSION["user_id"];
$user_name = $_SESSION["full_name"];


/* =========================================
   UNREAD NOTIFICATIONS
   ========================================= */

$notification_sql = "SELECT COUNT(*) AS unread_count
                     FROM notifications
                     WHERE user_id = ? AND is_read = 0";

$notification_stmt = mysqli_prepare($conn, $notification_sql);

mysqli_stmt_bind_param(
    $notification_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($notification_stmt);

$notification_result = mysqli_stmt_get_result(
    $notification_stmt
);

$notification_data = mysqli_fetch_assoc(
    $notification_result
);

$unread_notifications =
    (int) $notification_data["unread_count"];

mysqli_stmt_close($notification_stmt);


/* =========================================
   FEATURED RESOURCES
   ========================================= */

$resource_sql = "SELECT id, title, category, description,
                        exchange_type, condition_item, photo
                 FROM resources
                 WHERE status = 'Available'
                 ORDER BY id DESC
                 LIMIT 4";

$resource_result = mysqli_query(
    $conn,
    $resource_sql
);


/* =========================================
   LATEST ACCEPTED EXCHANGES
   ========================================= */

$exchange_sql = "SELECT r.title,
                        r.category,
                        r.created_at,
                        u.full_name
                 FROM requests r
                 LEFT JOIN users u
                 ON r.user_id = u.id
                 WHERE r.status = 'Accepted'
                 ORDER BY r.id DESC
                 LIMIT 3";

$exchange_result = mysqli_query(
    $conn,
    $exchange_sql
);


/* =========================================
   RESOURCE REQUESTS
   ========================================= */

$request_sql = "SELECT title,
                       category,
                       description,
                       created_at
                FROM requests
                WHERE status = 'Open'
                ORDER BY id DESC
                LIMIT 3";

$request_result = mysqli_query(
    $conn,
    $request_sql
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>CampusSwap Dashboard</title>


<style>

/* =========================================
   BASIC
   ========================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Arial, sans-serif;
}

body {
    background: #f7fbf8;
    color: #244735;
}

a {
    text-decoration: none;
    color: inherit;
}


/* =========================================
   NAVBAR
   ========================================= */

.navbar {
    height: 76px;
    background: white;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 5%;

    border-bottom: 1px solid #e5eee8;

    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-symbol {
    width: 48px;
    height: 48px;

    background: #e4f5e9;

    border-radius: 14px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 28px;
}

.logo-text h2 {
    color: #1f7544;
    font-size: 22px;
}

.logo-text span {
    display: block;
    color: #7b8c82;
    font-size: 9px;
    margin-top: 4px;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 25px;
}

.nav-links a {
    color: #506157;
    font-size: 14px;
    font-weight: 600;
}

.nav-links a:hover {
    color: #23804a;
}

.nav-notification {
    position: relative;
}

.nav-badge {
    position: absolute;
    top: -11px;
    right: -14px;

    background: #ef6262;
    color: white;

    min-width: 18px;
    height: 18px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 10px;
    font-weight: 700;
}

.profile-btn {
    background: #edf8f0;
    color: #27814b !important;

    padding: 10px 15px;

    border-radius: 9px;
}

.logout-btn {
    background: #23844d;
    color: white !important;

    padding: 10px 17px;

    border-radius: 9px;
}

.logout-btn:hover {
    background: #19693c !important;
}


/* =========================================
   HERO
   ========================================= */

.hero {
    width: 90%;
    max-width: 1250px;

    margin: 28px auto;

    min-height: 320px;

    border-radius: 28px;

    padding: 50px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    overflow: hidden;

    background:
        radial-gradient(
            circle at 85% 15%,
            rgba(255,255,255,.45),
            transparent 24%
        ),
        linear-gradient(
            120deg,
            #e7f7e9,
            #d8f1dc,
            #eef9ef
        );

    border: 1px solid #dcefe0;
}

.hero-content {
    width: 58%;
}

.hero-small {
    color: #318152;

    font-size: 13px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: 1px;

    margin-bottom: 12px;
}

.hero h1 {
    font-size: 42px;

    line-height: 1.15;

    color: #164d31;

    margin-bottom: 16px;
}

.hero h1 span {
    color: #2c9858;
}

.hero p {
    color: #557163;

    font-size: 15px;

    line-height: 1.7;

    max-width: 620px;

    margin-bottom: 25px;
}

.hero-buttons {
    display: flex;

    gap: 12px;

    flex-wrap: wrap;
}

.primary-btn {
    background: #24854d;

    color: white;

    padding: 12px 20px;

    border-radius: 9px;

    font-size: 14px;

    font-weight: 700;
}

.secondary-btn {
    background: white;

    color: #267749;

    border: 1px solid #72ad87;

    padding: 11px 20px;

    border-radius: 9px;

    font-size: 14px;

    font-weight: 700;
}

.secondary-btn:hover {
    background: #edf8f0;
}


/* =========================================
   HERO BOOK DESIGN
   ========================================= */

.hero-visual {
    width: 38%;

    display: flex;

    align-items: center;

    justify-content: center;

    position: relative;
}

.book-stack {
    width: 260px;
    height: 190px;

    position: relative;
}

.book {
    position: absolute;

    left: 25px;

    width: 220px;
    height: 45px;

    border-radius: 8px;

    box-shadow:
        0 8px 15px rgba(41,88,55,.15);
}

.book.one {
    bottom: 15px;

    background: #277b4b;

    transform: rotate(-4deg);
}

.book.two {
    bottom: 53px;

    background: #e5a62e;

    transform: rotate(2deg);
}

.book.three {
    bottom: 91px;

    background: #4089b9;

    transform: rotate(-3deg);
}

.book-top {
    position: absolute;

    bottom: 125px;
    left: 75px;

    width: 120px;
    height: 65px;

    background: #263b4d;

    border-radius: 8px 8px 4px 4px;

    transform:
        perspective(200px)
        rotateX(18deg);

    box-shadow:
        0 10px 20px rgba(20,40,50,.2);
}

.leaf {
    position: absolute;

    right: 15px;
    top: 25px;

    font-size: 60px;

    opacity: .5;
}


/* =========================================
   HIGHLIGHTS
   ========================================= */

.highlights {
    width: 90%;
    max-width: 1250px;

    margin: 0 auto 35px;

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    background: white;

    border: 1px solid #e5eee8;

    border-radius: 18px;

    overflow: hidden;
}

.highlight {
    padding: 22px;

    display: flex;

    align-items: center;

    gap: 13px;

    border-right:
        1px solid #e2ebe5;
}

.highlight:last-child {
    border-right: none;
}

.highlight-icon {
    width: 48px;
    height: 48px;

    flex-shrink: 0;

    border-radius: 50%;

    background: #e4f6e8;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;
}

.highlight h3 {
    color: #27583b;

    font-size: 14px;

    margin-bottom: 4px;
}

.highlight p {
    color: #78877e;

    font-size: 11px;

    line-height: 1.4;
}


/* =========================================
   MAIN
   ========================================= */

.main {
    width: 90%;
    max-width: 1250px;

    margin: 0 auto 50px;
}


/* =========================================
   SECTION HEADER
   ========================================= */

.section-header {
    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 16px;
}

.section-header-left {
    display: flex;

    align-items: center;

    gap: 10px;
}

.section-icon {
    font-size: 25px;
}

.section-header h2 {
    color: #214f35;

    font-size: 21px;
}

.view-all {
    color: #39855a;

    font-size: 13px;

    font-weight: 700;
}


/* =========================================
   FEATURED RESOURCES
   ========================================= */

.resource-grid {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 16px;

    margin-bottom: 35px;
}

.resource-card {
    background: white;

    border: 1px solid #e3ebe6;

    border-radius: 13px;

    overflow: hidden;

    transition: .3s;

    box-shadow:
        0 3px 12px rgba(35,76,50,.04);
}

.resource-card:hover {
    transform: translateY(-5px);

    box-shadow:
        0 12px 25px rgba(35,76,50,.10);
}

.resource-image {
    height: 120px;

    background: #edf7ef;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;
}

.resource-image img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.resource-placeholder {
    font-size: 48px;
}

.resource-content {
    padding: 15px;
}

.resource-title {
    color: #28513a;

    font-size: 14px;

    font-weight: 700;

    margin-bottom: 8px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.resource-line {
    font-size: 11px;

    color: #718078;

    margin-bottom: 5px;
}

.resource-line strong {
    color: #405b4a;
}

.resource-actions {
    display: flex;

    align-items: center;

    gap: 7px;

    margin-top: 12px;
}

.resource-btn {
    background: #25864d;

    color: white;

    padding: 7px 11px;

    border-radius: 6px;

    font-size: 10px;

    font-weight: 700;
}

.resource-type {
    color: #39865a;

    font-size: 10px;

    font-weight: 700;
}


/* =========================================
   THREE COLUMNS
   ========================================= */

.three-columns {
    display: grid;

    grid-template-columns:
        1.1fr 1.1fr .9fr;

    gap: 18px;
}

.panel {
    background: white;

    border: 1px solid #e3ebe6;

    border-radius: 15px;

    padding: 18px;

    box-shadow:
        0 3px 12px rgba(35,76,50,.04);
}

.panel-title {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 14px;
}

.panel-title h3 {
    color: #28563c;

    font-size: 16px;
}

.panel-title a {
    color: #43885e;

    font-size: 11px;

    font-weight: 700;
}


/* =========================================
   EXCHANGE
   ========================================= */

.exchange-item {
    display: flex;

    align-items: center;

    gap: 11px;

    padding: 12px 0;

    border-bottom:
        1px solid #edf2ee;
}

.exchange-item:last-child {
    border-bottom: none;
}

.exchange-avatar {
    width: 40px;
    height: 40px;

    flex-shrink: 0;

    border-radius: 9px;

    background: #e5f5e9;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 19px;
}

.exchange-info {
    min-width: 0;
}

.exchange-info h4 {
    color: #3b5947;

    font-size: 12px;

    margin-bottom: 4px;
}

.exchange-info p {
    color: #8a968f;

    font-size: 10px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


/* =========================================
   REQUESTS
   ========================================= */

.request-card {
    padding: 12px 0;

    border-bottom:
        1px solid #edf2ee;
}

.request-card:last-child {
    border-bottom: none;
}

.request-card h4 {
    color: #395a46;

    font-size: 12px;

    margin-bottom: 5px;
}

.request-card p {
    color: #849089;

    font-size: 10px;

    line-height: 1.5;
}

.request-tag {
    display: inline-block;

    margin-top: 6px;

    background: #edf8ef;

    color: #43875b;

    padding: 3px 7px;

    border-radius: 20px;

    font-size: 9px;

    font-weight: 700;
}


/* =========================================
   CATEGORIES
   ========================================= */

.category-grid {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 10px;
}

.category {
    background: #f7fbf8;

    border: 1px solid #e6eee8;

    border-radius: 11px;

    padding: 15px 8px;

    text-align: center;

    transition: .25s;
}

.category:hover {
    background: #eaf7ed;

    transform: translateY(-2px);
}

.category-icon {
    font-size: 25px;

    margin-bottom: 7px;
}

.category span {
    color: #50675a;

    font-size: 10px;

    font-weight: 700;
}


/* =========================================
   NOTES SHARING SPECIAL STYLE
   ========================================= */

.notes-category {
    background:
        linear-gradient(
            135deg,
            #eefaf1,
            #e3f6e8
        );

    border: 1px solid #cfe9d6;
}

.notes-category span {
    color: #247848;
}


/* =========================================
   RATINGS SPECIAL STYLE
   ========================================= */

.rating-category {
    background:
        linear-gradient(
            135deg,
            #fffaf0,
            #fff4d8
        );

    border: 1px solid #f0dfad;
}

.rating-category span {
    color: #a36a00;
}


/* =========================================
   NOTIFICATION
   ========================================= */

.notification-banner {
    margin-top: 20px;

    background:
        linear-gradient(
            120deg,
            #e5f7e9,
            #f3fbf4
        );

    border: 1px solid #d6eadb;

    border-radius: 14px;

    padding: 17px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;
}

.notification-left {
    display: flex;

    align-items: center;

    gap: 12px;
}

.notification-icon {
    width: 42px;
    height: 42px;

    border-radius: 50%;

    background: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;
}

.notification-left h3 {
    color: #2e6544;

    font-size: 14px;

    margin-bottom: 3px;
}

.notification-left p {
    color: #75857b;

    font-size: 11px;
}

.notification-button {
    background: #25864d;

    color: white;

    padding: 8px 13px;

    border-radius: 7px;

    font-size: 11px;

    font-weight: 700;
}

.notification-number {
    background: #ed6363;

    color: white;

    padding: 3px 7px;

    border-radius: 20px;

    font-size: 9px;

    margin-left: 4px;
}


/* =========================================
   FOOTER
   ========================================= */

footer {
    background: #205538;

    color: white;

    padding: 35px 5% 20px;
}

.footer-content {
    max-width: 1250px;

    margin: auto;

    display: flex;

    justify-content: space-between;

    align-items: center;
}

.footer-logo {
    display: flex;

    align-items: center;

    gap: 10px;
}

.footer-logo-icon {
    font-size: 34px;
}

.footer-logo h3 {
    font-size: 20px;
}

.footer-logo p {
    color: #cde1d3;

    font-size: 10px;

    margin-top: 3px;
}

.footer-links {
    display: flex;

    gap: 20px;
}

.footer-links a {
    color: #d6e9dc;

    font-size: 11px;
}

.copyright {
    text-align: center;

    border-top:
        1px solid rgba(255,255,255,.12);

    margin-top: 25px;

    padding-top: 15px;

    color: #bcd4c4;

    font-size: 10px;
}


/* =========================================
   EMPTY
   ========================================= */

.empty {
    padding: 25px 5px;

    text-align: center;

    color: #89958e;

    font-size: 11px;
}


/* =========================================
   RESPONSIVE
   ========================================= */

@media (max-width: 1050px) {

    .resource-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .three-columns {
        grid-template-columns:
            1fr 1fr;
    }

    .three-columns .panel:last-child {
        grid-column: 1 / -1;
    }

    .highlights {
        grid-template-columns:
            repeat(2, 1fr);
    }

}

@media (max-width: 800px) {

    .nav-links a:not(.logout-btn):not(.profile-btn) {
        display: none;
    }

    .hero {
        padding: 35px;
    }

    .hero-content {
        width: 100%;
    }

    .hero-visual {
        display: none;
    }

}

@media (max-width: 650px) {

    .navbar {
        padding: 0 4%;
    }

    .logo-text span {
        display: none;
    }

    .nav-links {
        gap: 8px;
    }

    .profile-btn {
        padding: 8px 9px;
    }

    .logout-btn {
        padding: 8px 10px;
    }

    .hero {
        width: 94%;

        padding: 30px 25px;

        min-height: auto;
    }

    .hero h1 {
        font-size: 31px;
    }

    .highlights {
        width: 94%;

        grid-template-columns: 1fr;
    }

    .highlight {
        border-right: none;

        border-bottom:
            1px solid #e2ebe5;
    }

    .main {
        width: 94%;
    }

    .resource-grid {
        grid-template-columns: 1fr;
    }

    .three-columns {
        grid-template-columns: 1fr;
    }

    .notification-banner {
        flex-direction: column;

        align-items: flex-start;
    }

    .footer-content {
        flex-direction: column;

        gap: 20px;

        text-align: center;
    }

}

</style>

</head>


<body>


<!-- =========================================
     NAVBAR
     ========================================= -->

<nav class="navbar">

    <a href="dashboard.php" class="logo">

        <div class="logo-symbol">
            🌿
        </div>

        <div class="logo-text">

            <h2>
                Campus<span style="color:#299458;">
                    Swap
                </span>
            </h2>

            <span>
                Share. Exchange. Grow Together.
            </span>

        </div>

    </a>


    <div class="nav-links">

        <a href="dashboard.php">
            Home
        </a>

        <a href="browse_resources.php">
            Browse Resources
        </a>

        <a href="requests.php">
            Requests
        </a>


        <!-- NOTIFICATIONS -->

        <a
            href="notifications.php"
            class="nav-notification"
        >

            🔔

            <?php

            if ($unread_notifications > 0) {

            ?>

                <span class="nav-badge">

                    <?php
                    echo $unread_notifications;
                    ?>

                </span>

            <?php

            }

            ?>

        </a>


        <!-- PROFILE -->

        <a
            href="profile.php"
            class="profile-btn"
        >
            👤 Profile
        </a>


        <!-- LOGOUT -->

        <a
            href="logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================================
     HERO
     ========================================= -->

<section class="hero">


    <div class="hero-content">

        <div class="hero-small">
            CampusSwap Student Community
        </div>


        <h1>

            Share.
            <span>Exchange.</span>
            <br>
            Grow Together.

        </h1>


        <p>

            Discover useful educational resources,
            exchange books and study materials,
            borrow what you need, and help other
            students learn through sharing.

        </p>


        <div class="hero-buttons">


            <a
                href="browse_resources.php"
                class="primary-btn"
            >
                🔍 Explore Resources
            </a>


            <a
                href="add_resource.php"
                class="secondary-btn"
            >
                ⬆️ Share a Resource
            </a>


            <a
                href="notes_sharing.php"
                class="secondary-btn"
            >
                📝 Notes Sharing
            </a>


            <!-- RATINGS & REVIEWS -->

            <a
                href="ratings.php"
                class="secondary-btn"
            >
                ⭐ Ratings & Reviews
            </a>


        </div>

    </div>


    <div class="hero-visual">

        <div class="book-stack">

            <div class="book one"></div>

            <div class="book two"></div>

            <div class="book three"></div>

            <div class="book-top"></div>

        </div>


        <div class="leaf">
            🌿
        </div>

    </div>

</section>


<!-- =========================================
     HIGHLIGHTS
     ========================================= -->

<section class="highlights">


    <div class="highlight">

        <div class="highlight-icon">
            ♻️
        </div>

        <div>

            <h3>
                Reuse Resources
            </h3>

            <p>
                Give useful books and materials a new life.
            </p>

        </div>

    </div>


    <div class="highlight">

        <div class="highlight-icon">
            🤝
        </div>

        <div>

            <h3>
                Share Knowledge
            </h3>

            <p>
                Help fellow students with useful resources.
            </p>

        </div>

    </div>


    <div class="highlight">

        <div class="highlight-icon">
            🌱
        </div>

        <div>

            <h3>
                Build Community
            </h3>

            <p>
                Connect students through resource sharing.
            </p>

        </div>

    </div>


    <div class="highlight">

        <div class="highlight-icon">
            🎓
        </div>

        <div>

            <h3>
                Support Learning
            </h3>

            <p>
                Make educational resources easier to access.
            </p>

        </div>

    </div>


</section>


<!-- =========================================
     MAIN
     ========================================= -->

<main class="main">


<!-- FEATURED RESOURCES -->

<div class="section-header">

    <div class="section-header-left">

        <div class="section-icon">
            📚
        </div>

        <h2>
            Featured Resources
        </h2>

    </div>


    <a
        href="browse_resources.php"
        class="view-all"
    >
        View All →
    </a>

</div>


<div class="resource-grid">


<?php

if (
    $resource_result &&
    mysqli_num_rows($resource_result) > 0
) {

    while (
        $resource =
        mysqli_fetch_assoc($resource_result)
    ) {

        $resource_title =
            htmlspecialchars(
                $resource["title"]
            );

        $resource_category =
            htmlspecialchars(
                $resource["category"]
            );

        $resource_condition =
            htmlspecialchars(
                $resource["condition_item"]
            );

        $resource_type =
            htmlspecialchars(
                $resource["exchange_type"]
            );

        $resource_photo =
            $resource["photo"];

?>

    <div class="resource-card">


        <div class="resource-image">

            <?php

            if (!empty($resource_photo)) {

            ?>

                <img
                    src="<?php
                    echo htmlspecialchars(
                        $resource_photo
                    );
                    ?>"
                    alt="Resource"
                >

            <?php

            } else {

            ?>

                <div class="resource-placeholder">

                    <?php

                    if (
                        strtolower(
                            $resource_category
                        ) == "book"
                    ) {

                        echo "📘";

                    } elseif (
                        strtolower(
                            $resource_category
                        ) == "notes"
                    ) {

                        echo "📝";

                    } elseif (
                        strtolower(
                            $resource_category
                        ) == "papers"
                    ) {

                        echo "📄";

                    } elseif (
                        strtolower(
                            $resource_category
                        ) == "lab manuals"
                    ) {

                        echo "🧪";

                    } else {

                        echo "📚";

                    }

                    ?>

                </div>

            <?php

            }

            ?>

        </div>


        <div class="resource-content">


            <div class="resource-title">

                <?php
                echo $resource_title;
                ?>

            </div>


            <div class="resource-line">

                <strong>
                    Category:
                </strong>

                <?php
                echo $resource_category;
                ?>

            </div>


            <div class="resource-line">

                <strong>
                    Condition:
                </strong>

                <?php
                echo $resource_condition;
                ?>

            </div>


            <div class="resource-line">

                <strong>
                    Available for:
                </strong>

                <?php
                echo $resource_type;
                ?>

            </div>


            <div class="resource-actions">

                <a
                    href="browse_resources.php"
                    class="resource-btn"
                >
                    View Details
                </a>


                <span class="resource-type">

                    <?php
                    echo $resource_type;
                    ?>

                </span>

            </div>

        </div>

    </div>

<?php

    }

} else {

?>

    <div
        class="panel"
        style="grid-column:1/-1;"
    >

        <div class="empty">

            📚 No resources available yet.

        </div>

    </div>

<?php

}

?>

</div>


<!-- =========================================
     THREE COLUMNS
     ========================================= -->

<div class="three-columns">


<!-- LATEST EXCHANGES -->

<div class="panel">


    <div class="panel-title">

        <h3>
            🔄 Latest Exchanges
        </h3>

        <a href="requests.php">
            View All →
        </a>

    </div>


<?php

if (
    $exchange_result &&
    mysqli_num_rows($exchange_result) > 0
) {

    while (
        $exchange =
        mysqli_fetch_assoc($exchange_result)
    ) {

        $exchange_title =
            htmlspecialchars(
                $exchange["title"]
            );

        $exchange_name =
            htmlspecialchars(
                $exchange["full_name"]
                ?? "Student"
            );

?>

        <div class="exchange-item">

            <div class="exchange-avatar">
                🔄
            </div>

            <div class="exchange-info">

                <h4>
                    <?php
                    echo $exchange_name;
                    ?>
                </h4>

                <p>

                    Accepted request for

                    <?php
                    echo $exchange_title;
                    ?>

                </p>

            </div>

        </div>

<?php

    }

} else {

?>

    <div class="empty">

        🔄 No completed exchanges yet.

    </div>

<?php

}

?>

</div>


<!-- RESOURCE REQUESTS -->

<div class="panel">


    <div class="panel-title">

        <h3>
            📢 Resource Requests
        </h3>

        <a href="requests.php">
            View All →
        </a>

    </div>


<?php

if (
    $request_result &&
    mysqli_num_rows($request_result) > 0
) {

    while (
        $request =
        mysqli_fetch_assoc($request_result)
    ) {

        $request_title =
            htmlspecialchars(
                $request["title"]
            );

        $request_category =
            htmlspecialchars(
                $request["category"]
            );

        $request_description =
            htmlspecialchars(
                $request["description"]
            );

?>

        <div class="request-card">

            <h4>

                📚

                <?php
                echo $request_title;
                ?>

            </h4>


            <p>

                <?php
                echo $request_description;
                ?>

            </p>


            <span class="request-tag">

                <?php
                echo $request_category;
                ?>

            </span>

        </div>

<?php

    }

} else {

?>

    <div class="empty">

        📢 No resource requests available.

    </div>

<?php

}

?>

</div>


<!-- =========================================
     CATEGORIES
     ========================================= -->

<div class="panel">


    <div class="panel-title">

        <h3>
            📂 Categories
        </h3>

        <a href="browse_resources.php">
            View All →
        </a>

    </div>


    <div class="category-grid">


        <!-- BOOKS -->

        <a
            href="browse_resources.php?category=Book"
            class="category"
        >

            <div class="category-icon">
                📚
            </div>

            <span>
                Books
            </span>

        </a>


        <!-- NOTES SHARING -->

        <a
            href="notes_sharing.php"
            class="category notes-category"
        >

            <div class="category-icon">
                📝
            </div>

            <span>
                Notes Sharing
            </span>

        </a>


        <!-- PAPERS -->

        <a
            href="browse_resources.php?category=Papers"
            class="category"
        >

            <div class="category-icon">
                📄
            </div>

            <span>
                Papers
            </span>

        </a>


        <!-- LAB MANUALS -->

        <a
            href="browse_resources.php?category=Lab%20Manuals"
            class="category"
        >

            <div class="category-icon">
                🧪
            </div>

            <span>
                Lab Manuals
            </span>

        </a>


        <!-- RATINGS -->

        <a
            href="ratings.php"
            class="category rating-category"
        >

            <div class="category-icon">
                ⭐
            </div>

            <span>
                Ratings & Reviews
            </span>

        </a>


        <!-- OTHER -->

        <a
            href="browse_resources.php?category=Other"
            class="category"
        >

            <div class="category-icon">
                📦
            </div>

            <span>
                Other
            </span>

        </a>


    </div>

</div>


</div>


<!-- =========================================
     NOTIFICATION SECTION
     ========================================= -->

<div class="notification-banner">


    <div class="notification-left">


        <div class="notification-icon">
            🔔
        </div>


        <div>

            <h3>

                CampusSwap Notifications


                <?php

                if (
                    $unread_notifications > 0
                ) {

                ?>

                    <span
                        class="notification-number"
                    >

                        <?php
                        echo $unread_notifications;
                        ?>

                        New

                    </span>

                <?php

                }

                ?>

            </h3>


            <p>

                <?php

                if (
                    $unread_notifications > 0
                ) {

                    echo "You have "
                        . $unread_notifications
                        . " unread notification"
                        . (
                            $unread_notifications > 1
                            ? "s."
                            : "."
                        );

                } else {

                    echo
                    "Stay updated about your CampusSwap activities.";

                }

                ?>

            </p>

        </div>

    </div>


    <a
        href="notifications.php"
        class="notification-button"
    >

        View Notifications →

    </a>


</div>


</main>


<!-- =========================================
     FOOTER
     ========================================= -->

<footer>


    <div class="footer-content">


        <div class="footer-logo">


            <div class="footer-logo-icon">
                🌿
            </div>


            <div>

                <h3>
                    CampusSwap
                </h3>

                <p>
                    Share. Exchange. Grow Together.
                </p>

            </div>

        </div>


        <div class="footer-links">


            <a href="dashboard.php">
                Home
            </a>


            <a href="browse_resources.php">
                Browse Resources
            </a>


            <a href="requests.php">
                Requests
            </a>


            <a href="notes_sharing.php">
                Notes Sharing
            </a>


            <a href="ratings.php">
                Ratings & Reviews
            </a>


            <a href="notifications.php">
                Notifications
            </a>


        </div>

    </div>


    <div class="copyright">

        © 2026 CampusSwap. All rights reserved.

    </div>


</footer>


</body>

</html>