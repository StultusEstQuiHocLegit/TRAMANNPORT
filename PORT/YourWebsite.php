<script>
    function submitFormYourWebsite() {
        const form = document.getElementById('yourWebsiteForm');
        const formData = new FormData(form);
        document.getElementById('yourWebsiteForm').submit(); // submit the form
    }
</script>





















<?php
// Check if the user requested to delete an image
if (isset($_GET['action']) && $_GET['action'] === 'deleteWebsitePicture' && isset($_GET['slot'])) {
    $slot = intval($_GET['slot']); // Sanitize slot input
    $userId = htmlspecialchars($_COOKIE['user_id']); // Assumes user_id is stored in a cookie
    $uploadDir = "uploads/CreatorWebsitePictures/";

    // Loop through valid extensions to find the file
    $deleted = false;
    foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
        $filePath = $uploadDir . $userId . '_' . $slot . '.' . $ext;
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                $deleted = true;
            } else {
                $errors[] = "Failed to delete the image for slot $slot.";
            }
            break; // Stop once the file is found and deleted
        }
    }

    if ($deleted) {
        // echo "Image for slot $slot deleted successfully.<br><br>";
    } else {
        // $errors[] = "No image found for slot $slot to delete.";
    }
}


















// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize an array to hold error messages
    $errors = [];






// ADD WebsitePictures HERE
$uploadDir = "uploads/CreatorWebsitePictures/";
$validExtensions = ['jpg', 'jpeg', 'png', 'gif'];

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $errors[] = "Failed to create upload directory.";
    }
}

// Get the user's ID from the cookie
$userId = htmlspecialchars($_COOKIE['user_id']); // Assumes user_id is stored in a cookie

// Handle file uploads
for ($i = 1; $i <= 10; $i++) {
    $fileKey = "WebsitePicture" . $i;

    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES[$fileKey]['tmp_name'];
        $fileName = basename($_FILES[$fileKey]['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file extension
        if (!in_array($fileExt, $validExtensions)) {
            $errors[] = "Invalid file type for image $i. Only " . implode(', ', $validExtensions) . " are allowed.";
            continue;
        }

        // Generate the new filename in the desired format
        $newFileName = $uploadDir . $userId . '_' . $i . '.' . $fileExt;

        // Move the uploaded file to the designated directory
        if (!move_uploaded_file($fileTmpName, $newFileName)) {
            $errors[] = "Failed to upload image $i.";
        }
    } elseif (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] != UPLOAD_ERR_NO_FILE) {
        $errors[] = "Error uploading image $i. Code: " . $_FILES[$fileKey]['error'];
    }
}






    // If there are errors, stop the script and display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        echo "<br><br><a href=\"index.php?content=YourWebsite.php\">◀️ GO BACK</a>";
        exit; // Stop further processing
    }





   // If no errors, prepare to update the database
try {
    $stmt = $pdo->prepare('
        UPDATE ExplorersAndCreators SET 
            Heading1 = :Heading1,
            Text1 = :Text1,
            Heading2 = :Heading2,
            Text2 = :Text2,
            Heading3 = :Heading3,
            Text3 = :Text3,
            Heading4 = :Heading4,
            Text4 = :Text4,
            Heading5 = :Heading5,
            Text5 = :Text5,
            Heading6 = :Heading6,
            Text6 = :Text6,
            Heading7 = :Heading7,
            Text7 = :Text7,
            Heading8 = :Heading8,
            Text8 = :Text8,
            Heading9 = :Heading9,
            Text9 = :Text9,
            Heading10 = :Heading10,
            Text10 = :Text10
        WHERE idpk = :id
    ');

    // Loop through headings and texts, convert headings to uppercase before binding
    for ($i = 1; $i <= 10; $i++) {
        // Convert the heading to uppercase
        $heading = strtoupper($_POST['Heading' . $i] ?? '');
        $text = $_POST['Text' . $i] ?? '';

        // Bind values
        $stmt->bindValue(':Heading' . $i, $heading, PDO::PARAM_STR);
        $stmt->bindValue(':Text' . $i, $text, PDO::PARAM_STR);
    }


    // Assuming user_id is stored in a cookie
    $user_id = $_COOKIE['user_id'];
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<br><br><br>Updated successfully!<br><br><a href=\"index.php?content=YourWebsite.php\">▶️ CONTINUE</a>";
    } else {
        echo "Error updating your website.";
        echo "<br><br><br>There was an unespected error updating your your website. Please try again or contact an administrator so we can fix the problem.<br><br><a href=\"index.php?content=YourWebsite.php\">▶️ CONTINUE</a>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

} else {








































echo "<h1>🌐 YOUR WEBSITE</h1>";

echo "<div style=\"opacity: 0.5;\">Here you can create and edit your website (scroll way down to save), which is shown to other explorers and creators.</div>";









echo "<br><br><br><br><br>";


// Define the base path for the uploaded images
$uploadDir = "uploads/CreatorWebsitePictures/" . htmlspecialchars($user['idpk']) . "_";

// Define possible file extensions
$validExtensions = ['jpg', 'jpeg', 'png', 'gif'];

// Arrays of unique placeholders for Heading and Text fields
$headingPlaceholders = [
    1 => 'THIS IS THE FIRST HEADING',
    2 => 'FOR EXAMPLE: ABOUT US',
    3 => 'OUR TEAM',
    4 => 'WHAT OTHERS SAY',
    5 => 'MORE SOCIAL PROOF',
    6 => 'FREQUENTLY ASKED QUESTIONS',
    7 => 'LATEST NEWS',
    8 => 'OUR PARTNERS',
    9 => 'JOIN OUR TEAM',
    10 => 'OUR VISION AND MISSION'
];

$textPlaceholders = [
    1 => 'and this is the first text, you can write what you want, in the following there are some suggestions',
    2 => 'tell the world more about your business, your history, ...',
    3 => 'Who is in your team and what are their qualifications and interests?',
    4 => 'to show the world indirectly that you can be trusted',
    5 => 'people tend to follow other people, tell them about your previous projects and success',
    6 => 'a good way to avoid drowning in emails a offer faster responses to others',
    7 => 'keep them up to date',
    8 => 'honor your partners, show that your are not alone, build trust by naming other trusted brands',
    9 => 'if you are hiring, tell the world about it (maybe also included specifics)',
    10 => 'Why are you doing what you are doing?'
];














echo "<form id=\"yourWebsiteForm\" onsubmit=\"submitFormYourWebsite()\" action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
    // Loop through 1 to 10 for Heading, Text, and Image fields
    for ($i = 1; $i <= 10; $i++) {
        // Heading and Text field names
        $headingField = "Heading" . $i;
        $textField = "Text" . $i;

        // Set unique placeholder for Heading and Text fields
        $headingPlaceholder = isset($headingPlaceholders[$i]) ? $headingPlaceholders[$i] : 'Heading Placeholder';
        $textPlaceholder = isset($textPlaceholders[$i]) ? $textPlaceholders[$i] : 'Text Placeholder';

        // Check if an image exists for the current index
        $imagePath = '';
        foreach ($validExtensions as $extension) {
            $filePath = "{$uploadDir}{$i}.{$extension}";
            if (file_exists($filePath)) {
                $imagePath = $filePath;  // Store the image path if file exists
                break;  // Stop checking other extensions if a file is found
            }
        }

        // Image display
        if ($imagePath) {
            echo "<br><br>";
            echo '<img src="' . $imagePath . '" style=\"width:100%;\"><br>';
            echo "<br><br>";
            echo '<a href="index.php?content=YourWebsite.php&action=deleteWebsitePicture&slot=' . $i . '" onclick="return confirm(\'Are you sure you want to delete this picture?\');" style="opacity: 0.5;">❌ REMOVE</a>';
            echo "<br><br>";
        }

        // File input
        echo '<input type="file" name="WebsitePicture' . $i . '" id="WebsitePicture' . $i . '" accept="image/*">';
        echo '<label for="WebsitePicture' . $i . '">';
        echo "<br>upload picture";
        echo "</label>";














        echo "<br><br>";

        // Heading Input
        echo '<input type="text" id="' . $headingField . '" name="' . $headingField . '" value="' . htmlspecialchars($user[$headingField] ?? '', ENT_QUOTES, 'UTF-8') . '" placeholder="' . htmlspecialchars($headingPlaceholder, ENT_QUOTES, 'UTF-8') . '" style="width: 50%;" oninput="this.value = this.value.toUpperCase();">';
        // echo ' <label for="' . $headingField . '">' . 'Heading ' . $i . '</label>';

        echo "<br><br>";

        // Textarea Input
        echo '<textarea id="' . $textField . '" name="' . $textField . '" rows="10" cols="50" placeholder="' . htmlspecialchars($textPlaceholder, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($user[$textField] ?? '', ENT_QUOTES, 'UTF-8') . '</textarea>';
        // echo ' <label for="' . $textField . '">' . 'Text ' . $i . '</label>';

        echo "<br><br><br><br><br>";
    }
    echo "<a href='javascript:void(0);' class='mainbutton' onclick='submitFormYourWebsite()'>↗️ SAVE</a>";
echo "</form>";




















}
?>


