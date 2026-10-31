```php
<?php

// Database connection details
$host = "sql200.byethost10.com";
$username = "b10_43311806";
$password = "••••••••••";
$database = "b10_43311806_Campusswap";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Unable to connect to the database. Please try again later.");
}

// Set character encoding
$conn->set_charset("utf8mb4");

?>
```