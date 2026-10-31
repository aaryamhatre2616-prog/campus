<?php

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $education_level = trim($_POST["education_level"]);
    $course = trim($_POST["course"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        // Check if email already exists
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {

            $message = "This email is already registered.";

        } else {

            // Store password securely
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            /*
             * Existing users table:
             * full_name
             * email
             * password
             * college
             * department
             * semester
             */

            $college = $education_level;
            $department = $course;
            $semester = 0;

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (full_name, email, password, college, department, semester)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssi",
                $full_name,
                $email,
                $hashed_password,
                $college,
                $department,
                $semester
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Registration successful! You can now login.";

            } else {

                $message = "Registration failed. Please try again.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register - CampusSwap</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            background: #f3faf5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .register-container {
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 22px;
            overflow: hidden;
            display: flex;
            box-shadow: 0 15px 45px rgba(42, 89, 56, 0.12);
        }

        .register-info {
            width: 45%;
            background: linear-gradient(145deg, #328b55, #246c3e);
            color: white;
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 35px;
        }

        .register-info h1 {
            font-size: 38px;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .register-info p {
            font-size: 15px;
            line-height: 1.8;
            color: #e1f1e5;
            margin-bottom: 25px;
        }

        .benefit {
            display: flex;
            gap: 12px;
            align-items: center;
            margin: 13px 0;
            color: #edf8f0;
            font-size: 14px;
        }

        .benefit-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.16);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-form {
            width: 55%;
            padding: 45px;
        }

        .register-form h2 {
            color: #294735;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #718078;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 18px;
            width: 100%;
        }

        label {
            display: block;
            color: #405348;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        input,
        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8e5dc;
            border-radius: 8px;
            outline: none;
            background: #fbfdfb;
            color: #405348;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            border-color: #328b55;
            box-shadow: 0 0 0 3px rgba(50,139,85,0.08);
            background: white;
        }

        .register-btn {
            width: 100%;
            border: none;
            background: #328b55;
            color: white;
            padding: 13px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 5px;
        }

        .register-btn:hover {
            background: #267343;
        }

        .login-text {
            text-align: center;
            margin-top: 20px;
            color: #718078;
            font-size: 13px;
        }

        .login-text a {
            color: #328b55;
            font-weight: 700;
            text-decoration: none;
        }

        .note {
            background: #eef8f1;
            color: #56705e;
            padding: 10px 12px;
            border-radius: 7px;
            font-size: 12px;
            line-height: 1.5;
            margin-bottom: 18px;
        }

        .message {
            background: #eef8f1;
            color: #267343;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 600;
        }

        @media (max-width: 800px) {

            .register-container {
                flex-direction: column;
            }

            .register-info,
            .register-form {
                width: 100%;
            }

            .register-info {
                padding: 35px;
            }

            .register-form {
                padding: 35px;
            }

        }

        @media (max-width: 500px) {

            body {
                padding: 15px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .register-info h1 {
                font-size: 30px;
            }

            .register-form {
                padding: 25px;
            }

        }

    </style>

</head>


<body>


<div class="register-container">


    <!-- LEFT SIDE -->

    <div class="register-info">

        <div class="logo">
            🔄 CampusSwap
        </div>

        <h1>
            Join the CampusSwap Community
        </h1>

        <p>
            Create your account and start sharing,
            exchanging, borrowing and donating useful
            educational resources with other students.
        </p>

        <div class="benefit">
            <div class="benefit-icon">📚</div>
            <span>Share educational resources</span>
        </div>

        <div class="benefit">
            <div class="benefit-icon">🔄</div>
            <span>Exchange resources with students</span>
        </div>

        <div class="benefit">
            <div class="benefit-icon">🎁</div>
            <span>Donate resources you no longer need</span>
        </div>

        <div class="benefit">
            <div class="benefit-icon">🤝</div>
            <span>Help other students learn</span>
        </div>

    </div>


    <!-- RIGHT SIDE -->

    <div class="register-form">

        <h2>Create Account</h2>

        <p class="subtitle">
            Register as a student to get started.
        </p>


        <div class="note">
            💡 Younger students can take help from a
            parent or guardian while creating an account.
        </div>


        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <form method="POST" action="register.php">


            <div class="form-row">

                <div class="form-group">

                    <label>Full Name</label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter your full name"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

            </div>


            <div class="form-row">

                <div class="form-group">

                    <label>Education Level</label>

                    <select
                        name="education_level"
                        required
                    >

                        <option value="">
                            Select level
                        </option>

                        <option value="School">
                            School
                        </option>

                        <option value="Junior College">
                            Junior College
                        </option>

                        <option value="College">
                            College
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>Class / Course</label>

                    <input
                        type="text"
                        name="course"
                        placeholder="e.g. 9th / B.Sc. CS"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Create a password"
                    required
                >

            </div>


            <div class="form-group">

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="register-btn"
            >
                Create Account
            </button>


        </form>


        <div class="login-text">

            Already have an account?

            <a href="login.php">
                Login here
            </a>

        </div>

    </div>

</div>


</body>

</html>