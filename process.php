<?php
require_once 'db_connect.php';

function isTokenValid($userToken, $tokens) {
    foreach ($tokens as $tokenObj) {
        if ($tokenObj['token'] == $userToken) {
            return true;
        }
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentName  = htmlspecialchars($_POST['student-name']);
    $studentId    = htmlspecialchars($_POST['student-id']);
    $studentEmail = htmlspecialchars($_POST['student-email']);
    $borrowDate   = htmlspecialchars($_POST['borrow-date']);
    $returnDate   = htmlspecialchars($_POST['return-date']);
    $bookTitle    = htmlspecialchars($_POST['book-title']);
    $fees         = htmlspecialchars($_POST['fees']);
    $userToken    = htmlspecialchars($_POST['token']);

    $tokens = json_decode(@file_get_contents('info.json'), true);
    if (!is_array($tokens)) {
        $tokens = [];
    }

    if (!file_exists('used_tokens.json')) {
        file_put_contents('used_tokens.json', '[]');
    }
    $usedTokens = json_decode(file_get_contents('used_tokens.json'), true);
    if (!is_array($usedTokens)) {
        $usedTokens = [];
    }

    $cookieName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $bookTitle);

    $borrowTimestamp = strtotime($borrowDate);
    $returnTimestamp = strtotime($returnDate);
    $dateDifference  = ($returnTimestamp - $borrowTimestamp) / (60 * 60 * 24);

    $borrowMoreThan10Days = false;

    if (!preg_match("/^[a-zA-Z\s]+$/", $studentName)) {
        echo "<p class='error'>Invalid Name format. The name cannot contain numbers or special characters.</p>";
        exit;
    }

    if (!preg_match("/^[0-9]{2}-[0-9]{5}-[0-9]$/", $studentId)) {
        echo "<p class='error'>Invalid Student ID format. Use XX-XXXXX-X.</p>";
        exit;
    }

    if (!preg_match("/^[0-9]{2}-[0-9]{5}-[0-9]@student\.aiub\.edu$/", $studentEmail)) {
        echo "<p class='error'>Invalid Email format! Use (xx-xxxxx-x@student.aiub.edu).</p>";
        exit;
    }

    if (!preg_match("/^\d+(\.\d{1,2})?$/", $fees)) {
        echo "<p class='error'>Invalid Fees format. Please enter a numeric value (fractions allowed).</p>";
        exit;
    }

    if (isset($_COOKIE[$cookieName])) {
        echo "<p class='error'>The book <strong>$bookTitle</strong> is already borrowed by <strong>{$_COOKIE[$cookieName]}</strong>.</p>";
        exit;
    }

    if ($dateDifference > 10) {
    
        if (!isTokenValid($userToken, $tokens)) {
            echo "<p class='error'>Invalid token! You cannot borrow more than 10 days.</p>";
            exit;
        } else {
            $borrowMoreThan10Days = true;
        }
    } elseif ($dateDifference <= 0) {
        echo "<p class='error'>Invalid date range. Return date must be after borrow date.</p>";
        exit;
    }

    if ($borrowMoreThan10Days) {
        $tokenIndex = null;
        foreach ($tokens as $index => $t) {
            if ($t['token'] == $userToken) {
                $tokenIndex = $index;
                break;
            }
        }
        if ($tokenIndex !== null) {
            $removedToken = array_splice($tokens, $tokenIndex, 1);
            file_put_contents('info.json', json_encode($tokens, JSON_PRETTY_PRINT));

            $usedTokens[] = $removedToken[0];
            file_put_contents('used_tokens.json', json_encode($usedTokens, JSON_PRETTY_PRINT));
        }
    }

    setcookie($cookieName, $studentName, time() + 40);

    
    $stmtInsert = $conn->prepare("
        INSERT INTO borrows 
            (student_name, student_id, student_email, borrow_date, return_date, book_title, fees)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->bind_param("ssssssd",
        $studentName,
        $studentId,
        $studentEmail,
        $borrowDate,
        $returnDate,
        $bookTitle,
        $fees
    );
    if (!$stmtInsert->execute()) {
        echo "<p class='error'>Error saving borrow info: " . $conn->error . "</p>";
        exit;
    }
    $stmtInsert->close();
    $conn->close();

    echo "<div class='receipt-info'>
            <h2>Borrow Receipt</h2>
            <p><strong>Student Full Name:</strong> $studentName</p>
            <p><strong>Student ID:</strong> $studentId</p>
            <p><strong>Student Email:</strong> $studentEmail</p>
            <p><strong>Book Title:</strong> $bookTitle</p>
            <p><strong>Borrow Date:</strong> $borrowDate</p>
            <p><strong>Return Date:</strong> $returnDate</p>
            <p><strong>Fees:</strong> $fees TK</p>";

    if ($borrowMoreThan10Days) {
        echo "<p><strong>Token Used:</strong> $userToken</p>";
        echo "<p class='success'><strong>Message:</strong> You can borrow the book for more than 10 days.</p>";
    }

    echo "</div>";
}
?>
