<?php
// Include configuration file
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from AJAX request
    $productId = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];
    $updatedAvailable = $_POST['updatedAvailable'] ?? null;

    // Fetch the user ID from cookies or session
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo "The idpk of the creator couldn't be found.";
        exit;
    }

    // Validate field names to prevent SQL injection
    if (!in_array($field, ['InventoryAvailable', 'InventoryInProduction'])) {
        echo "Invalid field";
        exit;
    }

    try {
        // If the field being updated is Inventory In Production
        if ($field === 'InventoryInProduction') {
            // First, update the Inventory In Production
            $stmt = $pdo->prepare("UPDATE ProductsAndServices SET InventoryInProduction = :value WHERE idpk = :id");
            $stmt->bindParam(':value', $value, PDO::PARAM_INT);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();

            // Now update Inventory Available if provided
            if ($updatedAvailable !== null) {
                $stmt = $pdo->prepare("UPDATE ProductsAndServices SET InventoryAvailable = :updatedAvailable WHERE idpk = :id");
                $stmt->bindParam(':updatedAvailable', $updatedAvailable, PDO::PARAM_INT);
                $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
                $stmt->execute();
            }

        } else {
            // For direct updates to Inventory Available
            $stmt = $pdo->prepare("UPDATE ProductsAndServices SET $field = :value WHERE idpk = :id");
            $stmt->bindParam(':value', $value, PDO::PARAM_INT);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
        }

        // If the execution reaches here, it means the updates were successful
        echo "Inventory updated successfully.";

    } catch (PDOException $e) {
        // Catch any database errors and echo the message
        echo "Error updating inventory: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
