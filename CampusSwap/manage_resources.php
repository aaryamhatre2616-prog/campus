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

/* Delete Resource */
if (isset($_POST["delete_resource"])) {

    $resource_id = intval($_POST["resource_id"]);

    $delete_sql = "DELETE FROM resources WHERE id = ?";

    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $resource_id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Resource deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete resource. It may be connected to another record.";
        $message_type = "error";
    }

    mysqli_stmt_close($stmt);
}


/* Get All Resources */
$resources_sql = "
    SELECT 
        r.id,
        r.title,
        r.category,
        r.description,
        r.exchange_type,
        r.condition_item,
        r.status,
        r.created_at,
        u.full_name
    FROM resources r
    LEFT JOIN users u ON r.user_id = u.id
    ORDER BY r.id DESC
";

$resources = mysqli_query($conn, $resources_sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Resources | CampusSwap</title>

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

        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .dashboard-btn {
            text-decoration: none;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 9px 15px;
            border-radius: 7px;
            font-size: 14px;
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
            max-width: 1350px;
            margin: 35px auto;
        }

        .page-header {
            background: linear-gradient(135deg, #2e7d32, #66bb6a);
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


        /* MESSAGE */

        .message {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .error {
            background: #ffebee;
            color: #c62828;
        }


        /* CARD */

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h2 {
            font-size: 21px;
        }

        .count {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }


        /* TABLE */

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
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
            vertical-align: middle;
        }

        tr:hover {
            background: #fafafa;
        }


        /* RESOURCE TITLE */

        .resource-title {
            font-weight: bold;
            color: #333;
        }

        .owner {
            color: #666;
            font-size: 13px;
        }

        .description {
            max-width: 230px;
            color: #777;
            line-height: 1.4;
        }


        /* BADGES */

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .exchange {
            background: #e3f2fd;
            color: #1565c0;
        }

        .borrow {
            background: #fff3e0;
            color: #e65100;
        }

        .donate {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .available {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .requested {
            background: #fff3cd;
            color: #856404;
        }

        .exchanged {
            background: #fce4ec;
            color: #ad1457;
        }

        .condition {
            background: #f1f1f1;
            color: #555;
        }


        /* DELETE BUTTON */

        .delete-btn {
            border: none;
            background: #ffebee;
            color: #c62828;
            padding: 8px 12px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }

        .delete-btn:hover {
            background: #ffcdd2;
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

            .dashboard-btn {
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


<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        Campus<span>Swap</span>
    </div>


    <div class="nav-right">

        <div class="admin-badge">
            👑 Admin: <?php echo htmlspecialchars($admin_name); ?>
        </div>


        <a href="admin_dashboard.php" class="dashboard-btn">
            Dashboard
        </a>


        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>



<!-- MAIN -->

<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <h1>📚 Manage Resources</h1>

        <p>
            View and manage all resources shared by CampusSwap students.
        </p>

    </div>



    <!-- MESSAGE -->

    <?php if (isset($message)) { ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php } ?>



    <!-- RESOURCE CARD -->

    <div class="card">


        <div class="card-header">

            <h2>
                All Resources
            </h2>


            <div class="count">

                <?php echo mysqli_num_rows($resources); ?>
                Resources

            </div>

        </div>



        <div class="table-container">


            <table>


                <tr>

                    <th>Resource</th>

                    <th>Owner</th>

                    <th>Category</th>

                    <th>Type</th>

                    <th>Condition</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>



                <?php

                if (mysqli_num_rows($resources) > 0) {

                    while ($resource = mysqli_fetch_assoc($resources)) {

                        /* Exchange Type Class */

                        $type_class = "exchange";

                        if ($resource["exchange_type"] == "Borrow") {
                            $type_class = "borrow";
                        }

                        if ($resource["exchange_type"] == "Donate") {
                            $type_class = "donate";
                        }


                        /* Status Class */

                        $status_class = "condition";

                        if ($resource["status"] == "Available") {
                            $status_class = "available";
                        }

                        if ($resource["status"] == "Requested") {
                            $status_class = "requested";
                        }

                        if ($resource["status"] == "Exchanged") {
                            $status_class = "exchanged";
                        }

                ?>



                    <tr>


                        <!-- RESOURCE -->

                        <td>

                            <div class="resource-title">

                                <?php
                                echo htmlspecialchars($resource["title"]);
                                ?>

                            </div>

                        </td>



                        <!-- OWNER -->

                        <td>

                            <div class="owner">

                                <?php

                                echo htmlspecialchars(
                                    $resource["full_name"] ?? "Unknown"
                                );

                                ?>

                            </div>

                        </td>



                        <!-- CATEGORY -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $resource["category"]
                            );

                            ?>

                        </td>



                        <!-- TYPE -->

                        <td>

                            <span class="badge <?php echo $type_class; ?>">

                                <?php

                                echo htmlspecialchars(
                                    $resource["exchange_type"]
                                );

                                ?>

                            </span>

                        </td>



                        <!-- CONDITION -->

                        <td>

                            <span class="badge condition">

                                <?php

                                echo htmlspecialchars(
                                    $resource["condition_item"]
                                );

                                ?>

                            </span>

                        </td>



                        <!-- STATUS -->

                        <td>

                            <span class="badge <?php echo $status_class; ?>">

                                <?php

                                echo htmlspecialchars(
                                    $resource["status"]
                                );

                                ?>

                            </span>

                        </td>



                        <!-- DELETE -->

                        <td>

                            <form
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this resource?');"
                            >

                                <input
                                    type="hidden"
                                    name="resource_id"
                                    value="<?php echo $resource["id"]; ?>"
                                >


                                <button
                                    type="submit"
                                    name="delete_resource"
                                    class="delete-btn"
                                >

                                    🗑 Delete

                                </button>

                            </form>

                        </td>


                    </tr>



                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="7" class="empty">

                            No resources have been added yet.

                        </td>

                    </tr>

                <?php

                }

                ?>


            </table>

        </div>

    </div>


</div>



<!-- FOOTER -->

<footer>

    © 2026 CampusSwap — Share. Exchange. Grow Together.

</footer>


</body>

</html>