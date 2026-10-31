<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";


/* =========================================
   CATEGORY FILTER
   ========================================= */

$selected_category = strtolower(
    trim($_GET["category"] ?? "")
);

$category_condition = "";
$page_heading = "Browse Resources 📚";
$page_description =
    "Discover books, notes and other educational resources shared by students.";


/* BOOKS */

if ($selected_category == "books") {

    $category_condition =
        "AND LOWER(category) IN ('book', 'books')";

    $page_heading = "Books 📚";

    $page_description =
        "Browse educational books shared by students.";

}


/* PAPERS */

elseif ($selected_category == "papers") {

    $category_condition =
        "AND LOWER(category) IN (
            'paper',
            'papers',
            'question paper',
            'question papers'
        )";

    $page_heading = "Papers 📄";

    $page_description =
        "Browse previous year papers and question papers.";

}


/* LAB MANUALS */

elseif ($selected_category == "lab_manuals") {

    $category_condition =
        "AND LOWER(category) IN (
            'lab manual',
            'lab manuals',
            'lab_manual',
            'lab_manuals'
        )";

    $page_heading = "Lab Manuals 🧪";

    $page_description =
        "Browse practical and laboratory manuals shared by students.";

}


/* OTHER */

elseif ($selected_category == "other") {

    $category_condition =
        "AND LOWER(category) = 'other'";

    $page_heading = "Other Resources 📦";

    $page_description =
        "Browse other useful educational resources.";

}


/* =========================================
   GET RESOURCES
   ========================================= */

$sql = "SELECT
            id,
            title,
            category,
            description,
            exchange_type,
            condition_item,
            status,
            created_at
        FROM resources
        WHERE status = 'Available'
        $category_condition
        ORDER BY id DESC";


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    <?php echo htmlspecialchars($page_heading); ?>
    - CampusSwap
</title>


<style>

/* =========================================
   BASIC
   ========================================= */

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


/* =========================================
   NAVBAR
   ========================================= */

.navbar {

    background: white;

    padding: 15px 7%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 3px 15px rgba(0,0,0,0.06);

}

.logo {

    font-size: 24px;

    font-weight: 800;

    color: #328b55;

}

.nav-links {

    display: flex;

    gap: 15px;

    align-items: center;

}

.nav-links a {

    text-decoration: none;

    color: #328b55;

    font-weight: 600;

    font-size: 14px;

}

.dashboard-btn {

    background: #e5f5e9;

    padding: 9px 15px;

    border-radius: 8px;

}


/* =========================================
   MAIN
   ========================================= */

.container {

    width: 88%;

    max-width: 1200px;

    margin: 40px auto 60px;

}


/* =========================================
   HEADING
   ========================================= */

.heading {

    margin-bottom: 25px;

}

.heading h1 {

    font-size: 32px;

    color: #294735;

    margin-bottom: 8px;

}

.heading p {

    color: #718078;

    font-size: 14px;

}


/* =========================================
   CATEGORY FILTER BUTTONS
   ========================================= */

.category-filter {

    background: white;

    border: 1px solid #e3eee6;

    border-radius: 14px;

    padding: 15px;

    margin-bottom: 25px;

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

}

.filter-btn {

    text-decoration: none;

    padding: 9px 15px;

    border-radius: 20px;

    background: #f1f8f3;

    color: #328b55;

    border: 1px solid #dcebe0;

    font-size: 13px;

    font-weight: 600;

    transition: 0.2s;

}

.filter-btn:hover {

    background: #e1f3e6;

}

.filter-btn.active {

    background: #328b55;

    color: white;

    border-color: #328b55;

}


/* =========================================
   SEARCH
   ========================================= */

.search-box {

    background: white;

    padding: 18px;

    border-radius: 14px;

    border: 1px solid #e3eee6;

    margin-bottom: 30px;

}

.search-box input {

    width: 100%;

    padding: 13px 15px;

    border: 1px solid #d8e6dc;

    border-radius: 9px;

    outline: none;

    font-size: 14px;

}

.search-box input:focus {

    border-color: #328b55;

}


/* =========================================
   RESOURCE GRID
   ========================================= */

.resource-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

}


/* =========================================
   RESOURCE CARD
   ========================================= */

.resource-card {

    background: white;

    border: 1px solid #e2eee5;

    border-radius: 17px;

    padding: 23px;

    box-shadow:
        0 5px 18px rgba(42,89,56,0.06);

    transition: 0.3s;

}

.resource-card:hover {

    transform: translateY(-5px);

    box-shadow:
        0 12px 28px rgba(42,89,56,0.10);

}


/* =========================================
   RESOURCE TOP
   ========================================= */

.resource-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;

}

.resource-icon {

    width: 52px;

    height: 52px;

    background: #e6f5ea;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

}

.category {

    background: #e4f5e8;

    color: #328b55;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

}


/* =========================================
   TITLE
   ========================================= */

.resource-card h2 {

    font-size: 18px;

    color: #294735;

    margin-bottom: 9px;

}


/* =========================================
   DESCRIPTION
   ========================================= */

.description {

    color: #718078;

    font-size: 13px;

    line-height: 1.6;

    min-height: 42px;

    margin-bottom: 17px;

}


/* =========================================
   INFORMATION
   ========================================= */

.info {

    border-top:
        1px solid #edf2ee;

    padding-top: 14px;

    margin-bottom: 17px;

}

.info-row {

    display: flex;

    justify-content: space-between;

    margin-bottom: 8px;

    font-size: 12px;

}

.info-label {

    color: #7b887f;

}

.info-value {

    color: #405448;

    font-weight: 600;

}

.status {

    color: #328b55;

}


/* =========================================
   ACTION BUTTON
   ========================================= */

.exchange-btn {

    display: block;

    width: 100%;

    text-align: center;

    text-decoration: none;

    background: #328b55;

    color: white;

    padding: 11px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

}

.exchange-btn:hover {

    background: #267343;

}


/* =========================================
   EMPTY
   ========================================= */

.empty {

    background: white;

    border: 1px solid #e3eee6;

    border-radius: 16px;

    padding: 50px;

    text-align: center;

    grid-column: 1 / -1;

}

.empty-icon {

    font-size: 45px;

    margin-bottom: 15px;

}

.empty h2 {

    margin-bottom: 8px;

}

.empty p {

    color: #718078;

    font-size: 14px;

}


/* =========================================
   FOOTER
   ========================================= */

footer {

    background: #244c32;

    color: white;

    text-align: center;

    padding: 28px;

}

footer p {

    color: #d5e8db;

    font-size: 13px;

    margin-top: 5px;

}


/* =========================================
   RESPONSIVE
   ========================================= */

@media(max-width:950px) {

    .resource-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width:650px) {

    .navbar {

        padding: 14px 5%;

    }

    .container {

        width: 92%;

    }

    .resource-grid {

        grid-template-columns: 1fr;

    }

    .heading h1 {

        font-size: 27px;

    }

    .category-filter {

        flex-direction: column;

    }

    .filter-btn {

        text-align: center;

    }

}

</style>

</head>


<body>


<!-- =========================================
     NAVBAR
     ========================================= -->

<nav class="navbar">

    <div class="logo">

        🔄 CampusSwap

    </div>


    <div class="nav-links">

        <a
            href="dashboard.php"
            class="dashboard-btn"
        >
            ← Dashboard
        </a>

    </div>

</nav>


<!-- =========================================
     MAIN
     ========================================= -->

<div class="container">


    <!-- HEADING -->

    <div class="heading">

        <h1>

            <?php
            echo htmlspecialchars($page_heading);
            ?>

        </h1>

        <p>

            <?php
            echo htmlspecialchars($page_description);
            ?>

        </p>

    </div>


    <!-- =====================================
         CATEGORY FILTER
         ===================================== -->

    <div class="category-filter">


        <!-- ALL -->

        <a
            href="browse_resources.php"
            class="filter-btn
            <?php
            echo ($selected_category == "")
                ? "active"
                : "";
            ?>"
        >
            📚 All Resources
        </a>


        <!-- BOOKS -->

        <a
            href="browse_resources.php?category=books"
            class="filter-btn
            <?php
            echo ($selected_category == "books")
                ? "active"
                : "";
            ?>"
        >
            📘 Books
        </a>


        <!-- PAPERS -->

        <a
            href="browse_resources.php?category=papers"
            class="filter-btn
            <?php
            echo ($selected_category == "papers")
                ? "active"
                : "";
            ?>"
        >
            📄 Papers
        </a>


        <!-- LAB MANUALS -->

        <a
            href="browse_resources.php?category=lab_manuals"
            class="filter-btn
            <?php
            echo ($selected_category == "lab_manuals")
                ? "active"
                : "";
            ?>"
        >
            🧪 Lab Manuals
        </a>


        <!-- OTHER -->

        <a
            href="browse_resources.php?category=other"
            class="filter-btn
            <?php
            echo ($selected_category == "other")
                ? "active"
                : "";
            ?>"
        >
            📦 Other
        </a>


    </div>


    <!-- =====================================
         SEARCH
         ===================================== -->

    <div class="search-box">

        <input
            type="text"
            id="searchInput"
            placeholder="🔍 Search resources..."
            onkeyup="searchResources()"
        >

    </div>


    <!-- =====================================
         RESOURCE GRID
         ===================================== -->

    <div class="resource-grid">


        <?php

        if (
            $result &&
            mysqli_num_rows($result) > 0
        ) {

            while (
                $row =
                mysqli_fetch_assoc($result)
            ) {

        ?>


        <div class="resource-card">


            <!-- RESOURCE TOP -->

            <div class="resource-top">


                <div class="resource-icon">


                    <?php

                    $category =
                        strtolower(
                            trim(
                                $row["category"]
                            )
                        );


                    if (
                        $category == "book" ||
                        $category == "books"
                    ) {

                        echo "📘";

                    }

                    elseif (
                        $category == "notes"
                    ) {

                        echo "📝";

                    }

                    elseif (
                        $category == "paper" ||
                        $category == "papers" ||
                        $category == "question paper" ||
                        $category == "question papers"
                    ) {

                        echo "📄";

                    }

                    elseif (
                        $category == "lab manual" ||
                        $category == "lab manuals" ||
                        $category == "lab_manual" ||
                        $category == "lab_manuals"
                    ) {

                        echo "🧪";

                    }

                    else {

                        echo "📚";

                    }

                    ?>


                </div>


                <span class="category">

                    <?php

                    echo htmlspecialchars(
                        $row["category"]
                    );

                    ?>

                </span>


            </div>


            <!-- TITLE -->

            <h2 class="resource-title">

                <?php

                echo htmlspecialchars(
                    $row["title"]
                );

                ?>

            </h2>


            <!-- DESCRIPTION -->

            <p class="description">

                <?php

                echo htmlspecialchars(
                    $row["description"]
                );

                ?>

            </p>


            <!-- INFORMATION -->

            <div class="info">


                <!-- CONDITION -->

                <div class="info-row">

                    <span class="info-label">

                        Condition

                    </span>


                    <span class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $row["condition_item"]
                        );

                        ?>

                    </span>

                </div>


                <!-- RESOURCE TYPE -->

                <div class="info-row">

                    <span class="info-label">

                        Resource Type

                    </span>


                    <span class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $row["exchange_type"]
                        );

                        ?>

                    </span>

                </div>


                <!-- STATUS -->

                <div class="info-row">

                    <span class="info-label">

                        Status

                    </span>


                    <span class="info-value status">

                        <?php

                        echo htmlspecialchars(
                            $row["status"]
                        );

                        ?>

                    </span>

                </div>


            </div>


            <!-- =================================
                 ACTION BUTTON
                 ================================= -->


            <?php

            if (
                $row["exchange_type"]
                == "Exchange"
            ) {

            ?>

                <a
                    href="request_exchange.php?resource_id=<?php echo (int)$row["id"]; ?>"
                    class="exchange-btn"
                >

                    Request Exchange

                </a>


            <?php

            }

            elseif (
                $row["exchange_type"]
                == "Borrow"
            ) {

            ?>

                <a
                    href="borrow_resource.php?resource_id=<?php echo (int)$row["id"]; ?>"
                    class="exchange-btn"
                >

                    Borrow Resource

                </a>


            <?php

            }

            elseif (
                $row["exchange_type"]
                == "Donate"
            ) {

            ?>

                <a
                    href="request_donation.php?resource_id=<?php echo (int)$row["id"]; ?>"
                    class="exchange-btn"
                >

                    Request Resource

                </a>


            <?php

            }

            ?>


        </div>


        <?php

            }

        }

        else {

        ?>


        <!-- NO RESOURCES -->

        <div class="empty">

            <div class="empty-icon">

                📚

            </div>


            <h2>

                No Resources Found

            </h2>


            <p>

                There are no resources in this category yet.

            </p>

        </div>


        <?php

        }

        ?>


    </div>


</div>


<!-- =========================================
     FOOTER
     ========================================= -->

<footer>

    <h3>

        CampusSwap

    </h3>

    <p>

        Share. Exchange. Grow Together.

    </p>

</footer>


<!-- =========================================
     SEARCH JAVASCRIPT
     ========================================= -->

<script>

function searchResources() {


    let input =
        document
        .getElementById("searchInput")
        .value
        .toLowerCase();


    let cards =
        document
        .querySelectorAll(".resource-card");


    cards.forEach(function(card) {


        let title =
            card
            .querySelector(".resource-title")
            .textContent
            .toLowerCase();


        let description =
            card
            .querySelector(".description")
            .textContent
            .toLowerCase();


        let category =
            card
            .querySelector(".category")
            .textContent
            .toLowerCase();


        if (

            title.includes(input) ||

            description.includes(input) ||

            category.includes(input)

        ) {

            card.style.display = "";

        }

        else {

            card.style.display = "none";

        }


    });

}

</script>


</body>

</html>