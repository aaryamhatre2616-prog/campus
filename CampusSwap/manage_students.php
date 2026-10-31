<?php
session_start();
include "db.php";

/* Check Admin Login */
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: admin_login.php");
    exit();
}

/* Delete Student */
if (isset($_POST["delete_student"])) {

    $student_id = intval($_POST["student_id"]);

    $delete_sql = "DELETE FROM users WHERE id = ? AND role = 'Student'";

    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Student deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete student.";
        $message_type = "error";
    }
}

/* Get All Students */
$students_sql = "SELECT id, full_name, email, college, department, semester, city, created_at
                 FROM users
                 WHERE role = 'Student'
                 ORDER BY id DESC";

$students = mysqli_query($conn, $students_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Students | CampusSwap</title>

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
            max-width: 1250px;
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

        tr:hover {
            background: #fafafa;
        }

        /* DELETE */

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

        @media (max-width: 900px) {

            .navbar {
                padding: 0 20px;
            }

            .admin-badge {
                display: none;
            }

            .container {
                width: 94%;
            }

        }

        @media (max-width: 600px) {

            .navbar {
                padding: 0 12px;
            }

            .dashboard-btn {
                display: none;
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
            👑 Admin
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

    <div class="page-header">

        <h1>👨‍🎓 Manage Students</h1>

        <p>
            View and manage all registered CampusSwap students.
        </p>

    </div>


    <?php if (isset($message)) { ?>

        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <div class="card">

        <div class="card-header">

            <h2>Registered Students</h2>

            <div class="count">
                <?php echo mysqli_num_rows($students); ?> Students
            </div>

        </div>


        <div class="table-container">

            <table>

                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>College</th>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>City</th>
                    <th>Action</th>
                </tr>


                <?php

                if (mysqli_num_rows($students) > 0) {

                    while ($student = mysqli_fetch_assoc($students)) {

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

                        <td>
                            <?php echo htmlspecialchars($student["semester"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($student["city"]); ?>
                        </td>

                        <td>

                            <form method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this student?');">

                                <input
                                    type="hidden"
                                    name="student_id"
                                    value="<?php echo $student["id"]; ?>"
                                >

                                <button
                                    type="submit"
                                    name="delete_student"
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
                            No students registered yet.
                        </td>

                    </tr>

                <?php

                }

                ?>

            </table>

        </div>

    </div>

</div>


<footer>

    © 2026 CampusSwap — Share. Exchange. Grow Together.

</footer>

</body>

</html>