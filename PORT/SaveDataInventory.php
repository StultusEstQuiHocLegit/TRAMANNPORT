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

    if (!in_array($field, ['InventoryAvailable', 'InventoryInProduction', 'InventoryMinimumLevel', 'PersonalNotes'])) {
        echo "invalid field";
        exit;
    }
    
    try {
        if ($field === 'PersonalNotes') {
            // For updating Personal Notes
            $stmt = $pdo->prepare("UPDATE ProductsAndServices SET PersonalNotes = :value WHERE idpk = :id AND IdpkCreator = $user_id");
            $stmt->bindParam(':value', $value, PDO::PARAM_STR); // Note: Use PARAM_STR for text fields
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
        } else if ($field === 'InventoryMinimumLevel') {
            // Same logic as before for Inventory In Production
            $stmt = $pdo->prepare("UPDATE ProductsAndServices SET InventoryMinimumLevel = :value WHERE idpk = :id AND IdpkCreator = $user_id");
            $stmt->bindParam(':value', $value, PDO::PARAM_INT);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
        } else if ($field === 'InventoryInProduction') {
            // Same logic as before for Inventory In Production
            $stmt = $pdo->prepare("UPDATE ProductsAndServices SET InventoryInProduction = :value WHERE idpk = :id AND IdpkCreator = $user_id");
            $stmt->bindParam(':value', $value, PDO::PARAM_INT);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
    
            if ($updatedAvailable !== null) {
                $stmt = $pdo->prepare("UPDATE ProductsAndServices SET InventoryAvailable = :updatedAvailable WHERE idpk = :id AND IdpkCreator = $user_id");
                $stmt->bindParam(':updatedAvailable', $updatedAvailable, PDO::PARAM_INT);
                $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            // For InventoryAvailable and other direct updates
            $stmt = $pdo->prepare("UPDATE ProductsAndServices SET $field = :value WHERE idpk = :id AND IdpkCreator = $user_id");
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
