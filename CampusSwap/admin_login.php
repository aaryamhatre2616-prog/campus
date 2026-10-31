<?php
session_start();
include "db.php";

$error = "";

if (isset($_POST["login"])) {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM users 
            WHERE email = ? AND role = 'Admin'
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);

        if ($password === $admin["password"]) {

            $_SESSION["user_id"] = $admin["id"];
            $_SESSION["user_name"] = $admin["full_name"];
            $_SESSION["role"] = "Admin";

            header("Location: admin_dashboard.php");
            exit();

        } else {
            $error = "Incorrect password.";
        }

    } else {
        $error = "Admin account not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | CampusSwap</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e9, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            width: 400px;
            background: white;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.12);
        }

        .logo {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #444;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #4caf50;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2e7d32;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #256628;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2e7d32;
            text-decoration: none;
        }

    </style>
</head>

<body>

<div class="login-box">

    <div class="logo">CampusSwap</div>

    <div class="subtitle">
        Share. Exchange. Grow Together.
    </div>

    <h2>Admin Login</h2>

    <?php if ($error != "") { ?>
        <div class="error">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST">

        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter admin email"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter admin password"
            required
        >

        <button type="submit" name="login">
            Login as Admin
        </button>

    </form>

    <a href="index.php" class="back">
        ← Back to CampusSwap
    </a>

</div>

</body>
</html>