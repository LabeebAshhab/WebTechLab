<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
</head>
<body>

<?php
session_start();

$availableTokens = json_decode(@file_get_contents('info.json'), true);
if (!is_array($availableTokens)) {
    $availableTokens = [];
}

if (!file_exists('used_tokens.json')) {
    file_put_contents('used_tokens.json', '[]');
}
$usedTokens = json_decode(file_get_contents('used_tokens.json'), true);
if (!is_array($usedTokens)) {
    $usedTokens = [];
}

require_once 'db_connect.php';

$trendingBooks = [];
$sqlTrend = "SELECT * FROM books ORDER BY quantity DESC LIMIT 3";
$resultTrend = $conn->query($sqlTrend);
if ($resultTrend && $resultTrend->num_rows > 0) {
    while ($rowT = $resultTrend->fetch_assoc()) {
        $trendingBooks[] = $rowT;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['show_books'])) {
    $sqlAll    = "SELECT * FROM books";
    $resultAll = $conn->query($sqlAll);

    $allBooksResults = [];
    while ($rowA = $resultAll->fetch_assoc()) {
        $allBooksResults[] = $rowA;
    }
    $_SESSION['allBooksResults'] = $allBooksResults;
    header("Location: index.php?show=1");
    exit();
}

$allBooksResults = [];
if (isset($_GET['show']) && $_GET['show'] == '1') {
    if (isset($_SESSION['allBooksResults'])) {
        $allBooksResults = $_SESSION['allBooksResults'];
        unset($_SESSION['allBooksResults']);
    }
}

$booksForDropdown = [];
$sqlDropdown = "SELECT * FROM books ORDER BY book_name ASC";
$resDropdown = $conn->query($sqlDropdown);
if ($resDropdown && $resDropdown->num_rows > 0) {
    while($b = $resDropdown->fetch_assoc()){
        $booksForDropdown[] = $b;
    }
}

$conn->close();
?>

<nav class="navbar">
    <ul>
        <li><a href="#home-section">Home</a></li>
        <li><a href="#add-books-section">Add Book</a></li>
        <li><a href="#search-section">Search</a></li>
        <li><a href="#all-books-section">All Books</a></li>
        <li><a href="#borrow-section">Borrow Form</a></li>
        <li><a href="#footer-tokens">Tokens</a></li>
    </ul>
</nav>

<div class="container" id="home-section">
    <div class="header">Book Borrow & Management</div>

    <div class="main-content">

        <div class="left-sidebar">
            <?php
            if (!empty($usedTokens)) {
                echo "<div class='scrollable-tokens'>";
                echo "<table class='token-table'>";
                echo "<tr><th>Used Tokens</th></tr>";
                foreach ($usedTokens as $tokenObj) {
                    echo "<tr><td>" . $tokenObj['token'] . "</td></tr>";
                }
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p>No data available.</p>";
            }
            ?>
        </div>
        <!-- Search books -->
        <div class="content-area">
            <div class="content1" id="search-section">
                <h3>Search Book </h3>
                <div style="background-color: #e8eaf6; padding: 10px;">
                    <label for="search_term">Enter Book Name:</label><br>
                    <input type="text" id="search_term" name="search_term" required>
                    <button id="searchBtn">Search</button>
                </div>

                <div id="searchResults" class="scrollable-results" style="margin-top:10px;">

                </div>
            </div>

            <div class="content2" id="add-books-section">
                <h3>Add Books</h3>
                <div class="form-container">
                    <form action="add_book.php" method="post">
                        <div class="form-group">
                            <label for="book_name">Book Name:</label>
                            <input type="text" id="book_name" name="book_name" required>
                        </div>

                        <div class="form-group">
                            <label for="author_name">Author Name:</label>
                            <input type="text" id="author_name" name="author_name" required>
                        </div>

                        <div class="form-group">
                            <label for="isbn">ISBN:</label>
                            <input type="text" id="isbn" name="isbn" required>
                        </div>

                        <div class="form-group">
                            <label for="price">Price:</label>
                            <input type="number" step="0.01" id="price" name="price" required>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" required>
                        </div>

                        <input type="submit" value="Submit">
                    </form>
                </div>

            </div>


            <div class="content3" id="all-books-section">
                <h3>All Books</h3>
                <form method="post" action="index.php" style="background-color: #e8eaf6; padding: 10px;">
                    <input type="submit" name="show_books" value="Show Available Books">
                </form>

                <?php
                if (!empty($allBooksResults)) {
                    echo "<div class='scrollable-results'>";
                    echo "<ul class='book-list'>";
                    foreach ($allBooksResults as $book) {
                        echo "<li>";
                        echo "<strong>Book:</strong> {$book['book_name']}<br>";
                        echo "<strong>Author:</strong> {$book['author_name']}<br>";
                        echo "<strong>ISBN:</strong> {$book['isbn']}<br>";
                        echo "<strong>Price:</strong> {$book['price']}<br>";
                        echo "<strong>Qty:</strong> {$book['quantity']}";
                        echo "</li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                } elseif (isset($_GET['show'])) {
                    echo "<p>No data available.</p>";
                }
                ?>
            </div>

            <div class="small-contents">
                <div class="small-content">
                    <img src="Book1.jpg" alt="Picture 1" style="width: 100%; height: auto;">
                </div>
                <div class="small-content">
                    <img src="Book2.jpg" alt="Picture 2" style="width: 100%; height: auto;">
                </div>
                <div class="small-content">
                    <img src="Book3.jpg" alt="Picture 3" style="width: 100%; height: auto;">
                </div>
            </div>
        </div>
       <!-- T-books -->
        <div class="right-sidebar">
            <h3>Trending Books</h3>
            <div class="scrollable-trending">
                <?php
                if (!empty($trendingBooks)) {
                    echo "<ul class='book-list'>";
                    foreach ($trendingBooks as $tb) {
                        echo "<li>";
                        echo "<strong>Book:</strong> {$tb['book_name']}<br>";
                        echo "<strong>Author:</strong> {$tb['author_name']}<br>";
                        echo "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>No data available.</p>";
                }
                ?>
            </div>
        </div>
    </div>

  
    <div class="footer" id="borrow-section">
        <div class="validation">
            <h2>Borrow Form</h2>
            <div class="top-right-image">
                <img src="image4.jpg" alt="Logo" style="width: 100px;">
            </div>

         
            <div class="form-container">
                <form action="process.php" method="post" target="_blank">
                    <div class="form-group">
                        <label for="sname">Student Name:</label>
                        <input type="text" id="sname" name="student-name" placeholder="Your Name" required>
                    </div>

                    <div class="form-group">
                        <label for="sid">Student ID:</label>
                        <input type="text" id="sid" name="student-id" placeholder="XX-XXXXX-X" required>
                    </div>

                    <div class="form-group">
                        <label for="semail">Student Email:</label>
                        <input type="text" id="semail" name="student-email" placeholder="xx-xxxxx-x@student.aiub.edu" required>
                    </div>
                    <!-- book name in borrow form -->
                    <div class="form-group">
                        <label for="books">Choose a Book:</label>
                        <select id="books" name="book-title" required>
                            <?php
                            if (!empty($booksForDropdown)) {
                                foreach ($booksForDropdown as $dbBook) {
                                    echo "<option value=\"{$dbBook['book_name']}\">";
                                    echo $dbBook['book_name'] . " by " . $dbBook['author_name'];
                                    echo "</option>";
                                }
                            } else {
                                echo "<option value=''>No data available</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="borrowDate">Borrow Date:</label>
                        <input type="date" id="borrowDate" name="borrow-date" required>
                    </div>

                    <div class="form-group">
                        <label for="returnDate">Return Date:</label>
                        <input type="date" id="returnDate" name="return-date" required>
                    </div>

                    <div class="form-group">
                        <label for="fees">Fees:</label>
                        <input type="number" id="fees" name="fees" placeholder="Enter Fees" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="token">Enter Token (For Borrowing Books More Than 10 Days):</label>
                        <input type="text" id="token" name="token" placeholder="Enter Token">
                    </div>

                    <input type="submit" value="Submit">
                </form>
            </div>
  
        </div>


        <div id="footer-tokens">
            <h3>Extended Borrow Tokens</h3>
            <div id="token-list" class="scrollable-tokens">
                <?php
                if (!empty($availableTokens)) {
                    echo "<table class='token-table'>";
                    echo "<tr><th>Available Tokens</th></tr>";
                    foreach ($availableTokens as $tokenObj) {
                        echo "<tr><td>" . $tokenObj['token'] . "</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No data available.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('#searchBtn').on('click', function(e) {
        e.preventDefault();
        const searchTerm = $('#search_term').val().trim();

        if(!searchTerm) {
            $('#searchResults').html("<p>Please enter a search term.</p>");
            return;
        }

        $.ajax({
            url: 'ajax_search.php',
            type: 'POST',
            data: { search_term: searchTerm },
            success: function(response) {
                $('#searchResults').html(response);
            },
            error: function() {
                $('#searchResults').html("<p style='color:red;'>Error occurred while searching.</p>");
            }
        });
    });
});
</script>

</body>
</html>
