<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$message = "";
$message_type = "";

/* Fetch current user details */
$sql = "SELECT id, full_name, email, college, department, semester, city, profile_photo
        FROM users
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {
    die("User profile not found.");
}


/* Update profile */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST['full_name']);
    $college = trim($_POST['college']);
    $department = trim($_POST['department']);
    $semester = (int) $_POST['semester'];
    $city = trim($_POST['city']);

    if ($full_name == "") {

        $message = "Please enter your full name.";
        $message_type = "error";

    } else {

        $update_sql = "UPDATE users
                       SET full_name = ?,
                           college = ?,
                           department = ?,
                           semester = ?,
                           city = ?
                       WHERE id = ?";

        $update_stmt = mysqli_prepare($conn, $update_sql);

        mysqli_stmt_bind_param(
            $update_stmt,
            "sssisi",
            $full_name,
            $college,
            $department,
            $semester,
            $city,
            $user_id
        );

        if (mysqli_stmt_execute($update_stmt)) {

            $_SESSION['user_name'] = $full_name;

            $message = "Profile updated successfully!";
            $message_type = "success";

            /* Update displayed values */
            $user['full_name'] = $full_name;
            $user['college'] = $college;
            $user['department'] = $department;
            $user['semester'] = $semester;
            $user['city'] = $city;

        } else {

            $message = "Unable to update profile. Please try again.";
            $message_type = "error";
        }

        mysqli_stmt_close($update_stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - CampusSwap</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4fbf6;
            color: #1f2937;
        }

        /* NAVBAR */

        .navbar {
            background: #ffffff;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dcefe2;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
            color: #198754;
        }

        .logo span {
            color: #222;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .nav-links a {
            text-decoration: none;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: #198754;
        }

        .logout {
            background: #198754;
            color: white !important;
            padding: 9px 16px;
            border-radius: 8px;
        }

        /* PAGE */

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 45px auto;
        }

        .page-title {
            margin-bottom: 25px;
        }

        .page-title h1 {
            font-size: 32px;
            color: #145c38;
            margin-bottom: 7px;
        }

        .page-title p {
            color: #6b7280;
        }

        /* MESSAGE */

        .message {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: #e7f8ed;
            color: #146c43;
            border: 1px solid #b7e4c7;
        }

        .error {
            background: #fdecec;
            color: #b42318;
            border: 1px solid #f3b5b5;
        }

        /* PROFILE LAYOUT */

        .profile-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 25px;
        }

        /* LEFT CARD */

        .profile-card {
            background: white;
            border-radius: 18px;
            padding: 30px 20px;
            text-align: center;
            border: 1px solid #dcefe2;
            box-shadow: 0 8px 25px rgba(25,135,84,0.08);
            height: fit-content;
        }

        .profile-avatar {
            width: 95px;
            height: 95px;
            border-radius: 50%;
            background: #dff5e7;
            color: #198754;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 38px;
            font-weight: bold;
            margin: 0 auto 18px;
        }

        .profile-card h2 {
            font-size: 21px;
            margin-bottom: 6px;
        }

        .profile-card p {
            color: #6b7280;
            font-size: 14px;
        }

        .profile-info {
            margin-top: 25px;
            text-align: left;
            border-top: 1px solid #edf4ef;
            padding-top: 18px;
        }

        .profile-info div {
            margin-bottom: 14px;
        }

        .profile-info strong {
            display: block;
            font-size: 12px;
            color: #198754;
            margin-bottom: 4px;
        }

        .profile-info span {
            font-size: 14px;
            color: #374151;
        }

        /* EDIT CARD */

        .edit-card {
            background: white;
            border-radius: 18px;
            padding: 30px;
            border: 1px solid #dcefe2;
            box-shadow: 0 8px 25px rgba(25,135,84,0.08);
        }

        .edit-card h2 {
            color: #145c38;
            margin-bottom: 6px;
        }

        .edit-card .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 7px;
            color: #374151;
        }

        input,
        select {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d5e6da;
            border-radius: 9px;
            font-size: 14px;
            outline: none;
            background: #fbfefc;
        }

        input:focus,
        select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 3px rgba(25,135,84,0.08);
        }

        input[readonly] {
            background: #f1f5f3;
            color: #6b7280;
        }

        .button-row {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .save-btn {
            background: #198754;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 9px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .save-btn:hover {
            background: #157347;
        }

        .back-btn {
            text-decoration: none;
            background: #eef7f1;
            color: #198754;
            padding: 12px 22px;
            border-radius: 9px;
            font-weight: 600;
            font-size: 14px;
        }

        .back-btn:hover {
            background: #dff2e5;
        }

        /* FOOTER */

        footer {
            margin-top: 60px;
            background: #123d29;
            color: white;
            text-align: center;
            padding: 22px;
            font-size: 13px;
        }

        /* MOBILE */

        @media (max-width: 750px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .container {
                width: 94%;
            }

        }

    </style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        Campus<span>Swap</span>
    </div>

    <div class="nav-links">

        <a href="dashboard.php">Home</a>

        <a href="browse_resources.php">
            Browse Resources
        </a>

        <a href="requests.php">
            Requests
        </a>

        <a href="notifications.php">
            🔔 Notifications
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</nav>


<!-- MAIN -->

<div class="container">

    <div class="page-title">

        <h1>My Profile 👤</h1>

        <p>
            Manage your CampusSwap student information.
        </p>

    </div>


    <?php if ($message != ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <div class="profile-grid">


        <!-- LEFT PROFILE CARD -->

        <div class="profile-card">

            <div class="profile-avatar">

                <?php
                echo strtoupper(
                    substr(
                        $user['full_name'],
                        0,
                        1
                    )
                );
                ?>

            </div>


            <h2>
                <?php echo htmlspecialchars($user['full_name']); ?>
            </h2>


            <p>
                CampusSwap Student
            </p>


            <div class="profile-info">

                <div>

                    <strong>Email</strong>

                    <span>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </span>

                </div>


                <div>

                    <strong>College</strong>

                    <span>
                        <?php
                        echo $user['college']
                            ? htmlspecialchars($user['college'])
                            : "Not added";
                        ?>
                    </span>

                </div>


                <div>

                    <strong>Department</strong>

                    <span>
                        <?php
                        echo $user['department']
                            ? htmlspecialchars($user['department'])
                            : "Not added";
                        ?>
                    </span>

                </div>


                <div>

                    <strong>City</strong>

                    <span>
                        <?php
                        echo $user['city']
                            ? htmlspecialchars($user['city'])
                            : "Not added";
                        ?>
                    </span>

                </div>

            </div>

        </div>


        <!-- EDIT PROFILE -->

        <div class="edit-card">

            <h2>
                Edit Profile
            </h2>

            <p class="subtitle">
                Update your personal and academic information.
            </p>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        value="<?php echo htmlspecialchars($user['full_name']); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                        readonly
                    >

                </div>


                <div class="form-row">


                    <div class="form-group">

                        <label>
                            College / School
                        </label>

                        <input
                            type="text"
                            name="college"
                            value="<?php echo htmlspecialchars($user['college']); ?>"
                            placeholder="Enter college or school"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Department / Course
                        </label>

                        <input
                            type="text"
                            name="department"
                            value="<?php echo htmlspecialchars($user['department']); ?>"
                            placeholder="Example: B.Sc. CS"
                        >

                    </div>

                </div>


                <div class="form-row">


                    <div class="form-group">

                        <label>
                            Semester / Class
                        </label>

                        <select name="semester">

                            <option value="0"
                                <?php
                                if ($user['semester'] == 0)
                                    echo "selected";
                                ?>>
                                Select
                            </option>

                            <option value="1"
                                <?php
                                if ($user['semester'] == 1)
                                    echo "selected";
                                ?>>
                                1
                            </option>

                            <option value="2"
                                <?php
                                if ($user['semester'] == 2)
                                    echo "selected";
                                ?>>
                                2
                            </option>

                            <option value="3"
                                <?php
                                if ($user['semester'] == 3)
                                    echo "selected";
                                ?>>
                                3
                            </option>

                            <option value="4"
                                <?php
                                if ($user['semester'] == 4)
                                    echo "selected";
                                ?>>
                                4
                            </option>

                            <option value="5"
                                <?php
                                if ($user['semester'] == 5)
                                    echo "selected";
                                ?>>
                                5
                            </option>

                            <option value="6"
                                <?php
                                if ($user['semester'] == 6)
                                    echo "selected";
                                ?>>
                                6
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            City
                        </label>

                        <input
                            type="text"
                            name="city"
                            value="<?php echo htmlspecialchars($user['city']); ?>"
                            placeholder="Enter your city"
                        >

                    </div>

                </div>


                <div class="button-row">

                    <button
                        type="submit"
                        class="save-btn"
                    >
                        💾 Save Changes
                    </button>


                    <a
                        href="dashboard.php"
                        class="back-btn"
                    >
                        ← Dashboard
                    </a>

                </div>


            </form>

        </div>

    </div>

</div>


<footer>

    © 2026 CampusSwap · Share. Exchange. Grow Together.

</footer>


</body>

</html>