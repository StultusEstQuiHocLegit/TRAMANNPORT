<?php
// Include configuration file
include_once '../config.php';

// Ensure this script only runs for valid requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idpk']) && isset($_POST['user_id'])) {
    // Get the data from the POST request
    $idpk = intval($_POST['idpk']);
    $user_id = $_COOKIE['user_id'] ?? null;

    // Validate the user ID (cookie)
    if (!$user_id) {
        echo json_encode(["success" => false, "error" => "The idpk of the creator couldn't be found."]);
        exit;
    }

    // Validate if product idpk is valid
    if ($idpk <= 0) {
        echo json_encode(["success" => false, "error" => "Invalid product ID."]);
        exit;
    }

    // Prepare other values for the transaction
    $quantity = 1; // You could modify this if you need dynamic quantities
    $state = 0; // Default state (unprocessed or pending)
    $timestamp = time(); // current timestamp

    try {
        // Check if a transaction with the same product, user, and state=0 already exists
        $stmt = $pdo->prepare("SELECT quantity FROM transactions WHERE IdpkExplorer = ? AND IdpkProductOrService = ? AND state = ?");
        $stmt->execute([$user_id, $idpk, $state]);
        $existingTransaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingTransaction) {
            // Transaction exists, update the quantity
            $newQuantity = $existingTransaction['quantity'] + 1;
            $stmt = $pdo->prepare("UPDATE transactions SET quantity = ?, TimestampCreation = ? WHERE IdpkExplorer = ? AND IdpkProductOrService = ? AND state = ?");
            $stmt->execute([$newQuantity, $timestamp, $user_id, $idpk, $state]);
            echo json_encode(["success" => true, "message" => "Transaction quantity updated successfully."]);
        } else {
            // No existing transaction, insert a new one
            $stmt = $pdo->prepare("INSERT INTO transactions (TimestampCreation, IdpkExplorer, IdpkProductOrService, quantity, ForTRAMANNPORTInDollars, TaxesInDollars, state) VALUES (?, ?, ?, ?, 0, 0, ?)");
            $stmt->execute([$timestamp, $user_id, $idpk, $quantity, $state]);
            echo json_encode(["success" => true, "message" => "Transaction added successfully."]);
        }

    } catch (PDOException $e) {
        // Handle any database errors and return a JSON response
        echo json_encode(["success" => false, "error" => "Error updating inventory: " . $e->getMessage()]);
    }
} else {
    // Handle invalid request methods or missing data
    echo json_encode(["success" => false, "error" => "Invalid request or missing data."]);
}
?>

