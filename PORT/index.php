<?php
include ("header.php"); // Include the header
echo "<div class=\"content\">";

// Check if the user is logged in using cookies
$isLoggedIn = isset($_COOKIE['user_id']);
$user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

$userRole = null; // Initialize user role

if ($user_id !== null) {
    try {
        // Prepare the SQL query to get the user role
        $stmt = $pdo->prepare('SELECT ExplorerOrCreator FROM ExplorersAndCreators WHERE idpk = :id');
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if a role was found
        if ($result) {
            $userRole = (int)$result['ExplorerOrCreator']; // Cast to integer if needed
        } else {
            // Handle case where user role is not found
            $userRole = null; // Or set to a default value
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Database error: " . $e->getMessage();
    }
}

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

// Fetch user ID from cookies
$user_id = $_COOKIE['user_id'];

// Connection to the database
try {
    // Create a new PDO instance
    $dsn = "mysql:host=$mysqlDbServer;dbname=$mysqlDbName;charset=utf8";
    $pdo = new PDO($dsn, $mysqlDbUser, $mysqlDbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the user's data from the database
    $stmt = $pdo->prepare('SELECT * FROM ExplorersAndCreators WHERE idpk = :id');
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "No user found with the given idpk.";
        exit();
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

    include ("ExchangeRates.php"); // include ExchangeRates.php for recalculation of prices

    // User is logged in, check the role from cookie
    if ($userRole === 0) {
        // Explorer
        if ($content === 'explore.php') {
            include("explore.php");
        } elseif ($content === 'cart.php') {
            include("cart.php");
        } elseif ($content === 'PreviousCarts.php') {
            include("PreviousCarts.php");
        } elseif ($content === 'help.php') {
            include("help.php");
        } elseif ($content === 'account.php') {
            include("account.php");
        } elseif ($content === 'calendar.php') {
            include("calendar.php");
        } elseif ($content === 'ForgotPassword.php') {
            include("ForgotPassword.php");
        } elseif ($content === 'menu.php') {
            include("menu.php");
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
        } elseif ($content === 'documents.php') {
            include("documents.php");
        } elseif ($content === 'orders.php') {
            include("orders.php");
        } elseif ($content === 'ManualSelling.php') {
            include("ManualSelling.php");
        } elseif ($content === 'inventory.php') {
            include("inventory.php");
        } elseif ($content === 'products.php') {
            include("products.php");
        } elseif ($content === 'CustomerRelationships.php') {
            include("CustomerRelationships.php");
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
        } elseif ($content === 'ManualBuying.php') {
            include("ManualBuying.php");
        } elseif ($content === 'accounting.php') {
            include("accounting.php");
        } elseif ($content === 'help.php') {
            include("help.php");
        } elseif ($content === 'account.php') {
            include("account.php");
        } elseif ($content === 'calendar.php') {
            include("calendar.php");
        } elseif ($content === 'ForgotPassword.php') {
            include("ForgotPassword.php");
        } elseif ($content === 'menu.php') {
            include("menu.php");
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

