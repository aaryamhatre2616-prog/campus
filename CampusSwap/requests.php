<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["full_name"];

// Get all resource requests
$sql = "SELECT id, user_id, title, description, category, status, created_at
        FROM requests
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Resource Requests - CampusSwap</title>

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

        a {
            text-decoration: none;
        }

        /* NAVBAR */

        .navbar {
            background: white;
            padding: 15px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 15px rgba(0,0,0,0.06);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #328b55;
            font-size: 25px;
            font-weight: 800;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: #e2f4e7;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: #526158;
            font-size: 14px;
            font-weight: 600;
        }

        .profile-btn {
            background: #e7f5eb;
            color: #328b55 !important;
            padding: 9px 15px;
            border-radius: 8px;
        }

        .logout-btn {
            background: #328b55;
            color: white !important;
            padding: 9px 17px;
            border-radius: 8px;
        }

        /* MAIN */

        .container {
            width: 86%;
            max-width: 1100px;
            margin: 40px auto 70px;
        }

        .page-header {
            background: linear-gradient(135deg, #328b55, #246c3e);
            color: white;
            padding: 35px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 12px 30px rgba(42,100,61,0.14);
        }

        .page-header h1 {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #e4f3e8;
            line-height: 1.7;
            font-size: 14px;
        }

        .top-actions {
            margin-bottom: 25px;
        }

        .post-btn {
            display: inline-block;
            background: #328b55;
            color: white;
            padding: 11px 18px;
            border-radius: 9px;
            font-weight: 700;
            font-size: 14px;
        }

        /* REQUEST CARDS */

        .request-card {
            background: white;
            border: 1px solid #e5eee8;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 18px;
            box-shadow: 0 5px 18px rgba(42,89,56,0.05);
        }

        .request-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .request-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .request-icon {
            width: 48px;
            height: 48px;
            background: #e6f5ea;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .request-title h2 {
            font-size: 19px;
            color: #294735;
        }

        .category {
            display: inline-block;
            margin-top: 5px;
            background: #e4f5e8;
            color: #388451;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            background: #fff4d6;
            color: #9a761c;
            font-size: 12px;
            font-weight: 700;
        }

        .request-description {
            margin: 18px 0;
            color: #65736b;
            font-size: 14px;
            line-height: 1.7;
        }

        .request-info {
            border-top: 1px solid #edf2ee;
            padding-top: 14px;
            color: #7b887f;
            font-size: 12px;
        }

        .empty {
            background: white;
            border: 1px solid #e5eee8;
            border-radius: 16px;
            padding: 45px;
            text-align: center;
            color: #718078;
        }

        /* FOOTER */

        footer {
            background: #244c32;
            color: white;
            text-align: center;
            padding: 28px;
        }

        footer h3 {
            margin-bottom: 7px;
        }

        footer p {
            color: #d5e8db;
            font-size: 13px;
        }

        @media (max-width: 700px) {

            .navbar {
                padding: 13px 5%;
            }

            .nav-links a:not(.logout-btn) {
                display: none;
            }

            .container {
                width: 92%;
            }

            .page-header {
                padding: 28px;
            }

            .page-header h1 {
                font-size: 25px;
            }

            .request-top {
                align-items: flex-start;
            }

        }

    </style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">

        <div class="logo-icon">
            🔄
        </div>

        CampusSwap

    </div>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="browse_resources.php">
            Resources
        </a>

        <a href="requests.php">
            Requests
        </a>

        <a href="#" class="profile-btn">
            👤 Profile
        </a>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</nav>


<!-- MAIN -->

<div class="container">

    <div class="page-header">

        <h1>
            📢 Student Resource Requests
        </h1>

        <p>
            View resources requested by students and help them
            by sharing, exchanging or donating useful educational materials.
        </p>

    </div>


    <div class="top-actions">

        <a href="post_request.php" class="post-btn">
            + Post New Request
        </a>

    </div>


    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>

            <div class="request-card">

                <div class="request-top">

                    <div class="request-title">

                        <div class="request-icon">
                            📚
                        </div>

                        <div>

                            <h2>
                                <?php echo htmlspecialchars($row["title"]); ?>
                            </h2>

                            <span class="category">
                                <?php echo htmlspecialchars($row["category"]); ?>
                            </span>

                        </div>

                    </div>

                    <span class="status">
                        <?php echo htmlspecialchars($row["status"]); ?>
                    </span>

                </div>


                <div class="request-description">

                    <?php echo nl2br(htmlspecialchars($row["description"])); ?>

                </div>


                <div class="request-info">

                    📅 Requested on:
                    <?php echo date("d M Y", strtotime($row["created_at"])); ?>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">

            <h2>No Resource Requests Yet</h2>

            <p>
                Be the first student to post a resource request.
            </p>

        </div>

    <?php endif; ?>

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