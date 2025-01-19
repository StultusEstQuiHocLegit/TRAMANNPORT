<?php
// Include configuration file
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from AJAX request
    $id = $_POST['id'] ?? null;
    $notes = $_POST['notes'] ?? null;

    // Fetch the user ID from cookies or session
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo "The idpk of the creator couldn't be found.";
        exit;
    }

    if (!$id || !$notes) {
        echo "Invalid input data.";
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE CustomerRelationships SET notes = :notes WHERE idpk = :id AND IdpkCreator = :user_id");
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Success: Notes updated.";
        } else {
            echo "No records updated. Either the id doesn't match or you don't have permission.";
        }
    } catch (PDOException $e) {
        // Catch any database errors and echo the message
        echo "Error updating notes: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
