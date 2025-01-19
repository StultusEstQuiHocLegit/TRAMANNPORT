<?php
// Include configuration file
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from AJAX request
    $id = $_POST['id'];

    try {
        // Prepare and execute the query
        $stmt = $pdo->prepare("SELECT CompanyName, idpk FROM ExplorersAndCreators WHERE idpk = :id AND ExplorerOrCreator = '1'");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Return the result as JSON
            echo json_encode($result);
        } else {
            // No matching record found
            echo "Creator wasn't found.";
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error fetching data: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
