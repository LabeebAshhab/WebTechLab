<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchTerm = htmlspecialchars($_POST['search_term'] ?? '');
    $sql  = "SELECT * FROM books WHERE book_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeTerm = '%' . $searchTerm . '%';
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        echo "<ul class='book-list'>";
        while ($book = $result->fetch_assoc()) {
            echo "<li>";
            echo "<strong>Book:</strong> {$book['book_name']}<br>";
            echo "<strong>Author:</strong> {$book['author_name']}<br>";
            echo "<strong>ISBN:</strong> {$book['isbn']}<br>";
            echo "<strong>Price:</strong> {$book['price']}<br>";
            echo "<strong>Quantity:</strong> {$book['quantity']}<br><br>";

            echo "<form method='post' action='edit_delete_book.php' style='display:inline; margin-right:10px;'>";
            echo "  <input type='hidden' name='book_id' value='{$book['id']}'>";
            echo "  <input type='submit' name='edit_book' value='Edit'>";
            echo "</form>";

            echo "<form method='post' action='edit_delete_book.php' style='display:inline;'>";
            echo "  <input type='hidden' name='book_id' value='{$book['id']}'>";
            echo "  <input type='submit' name='delete_book' value='Delete'>";
            echo "</form>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No data available.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
