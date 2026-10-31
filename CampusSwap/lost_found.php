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

/* Add Lost / Found Item */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = $_POST["status"] ?? "";
    $location = trim($_POST["location"] ?? "");

    if ($title === "" || $status === "" || $location === "") {

        $message = "Please fill all required fields.";
        $message_type = "error";

    } else {

        $photo_path = "";

        /* Photo Upload */
        if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {

            $original_name = basename($_FILES["photo"]["name"]);
            $extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );

            $allowed_extensions = ["jpg", "jpeg", "png", "webp"];

            if (!in_array($extension, $allowed_extensions)) {

                $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";
                $message_type = "error";

            } elseif ($_FILES["photo"]["size"] > 5 * 1024 * 1024) {

                $message = "Photo size must be less than 5 MB.";
                $message_type = "error";

            } else {

                $upload_directory = "uploads/lost_found/";

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                $new_file_name =
                    time() . "_" .
                    uniqid() . "." .
                    $extension;

                $photo_path =
                    $upload_directory . $new_file_name;

                if (!move_uploaded_file(
                    $_FILES["photo"]["tmp_name"],
                    $photo_path
                )) {

                    $message = "Unable to upload the photo.";
                    $message_type = "error";
                }
            }
        }

        /* Insert into database */
        if ($message_type !== "error") {

            $sql = "INSERT INTO lost_found
                    (user_id, title, description, category, photo, status, location)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "issssss",
                $user_id,
                $title,
                $description,
                $category,
                $photo_path,
                $status,
                $location
            );

            if (mysqli_stmt_execute($stmt)) {

                $message =
                    "Lost & Found item posted successfully!";

                $message_type = "success";

            } else {

                $message =
                    "Unable to save the item.";

                $message_type = "error";
            }

            mysqli_stmt_close($stmt);
        }
    }
}


/* Fetch Lost & Found Items */

$sql = "SELECT
            lost_found.id,
            lost_found.title,
            lost_found.description,
            lost_found.category,
            lost_found.photo,
            lost_found.status,
            lost_found.location,
            lost_found.created_at,
            users.full_name
        FROM lost_found
        INNER JOIN users
            ON lost_found.user_id = users.id
        ORDER BY lost_found.id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Lost & Found - CampusSwap</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4fbf6;
    color: #24352a;
}

/* Navbar */

.navbar {
    background: white;
    border-bottom: 1px solid #dceee1;
    padding: 16px 7%;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 24px;
    font-weight: 700;
    color: #238b45;
}

.nav-links {
    display: flex;
    gap: 22px;
    align-items: center;
}

.nav-links a {
    text-decoration: none;
    color: #52645a;
    font-size: 14px;
    font-weight: 600;
}

.nav-links a:hover {
    color: #238b45;
}

/* Container */

.container {
    width: 86%;
    max-width: 1200px;
    margin: 35px auto 60px;
}

/* Header */

.page-header {
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 32px;
    color: #1e7139;
    margin-bottom: 8px;
}

.page-header p {
    color: #718078;
    font-size: 15px;
}

/* Report Section */

.report-section {
    background: white;
    border: 1px solid #dceee1;
    border-radius: 18px;
    padding: 28px;
    margin-bottom: 35px;

    box-shadow:
        0 8px 25px rgba(39, 107, 60, 0.06);
}

.section-title {
    font-size: 21px;
    font-weight: 700;
    color: #246d3a;
    margin-bottom: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    font-size: 14px;
    font-weight: 600;
    color: #405148;
    margin-bottom: 8px;
}

input,
select,
textarea {
    width: 100%;
    padding: 12px 14px;

    border: 1px solid #cfe3d4;
    border-radius: 10px;

    outline: none;
    font-size: 14px;

    background: #fbfefc;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #55a96e;

    box-shadow:
        0 0 0 3px rgba(85, 169, 110, 0.1);
}

textarea {
    min-height: 100px;
    resize: vertical;
}

.file-info {
    margin-top: 7px;
    color: #7a8980;
    font-size: 12px;
}

.post-btn {
    margin-top: 20px;

    padding: 12px 22px;

    border: none;
    border-radius: 10px;

    background: #2f914d;
    color: white;

    font-weight: 700;
    cursor: pointer;
    font-size: 14px;
}

.post-btn:hover {
    background: #257b40;
}

/* Messages */

.message {
    padding: 13px 16px;
    border-radius: 10px;
    margin-bottom: 20px;

    font-size: 14px;
    font-weight: 600;
}

.success {
    background: #e9f8ed;
    color: #26753d;
    border: 1px solid #bfe4c8;
}

.error {
    background: #fff0f0;
    color: #b33a3a;
    border: 1px solid #efc4c4;
}

/* Items Header */

.items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 18px;
}

.items-header h2 {
    font-size: 23px;
    color: #246d3a;
}

.items-count {
    color: #718078;
    font-size: 13px;
}

/* Cards */

.items-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.item-card {
    background: white;

    border: 1px solid #dceee1;
    border-radius: 16px;

    padding: 20px;

    box-shadow:
        0 6px 20px rgba(39, 107, 60, 0.05);

    transition: 0.2s;
}

.item-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 10px 25px rgba(39, 107, 60, 0.09);
}

/* Photo */

.item-photo {
    width: 100%;
    height: 170px;

    object-fit: cover;

    border-radius: 12px;
    margin-bottom: 15px;
}

.no-photo {
    width: 100%;
    height: 170px;

    background: #eaf7ed;

    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 45px;

    margin-bottom: 15px;
}

/* Status */

.status {
    display: inline-block;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 12px;
    font-weight: 700;

    margin-bottom: 10px;
}

.status-lost {
    background: #fff0f0;
    color: #b33a3a;
}

.status-found {
    background: #e9f8ed;
    color: #287542;
}

/* Card Text */

.item-card h3 {
    font-size: 18px;
    color: #294536;

    margin-bottom: 8px;
}

.category {
    display: inline-block;

    background: #e8f6ec;
    color: #287542;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 12px;
    font-weight: 700;

    margin-bottom: 12px;
}

.item-description {
    color: #6e7d74;

    font-size: 13px;
    line-height: 1.6;

    min-height: 42px;
}

.item-info {
    border-top: 1px solid #edf3ee;

    margin-top: 15px;
    padding-top: 12px;

    color: #7b887f;

    font-size: 12px;
    line-height: 1.8;
}

/* Empty */

.empty {
    background: white;

    border: 1px dashed #c9dfcf;

    border-radius: 16px;

    padding: 45px;

    text-align: center;

    color: #78877e;
}

/* Footer */

footer {
    text-align: center;

    padding: 25px;

    color: #7a887f;

    font-size: 13px;
}

/* Responsive */

@media (max-width: 900px) {

    .items-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 650px) {

    .navbar {
        padding: 15px 5%;
    }

    .nav-links {
        gap: 10px;
    }

    .nav-links a {
        font-size: 12px;
    }

    .container {
        width: 92%;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: auto;
    }

    .items-grid {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<!-- Navbar -->

<nav class="navbar">

    <div class="logo">
        CampusSwap
    </div>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="browse_resources.php">
            Resources
        </a>

        <a href="notes_sharing.php">
            Notes
        </a>

        <a href="wishlist.php">
            Wishlist
        </a>

        <a href="notifications.php">
            Notifications
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<div class="container">

    <!-- Header -->

    <div class="page-header">

        <h1>🔎 Lost & Found</h1>

        <p>
            Report lost items and help students find their belongings.
        </p>

    </div>


    <?php if ($message !== ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- Report Form -->

    <div class="report-section">

        <div class="section-title">
            📢 Report Lost or Found Item
        </div>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Item Title *
                    </label>

                    <input
                        type="text"
                        name="title"
                        placeholder="Example: Black College Bag"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Status *
                    </label>

                    <select name="status" required>

                        <option value="">
                            Select Status
                        </option>

                        <option value="Lost">
                            Lost
                        </option>

                        <option value="Found">
                            Found
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Category
                    </label>

                    <select name="category">

                        <option value="">
                            Select Category
                        </option>

                        <option value="Books">
                            Books
                        </option>

                        <option value="Stationery">
                            Stationery
                        </option>

                        <option value="Electronics">
                            Electronics
                        </option>

                        <option value="ID Card">
                            ID Card
                        </option>

                        <option value="Bag">
                            Bag
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Location *
                    </label>

                    <input
                        type="text"
                        name="location"
                        placeholder="Example: College Library"
                        required
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Describe the item and any useful details..."
                    ></textarea>

                </div>


                <div class="form-group full">

                    <label>
                        Item Photo
                    </label>

                    <input
                        type="file"
                        name="photo"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <div class="file-info">
                        JPG, JPEG, PNG or WEBP | Maximum size: 5 MB
                    </div>

                </div>

            </div>


            <button
                type="submit"
                class="post-btn"
            >
                📢 Post Lost & Found Report
            </button>

        </form>

    </div>


    <!-- Reports -->

    <div class="items-header">

        <h2>
            📋 Lost & Found Reports
        </h2>

        <?php
        $item_count = $result
            ? mysqli_num_rows($result)
            : 0;
        ?>

        <span class="items-count">
            <?php echo $item_count; ?> reports
        </span>

    </div>


    <?php if ($result && mysqli_num_rows($result) > 0): ?>

        <div class="items-grid">

            <?php while ($item = mysqli_fetch_assoc($result)): ?>

                <div class="item-card">

                    <?php if (!empty($item["photo"])): ?>

                        <img
                            src="<?php echo htmlspecialchars($item["photo"]); ?>"
                            class="item-photo"
                            alt="Lost or Found Item"
                        >

                    <?php else: ?>

                        <div class="no-photo">
                            🔎
                        </div>

                    <?php endif; ?>


                    <?php if ($item["status"] === "Lost"): ?>

                        <span class="status status-lost">
                            🔴 Lost
                        </span>

                    <?php else: ?>

                        <span class="status status-found">
                            🟢 Found
                        </span>

                    <?php endif; ?>


                    <h3>
                        <?php
                        echo htmlspecialchars($item["title"]);
                        ?>
                    </h3>


                    <?php if (!empty($item["category"])): ?>

                        <span class="category">
                            <?php
                            echo htmlspecialchars(
                                $item["category"]
                            );
                            ?>
                        </span>

                    <?php endif; ?>


                    <p class="item-description">

                        <?php
                        echo htmlspecialchars(
                            $item["description"]
                            ?: "No description provided."
                        );
                        ?>

                    </p>


                    <div class="item-info">

                        📍 Location:
                        <?php
                        echo htmlspecialchars(
                            $item["location"]
                        );
                        ?>

                        <br>

                        👤 Reported by:
                        <?php
                        echo htmlspecialchars(
                            $item["full_name"]
                        );
                        ?>

                        <br>

                        📅
                        <?php
                        echo date(
                            "d M Y",
                            strtotime(
                                $item["created_at"]
                            )
                        );
                        ?>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <h3>
                🔎 No Lost & Found Reports Yet
            </h3>

            <p>
                Be the first student to report a lost or found item.
            </p>

        </div>

    <?php endif; ?>

</div>


<footer>

    CampusSwap © 2026 |
    Share. Exchange. Grow Together.

</footer>

</body>

</html>