<?php

session_start();

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, full_name, password FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];

            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Invalid email or password.";

        }

    } else {

        $message = "Invalid email or password.";

    }

    mysqli_stmt_close($stmt);
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

    <title>Login - CampusSwap</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f3faf5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .login-container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 22px;
            overflow: hidden;
            display: flex;
            box-shadow: 0 15px 45px rgba(42, 89, 56, 0.12);
        }

        /* LEFT SIDE */

        .login-info {
            width: 45%;
            background: linear-gradient(
                145deg,
                #328b55,
                #246c3e
            );
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

        .login-info h1 {
            font-size: 38px;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .login-info p {
            color: #e1f1e5;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 28px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 12px 0;
            font-size: 14px;
            color: #edf8f0;
        }

        .feature-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.16);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* RIGHT SIDE */

        .login-form {
            width: 55%;
            padding: 55px 45px;
        }

        .login-form h2 {
            color: #294735;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #718078;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .message {
            background: #fff0f0;
            color: #b33a3a;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #405348;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d8e5dc;
            border-radius: 8px;
            outline: none;
            background: #fbfdfb;
            color: #405348;
            font-size: 14px;
        }

        input:focus {
            border-color: #328b55;
            box-shadow: 0 0 0 3px rgba(50,139,85,0.08);
            background: white;
        }

        .login-btn {
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

        .login-btn:hover {
            background: #267343;
        }

        .register-text {
            text-align: center;
            margin-top: 22px;
            color: #718078;
            font-size: 13px;
        }

        .register-text a {
            color: #328b55;
            font-weight: 700;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {

            .login-container {
                flex-direction: column;
            }

            .login-info,
            .login-form {
                width: 100%;
            }

            .login-info {
                padding: 35px;
            }

            .login-form {
                padding: 35px;
            }

        }

        @media (max-width: 500px) {

            body {
                padding: 15px;
            }

            .login-info h1 {
                font-size: 30px;
            }

            .login-form {
                padding: 25px;
            }

        }

    </style>

</head>


<body>


<div class="login-container">


    <!-- LEFT SIDE -->

    <div class="login-info">

        <div class="logo">
            🔄 CampusSwap
        </div>

        <h1>
            Welcome Back!
        </h1>

        <p>
            Login to your CampusSwap account and
            continue sharing, exchanging and discovering
            useful educational resources.
        </p>


        <div class="feature">
            <div class="feature-icon">📚</div>
            <span>Access your resources</span>
        </div>


        <div class="feature">
            <div class="feature-icon">🔄</div>
            <span>Manage your exchanges</span>
        </div>


        <div class="feature">
            <div class="feature-icon">📢</div>
            <span>Track your requests</span>
        </div>


        <div class="feature">
            <div class="feature-icon">🔔</div>
            <span>Receive notifications</span>
        </div>

    </div>


    <!-- LOGIN FORM -->

    <div class="login-form">

        <h2>Login</h2>

        <p class="subtitle">
            Enter your account details to continue.
        </p>


        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <form method="POST" action="login.php">


            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Login
            </button>


        </form>


        <div class="register-text">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </div>

    </div>

</div>


</body>

</html>