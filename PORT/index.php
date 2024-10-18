<?php
include ("header.php"); // Include the header
echo "<div class=\"content\">";

// Check if the user is logged in using cookies
$isLoggedIn = isset($_COOKIE['user_id']);
$userRole = $isLoggedIn ? (int)$_COOKIE['ExplorerOrCreator'] : null; // Get user role from cookie if logged in

// Check for forwarding content
$content = isset($_GET['content']) ? $_GET['content'] : null;

if (!$isLoggedIn) {
    // User is not logged in
    if ($content === 'login.php') {
        // Include login page if forwarding to login.php
        echo "<script>window.location.href = 'login.php';</script>";
        exit();
    } elseif ($content === 'CreateAccount.php') {
        include("CreateAccount.php");
    } elseif ($content === 'ForgotPassword.php') {
        include("ForgotPassword.php");
    } else {
        // No forwarding, include landing page
        include("LandingPage.php");
    }
} else {
    // User is logged in, check the role from cookie
    if ($userRole === 0) {
        // Explorer
        include("explore.php");
    } elseif ($userRole === 1) {
        // Creator
        include("dashboard.php");
    }
}
echo "<br><br><br><br><br><br><br><br><br><br>";
echo "</div>";
?>
