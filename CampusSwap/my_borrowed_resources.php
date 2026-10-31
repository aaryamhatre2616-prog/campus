<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

$sql = "SELECT 
            br.id,
            br.resource_id,
            br.borrow_date,
            br.return_date,
            br.status,
            r.title,
            r.category,
            r.description,
            r.condition_item
        FROM borrow_records br
        INNER JOIN resources r
            ON br.resource_id = r.id
        WHERE br.borrower_id = ?
        ORDER BY br.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Borrowed Resources - CampusSwap</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4fbf6;
    color: #243b2a;
}

.navbar {
    background: white;
    padding: 18px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}

.logo {
    font-size: 24px;
    font-weight: bold;
    color: #2e7d32;
}

.navbar a {
    text-decoration: none;
    color: #355e3b;
    margin-left: 20px;
    font-weight: 600;
}

.container {
    width: 86%;
    margin: 40px auto;
}

.heading {
    margin-bottom: 25px;
}

.heading h1 {
    margin-bottom: 8px;
    color: #245c2a;
}

.heading p {
    color: #66756a;
}

.resources {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 22px;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 24px;
    box-shadow: 0 5px 20px rgba(40, 90, 50, 0.08);
    border: 1px solid #e1f0e4;
}

.card h2 {
    margin-top: 0;
    color: #286b32;
}

.info {
    margin: 9px 0;
    color: #59675d;
}

.status {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 20px;
    background: #dff3e3;
    color: #28743a;
    font-weight: bold;
    font-size: 13px;
    margin-top: 8px;
}

.return-btn {
    display: inline-block;
    margin-top: 18px;
    padding: 11px 18px;
    background: #3f8f4a;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
}

.return-btn:hover {
    background: #2f7338;
}

.empty {
    background: white;
    padding: 35px;
    border-radius: 15px;
    text-align: center;
    color: #66756a;
    box-shadow: 0 5px 20px rgba(40, 90, 50, 0.07);
}

.back {
    display: inline-block;
    margin-bottom: 25px;
    color: #2e7d32;
    text-decoration: none;
    font-weight: bold;
}

</style>
</head>

<body>

<div class="navbar">

    <div class="logo">
        CampusSwap
    </div>

    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="browse_resources.php">Browse Resources</a>
        <a href="logout.php">Logout</a>
    </div>

</div>

<div class="container">

    <a href="dashboard.php" class="back">
        ← Back to Dashboard
    </a>

    <div class="heading">
        <h1>My Borrowed Resources</h1>
        <p>View the resources you have borrowed and return them when finished.</p>
    </div>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <div class="resources">

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <div class="card">

                    <h2>
                        <?php echo htmlspecialchars($row["title"]); ?>
                    </h2>

                    <div class="info">
                        <strong>Category:</strong>
                        <?php echo htmlspecialchars($row["category"]); ?>
                    </div>

                    <div class="info">
                        <strong>Condition:</strong>
                        <?php echo htmlspecialchars($row["condition_item"]); ?>
                    </div>

                    <div class="info">
                        <strong>Description:</strong>
                        <?php echo htmlspecialchars($row["description"]); ?>
                    </div>

                    <div class="info">
                        <strong>Borrow Date:</strong>
                        <?php echo htmlspecialchars($row["borrow_date"]); ?>
                    </div>

                    <?php if (!empty($row["return_date"])) { ?>

                        <div class="info">
                            <strong>Return Date:</strong>
                            <?php echo htmlspecialchars($row["return_date"]); ?>
                        </div>

                    <?php } ?>

                    <div class="status">
                        <?php echo htmlspecialchars($row["status"]); ?>
                    </div>

                    <?php if ($row["status"] == "Borrowed") { ?>

                        <br>

                        <a
                            href="return_resource.php?id=<?php echo (int)$row["id"]; ?>"
                            class="return-btn"
                        >
                            Return Resource
                        </a>

                    <?php } ?>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty">

            <h2>No Borrowed Resources</h2>

            <p>
                You have not borrowed any resources yet.
            </p>

            <a href="browse_resources.php" class="return-btn">
                Browse Resources
            </a>

        </div>

    <?php } ?>

</div>

</body>
</html>