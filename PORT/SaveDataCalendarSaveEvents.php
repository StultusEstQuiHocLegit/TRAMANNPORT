<?php
// Include configuration file
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from AJAX request
    $eventData = json_decode(file_get_contents('php://input'), true); // Decode the JSON data
    if (!$eventData) {
        echo json_encode(['error' => 'Invalid JSON data received.']);
        exit;
    }

    // Extract individual event data
    $eventId = $eventData['idpk'] ?? null;
    $eventName = $eventData['eventname'];
    $eventDescription = $eventData['eventdescription'];
    $startTime = $eventData['starttime']; // This is a Unix timestamp
    $endTime = $eventData['endtime'];     // This is a Unix timestamp
    $location = $eventData['location'] ?? null;
    $action = $eventData['action']; // 'create' or 'update'
    $allDay = $eventData['allday'] ?? 1; // Default to 1 (true)

    // Fetch the user ID from cookies
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(['error' => 'User ID not found.']);
        exit;
    }

    try {
        if ($action === 'create') {
            // Insert new event
            $stmt = $pdo->prepare("INSERT INTO CalendarEvents (IdpkExplorerOrCreator, EventName, EventDescription, StartTime, EndTime, Location, AllDay) 
                VALUES (:user_id, :event_name, :event_description, :start_time, :end_time, :location, :all_day)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':event_name', $eventName, PDO::PARAM_STR);
            $stmt->bindParam(':event_description', $eventDescription, PDO::PARAM_STR);
            $stmt->bindParam(':all_day', $allDay, PDO::PARAM_INT);
            $stmt->bindParam(':start_time', $startTime, PDO::PARAM_INT); // Store as integer timestamp
            $stmt->bindParam(':end_time', $endTime, PDO::PARAM_INT);     // Store as integer timestamp
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Event created successfully']);
        } elseif ($action === 'update' && $eventId) {
            // Update existing event
            $stmt = $pdo->prepare("UPDATE CalendarEvents SET EventName = :event_name, EventDescription = :event_description, 
                StartTime = :start_time, EndTime = :end_time, Location = :location, AllDay = :all_day WHERE idpk = :event_id AND IdpkExplorerOrCreator = :user_id");
            $stmt->bindParam(':event_name', $eventName, PDO::PARAM_STR);
            $stmt->bindParam(':event_description', $eventDescription, PDO::PARAM_STR);
            $stmt->bindParam(':all_day', $allDay, PDO::PARAM_INT);
            $stmt->bindParam(':start_time', $startTime, PDO::PARAM_INT); // Store as integer timestamp
            $stmt->bindParam(':end_time', $endTime, PDO::PARAM_INT);     // Store as integer timestamp
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            echo json_encode(['error' => 'Invalid action or missing event ID']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error saving event: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
