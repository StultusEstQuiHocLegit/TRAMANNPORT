<?php
// Include configuration file
include_once '../config.php';

// Fetch the user ID from cookies or session
$user_id = $_COOKIE['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'User ID not found.']);
    exit;
}

// Query to fetch events for the logged-in user
try {
    $stmt = $pdo->prepare("SELECT * FROM CalendarEvents WHERE IdpkExplorerOrCreator = :user_id ORDER BY StartTime ASC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all events
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert the timestamps to ISO 8601 format
    foreach ($events as &$event) {
        $event['allDay'] = (bool)$event['AllDay']; // Cast to boolean for JavaScript compatibility
        $event['start'] = date('c', $event['StartTime']);
        $event['end'] = date('c', $event['EndTime']);
    }

    // Return the events as JSON
    echo json_encode($events ?: []);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching events: ' . $e->getMessage()]);
}

?>
