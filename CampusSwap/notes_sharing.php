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

/* Upload Note */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($title === "" || $subject === "" || !isset($_FILES["note_file"])) {

        $message = "Please fill all required fields and select a file.";
        $message_type = "error";

    } else {

        $file = $_FILES["note_file"];

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $message = "There was an error uploading the file.";
            $message_type = "error";

        } else {

            $original_name = basename($file["name"]);
            $file_extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );

            $allowed_extensions = ["pdf", "doc", "docx", "txt"];

            if (!in_array($file_extension, $allowed_extensions)) {

                $message = "Only PDF, DOC, DOCX and TXT files are allowed.";
                $message_type = "error";

            } elseif ($file["size"] > 10 * 1024 * 1024) {

                $message = "File size must be less than 10 MB.";
                $message_type = "error";

            } else {

                /* Create uploads folder if it does not exist */
                $upload_directory = "uploads/notes/";

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                /* Generate unique file name */
                $new_file_name =
                    time() . "_" .
                    uniqid() . "." .
                    $file_extension;

                $file_path = $upload_directory . $new_file_name;

                if (move_uploaded_file(
                    $file["tmp_name"],
                    $file_path
                )) {

                    $sql = "INSERT INTO notes
                            (user_id, title, subject, description,
                             file_name, file_path)
                            VALUES (?, ?, ?, ?, ?, ?)";

                    $stmt = mysqli_prepare($conn, $sql);

                    mysqli_stmt_bind_param(
                        $stmt,
                        "isssss",
                        $user_id,
                        $title,
                        $subject,
                        $description,
                        $original_name,
                        $file_path
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        $message =
                            "Notes uploaded successfully!";

                        $message_type = "success";

                    } else {

                        $message =
                            "Unable to save note information.";

                        $message_type = "error";
                    }

                    mysqli_stmt_close($stmt);

                } else {

                    $message =
                        "Unable to upload the selected file.";

                    $message_type = "error";
                }
            }
        }
    }
}

/* Fetch shared notes */
$sql = "SELECT
            notes.id,
            notes.title,
            notes.subject,
            notes.description,
            notes.file_name,
            notes.file_path,
            notes.created_at,
            users.full_name
        FROM notes
        INNER JOIN users
            ON notes.user_id = users.id
        ORDER BY notes.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Notes Sharing - CampusSwap</title>

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

/* Main */

.container {
    width: 86%;
    max-width: 1200px;
    margin: 35px auto 60px;
}

/* Heading */

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

/* Upload Section */

.upload-section {
    background: white;
    border: 1px solid #dceee1;
    border-radius: 18px;
    padding: 28px;
    margin-bottom: 35px;
    box-shadow: 0 8px 25px rgba(39, 107, 60, 0.06);
}

.upload-title {
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
    box-shadow: 0 0 0 3px rgba(85, 169, 110, 0.1);
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

.upload-btn {
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

.upload-btn:hover {
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

/* Notes */

.notes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.notes-header h2 {
    font-size: 23px;
    color: #246d3a;
}

.notes-count {
    color: #718078;
    font-size: 13px;
}

.notes-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.note-card {
    background: white;
    border: 1px solid #dceee1;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 6px 20px rgba(39, 107, 60, 0.05);
    transition: 0.2s;
}

.note-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(39, 107, 60, 0.09);
}

.note-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #eaf7ed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
    margin-bottom: 14px;
}

.note-card h3 {
    font-size: 18px;
    color: #294536;
    margin-bottom: 8px;
}

.subject {
    display: inline-block;
    background: #e8f6ec;
    color: #287542;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 12px;
}

.note-description {
    color: #6e7d74;
    font-size: 13px;
    line-height: 1.6;
    min-height: 42px;
}

.note-info {
    border-top: 1px solid #edf3ee;
    margin-top: 15px;
    padding-top: 12px;
    color: #7b887f;
    font-size: 12px;
    line-height: 1.7;
}

.download-btn {
    display: block;
    text-align: center;
    text-decoration: none;
    background: #2f914d;
    color: white;
    padding: 10px;
    border-radius: 9px;
    margin-top: 15px;
    font-size: 13px;
    font-weight: 700;
}

.download-btn:hover {
    background: #257b40;
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

    .notes-grid {
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

    .notes-grid {
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
        <a href="dashboard.php">Dashboard</a>
        <a href="browse_resources.php">Resources</a>
        <a href="wishlist.php">Wishlist</a>
        <a href="notifications.php">Notifications</a>
        <a href="logout.php">Logout</a>
    </div>

</nav>


<div class="container">

    <!-- Page Header -->

    <div class="page-header">

        <h1>📚 Notes Sharing</h1>

        <p>
            Share useful study notes and help other students learn better.
        </p>

    </div>


    <?php if ($message !== ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- Upload -->

    <div class="upload-section">

        <div class="upload-title">
            📤 Share Your Notes
        </div>

        <form method="POST"
              enctype="multipart/form-data">

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Note Title *
                    </label>

                    <input
                        type="text"
                        name="title"
                        placeholder="Example: Java Programming Notes"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Subject *
                    </label>

                    <input
                        type="text"
                        name="subject"
                        placeholder="Example: Java Programming"
                        required
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Write a short description about these notes..."
                    ></textarea>

                </div>


                <div class="form-group full">

                    <label>
                        Select File *
                    </label>

                    <input
                        type="file"
                        name="note_file"
                        accept=".pdf,.doc,.docx,.txt"
                        required
                    >

                    <div class="file-info">
                        Accepted formats: PDF, DOC, DOCX, TXT | Maximum size: 10 MB
                    </div>

                </div>

            </div>


            <button
                type="submit"
                class="upload-btn"
            >
                📤 Upload Notes
            </button>

        </form>

    </div>


    <!-- Shared Notes -->

    <div class="notes-header">

        <h2>📖 Shared Notes</h2>

        <?php
        $note_count = $result ? mysqli_num_rows($result) : 0;
        ?>

        <span class="notes-count">
            <?php echo $note_count; ?> notes shared
        </span>

    </div>


    <?php if ($result && mysqli_num_rows($result) > 0): ?>

        <div class="notes-grid">

            <?php while ($note = mysqli_fetch_assoc($result)): ?>

                <div class="note-card">

                    <div class="note-icon">
                        📄
                    </div>

                    <h3>
                        <?php
                        echo htmlspecialchars($note["title"]);
                        ?>
                    </h3>

                    <span class="subject">
                        <?php
                        echo htmlspecialchars($note["subject"]);
                        ?>
                    </span>

                    <p class="note-description">

                        <?php
                        echo htmlspecialchars(
                            $note["description"] ?: "No description provided."
                        );
                        ?>

                    </p>

                    <div class="note-info">

                        👤 Shared by:
                        <?php
                        echo htmlspecialchars($note["full_name"]);
                        ?>
                        <br>

                        📅
                        <?php
                        echo date(
                            "d M Y",
                            strtotime($note["created_at"])
                        );
                        ?>
                        <br>

                        📎
                        <?php
                        echo htmlspecialchars($note["file_name"]);
                        ?>

                    </div>


                    <a
                        href="<?php echo htmlspecialchars($note["file_path"]); ?>"
                        target="_blank"
                        class="download-btn"
                    >
                        ⬇️ View / Download
                    </a>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <h3>📚 No Notes Shared Yet</h3>

            <p>
                Be the first student to share useful study notes!
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