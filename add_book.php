<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bookName   = htmlspecialchars($_POST['book_name']);
    $authorName = htmlspecialchars($_POST['author_name']);
    $isbn       = htmlspecialchars($_POST['isbn']);
    $price      = htmlspecialchars($_POST['price']);
    $quantity   = htmlspecialchars($_POST['quantity']);

    if (empty($bookName) || empty($authorName) || empty($isbn) || empty($price) || empty($quantity)) {
        echo "<p style='color: red;'>Error: Please fill in all fields.</p>";
        exit;
    }

    if ($quantity < 0) {
        echo "<p style='color: red;'>Error: Quantity cannot be a negative value.</p>";
        exit;
    }

    $stmtISBN = $conn->prepare("SELECT COUNT(*) FROM books WHERE isbn = ?");
    $stmtISBN->bind_param("s", $isbn);
    $stmtISBN->execute();
    $stmtISBN->bind_result($countISBN);
    $stmtISBN->fetch();
    $stmtISBN->close();

    if ($countISBN > 0) {
        echo "<p style='color: red;'>Invalid ISBN. A book with this ISBN already exists in the database.</p>";
        exit;
    }

    $stmtBook = $conn->prepare("SELECT COUNT(*) FROM books WHERE book_name = ?");
    $stmtBook->bind_param("s", $bookName);
    $stmtBook->execute();
    $stmtBook->bind_result($countBookName);
    $stmtBook->fetch();
    $stmtBook->close();

    if ($countBookName > 0) {
        echo "<p style='color: red;'>Invalid Book Name. A book with this name already exists.</p>";
        exit;
    }

    $stmtInsert = $conn->prepare("INSERT INTO books (book_name, author_name, isbn, price, quantity) VALUES (?, ?, ?, ?, ?)");
    $stmtInsert->bind_param("sssdi", $bookName, $authorName, $isbn, $price, $quantity);

    if ($stmtInsert->execute()) {
        echo "<p style='color: green;'>Book added successfully!</p>";
        echo "<p><strong>Book Name:</strong> $bookName</p>";
        echo "<p><strong>Author Name:</strong> $authorName</p>";
        echo "<p><strong>ISBN:</strong> $isbn</p>";
        echo "<p><strong>Price:</strong> $price</p>";
        echo "<p><strong>Quantity:</strong> $quantity</p>";
    } else {
        echo "<p style='color: red;'>Error adding book: " . $conn->error . "</p>";
    }

    $stmtInsert->close();
    $conn->close();
}
?>
