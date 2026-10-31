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


/* =========================
   SUBMIT RATING
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $resource_id = (int) ($_POST["resource_id"] ?? 0);
    $rating = (int) ($_POST["rating"] ?? 0);
    $review = trim($_POST["review"] ?? "");

    if ($resource_id <= 0 || $rating < 1 || $rating > 5) {

        $message = "Please select a resource and rating.";
        $message_type = "error";

    } else {

        /* Get resource owner */

        $sql = "SELECT id, title, user_id
                FROM resources
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $resource_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $resource = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$resource) {

            $message = "Resource not found.";
            $message_type = "error";

        } elseif ((int)$resource["user_id"] === $user_id) {

            $message = "You cannot rate your own resource.";
            $message_type = "error";

        } else {

            $reviewed_user_id = (int) $resource["user_id"];


            /* Check duplicate rating */

            $check_sql = "SELECT id
                          FROM ratings
                          WHERE reviewer_id = ?
                          AND reviewed_user_id = ?
                          AND resource_id = ?";

            $check_stmt = mysqli_prepare($conn, $check_sql);

            mysqli_stmt_bind_param(
                $check_stmt,
                "iii",
                $user_id,
                $reviewed_user_id,
                $resource_id
            );

            mysqli_stmt_execute($check_stmt);

            $check_result = mysqli_stmt_get_result($check_stmt);

            $already_rated = mysqli_num_rows($check_result) > 0;

            mysqli_stmt_close($check_stmt);


            if ($already_rated) {

                $message =
                    "You have already rated this resource.";

                $message_type = "error";

            } else {

                /* Insert rating */

                $insert_sql = "INSERT INTO ratings
                               (
                                   reviewer_id,
                                   reviewed_user_id,
                                   resource_id,
                                   rating,
                                   review,
                                   created_at
                               )
                               VALUES (?, ?, ?, ?, ?, NOW())";

                $insert_stmt = mysqli_prepare(
                    $conn,
                    $insert_sql
                );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iiiis",
                    $user_id,
                    $reviewed_user_id,
                    $resource_id,
                    $rating,
                    $review
                );

                if (mysqli_stmt_execute($insert_stmt)) {

                    $message =
                        "Rating and review submitted successfully!";

                    $message_type = "success";

                } else {

                    $message =
                        "Unable to submit the rating.";

                    $message_type = "error";
                }

                mysqli_stmt_close($insert_stmt);
            }
        }
    }
}


/* =========================
   FETCH RESOURCES TO RATE
========================= */

$resource_sql = "SELECT
                    resources.id,
                    resources.title,
                    resources.category,
                    users.full_name
                 FROM resources
                 INNER JOIN users
                    ON resources.user_id = users.id
                 WHERE resources.user_id != ?
                 ORDER BY resources.id DESC";

$resource_stmt = mysqli_prepare(
    $conn,
    $resource_sql
);

mysqli_stmt_bind_param(
    $resource_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($resource_stmt);

$resource_result =
    mysqli_stmt_get_result($resource_stmt);


/* =========================
   FETCH RATINGS
========================= */

$rating_sql = "SELECT
                  ratings.id,
                  ratings.rating,
                  ratings.review,
                  ratings.created_at,
                  resources.title,
                  users.full_name AS reviewer_name,
                  reviewed_users.full_name AS reviewed_name
               FROM ratings

               INNER JOIN resources
                  ON ratings.resource_id = resources.id

               INNER JOIN users
                  ON ratings.reviewer_id = users.id

               INNER JOIN users AS reviewed_users
                  ON ratings.reviewed_user_id = reviewed_users.id

               ORDER BY ratings.id DESC";

$rating_result = mysqli_query(
    $conn,
    $rating_sql
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Ratings & Reviews - CampusSwap</title>

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


/* =========================
   NAVBAR
========================= */

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


/* =========================
   CONTAINER
========================= */

.container {
    width: 86%;
    max-width: 1150px;

    margin: 35px auto 60px;
}


/* =========================
   HEADER
========================= */

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


/* =========================
   MESSAGE
========================= */

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


/* =========================
   RATING FORM
========================= */

.rating-section {
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

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;

    font-size: 14px;
    font-weight: 600;

    color: #405148;

    margin-bottom: 8px;
}

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


/* =========================
   STAR RATING
========================= */

.star-rating {
    display: flex;
    flex-direction: row-reverse;

    justify-content: flex-end;

    gap: 5px;

    margin-top: 5px;
}

.star-rating input {
    display: none;
}

.star-rating label {
    font-size: 32px;

    color: #d7dfd9;

    cursor: pointer;

    transition: 0.2s;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #f4b400;
}


.submit-btn {
    padding: 12px 22px;

    border: none;

    border-radius: 10px;

    background: #2f914d;

    color: white;

    font-weight: 700;

    cursor: pointer;

    font-size: 14px;
}

.submit-btn:hover {
    background: #257b40;
}


/* =========================
   REVIEWS
========================= */

.reviews-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;
}

.reviews-header h2 {
    font-size: 23px;
    color: #246d3a;
}

.review-count {
    color: #718078;
    font-size: 13px;
}


/* Review Cards */

.reviews-grid {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;
}

.review-card {
    background: white;

    border: 1px solid #dceee1;

    border-radius: 16px;

    padding: 20px;

    box-shadow:
        0 6px 20px rgba(39, 107, 60, 0.05);
}

.review-card h3 {
    color: #294536;

    font-size: 18px;

    margin-bottom: 8px;
}

.resource-name {
    display: inline-block;

    background: #e8f6ec;

    color: #287542;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

    margin-bottom: 12px;
}


/* Stars Display */

.stars {
    color: #f4b400;

    font-size: 20px;

    letter-spacing: 2px;

    margin-bottom: 10px;
}

.review-text {
    color: #68776e;

    font-size: 14px;

    line-height: 1.6;

    margin-bottom: 15px;
}

.review-info {
    border-top: 1px solid #edf3ee;

    padding-top: 12px;

    color: #7b887f;

    font-size: 12px;

    line-height: 1.7;
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


/* =========================
   FOOTER
========================= */

footer {
    text-align: center;

    padding: 25px;

    color: #7a887f;

    font-size: 13px;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 700px) {

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

    .reviews-grid {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

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


    <!-- =========================
         HEADER
    ========================= -->

    <div class="page-header">

        <h1>⭐ Ratings & Reviews</h1>

        <p>
            Share your experience and help students build trust.
        </p>

    </div>


    <?php if ($message !== ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         RATING FORM
    ========================= -->

    <div class="rating-section">

        <div class="section-title">
            ⭐ Give a Rating
        </div>


        <form method="POST">


            <!-- Resource -->

            <div class="form-group">

                <label>
                    Select Resource *
                </label>

                <select
                    name="resource_id"
                    required
                >

                    <option value="">
                        Select a resource
                    </option>

                    <?php
                    if ($resource_result) {

                        while (
                            $resource =
                            mysqli_fetch_assoc(
                                $resource_result
                            )
                        ) {
                    ?>

                        <option
                            value="<?php
                            echo (int)$resource["id"];
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $resource["title"]
                            );
                            ?>

                            —
                            <?php
                            echo htmlspecialchars(
                                $resource["full_name"]
                            );
                            ?>

                        </option>

                    <?php
                        }
                    }
                    ?>

                </select>

            </div>


            <!-- Stars -->

            <div class="form-group">

                <label>
                    Your Rating *
                </label>


                <div class="star-rating">

                    <input
                        type="radio"
                        id="star5"
                        name="rating"
                        value="5"
                        required
                    >

                    <label for="star5">
                        ★
                    </label>


                    <input
                        type="radio"
                        id="star4"
                        name="rating"
                        value="4"
                    >

                    <label for="star4">
                        ★
                    </label>


                    <input
                        type="radio"
                        id="star3"
                        name="rating"
                        value="3"
                    >

                    <label for="star3">
                        ★
                    </label>


                    <input
                        type="radio"
                        id="star2"
                        name="rating"
                        value="2"
                    >

                    <label for="star2">
                        ★
                    </label>


                    <input
                        type="radio"
                        id="star1"
                        name="rating"
                        value="1"
                    >

                    <label for="star1">
                        ★
                    </label>

                </div>

            </div>


            <!-- Review -->

            <div class="form-group">

                <label>
                    Review
                </label>

                <textarea
                    name="review"
                    placeholder="Write your experience about the resource or exchange..."
                ></textarea>

            </div>


            <button
                type="submit"
                class="submit-btn"
            >
                ⭐ Submit Rating
            </button>


        </form>

    </div>


    <!-- =========================
         REVIEWS
    ========================= -->

    <div class="reviews-header">

        <h2>
            💬 Student Reviews
        </h2>

        <?php

        $review_count =
            $rating_result
            ? mysqli_num_rows($rating_result)
            : 0;

        ?>

        <span class="review-count">

            <?php
            echo $review_count;
            ?>

            reviews

        </span>

    </div>


    <?php if (
        $rating_result &&
        mysqli_num_rows($rating_result) > 0
    ): ?>


        <div class="reviews-grid">


            <?php
            while (
                $review =
                mysqli_fetch_assoc(
                    $rating_result
                )
            ):
            ?>


                <div class="review-card">


                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $review["reviewed_name"]
                        );
                        ?>

                    </h3>


                    <span class="resource-name">

                        📚

                        <?php
                        echo htmlspecialchars(
                            $review["title"]
                        );
                        ?>

                    </span>


                    <div class="stars">

                        <?php

                        $rating_value =
                            (int)$review["rating"];

                        echo str_repeat(
                            "★",
                            $rating_value
                        );

                        echo str_repeat(
                            "☆",
                            5 - $rating_value
                        );

                        ?>

                    </div>


                    <p class="review-text">

                        <?php

                        echo htmlspecialchars(
                            $review["review"]
                            ?: "No written review."
                        );

                        ?>

                    </p>


                    <div class="review-info">

                        👤 Reviewed by:

                        <?php
                        echo htmlspecialchars(
                            $review["reviewer_name"]
                        );
                        ?>

                        <br>

                        📅

                        <?php
                        echo date(
                            "d M Y",
                            strtotime(
                                $review["created_at"]
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
                ⭐ No Reviews Yet
            </h3>

            <p>
                Be the first student to share a rating!
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