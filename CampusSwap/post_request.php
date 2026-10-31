<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);

    if ($title == "" || $category == "" || $description == "") {

        $message = "Please fill all the fields.";
        $message_type = "error";

    } else {

        $sql = "INSERT INTO requests 
                (user_id, title, description, category, status, created_at)
                VALUES (?, ?, ?, ?, 'Pending', NOW())";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $user_id,
            $title,
            $description,
            $category
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Resource request posted successfully!";
            $message_type = "success";

        } else {

            $message = "Something went wrong. Please try again.";
            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Post Request - CampusSwap</title>

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

.navbar {
    background: white;
    padding: 15px 6%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 3px 15px rgba(0,0,0,0.06);
}

.logo {
    color: #328b55;
    font-size: 25px;
    font-weight: 800;
}

.nav-links {
    display: flex;
    gap: 15px;
}

.nav-links a {
    text-decoration: none;
    color: #526158;
    font-size: 14px;
    font-weight: 600;
    padding: 9px 15px;
    border-radius: 8px;
}

.nav-links a:hover {
    background: #e7f5eb;
    color: #328b55;
}

.logout {
    background: #328b55;
    color: white !important;
}

.container {
    width: 90%;
    max-width: 750px;
    margin: 45px auto;
}

.card {
    background: white;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(42, 89, 56, 0.08);
    border: 1px solid #e5eee8;
}

h1 {
    color: #294735;
    margin-bottom: 8px;
}

.subtitle {
    color: #718078;
    margin-bottom: 28px;
    line-height: 1.6;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #3c5545;
}

input,
select,
textarea {
    width: 100%;
    padding: 13px;
    border: 1px solid #dce8df;
    border-radius: 10px;
    outline: none;
    font-size: 14px;
    background: #fbfefc;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #328b55;
}

textarea {
    min-height: 130px;
    resize: vertical;
}

button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: #328b55;
    color: white;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
}

button:hover {
    background: #267343;
}

.message {
    padding: 13px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.success {
    background: #e6f6ea;
    color: #267343;
}

.error {
    background: #fdeaea;
    color: #b33a3a;
}

.back {
    display: inline-block;
    margin-top: 18px;
    color: #328b55;
    text-decoration: none;
    font-weight: 600;
}

</style>

</head>

<body>

<nav class="navbar">

    <div class="logo">
        🔄 CampusSwap
    </div>

    <div class="nav-links">

        <a href="dashboard.php">Dashboard</a>

        <a href="browse_resources.php">Resources</a>

        <a href="my_requests.php">My Requests</a>

        <a href="logout.php" class="logout">Logout</a>

    </div>

</nav>


<div class="container">

    <div class="card">

        <h1>📢 Post a Resource Request</h1>

        <p class="subtitle">
            Tell other students what educational resource you are looking for.
            Someone may have the resource and can share or exchange it with you.
        </p>


        <?php if ($message != "") { ?>

            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <form method="POST">

            <div class="form-group">

                <label>Resource Title</label>

                <input
                    type="text"
                    name="title"
                    placeholder="Example: Python Programming Book"
                    required
                >

            </div>


            <div class="form-group">

                <label>Category</label>

                <select name="category" required>

                    <option value="">Select Category</option>

                    <option value="Book">Book</option>

                    <option value="Notes">Notes</option>

                    <option value="Question Paper">Question Paper</option>

                    <option value="Study Material">Study Material</option>

                    <option value="Other">Other</option>

                </select>

            </div>


            <div class="form-group">

                <label>Description</label>

                <textarea
                    name="description"
                    placeholder="Describe the resource you are looking for..."
                    required
                ></textarea>

            </div>


            <button type="submit">
                Post Request
            </button>

        </form>


        <a href="dashboard.php" class="back">
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>

</html>