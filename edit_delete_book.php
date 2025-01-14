<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = $_POST['book_id'] ?? null;

    // delete through search
    if (isset($_POST['delete_book']) && $bookId) {
        $deleteSql = "DELETE FROM books WHERE id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $bookId);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Book deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting book: " . $conn->error . "</p>";
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    // edit form (new page)
    if (isset($_POST['edit_book']) && $bookId) {
        $selectSql = "SELECT * FROM books WHERE id = ?";
        $stmt = $conn->prepare($selectSql);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();

        if (!$book) {
            echo "<p style='color:red;'>Book not found.</p>";
            $conn->close();
            exit;
        }

        // Display form
        echo "<h2>Edit Book</h2>";
        echo "<form method='POST' action='edit_delete_book.php'>";
        echo "  <input type='hidden' name='book_id' value='{$book['id']}'>";
        echo "  <label>Book Name:</label><br>";
        echo "  <input type='text' name='new_book_name' value='{$book['book_name']}' required><br><br>";

        echo "  <label>Author Name:</label><br>";
        echo "  <input type='text' name='new_author_name' value='{$book['author_name']}' required><br><br>";

        echo "  <label>Quantity:</label><br>";
        echo "  <input type='number' name='new_quantity' value='{$book['quantity']}' required><br><br>";

        echo "  <input type='submit' name='update_book' value='Update'>";
        echo "</form>";

        $conn->close();
        exit;
    }

    // update through search.
    if (isset($_POST['update_book']) && $bookId) {
        $newBookName   = htmlspecialchars($_POST['new_book_name']);
        $newAuthorName = htmlspecialchars($_POST['new_author_name']);
        $newQuantity   = (int)$_POST['new_quantity'];

        $updateSql = "UPDATE books 
                      SET book_name = ?, author_name = ?, quantity = ?
                      WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssii", $newBookName, $newAuthorName, $newQuantity, $bookId);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Book updated successfully!</p>";
            echo "<p><strong>New Name:</strong> $newBookName</p>";
            echo "<p><strong>New Author:</strong> $newAuthorName</p>";
            echo "<p><strong>New Quantity:</strong> $newQuantity</p>";
        } else {
            echo "<p style='color: red;'>Error updating book: " . $conn->error . "</p>";
        }
        $stmt->close();
        $conn->close();
        exit;
    }
}
?>
