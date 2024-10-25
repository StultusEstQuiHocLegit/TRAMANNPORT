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

    // List of allowed fields to prevent SQL injection
    $allowedFields = [
        'PersonalToDoList',
        'PersonalCollectionOfLinks',
        'PersonalNotes',
        'PersonalStrategicPlaningNotes'
    ];

    // Capture and sanitize POST variables
    $fieldName = $_POST['fieldName'] ?? null;
    $value = trim($_POST['value'] ?? ''); // trimming the value

    // Sanitize inputs
    $value = trim($value);

    // Validate fieldName and value to prevent null or undefined issues
    if (in_array($fieldName, $allowedFields) && !empty($value)) { // Check if value is not empty
        try {
            $stmt = $pdo->prepare("UPDATE ExplorersAndCreators SET $fieldName = :value WHERE idpk = :id");
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            echo "Data saved successfully for $fieldName";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid field name or value.";
    }
}
?>
