<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Get wishlist resources */
$sql = "SELECT 
            wishlist.id AS wishlist_id,
            resources.id AS resource_id,
            resources.title,
            resources.category,
            resources.description,
            resources.exchange_type,
            resources.condition_item,
            resources.status,
            resources.created_at
        FROM wishlist
        INNER JOIN resources 
            ON wishlist.resource_id = resources.id
        WHERE wishlist.user_id = ?
        ORDER BY wishlist.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Wishlist - CampusSwap</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4fbf6;
            color: #26352b;
        }

        /* Navbar */
        .navbar {
            background: white;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 7%;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
            color: #2e7d32;
        }

        .logo span {
            color: #81b985;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #405046;
            font-size: 14px;
            font-weight: 600;
        }

        .nav-links a:hover {
            color: #2e7d32;
        }

        .logout {
            background: #e8f5e9;
            color: #2e7d32 !important;
            padding: 10px 17px;
            border-radius: 8px;
        }

        /* Main */
        .container {
            width: 86%;
            max-width: 1200px;
            margin: 45px auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 30px;
            color: #245b2a;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #708078;
            font-size: 14px;
        }

        .browse-btn {
            text-decoration: none;
            background: #2e7d32;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .browse-btn:hover {
            background: #256528;
        }

        /* Message */
        .message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 13px 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2e7d32;
        }

        /* Cards */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 22px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 5px 20px rgba(38, 73, 48, 0.08);
            border: 1px solid #e5f0e7;
            transition: 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(38, 73, 48, 0.12);
        }

        .heart {
            font-size: 25px;
            margin-bottom: 12px;
        }

        .card h2 {
            font-size: 19px;
            color: #285d30;
            margin-bottom: 9px;
        }

        .description {
            color: #68766e;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .details {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .tag {
            background: #eef8f0;
            color: #397340;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status {
            color: #2e7d32;
        }

        .actions {
            display: flex;
            gap: 9px;
        }

        .view-btn,
        .remove-btn {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .view-btn {
            background: #2e7d32;
            color: white;
        }

        .remove-btn {
            background: #fff0f0;
            color: #c62828;
        }

        .view-btn:hover {
            background: #256528;
        }

        .remove-btn:hover {
            background: #ffe1e1;
        }

        /* Empty wishlist */
        .empty {
            background: white;
            text-align: center;
            padding: 70px 20px;
            border-radius: 15px;
            border: 1px solid #e5f0e7;
        }

        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty h2 {
            color: #315b38;
            margin-bottom: 10px;
        }

        .empty p {
            color: #718078;
            margin-bottom: 22px;
        }

        /* Footer */
        footer {
            margin-top: 70px;
            background: white;
            border-top: 1px solid #e5eee7;
            text-align: center;
            padding: 25px;
            color: #7a877f;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            .navbar {
                padding: 0 4%;
            }

            .nav-links {
                gap: 10px;
            }

            .nav-links a:not(.logout) {
                display: none;
            }

            .container {
                width: 92%;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar">

    <div class="logo">
        Campus<span>Swap</span>
    </div>

    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="browse_resources.php">Resources</a>
        <a href="requests.php">Requests</a>
        <a href="wishlist.php">Wishlist</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

</nav>


<!-- Main Content -->
<div class="container">

    <div class="page-header">
        <div>
            <h1>My Wishlist ❤️</h1>
            <p>Resources you saved for later.</p>
        </div>

        <a href="browse_resources.php" class="browse-btn">
            + Browse Resources
        </a>
    </div>


    <?php if (isset($_GET['message']) && $_GET['message'] == 'added'): ?>

        <div class="message">
            ❤️ Resource added to your wishlist successfully!
        </div>

    <?php elseif (isset($_GET['message']) && $_GET['message'] == 'already_added'): ?>

        <div class="message">
            This resource is already in your wishlist.
        </div>

    <?php elseif (isset($_GET['message']) && $_GET['message'] == 'removed'): ?>

        <div class="message">
            Resource removed from your wishlist.
        </div>

    <?php endif; ?>


    <?php if (mysqli_num_rows($result) > 0): ?>

        <div class="wishlist-grid">

            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <div class="card">

                    <div class="heart">❤️</div>

                    <h2>
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h2>

                    <p class="description">
                        <?php echo htmlspecialchars($row['description']); ?>
                    </p>

                    <div class="details">

                        <span class="tag">
                            <?php echo htmlspecialchars($row['category']); ?>
                        </span>

                        <span class="tag">
                            <?php echo htmlspecialchars($row['exchange_type']); ?>
                        </span>

                        <span class="tag">
                            <?php echo htmlspecialchars($row['condition_item']); ?>
                        </span>

                        <span class="tag status">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>

                    </div>

                    <div class="actions">

                        <a
                            href="browse_resources.php"
                            class="view-btn">
                            View Resources
                        </a>

                        <a
                            href="remove_wishlist.php?id=<?php echo $row['wishlist_id']; ?>"
                            class="remove-btn">
                            Remove
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <div class="empty-icon">🤍</div>

            <h2>Your Wishlist is Empty</h2>

            <p>
                Browse resources and save the ones you may need later.
            </p>

            <a href="browse_resources.php" class="browse-btn">
                Browse Resources
            </a>

        </div>

    <?php endif; ?>

</div>


<footer>
    CampusSwap — Share. Exchange. Grow Together.
</footer>

</body>
</html>