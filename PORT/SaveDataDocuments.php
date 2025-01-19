<?php
// Include configuration file
include_once '../config.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Fetch the user ID from cookies or session
    $user_id = $_COOKIE['user_id'] ?? null;

    if (!$user_id) {
        echo "The idpk of the creator couldn't be found.";
        exit;
    }

    // Check if the idpk is provided
    $idpk = $_POST['idpk'] ?? null;

    if (!$idpk) {
        echo json_encode(["status" => "error", "message" => "The idpk is missing."]);
        exit;
    }

    // Check the action type (save, load, or list)
    $action = $_POST['action'] ?? null;

    if ($action === 'list') {
        // List all documents for the user
        try {
            $stmt = $pdo->prepare("SELECT title, idpk FROM documents WHERE IdpkCreator = :id ORDER BY title ASC");
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($documents) {
                echo json_encode(["status" => "success", "data" => $documents]);
            } else {
                echo json_encode(["status" => "error", "message" => "No documents found."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Error listing documents: " . $e->getMessage()]);
        }
    } elseif ($action === 'create') {
        // create a new document the database
        $title = 'NewDocument';
        $content = trim($_POST['content'] ?? '');
        $timestamp = time();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO documents (title, content, TimestampCreation, TimestampLastEdit, IdpkCreator)
                VALUES (:title, :content, :timestampCreation, :timestampLastEdit, :id)
            ");
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':timestampCreation', $timestamp, PDO::PARAM_INT);
            $stmt->bindParam(':timestampLastEdit', $timestamp, PDO::PARAM_INT);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Get the idpk of the newly created document
            $newIdpk = $pdo->lastInsertId();

            echo json_encode([
                "status" => "success",
                "message" => "Document created successfully.",
                "idpk" => $newIdpk // Send the idpk back in the response
            ]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Error creating document: " . $e->getMessage()]);
        }
    } elseif ($action === 'remove') {
        // remove the document from the database
        $idpk = trim($_POST['idpk'] ?? '');  // Document idpk
        
        if (!$idpk || !$user_id) {
            echo json_encode(["status" => "error", "message" => "Document idpk or user idpk is missing."]);
            exit;
        }
    
        try {
            // First, check if the document exists and if the current user is the creator
            $stmt = $pdo->prepare("
                SELECT IdpkCreator FROM documents WHERE idpk = :idpk
            ");
            $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
            $stmt->execute();
            $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($document) {
                if ($document['IdpkCreator'] == $user_id) {
                    // User is the creator of the document, proceed with deletion
                    $stmt = $pdo->prepare("
                        DELETE FROM documents WHERE idpk = :idpk AND IdpkCreator = :id
                    ");
                    $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
                    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                
                    echo json_encode(["status" => "success", "message" => "Document removed successfully."]);
                } else {
                    // User is not the creator of the document
                    echo json_encode(["status" => "error", "message" => "You are not authorized to remove this document."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Document not found."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Error removing document: " . $e->getMessage()]);
        }
    } elseif ($action === 'save') {
        // Save the title and content to the database
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (!$title || !$content) {
            echo json_encode(["status" => "error", "message" => "Title or content is missing."]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE documents SET title = :title, content = :content, TimestampLastEdit = :timestamp WHERE idpk = :idpk AND IdpkCreator = :id");
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':timestamp', time(), PDO::PARAM_INT);
            $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["status" => "success", "message" => "Document saved successfully."]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Error saving document: " . $e->getMessage()]);
        }
    } elseif ($action === 'saveTitle') {
        // Save the title to the database
        $title = trim($_POST['title'] ?? '');

        if (!$title) {
            echo json_encode(["status" => "error", "message" => "Title is missing."]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE documents SET title = :title, TimestampLastEdit = :timestamp WHERE idpk = :idpk AND IdpkCreator = :id");
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':timestamp', time(), PDO::PARAM_INT);
            $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["status" => "success", "message" => "Document saved successfully."]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Error saving document: " . $e->getMessage()]);
        }
    } elseif ($action === 'load') {
        // Load the document from the database
        try {
            $stmt = $pdo->prepare("SELECT title, content FROM documents WHERE idpk = :idpk AND IdpkCreator = :id");
            $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $document = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($document) {
                echo json_encode(["status" => "success", "data" => $document]);
            } else {
                echo json_encode(["status" => "error", "message" => "Document not found."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Error loading document: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
