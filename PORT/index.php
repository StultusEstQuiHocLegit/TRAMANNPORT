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
        if ($content === 'explore.php') {
            include("explore.php");
        } elseif ($content === 'cart.php') {
            include("cart.php");
        } elseif ($content === 'PreviousCarts.php') {
            include("PreviousCarts.php");
        } elseif ($content === 'account.php') {
            include("account.php");
        } elseif ($content === 'ForgotPassword.php') {
            include("ForgotPassword.php");
        } elseif ($content === 'logout.php') {
            echo "<script>window.location.href = 'logout.php';</script>";
        } else {
            // Default landing page for explorers
            include("explore.php");
        }
    } elseif ($userRole === 1) {
        // Creator
        if ($content === 'dashboard.php') {
            include("dashboard.php");
        } elseif ($content === 'orders.php') {
            include("orders.php");
        } elseif ($content === 'inventory.php') {
            include("inventory.php");
        } elseif ($content === 'products.php') {
            include("products.php");
        } elseif ($content === 'ExplorersCustomers.php') {
            include("ExplorersCustomers.php");
        } elseif ($content === 'CreatorsSuppliers.php') {
            include("CreatorsSuppliers.php");
        } elseif ($content === 'explore.php') {
            include("explore.php");
        } elseif ($content === 'cart.php') {
            include("cart.php");
        } elseif ($content === 'PreviousCarts.php') {
            include("PreviousCarts.php");
        } elseif ($content === 'accounting.php') {
            include("accounting.php");
        } elseif ($content === 'help.php') {
            include("help.php");
        } elseif ($content === 'account.php') {
            include("account.php");
        } elseif ($content === 'ForgotPassword.php') {
            include("ForgotPassword.php");
        } elseif ($content === 'logout.php') {
            echo "<script>window.location.href = 'logout.php';</script>";
        } elseif ($content === 'YourWebsite.php') {
            include("YourWebsite.php");
        } else {
            // Default landing page for creators
            include("dashboard.php");
        }
    }
}
echo "<br><br><br><br><br><br><br><br><br><br>";
echo "</div>";
?>
