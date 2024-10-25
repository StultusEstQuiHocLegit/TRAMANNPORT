<?php
// Include configuration file
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from AJAX request
    $productId = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    // Fetch the user ID from cookies or session
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo "The idpk of the creator couldn't be found.";
        exit;
    }

    // Validate field names to prevent SQL injection
    if (!in_array($field, ['InventoryAvailable', 'InventoryInProduction'])) {
        echo "invalid field";
        exit;
    }

    // Prepare and execute the update statement
    $stmt = $pdo->prepare("UPDATE ProductsAndServices SET $field = :value WHERE idpk = :id");
    $stmt->bindParam(':value', $value, PDO::PARAM_INT);
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Inventory updated successfully";
    } else {
        echo "Error updating inventory";
    }
}




?>