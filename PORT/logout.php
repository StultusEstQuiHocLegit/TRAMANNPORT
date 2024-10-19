<?php
// Clear cookies (if set)
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/'); // Expire the cookie
}

// Destroy the session
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

include ("header.php"); // Include the header
echo "<div class=\"content\">";

// Redirect to the desired page after logout
echo "<br><br><br>Logged out successfully!<br><br><a href=\"index.php?content=login.php\">LOGIN</a>";

echo "</div>";
?>
