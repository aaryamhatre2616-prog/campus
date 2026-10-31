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


/* =========================
   ACCEPT REQUEST
========================= */

if (isset($_GET["accept"])) {

    $request_id = intval($_GET["accept"]);

    $update = mysqli_query(
        $conn,
        "UPDATE requests 
         SET status='Accepted' 
         WHERE id=$request_id"
    );

    header("Location: manage_requests.php");
    exit();
}


/* =========================
   REJECT REQUEST
========================= */

if (isset($_GET["reject"])) {

    $request_id = intval($_GET["reject"]);

    $update = mysqli_query(
        $conn,
        "UPDATE requests 
         SET status='Rejected' 
         WHERE id=$request_id"
    );

    header("Location: manage_requests.php");
    exit();
}


/* =========================
   GET ALL REQUESTS
========================= */

$requests = mysqli_query(
    $conn,
    "SELECT 
        r.id,
        r.title,
        r.description,
        r.category,
        r.status,
        r.created_at,
        u.full_name,
        u.email
     FROM requests r
     LEFT JOIN users u ON r.user_id = u.id
     ORDER BY r.id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Requests | CampusSwap</title>

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
            width: 94%;
            max-width: 1300px;
            margin: 35px auto;
        }


        /* HEADER */

        .page-header {
            background: linear-gradient(
                135deg,
                #2e7d32,
                #66bb6a
            );

            color: white;

            padding: 30px;

            border-radius: 16px;

            margin-bottom: 25px;
        }

        .page-header h1 {
            margin-bottom: 8px;
        }

        .page-header p {
            opacity: 0.9;
        }


        /* BACK BUTTON */

        .back-btn {
            display: inline-block;

            text-decoration: none;

            background: white;

            color: #2e7d32;

            padding: 9px 15px;

            border-radius: 8px;

            font-size: 13px;

            font-weight: bold;

            margin-top: 18px;
        }

        .back-btn:hover {
            background: #f1f8f2;
        }


        /* SECTION */

        .section {
            background: white;

            border-radius: 15px;

            padding: 25px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);
        }


        .section-title {
            margin-bottom: 20px;
        }

        .section-title h2 {
            font-size: 20px;
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

            vertical-align: top;
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

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .accepted {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .rejected {
            background: #ffebee;
            color: #c62828;
        }

        .open {
            background: #e3f2fd;
            color: #1565c0;
        }

        .closed {
            background: #eeeeee;
            color: #555;
        }

        .other {
            background: #eeeeee;
            color: #555;
        }


        /* ACTION BUTTONS */

        .action-btn {
            display: inline-block;

            text-decoration: none;

            padding: 7px 11px;

            border-radius: 6px;

            font-size: 12px;

            font-weight: bold;

            margin: 2px;
        }

        .accept-btn {
            background: #2e7d32;
            color: white;
        }

        .accept-btn:hover {
            background: #256628;
        }

        .reject-btn {
            background: #c62828;
            color: white;
        }

        .reject-btn:hover {
            background: #a51f1f;
        }


        /* EMPTY */

        .empty {
            text-align: center;

            padding: 35px;

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

        @media (max-width: 700px) {

            .navbar {
                padding: 0 15px;
            }

            .admin-badge {
                display: none;
            }

            .container {
                width: 94%;
            }

            .page-header {
                padding: 22px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<div class="navbar">

    <div class="logo">
        Campus<span>Swap</span>
    </div>

    <div class="admin-info">

        <div class="admin-badge">
            👑 Admin:
            <?php echo htmlspecialchars($admin_name); ?>
        </div>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>



<!-- =========================
     MAIN
========================= -->

<div class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>
            📢 Manage Requests & Exchanges
        </h1>

        <p>
            Review student requests and manage their request status.
        </p>

        <a
            href="admin_dashboard.php"
            class="back-btn"
        >
            ← Back to Dashboard
        </a>

    </div>



    <!-- REQUESTS -->

    <div class="section">

        <div class="section-title">

            <h2>
                📋 All Student Requests
            </h2>

        </div>


        <div class="table-container">

            <table>

                <tr>

                    <th>
                        Student
                    </th>

                    <th>
                        Request
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Description
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Action
                    </th>

                </tr>


                <?php

                if (mysqli_num_rows($requests) > 0) {

                    while ($request = mysqli_fetch_assoc($requests)) {

                        $status_class = "other";

                        if ($request["status"] == "Pending") {
                            $status_class = "pending";
                        }

                        elseif ($request["status"] == "Accepted") {
                            $status_class = "accepted";
                        }

                        elseif ($request["status"] == "Rejected") {
                            $status_class = "rejected";
                        }

                        elseif ($request["status"] == "Open") {
                            $status_class = "open";
                        }

                        elseif ($request["status"] == "Closed") {
                            $status_class = "closed";
                        }

                ?>


                <tr>


                    <!-- STUDENT -->

                    <td>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $request["full_name"]
                            );
                            ?>
                        </strong>

                        <br>

                        <small style="color:#777;">

                            <?php
                            echo htmlspecialchars(
                                $request["email"]
                            );
                            ?>

                        </small>

                    </td>


                    <!-- REQUEST -->

                    <td>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $request["title"]
                            );
                            ?>

                        </strong>

                    </td>


                    <!-- CATEGORY -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $request["category"]
                        );
                        ?>

                    </td>


                    <!-- DESCRIPTION -->

                    <td>

                        <?php

                        $description =
                            $request["description"];

                        if (strlen($description) > 80) {

                            echo htmlspecialchars(
                                substr($description, 0, 80)
                            ) . "...";

                        } else {

                            echo htmlspecialchars(
                                $description
                            );

                        }

                        ?>

                    </td>


                    <!-- STATUS -->

                    <td>

                        <span
                            class="status <?php echo $status_class; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $request["status"]
                            );
                            ?>

                        </span>

                    </td>


                    <!-- ACTION -->

                    <td>

                        <?php

                        if (
                            $request["status"] == "Pending" ||
                            $request["status"] == "Open"
                        ) {

                        ?>

                            <a
                                href="manage_requests.php?accept=<?php echo $request["id"]; ?>"
                                class="action-btn accept-btn"
                                onclick="return confirm('Accept this request?');"
                            >
                                ✓ Accept
                            </a>


                            <a
                                href="manage_requests.php?reject=<?php echo $request["id"]; ?>"
                                class="action-btn reject-btn"
                                onclick="return confirm('Reject this request?');"
                            >
                                ✕ Reject
                            </a>

                        <?php

                        } else {

                            echo '<span style="color:#999;">
                                    No action
                                  </span>';

                        }

                        ?>

                    </td>


                </tr>


                <?php

                    }

                } else {

                    echo '
                    <tr>

                        <td
                            colspan="6"
                            class="empty"
                        >

                            📭 No student requests found.

                        </td>

                    </tr>';

                }

                ?>


            </table>

        </div>

    </div>


</div>



<!-- FOOTER -->

<footer>

    © 2026 CampusSwap —
    Share. Exchange. Grow Together.

</footer>


</body>

</html>