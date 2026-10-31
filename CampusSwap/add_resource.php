<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION["user_id"];

    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);
    $exchange_type = trim($_POST["exchange_type"]);
    $condition_item = trim($_POST["condition_item"]);

    $photo = "";

    $status = "Available";

    $sql = "INSERT INTO resources
            (user_id, title, category, description, exchange_type,
             condition_item, photo, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "isssssss",
        $user_id,
        $title,
        $category,
        $description,
        $exchange_type,
        $condition_item,
        $photo,
        $status
    );

    if (mysqli_stmt_execute($stmt)) {

        $message = "Resource uploaded successfully!";

    } else {

        $message = "Error: " . mysqli_error($conn);

    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Upload Resource - CampusSwap</title>

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
    padding: 15px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.06);
}

.logo {
    font-size: 24px;
    font-weight: 800;
    color: #328b55;
}

.back {
    text-decoration: none;
    color: #328b55;
    font-weight: 600;
}

.container {
    width: 90%;
    max-width: 800px;
    margin: 45px auto;
}

.card {
    background: white;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(40,80,50,0.08);
    border: 1px solid #e3eee6;
}

.card h1 {
    color: #294735;
    margin-bottom: 8px;
}

.card .subtitle {
    color: #748078;
    font-size: 14px;
    margin-bottom: 28px;
}

.message {
    background: #e5f6e9;
    color: #287345;
    padding: 12px 15px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 14px;
}

.form-group {
    margin-bottom: 19px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
    font-weight: 600;
    color: #405448;
}

input,
select,
textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #d8e6dc;
    border-radius: 9px;
    outline: none;
    font-size: 14px;
    background: #fbfdfb;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #328b55;
    background: white;
}

textarea {
    min-height: 110px;
    resize: vertical;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

button {
    width: 100%;
    border: none;
    background: #328b55;
    color: white;
    padding: 14px;
    border-radius: 9px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 8px;
}

button:hover {
    background: #267343;
}

@media(max-width:650px) {

    .row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .card {
        padding: 25px;
    }

}

</style>

</head>

<body>

<nav class="navbar">

    <div class="logo">
        🔄 CampusSwap
    </div>

    <a href="dashboard.php" class="back">
        ← Back to Dashboard
    </a>

</nav>


<div class="container">

    <div class="card">

        <h1>Upload Resource 📚</h1>

        <p class="subtitle">
            Share a useful educational resource with other students.
        </p>


        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <form method="POST">


            <div class="form-group">

                <label>Resource Title</label>

                <input
                    type="text"
                    name="title"
                    placeholder="Example: Java Programming Book"
                    required
                >

            </div>


            <div class="row">


                <div class="form-group">

                    <label>Category</label>

                    <select name="category" required>

                        <option value="">Select Category</option>

                        <option value="Book">
                            Book
                        </option>

                        <option value="Notes">
                            Notes
                        </option>

                        <option value="Question Paper">
                            Question Paper
                        </option>

                        <option value="Study Material">
                            Study Material
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>Condition</label>

                    <select name="condition_item" required>

                        <option value="">
                            Select Condition
                        </option>

                        <option value="New">
                            New
                        </option>

                        <option value="Good">
                            Good
                        </option>

                        <option value="Used">
                            Used
                        </option>

                    </select>

                </div>


            </div>


            <div class="form-group">

                <label>Description</label>

                <textarea
                    name="description"
                    placeholder="Describe your resource..."
                    required
                ></textarea>

            </div>


            <div class="form-group">

                <label>Exchange Type</label>

                <select name="exchange_type" required>

                    <option value="">
                        Select Exchange Type
                    </option>

                    <option value="Exchange">
                        Exchange
                    </option>

                    <option value="Borrow">
                        Borrow
                    </option>

                    <option value="Donate">
                        Donate
                    </option>

                </select>

            </div>


            <button type="submit">
                Upload Resource
            </button>


        </form>

    </div>

</div>

</body>

</html>