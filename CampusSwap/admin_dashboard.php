<?php
session_start();
include "db.php";

/* Check Admin Login */
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "Admin"
) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION["user_name"];

/* Total Students */
$student_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role='Student'"
);
$student_data = mysqli_fetch_assoc($student_query);
$total_students = $student_data["total"];

/* Total Resources */
$resource_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM resources"
);
$resource_data = mysqli_fetch_assoc($resource_query);
$total_resources = $resource_data["total"];

/* Available Resources */
$available_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM resources WHERE status='Available'"
);
$available_data = mysqli_fetch_assoc($available_query);
$available_resources = $available_data["total"];

/* Pending Requests */
$pending_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM requests WHERE status='Pending'"
);
$pending_data = mysqli_fetch_assoc($pending_query);
$pending_requests = $pending_data["total"];

/* Open Resource Requests */
$open_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM requests WHERE status='Open'"
);
$open_data = mysqli_fetch_assoc($open_query);
$open_requests = $open_data["total"];

/* Active Borrowings */
$borrow_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM borrow_records WHERE status='Borrowed'"
);
$borrow_data = mysqli_fetch_assoc($borrow_query);
$active_borrowings = $borrow_data["total"];

/* Total Ratings */
$rating_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM ratings"
);
$rating_data = mysqli_fetch_assoc($rating_query);
$total_ratings = $rating_data["total"];

/* Latest Students */
$latest_students = mysqli_query(
    $conn,
    "SELECT full_name, email, college, department, created_at
     FROM users
     WHERE role='Student'
     ORDER BY id DESC
     LIMIT 5"
);

/* Latest Resources */
$latest_resources = mysqli_query(
    $conn,
    "SELECT r.title, r.category, r.exchange_type, r.status, u.full_name
     FROM resources r
     LEFT JOIN users u ON r.user_id = u.id
     ORDER BY r.id DESC
     LIMIT 5"
);

/* Latest Requests */
$latest_requests = mysqli_query(
    $conn,
    "SELECT title, category, status, created_at
     FROM requests
     ORDER BY id DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | CampusSwap</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f8f5;
            color: #333;
        }

        /* NAVBAR */

        .navbar {
            height: 70px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
            color: #2e7d32;
        }

        .logo span {
            color: #555;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .logout {
            text-decoration: none;
            background: #2e7d32;
            color: white;
            padding: 9px 16px;
            border-radius: 7px;
            font-size: 14px;
        }

        .logout:hover {
            background: #256628;
        }

        /* MAIN */

        .container {
            width: 92%;
            max-width: 1250px;
            margin: 35px auto;
        }

        .welcome {
            background: linear-gradient(135deg, #2e7d32, #66bb6a);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .welcome h1 {
            margin-bottom: 8px;
        }

        .welcome p {
            opacity: 0.9;
        }

        /* STAT CARDS */

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 23px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
            border-left: 5px solid #4caf50;
        }

        .stat-icon {
            font-size: 27px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: bold;
            color: #2e7d32;
        }

        .stat-title {
            color: #777;
            margin-top: 5px;
            font-size: 14px;
        }

        /* SECTION */

        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title h2 {
            font-size: 20px;
            color: #333;
        }

        /* MANAGE BUTTON */

        .manage-btn {
            display: inline-block;
            text-decoration: none;
            background: #2e7d32;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            transition: 0.2s;
        }

        .manage-btn:hover {
            background: #256628;
            transform: translateY(-1px);
        }

        /* ADMIN MANAGEMENT BUTTONS */

        .management-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .management-btn {
            display: block;
            text-decoration: none;
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
            padding: 17px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            transition: 0.2s;
        }

        .management-btn:hover {
            background: #2e7d32;
            color: white;
            transform: translateY(-2px);
        }

        /* TABLE */

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 13px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* STATUS */

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .available {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .open {
            background: #e3f2fd;
            color: #1565c0;
        }

        .borrowed {
            background: #fce4ec;
            color: #ad1457;
        }

        .other {
            background: #eeeeee;
            color: #555;
        }

        /* TWO COLUMN */

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        /* EMPTY */

        .empty {
            text-align: center;
            padding: 25px;
            color: #888;
        }

        /* FOOTER */

        footer {
            text-align: center;
            padding: 25px;
            color: #777;
            font-size: 13px;
        }

        /* MOBILE */

        @media (max-width: 900px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .two-column {
                grid-template-columns: 1fr;
            }

            .management-buttons {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .navbar {
                padding: 0 15px;
            }

            .admin-badge {
                display: none;
            }

            .container {
                width: 94%;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .welcome {
                padding: 22px;
            }

            .manage-btn {
                padding: 8px 11px;
                font-size: 12px;
            }

        }

    </style>

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        Campus<span>Swap</span>
    </div>

    <div class="admin-info">

        <div class="admin-badge">
            👑 Admin: <?php echo htmlspecialchars($admin_name); ?>
        </div>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>


<!-- MAIN -->

<div class="container">

    <!-- WELCOME -->

    <div class="welcome">

        <h1>
            Welcome, <?php echo htmlspecialchars($admin_name); ?> 👋
        </h1>

        <p>
            Manage CampusSwap students, resources and requests from one place.
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats">

        <div class="stat-card">

            <div class="stat-icon">👨‍🎓</div>

            <div class="stat-number">
                <?php echo $total_students; ?>
            </div>

            <div class="stat-title">
                Total Students
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">📚</div>

            <div class="stat-number">
                <?php echo $total_resources; ?>
            </div>

            <div class="stat-title">
                Total Resources
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">⏳</div>

            <div class="stat-number">
                <?php echo $pending_requests; ?>
            </div>

            <div class="stat-title">
                Pending Requests
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">🔄</div>

            <div class="stat-number">
                <?php echo $active_borrowings; ?>
            </div>

            <div class="stat-title">
                Active Borrowings
            </div>

        </div>

    </div>


    <!-- SECONDARY STATISTICS -->

    <div class="stats">

        <div class="stat-card">

            <div class="stat-icon">✅</div>

            <div class="stat-number">
                <?php echo $available_resources; ?>
            </div>

            <div class="stat-title">
                Available Resources
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">📢</div>

            <div class="stat-number">
                <?php echo $open_requests; ?>
            </div>

            <div class="stat-title">
                Open Resource Requests
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">⭐</div>

            <div class="stat-number">
                <?php echo $total_ratings; ?>
            </div>

            <div class="stat-title">
                Ratings & Reviews
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">🌱</div>

            <div class="stat-number">
                <?php echo $total_resources - $available_resources; ?>
            </div>

            <div class="stat-title">
                Used / Requested Resources
            </div>

        </div>

    </div>


    <!-- LATEST STUDENTS -->

    <div class="section">

        <div class="section-title">

            <h2>👨‍🎓 Latest Students</h2>

            <a href="manage_students.php" class="manage-btn">
                Manage Students →
            </a>

        </div>


        <div class="table-container">

            <table>

                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>College</th>
                    <th>Department</th>
                </tr>


                <?php

                if (mysqli_num_rows($latest_students) > 0) {

                    while ($student = mysqli_fetch_assoc($latest_students)) {

                ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($student["full_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($student["email"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($student["college"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($student["department"]); ?>
                        </td>

                    </tr>

                <?php

                    }

                } else {

                    echo '<tr><td colspan="4" class="empty">
                            No students registered yet.
                          </td></tr>';

                }

                ?>

            </table>

        </div>

    </div>


    <!-- TWO COLUMNS -->

    <div class="two-column">


        <!-- LATEST RESOURCES -->

        <div class="section">

            <div class="section-title">

                <h2>📚 Latest Resources</h2>

                <a href="manage_resources.php" class="manage-btn">
                    Manage Resources →
                </a>

            </div>


            <div class="table-container">

                <table>

                    <tr>
                        <th>Resource</th>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>


                    <?php

                    if (mysqli_num_rows($latest_resources) > 0) {

                        while ($resource = mysqli_fetch_assoc($latest_resources)) {

                            $status_class = "other";

                            if ($resource["status"] == "Available") {
                                $status_class = "available";
                            }

                    ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($resource["title"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($resource["category"]); ?>
                            </td>

                            <td>

                                <span class="status <?php echo $status_class; ?>">

                                    <?php echo htmlspecialchars($resource["status"]); ?>

                                </span>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                        echo '<tr>
                                <td colspan="3" class="empty">
                                    No resources added yet.
                                </td>
                              </tr>';

                    }

                    ?>

                </table>

            </div>

        </div>


        <!-- LATEST REQUESTS -->

        <div class="section">

            <div class="section-title">

                <h2>📢 Latest Requests</h2>

                <a href="manage_requests.php" class="manage-btn">
                    Manage Requests →
                </a>

            </div>


            <div class="table-container">

                <table>

                    <tr>
                        <th>Request</th>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>


                    <?php

                    if (mysqli_num_rows($latest_requests) > 0) {

                        while ($request = mysqli_fetch_assoc($latest_requests)) {

                            $status_class = "other";

                            if ($request["status"] == "Pending") {
                                $status_class = "pending";
                            }

                            if ($request["status"] == "Open") {
                                $status_class = "open";
                            }

                    ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($request["title"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($request["category"]); ?>
                            </td>

                            <td>

                                <span class="status <?php echo $status_class; ?>">

                                    <?php echo htmlspecialchars($request["status"]); ?>

                                </span>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                        echo '<tr>
                                <td colspan="3" class="empty">
                                    No requests available.
                                </td>
                              </tr>';

                    }

                    ?>

                </table>

            </div>

        </div>

    </div>


    <!-- ADMIN MANAGEMENT -->

    <div class="section">

        <div class="section-title">

            <h2>🛠️ Admin Management</h2>

        </div>

        <p style="color:#777; line-height:1.7;">

            Use the management options below to control students,
            resources and student requests.

        </p>


        <!-- MANAGEMENT BUTTONS -->

        <div class="management-buttons">

            <a href="manage_students.php" class="management-btn">
                👨‍🎓 Manage Students →
            </a>

            <a href="manage_resources.php" class="management-btn">
                📚 Manage Resources →
            </a>

            <a href="manage_requests.php" class="management-btn">
                📢 Manage Requests & Exchanges →
            </a>

        </div>

    </div>

</div>


<!-- FOOTER -->

<footer>

    © 2026 CampusSwap — Share. Exchange. Grow Together.

</footer>

</body>

</html>