
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusSwap - Share. Exchange. Grow Together.</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            background: #f5fbf7;
            color: #24352b;
        }

        a {
            text-decoration: none;
        }

        /* NAVBAR */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            padding: 17px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 27px;
            font-weight: 800;
            color: #328b55;
            letter-spacing: 0.3px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: #e0f3e6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav-links a {
            color: #45584c;
            font-size: 15px;
            font-weight: 600;
            transition: 0.25s;
        }

        .nav-links a:hover {
            color: #328b55;
        }

        .nav-links .login-btn {
            background: #328b55;
            color: white;
            padding: 11px 22px;
            border-radius: 9px;
            box-shadow: 0 5px 14px rgba(50, 139, 85, 0.18);
        }

        .nav-links .login-btn:hover {
            background: #267343;
            color: white;
            transform: translateY(-2px);
        }

        /* HERO */
        .hero {
            padding: 75px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 45px;
            background:
                radial-gradient(circle at 85% 30%, #e0f4e6 0, transparent 28%),
                linear-gradient(135deg, #f7fcf8, #edf8f0);
        }

        .hero-text {
            max-width: 600px;
        }

        .small-badge {
            display: inline-block;
            background: #e0f3e6;
            color: #2f8652;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero-text h1 {
            font-size: 52px;
            line-height: 1.15;
            margin-bottom: 20px;
            color: #254c34;
        }

        .hero-text h1 span {
            color: #3b8f5a;
        }

        .hero-text p {
            font-size: 17px;
            line-height: 1.8;
            color: #65736b;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 13px;
            flex-wrap: wrap;
        }

        .hero-btn {
            display: inline-block;
            background: #328b55;
            color: white;
            text-decoration: none;
            padding: 14px 25px;
            border-radius: 9px;
            font-weight: 700;
            transition: 0.25s;
        }

        .hero-btn:hover {
            background: #267343;
            transform: translateY(-2px);
        }

        .secondary-btn {
            display: inline-block;
            background: white;
            color: #328b55;
            padding: 13px 24px;
            border-radius: 9px;
            border: 1px solid #cfe5d5;
            font-weight: 700;
            transition: 0.25s;
        }

        .secondary-btn:hover {
            background: #eaf6ed;
        }

        /* HERO CARD */
        .hero-card {
            background: white;
            width: 390px;
            min-height: 300px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(39, 89, 55, 0.12);
        }

        .hero-card-inner {
            background: #e7f6eb;
            width: 100%;
            padding: 30px 20px;
            border-radius: 20px;
        }

        .hero-card .big-icon {
            font-size: 55px;
            margin-bottom: 16px;
        }

        .hero-card h2 {
            color: #2f7047;
            font-size: 25px;
            margin-bottom: 12px;
        }

        .hero-card p {
            color: #54705e;
            line-height: 1.7;
            font-size: 14px;
        }

        /* SEARCH */
        .search-section {
            margin: -28px auto 0;
            width: 86%;
            max-width: 1100px;
            background: white;
            padding: 17px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(42, 89, 56, 0.10);
            position: relative;
            z-index: 5;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f4f8f5;
            border-radius: 11px;
            padding: 12px 16px;
        }

        .search-box .search-icon {
            font-size: 20px;
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 15px;
            color: #536158;
        }

        .search-button {
            border: none;
            background: #328b55;
            color: white;
            padding: 12px 23px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        /* GENERAL SECTION */
        .section {
            padding: 65px 7%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .section-title h2 {
            font-size: 32px;
            color: #254c34;
            margin-bottom: 10px;
        }

        .section-title p {
            color: #718078;
            font-size: 15px;
        }

        /* QUICK ACTIONS */
        .actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .action-card {
            background: white;
            padding: 25px 20px;
            border-radius: 15px;
            text-align: left;
            border: 1px solid #e7f0e9;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            transition: 0.3s;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(42, 91, 57, 0.10);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            background: #e3f5e8;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin-bottom: 16px;
        }

        .action-card h3 {
            margin-bottom: 8px;
            color: #31533d;
            font-size: 17px;
        }

        .action-card p {
            color: #748078;
            font-size: 13px;
            line-height: 1.6;
        }

        /* FEATURED RESOURCES */
        .resources {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .resource-card {
            background: white;
            padding: 24px;
            border-radius: 15px;
            border: 1px solid #e8f0ea;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            transition: 0.3s;
        }

        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(42, 91, 57, 0.10);
        }

        .resource-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .resource-icon {
            width: 52px;
            height: 52px;
            background: #e4f5e9;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        .tag {
            display: inline-block;
            background: #e2f5e7;
            color: #388451;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .resource-card h3 {
            margin-bottom: 9px;
            color: #294735;
            font-size: 17px;
        }

        .resource-card p {
            color: #718078;
            font-size: 13px;
            line-height: 1.6;
        }

        .resource-card a {
            display: inline-block;
            margin-top: 15px;
            color: #3b8f5a;
            font-size: 13px;
            font-weight: 700;
        }

        /* CATEGORIES */
        .categories {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .category {
            background: white;
            padding: 15px 23px;
            border-radius: 30px;
            border: 1px solid #e4eee7;
            color: #456250;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
            transition: 0.25s;
        }

        .category:hover {
            background: #e8f7ec;
            transform: translateY(-3px);
        }

        /* REQUEST SECTION */
        .request-box {
            background: linear-gradient(135deg, #dff3e5, #edf9f0);
            padding: 42px;
            border-radius: 20px;
            text-align: center;
        }

        .request-box h2 {
            color: #285438;
            margin-bottom: 12px;
            font-size: 30px;
        }

        .request-box p {
            color: #5d7264;
            margin-bottom: 22px;
            line-height: 1.6;
        }

        /* FOOTER */
        footer {
            background: #244c32;
            color: white;
            padding: 32px 7%;
            text-align: center;
            margin-top: 35px;
        }

        footer p {
            margin: 7px;
            color: #d5e8db;
            font-size: 13px;
        }

        /* RESPONSIVE */
        @media (max-width: 950px) {
            .hero {
                flex-direction: column;
                text-align: center;
            }

            .hero-buttons {
                justify-content: center;
            }

            .actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .resources {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .nav-links {
                gap: 14px;
            }

            .nav-links a:not(.login-btn) {
                display: none;
            }

            .hero-text h1 {
                font-size: 42px;
            }

            .hero-card {
                width: 100%;
            }

            .search-section {
                width: 92%;
            }

            .search-box {
                flex-wrap: wrap;
            }

            .search-button {
                width: 100%;
            }
        }

        @media (max-width: 500px) {
            .navbar {
                padding: 14px 5%;
            }

            .logo {
                font-size: 23px;
            }

            .hero {
                padding: 55px 6%;
            }

            .hero-text h1 {
                font-size: 36px;
            }

            .actions {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 50px 6%;
            }

            .section-title h2 {
                font-size: 27px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon">🔄</div>
            CampusSwap
        </div>

        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">Resources</a>
            <a href="#">Requests</a>
            <a href="#">About</a>
            <a href="login.php" class="login-btn">Login</a>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-text">
            <div class="small-badge">
                🌱 Student Resource Exchange Platform
            </div>

            <h1>
                Share. <span>Exchange.</span><br>
                Grow Together.
            </h1>

            <p>
                CampusSwap is a student resource exchange platform
                where students can share, borrow, exchange and donate
                useful educational resources.
            </p>

            <div class="hero-buttons">
                <a href="#" class="hero-btn">Explore Resources →</a>
                <a href="#" class="secondary-btn">Post a Request</a>
            </div>
        </div>

        <div class="hero-card">
            <div class="hero-card-inner">
                <div class="big-icon">📚</div>
                <h2>Learn. Share. Reuse.</h2>
                <p>
                    Give your unused books and study materials
                    a new purpose by helping another student.
                </p>
            </div>
        </div>
    </section>

    <!-- SEARCH BAR -->
    <div class="search-section">
        <div class="search-box">
            <span class="search-icon">🔎</span>
            <input
                type="text"
                placeholder="Search books, notes, question papers, lab materials..."
            >
            <button class="search-button">Search</button>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <section class="section">
        <div class="section-title">
            <h2>Quick Actions</h2>
            <p>Everything you need in one place</p>
        </div>

        <div class="actions">
            <div class="action-card">
                <div class="action-icon">📚</div>
                <h3>Browse Resources</h3>
                <p>Find books, notes and useful study materials.</p>
            </div>

            <div class="action-card">
                <div class="action-icon">🔄</div>
                <h3>Exchange</h3>
                <p>Exchange your resources with other students.</p>
            </div>

            <div class="action-card">
                <div class="action-icon">📢</div>
                <h3>Post Request</h3>
                <p>Post a request when you need a resource.</p>
            </div>

            <div class="action-card">
                <div class="action-icon">🎁</div>
                <h3>Donate</h3>
                <p>Donate resources that you no longer need.</p>
            </div>
        </div>
    </section>

    <!-- FEATURED RESOURCES -->
    <section class="section">
        <div class="section-title">
            <h2>Featured Resources</h2>
            <p>Recently shared resources</p>
        </div>

        <div class="resources">
            <div class="resource-card">
                <div class="resource-top">
                    <div class="resource-icon">📘</div>
                    <span class="tag">Book</span>
                </div>
                <h3>Java Programming Book</h3>
                <p>Useful reference book for learning Java programming.</p>
                <a href="#">View Resource →</a>
            </div>

            <div class="resource-card">
                <div class="resource-top">
                    <div class="resource-icon">📝</div>
                    <span class="tag">Notes</span>
                </div>
                <h3>Database Management Notes</h3>
                <p>Helpful notes for understanding DBMS concepts.</p>
                <a href="#">View Resource →</a>
            </div>

            <div class="resource-card">
                <div class="resource-top">
                    <div class="resource-icon">📄</div>
                    <span class="tag">Papers</span>
                </div>
                <h3>Previous Year Papers</h3>
                <p>Previous examination papers for practice and preparation.</p>
                <a href="#">View Resource →</a>
            </div>
        </div>
    </section>

    <!-- CATEGORIES -->
    <section class="section">
        <div class="section-title">
            <h2>Browse by Category</h2>
            <p>Find resources according to your needs</p>
        </div>

        <div class="categories">
            <div class="category">📚 Textbooks</div>
            <div class="category">📝 Notes</div>
            <div class="category">📄 Question Papers</div>
            <div class="category">💻 Programming</div>
            <div class="category">🔬 Lab Materials</div>
            <div class="category">📖 Reference Books</div>
        </div>
    </section>

    <!-- RESOURCE REQUEST -->
    <section class="section">
        <div class="request-box">
            <h2>Looking for Something?</h2>
            <p>
                Post your resource request and let other students
                help you find what you need.
            </p>
            <a href="#" class="hero-btn">Post a Request</a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <h2>CampusSwap</h2>
        <p>Share. Exchange. Grow Together.</p>
        <p>© 2026 CampusSwap. All Rights Reserved.</p>
    </footer>

</body>
</html>