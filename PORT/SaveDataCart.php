<?php
// Include configuration file
include_once '../config.php';

// Start by enabling error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Fetch the user ID from cookies or session
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo "The idpk of the creator couldn't be found.";
        exit;
    }

    // Define allowed fields to prevent SQL injection
    $allowedFields = [
        'CommentsNotesSpecialRequests',
        'quantity'
    ];

    // Capture and sanitize POST variables
    $fieldName = $_POST['fieldName'] ?? null;
    $value = trim($_POST['fieldValue'] ?? ''); // Trim whitespace from input value
    $productId = $_POST['productId'] ?? null;
    $action = $_POST['action'] ?? null;  // Determine if the action is update or remove

    // Define contribution percentage
    $ContributionForTRAMANNPORT = 3; // In percent
    $contributionMultiplier = 1 + $ContributionForTRAMANNPORT / 100;

    // Determine user role (fetch from session or database if not directly available)
    $userRole = $_COOKIE['userRole'] ?? 1; // Example: Default to Explorer

    // Validate product ID and check if the field name is allowed
    if ($productId && ($action === 'update' && in_array($fieldName, $allowedFields)) || $action === 'remove') {
        
        if ($action === 'remove') {
            // Handle removal of the product from the cart
            try {
                // Prepare the SQL query to delete the product from the cart
                $stmt = $pdo->prepare("DELETE FROM transactions WHERE IdpkProductOrService = :productId AND IdpkExplorer = :userId AND state = 0");
                $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
                $stmt->execute();

                // Check if the product was successfully removed
                if ($stmt->rowCount() > 0) {
                    echo "Product or service removed successfully.";
                } else {
                    echo "Error: product or service not found or already processed.";
                }
            } catch (PDOException $e) {
                // Log the error and return a generic message
                error_log("Database error: " . $e->getMessage());
                echo "An error occurred while removing the product.";
            }
        } 
        // If the action is 'update', handle updating the cart data
        else if ($action === 'update') {
            // If the field is 'quantity', ensure it's at least 1
            if ($fieldName === 'quantity') {
                // Check if the quantity is numeric, and if it's less than 1, set it to 1
                if (!is_numeric($value) || $value < 1) {
                    $value = 1; // Set quantity to 1 if it's less than 1
                }
            }

            try {
                // Prepare the SQL query to update the cart data
                $stmt = $pdo->prepare("UPDATE transactions SET $fieldName = :value WHERE IdpkProductOrService = :productId AND IdpkExplorer = :userId AND state = 0");
                $stmt->bindParam(':value', $value, PDO::PARAM_STR);
                $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
                $stmt->execute();

                // Get the updated prices from the ProductsAndServices table
                $stmt = $pdo->prepare("SELECT SellingPriceProductOrServiceInDollars, SellingPricePackagingAndShippingInDollars, TaxesInPercent FROM ProductsAndServices WHERE idpk = :productId");
                $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
                $stmt->execute();
                $productData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($productData) {
                    // Calculate price multiplier
                    $priceMultiplier = $contributionMultiplier;
                    if ($userRole !== 1) { // Apply taxes for Creators
                        $priceMultiplier *= 1 + ($productData['TaxesInPercent'] / 100);
                    }

                    // Calculate the total product price, total shipping price, and total price
                    $baseProductPrice = $productData['SellingPriceProductOrServiceInDollars'];
                    $baseShippingPrice = $productData['SellingPricePackagingAndShippingInDollars'];
                    $totalProductPrice = $baseProductPrice * $priceMultiplier * $value;
                    $totalShippingPrice = $baseShippingPrice * $priceMultiplier * $value;
                    $totalPrice = $totalProductPrice + $totalShippingPrice;

                    // Return the updated prices and other details in the response
                    echo json_encode([
                        'success' => true,
                        'baseProductPrice' => $baseProductPrice,
                        'baseShippingPrice' => $baseShippingPrice,
                        'totalProductPrice' => number_format($totalProductPrice, 2),
                        'totalShippingPrice' => number_format($totalShippingPrice, 2),
                        'totalPrice' => number_format($totalPrice, 2),
                        'contributionPercent' => $ContributionForTRAMANNPORT,
                        'taxesPercent' => $productData['TaxesInPercent'],
                        'userRole' => $userRole
                    ]);
                } else {
                    echo "Product or service couldn't be found.";
                }
            } catch (PDOException $e) {
                // Log the error and return a generic message
                error_log("Database error: " . $e->getMessage());
                echo "An error occurred while saving data.";
            }
        }
    } else {
        echo "Invalid field name, value, or idpk of the product or service.";
    }
} else {
    echo "Invalid request method.";
}





?>