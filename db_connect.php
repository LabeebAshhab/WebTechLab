<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "library_db";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$createDBSql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($createDBSql)) {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

$createBooksTableSql = "
    CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_name VARCHAR(255) NOT NULL,
        author_name VARCHAR(255) NOT NULL,
        isbn VARCHAR(50) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL
    )
";
if (!$conn->query($createBooksTableSql)) {
    die("Error creating `books` table: " . $conn->error);
}

$createBorrowsTableSql = "
    CREATE TABLE IF NOT EXISTS borrows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(255) NOT NULL,
        student_id VARCHAR(50) NOT NULL,
        student_email VARCHAR(100) NOT NULL,
        borrow_date DATE NOT NULL,
        return_date DATE NOT NULL,
        book_title VARCHAR(255) NOT NULL,
        fees DECIMAL(10,2) NOT NULL,
        borrowed_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
";
if (!$conn->query($createBorrowsTableSql)) {
    die("Error creating `borrows` table: " . $conn->error);
}
?>
