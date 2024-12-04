<?php
// Include configuration file
include_once '../config.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(['success' => false, 'error' => 'User ID not found.']);
        exit;
    }

    // Fetch posted data
    $TransactionId = $_POST['TransactionId'] ?? null;
    $state = $_POST['state'] ?? null; // The new state value as an integer

    if (!$TransactionId || $state === null) {
        echo json_encode(['success' => false, 'error' => 'Invalid transaction or state.']);
        exit;
    }

    try {
        // Begin a transaction to ensure atomicity
        $pdo->beginTransaction();

        // Prepare the SQL statement to update the transaction state
        $stmt = $pdo->prepare("
            UPDATE transactions t
            INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
            SET t.state = :state
            WHERE t.idpk = :TransactionId
            AND p.IdpkCreator = :userId
        ");
        
        // Bind parameters to the SQL statement
        $stmt->bindParam(':state', $state, PDO::PARAM_INT);
        $stmt->bindParam(':TransactionId', $TransactionId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            // If the state has just been set to 5, decrease the inventory
            if ($state == 5) {
                // Fetch the transaction details (quantity and product ID)
                $transactionDetailsStmt = $pdo->prepare("
                    SELECT IdpkProductOrService, quantity 
                    FROM transactions 
                    WHERE idpk = :TransactionId
                ");
                $transactionDetailsStmt->bindParam(':TransactionId', $TransactionId, PDO::PARAM_INT);
                $transactionDetailsStmt->execute();
                $transaction = $transactionDetailsStmt->fetch(PDO::FETCH_ASSOC);

                if ($transaction) {
                    // Decrease inventory
                    $updateInventoryStmt = $pdo->prepare("
                        UPDATE ProductsAndServices
                        SET InventoryAvailable = InventoryAvailable - :quantity
                        WHERE idpk = :productId
                        AND ManageInventory = 1
                    ");
                    $updateInventoryStmt->bindParam(':quantity', $transaction['quantity'], PDO::PARAM_INT);
                    $updateInventoryStmt->bindParam(':productId', $transaction['IdpkProductOrService'], PDO::PARAM_INT);

                    $updateInventoryStmt->execute();
                } else {
                    throw new Exception('Transaction details not found.');
                }
            }

            // Commit the transaction
            $pdo->commit();

            echo json_encode([
                'success' => true, 
                'message' => 'State updated successfully.',
                'newState' => $state // Send back the updated state
            ]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Failed to update transaction state.']);
        }
    } catch (Exception $e) {
        // Rollback in case of any error
        $pdo->rollBack();

        // Log the error and return a generic message
        error_log("Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred while processing the request.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>

