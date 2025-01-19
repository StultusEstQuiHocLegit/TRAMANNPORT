<?php
// Include configuration file
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from AJAX request
    $requestData = json_decode(file_get_contents('php://input'), true); // Decode the JSON data
    if (!$requestData) {
        echo json_encode(['error' => 'Invalid JSON data received.']);
        exit;
    }

    // Fetch the user ID from cookies or session
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo "The idpk of the creator couldn't be found.";
        exit;
    }

    // Extract individual request data
    $query = $requestData['query'] ?? null; // The user's search query
    $type = $requestData['type'] ?? null;
    $user_id = $_COOKIE['user_id'] ?? null; // Fetch the user ID from cookies

    if (!$user_id) {
        echo json_encode(['error' => 'User ID not found.']);
        exit;
    }

    if (!$query || strlen(trim($query)) < 1) {
        echo json_encode(['error' => 'Search query is required.']);
        exit;
    }

    try {
        if ($type === 'ManualSelling') {
            // Query for Manual Selling
            $stmt = $pdo->prepare("
                SELECT DISTINCT c.IfManualFurtherInformation, c.TimestampCreation
                FROM carts c
                INNER JOIN transactions t ON c.idpk = t.IdpkCart
                INNER JOIN ProductsAndServices ps ON t.IdpkProductOrService = ps.idpk
                WHERE ps.IdpkCreator = :user_id
                AND c.IfManualFurtherInformation LIKE :query
                ORDER BY c.TimestampCreation DESC
                LIMIT 5
            ");
        } elseif ($type === 'ManualBuying') {
            // Query for Manual Buying
            $stmt = $pdo->prepare("
                SELECT DISTINCT IfManualFurtherInformation, TimestampCreation
                FROM carts
                WHERE IdpkExplorerOrCreator = :user_id
                AND IfManualFurtherInformation LIKE :query
                ORDER BY TimestampCreation DESC
                LIMIT 5
            ");
        } else {
            echo json_encode(['error' => 'Invalid operation type.']);
            exit;
        }

        $stmt->execute([
            ':user_id' => $user_id,
            ':query' => '%' . $query . '%'
        ]);

        // Fetch results and format the response
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fullText = $row['IfManualFurtherInformation'];
            $results[] = [
                'text' => mb_strimwidth($fullText, 0, 30, "..."),
                'fullText' => $fullText,
            ];
        }

        echo json_encode(['success' => true, 'suggestions' => $results]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error fetching suggestions: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
