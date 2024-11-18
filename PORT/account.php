<?php
if (isset($_GET['action']) && $_GET['action'] === 'deleteProfilePicture' && isset($_GET['idpk'])) {
    $idpk = $_COOKIE['user_id'];
    $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];
    $uploadDir = './uploads/AccountPictures/';
    $deleted = false;

    foreach ($imageExtensions as $ext) {
        $potentialPath = $uploadDir . $idpk . '.' . $ext;
        if (file_exists($potentialPath)) {
            if (unlink($potentialPath)) {
                $deleted = true;
                break;
            }
        }
    }

    if ($deleted) {
        // echo "Profile picture successfully removed.";
    } else {
        // echo "No profile picture found to delete.";
    }
}































// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize an array to hold error messages
    $errors = [];

    // Check required fields
    $requiredFields = [
        'email',
        'password',
        'FirstName', 
        'LastName', 
        'street', 
        'HouseNumber', 
        'ZIPCode', 
        'city', 
        'country',
        'IBAN'
    ];

    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[] = ucfirst($field) . " is required.";
        }
    }

    // Handle profile picture upload (if there's an error or no file, it won't break form submission)
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profilePicture']['tmp_name'];
            $fileName = $_FILES['profilePicture']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Validate file extension (allowed types)
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "Invalid file type. Only PNG, JPG, JPEG, GIF, and SVG files are allowed.";
            } else {
                // Set the upload directory
                $uploadDir = './uploads/AccountPictures/';
                
                // Assuming user ID is stored in a cookie (or another source)
                $user_id = $_COOKIE['user_id'];

                // Delete old profile picture if exists
                foreach ($allowedExtensions as $ext) {
                    $existingFilePath = $uploadDir . $user_id . '.' . $ext;
                    if (file_exists($existingFilePath)) {
                        unlink($existingFilePath); // Delete the old file
                    }
                }

                // Generate the new file path
                $destPath = $uploadDir . $user_id . '.' . $fileExtension;

                // Move the uploaded file
                if (!move_uploaded_file($fileTmpPath, $destPath)) {
                    $errors[] = "Error moving the uploaded file.";
                }
            }
        } else {
            $errors[] = "There was an error uploading the file.";
        }
    }

    // If there are errors, stop the script and display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        echo "<br><br><a href=\"index.php?content=account.php\">GO BACK</a>";
        exit; // Stop further processing
    }





   // If no errors, prepare to update the database
try {
    // Validate CloseOnlineShopIfPhysicalShopIsClosed based on CanExplorersVisitYou
    if (isset($_POST['CanExplorersVisitYou']) && $_POST['CanExplorersVisitYou'] == '0') {
        // If CanExplorersVisitYou is '0', set CloseOnlineShopIfPhysicalShopIsClosed to '0'
        $_POST['CloseOnlineShopIfPhysicalShopIsClosed'] = '0';
    }

    // Connection to the database (ensure this is already established in your script)
    $dsn = "mysql:host=$mysqlDbServer;dbname=$mysqlDbName;charset=utf8";
    $pdo = new PDO($dsn, $mysqlDbUser, $mysqlDbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve the form data
    $existingPassword = $_POST['password']; // hidden field with the current hashed password
    $newPassword = $_POST['NewPassword'];

    // Decide which password to use
    if (!empty($newPassword)) {
        // If a new password is provided, hash it
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    } else {
        // Otherwise, use the existing hashed password
        $hashedPassword = $existingPassword;
    }

    // Prepare the update statement with all relevant fields
    $stmt = $pdo->prepare('
        UPDATE ExplorersAndCreators SET 
            email = :email,
            password = :password,
            PhoneNumber = :PhoneNumber,
            FirstName = :FirstName,
            LastName = :LastName, 
            street = :street, 
            HouseNumber = :HouseNumber, 
            ZIPCode = :ZIPCode, 
            city = :city, 
            country = :country, 
            planet = :planet, 
            IBAN = :IBAN,
            darkmode = :darkmode,
            OpeningHoursMondayOpening = :OpeningHoursMondayOpening,
            OpeningHoursMondayClosing = :OpeningHoursMondayClosing,
            OpeningHoursTuesdayOpening = :OpeningHoursTuesdayOpening,
            OpeningHoursTuesdayClosing = :OpeningHoursTuesdayClosing,
            OpeningHoursWednesdayOpening = :OpeningHoursWednesdayOpening,
            OpeningHoursWednesdayClosing = :OpeningHoursWednesdayClosing,
            OpeningHoursThursdayOpening = :OpeningHoursThursdayOpening,
            OpeningHoursThursdayClosing = :OpeningHoursThursdayClosing,
            OpeningHoursFridayOpening = :OpeningHoursFridayOpening,
            OpeningHoursFridayClosing = :OpeningHoursFridayClosing,
            OpeningHoursSaturdayOpening = :OpeningHoursSaturdayOpening,
            OpeningHoursSaturdayClosing = :OpeningHoursSaturdayClosing,
            OpeningHoursSundayOpening = :OpeningHoursSundayOpening,
            OpeningHoursSundayClosing = :OpeningHoursSundayClosing,
            OpeningHoursNationalHolidaysOpening = :OpeningHoursNationalHolidaysOpening,
            OpeningHoursNationalHolidaysClosing = :OpeningHoursNationalHolidaysClosing,
            CompanyName = :CompanyName,
            VATID = :VATID,
            PhoneNumberForExplorersAsContact = :PhoneNumberForExplorersAsContact,
            EmailForExplorersAsContact = :EmailForExplorersAsContact,
            ShortDescription = :ShortDescription,
            LongDescription = :LongDescription,
            LinksToSocialMediaAndOtherSites = :LinksToSocialMediaAndOtherSites,
            ExplorerOrCreator = :ExplorerOrCreator,
            ShowAddressToExplorers = :ShowAddressToExplorers,
            CanExplorersVisitYou = :CanExplorersVisitYou,
            CloseOnlineShopIfPhysicalShopIsClosed = :CloseOnlineShopIfPhysicalShopIsClosed
        WHERE idpk = :id
    ');

    // Bind parameters
    $stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':PhoneNumber', $_POST['PhoneNumber'], PDO::PARAM_STR);
    $stmt->bindParam(':FirstName', $_POST['FirstName'], PDO::PARAM_STR);
    $stmt->bindParam(':LastName', $_POST['LastName'], PDO::PARAM_STR);
    $stmt->bindParam(':street', $_POST['street'], PDO::PARAM_STR);
    $stmt->bindParam(':HouseNumber', $_POST['HouseNumber'], PDO::PARAM_INT);
    $stmt->bindParam(':ZIPCode', $_POST['ZIPCode'], PDO::PARAM_STR);
    $stmt->bindParam(':city', $_POST['city'], PDO::PARAM_STR);
    $stmt->bindParam(':country', $_POST['country'], PDO::PARAM_STR);
    $stmt->bindParam(':planet', $_POST['planet'], PDO::PARAM_STR);
    $stmt->bindParam(':IBAN', $_POST['IBAN'], PDO::PARAM_STR);
    $stmt->bindParam(':darkmode', $_POST['darkmode'], PDO::PARAM_STR);

    // Bind opening hours
    $stmt->bindParam(':OpeningHoursMondayOpening', $_POST['OpeningHoursMondayOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursMondayClosing', $_POST['OpeningHoursMondayClosing'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursTuesdayOpening', $_POST['OpeningHoursTuesdayOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursTuesdayClosing', $_POST['OpeningHoursTuesdayClosing'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursWednesdayOpening', $_POST['OpeningHoursWednesdayOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursWednesdayClosing', $_POST['OpeningHoursWednesdayClosing'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursThursdayOpening', $_POST['OpeningHoursThursdayOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursThursdayClosing', $_POST['OpeningHoursThursdayClosing'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursFridayOpening', $_POST['OpeningHoursFridayOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursFridayClosing', $_POST['OpeningHoursFridayClosing'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursSaturdayOpening', $_POST['OpeningHoursSaturdayOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursSaturdayClosing', $_POST['OpeningHoursSaturdayClosing'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursSundayOpening', $_POST['OpeningHoursSundayOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursSundayClosing', $_POST['OpeningHoursSundayClosing'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursNationalHolidaysOpening', $_POST['OpeningHoursNationalHolidaysOpening'], PDO::PARAM_STR);
    $stmt->bindParam(':OpeningHoursNationalHolidaysClosing', $_POST['OpeningHoursNationalHolidaysClosing'], PDO::PARAM_STR);

    // Bind other text fields
    $stmt->bindParam(':CompanyName', $_POST['CompanyName'], PDO::PARAM_STR);
    $stmt->bindParam(':VATID', $_POST['VATID'], PDO::PARAM_STR);
    $stmt->bindParam(':PhoneNumberForExplorersAsContact', $_POST['PhoneNumberForExplorersAsContact'], PDO::PARAM_STR);
    $stmt->bindParam(':EmailForExplorersAsContact', $_POST['EmailForExplorersAsContact'], PDO::PARAM_STR);
    $stmt->bindParam(':ShortDescription', $_POST['ShortDescription'], PDO::PARAM_STR);
    $stmt->bindParam(':LongDescription', $_POST['LongDescription'], PDO::PARAM_STR);
    $stmt->bindParam(':LinksToSocialMediaAndOtherSites', $_POST['LinksToSocialMediaAndOtherSites'], PDO::PARAM_STR);

    // Handle checkbox values
    $explorerOrCreator = isset($_POST['ExplorerOrCreator']) ? (int) $_POST['ExplorerOrCreator'] : 0; // Default to 0 for Explorer
    $showAddressToExplorers = isset($_POST['ShowAddressToExplorers']) ? (int) $_POST['ShowAddressToExplorers'] : 0;
    $canExplorersVisitYou = isset($_POST['CanExplorersVisitYou']) ? (int) $_POST['CanExplorersVisitYou'] : 0;
    $closeOnlineShopIfPhysicalShopIsClosed = isset($_POST['CloseOnlineShopIfPhysicalShopIsClosed']) ? (int) $_POST['CloseOnlineShopIfPhysicalShopIsClosed'] : 0;
    
    // Ensure that all values are bound to the statement
    $stmt->bindParam(':ExplorerOrCreator', $explorerOrCreator, PDO::PARAM_INT);
    $stmt->bindParam(':ShowAddressToExplorers', $showAddressToExplorers, PDO::PARAM_INT);
    $stmt->bindParam(':CanExplorersVisitYou', $canExplorersVisitYou, PDO::PARAM_INT);
    $stmt->bindParam(':CloseOnlineShopIfPhysicalShopIsClosed', $closeOnlineShopIfPhysicalShopIsClosed, PDO::PARAM_INT);

    // Assuming user_id is stored in a cookie
    $user_id = $_COOKIE['user_id'];
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<br><br><br>Account updated successfully!<br><br><a href=\"index.php?content=account.php\">CONTINUE</a>";
    } else {
        echo "Error updating account.";
        echo "<br><br><br>There was an unespected error updating your account. Please try again or contact an administrator so we can fix the problem.<br><br><a href=\"index.php?content=account.php\">CONTINUE</a>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

} else {
?>






































<script>
    function toggleCreatorFields() {
        var creatorFields = document.getElementById('creatorFields');
        var isChecked = document.getElementById('ExplorerOrCreator').checked;
        creatorFields.style.display = isChecked ? 'block' : 'none';
    }

    function togglePhysicalShopOpeningHours() {
        var PhysicalShopOpeningHours = document.getElementById('PhysicalShopOpeningHours');
        var isChecked = document.getElementById('CanExplorersVisitYou').checked;
        PhysicalShopOpeningHours.style.display = isChecked ? 'block' : 'none';
    }

    function submitForm() {
        let isValid = true; // Assume the form is valid initially
        const requiredFields = [
            'email',
            'FirstName', 
            'LastName', 
            'street', 
            'HouseNumber', 
            'ZIPCode', 
            'city', 
            'country',
            'IBAN'
        ];

        
        
        requiredFields.forEach(function(field) {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                isValid = false; // If a field is empty, set isValid to false
                input.style.border = '3px solid yellow'; // Highlight empty field
            } else {
                input.style.border = ''; // Reset border color if field is valid
            }
        });

        if (!isValid) {
            alert('Please fill out all required fields.'); // Show alert if any required field is empty
        } else {
            document.getElementById('updateAccountForm').submit(); // Submit the form if all required fields are filled
        }
    }
</script>



<div class="registration-container">
    <h1>⚙️ ACCOUNT</h1>

    <form id="updateAccountForm" action="" method="post" enctype="multipart/form-data">
        <div class="steps">
            idpk: <?php echo htmlspecialchars($user['idpk']); ?>

            <br><br><br><br><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="it's also your login and has to be legit" style="width: 300px;" required>
            <label for="email">email*<br><div style="opacity: 0.4;">(* means that this field is required)</div></label>

            <br>
            <!-- <a href="index.php?content=ForgotPassword.php" style="opacity: 0.4;">CREATE NEW PASSWORD</a> -->
            <!-- hidden field to have a value if no other valie is given -->
            <input type="hidden" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" placeholder="something easy to remember, but hard to guess" style="width: 300px;" required>
            <div style="opacity: 0.4;"><input type="text" id="NewPassword" name="NewPassword" placeholder="just enter if you want to change it" style="width: 300px;">
            <label for="NewPassword">new password (if wished)</label></div>

            <br><br>
            <input type="number" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($user['PhoneNumber']); ?>" placeholder="not required, but it increases your security" style="width: 300px;">
            <label for="PhoneNumber">phone number</label>

            <br><br><br><br><br>
            <?php
                // Get the user's `idpk` (already sanitized with `htmlspecialchars`)
                $idpk = htmlspecialchars($user['idpk']);

                // Define the possible image file extensions
                $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];

                // Base directory for profile pictures
                $uploadDir = './uploads/AccountPictures/';

                // Initialize a variable to hold the profile picture path (if found)
                $profilePicturePath = null;

                // Iterate through the possible extensions and check if the file exists
                foreach ($imageExtensions as $ext) {
                    $potentialPath = $uploadDir . $idpk . '.' . $ext;
                    if (file_exists($potentialPath)) {
                        $profilePicturePath = $potentialPath;
                        break; // Exit the loop once we find the file
                    }
                }

                // Display the profile picture if it exists
                if ($profilePicturePath) {
                    // Output the image tag for the found profile picture
                    echo "<img src=\"$profilePicturePath\" style=\"height:150px;\">";
                    echo "<br><br><a href=\"javascript:void(0);\" onclick=\"confirmRemoval()\" style='opacity: 0.5;'>❌ REMOVE</a>";
                } else {
                    // If no profile picture is found, display nothing
                }
            ?>
            <br><br>
            <input type="file" name="profilePicture" id="profilePicture" accept="image/*">
            <label for="profilePicture"><br><div style="opacity: 0.4;">(upload new profile picture)</div></label>

            <br><br><br><br>
            <input type="text" id="FirstName" name="FirstName" value="<?php echo htmlspecialchars($user['FirstName']); ?>" placeholder="how your friends call you" style="width: 300px;" required>
            <label for="FirstName">first name*</label>

            <br><br>
            <input type="text" id="LastName" name="LastName" value="<?php echo htmlspecialchars($user['LastName']); ?>" placeholder="how the government calls you" style="width: 300px;" required>
            <label for="LastName">last name*</label>

            <br><br>
            <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($user['street']); ?>" placeholder="so we can send you packages" style="width: 300px;" required>
            <label for="street">street*</label>

            <br><br>
            <input type="number" id="HouseNumber" name="HouseNumber" value="<?php echo htmlspecialchars($user['HouseNumber']); ?>" placeholder="42" style="width: 300px;" required>
            <label for="HouseNumber">house number*</label>

            <br><br>
            <input type="number" id="ZIPCode" name="ZIPCode" value="<?php echo htmlspecialchars($user['ZIPCode']); ?>" placeholder="introduced in 1963" style="width: 300px;" required>
            <label for="ZIPCode">ZIP code*</label>

            <br><br>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" placeholder="big city life ..." style="width: 300px;" required>
            <label for="city">city*</label>

            <br><br>
            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" placeholder="Anybody from Wakanda?" style="width: 300px;" required>
            <label for="country">country*</label>

            <br><br>
            <input type="text" id="planet" name="planet" value="<?php echo htmlspecialchars($user['planet']); ?>" placeholder="waiting for the first people from mars" style="width: 300px;">
            <label for="planet">planet</label>

            <br><br><br><br><br>
            <input type="text" id="IBAN" name="IBAN" value="<?php echo htmlspecialchars($user['IBAN']); ?>" placeholder="a long number on your banking card" style="width: 300px;" required>
            <label for="IBAN">IBAN*</label>

            <br><br>
            capital in account (in USD): <?php echo htmlspecialchars($user['CapitalInAccountInDollars']); ?>$

            <br><br>
            level: <?php echo htmlspecialchars($user['level']); ?> (<?php 
                    // Map the user level to corresponding text
                    switch ($user['level']) {
                        case 0:
                            echo "new";
                            break;
                        case 1:
                            echo "experienced";
                            break;
                        case 2:
                            echo "expert";
                            break;
                        case 3:
                            echo "checked expert";
                            break;
                        case 4:
                            echo "official partner";
                            break;
                        default:
                            echo "unknown level";
                            break;
                    }
                ?>)
            
            <br><br><br><br><br>
                <input type="hidden" name="darkmode" value="0">
            <input type="checkbox" id="darkmode" name="darkmode" value="1" <?php echo ($user['darkmode'] == 1) ? 'checked' : ''; ?>>
            <label for="darkmode">darkmode (check if you want things to look way cooler)</label>

            <br><br><br><br>
                <input type="hidden" name="ExplorerOrCreator" value="0">
            <input type="checkbox" id="ExplorerOrCreator" name="ExplorerOrCreator" value="1" <?php echo ($user['ExplorerOrCreator'] == 1) ? 'checked' : ''; ?> onclick="toggleCreatorFields()">
            <label for="ExplorerOrCreator">business account (check if you<br>want to create and sell products too)</label>

            <div id="creatorFields" style="display: <?php echo ($user['ExplorerOrCreator'] == 1) ? 'block' : 'none'; ?>;">
                <br><br><br><br><br>
                <input type="text" id="CompanyName" name="CompanyName" value="<?php echo htmlspecialchars($user['CompanyName']); ?>" placeholder="for example ACME Corporation" style="width: 300px;">
                <label for="CompanyName">company name</label>

                <br><br>
                <input type="text" id="VATID" name="VATID" value="<?php echo htmlspecialchars($user['VATID']); ?>" placeholder="for uncle sam" style="width: 300px;">
                <label for="VATID">VAT ID</label>

                <br><br>
                <input type="number" id="PhoneNumberForExplorersAsContact" name="PhoneNumberForExplorersAsContact" value="<?php echo htmlspecialchars($user['PhoneNumberForExplorersAsContact']); ?>" placeholder="another phone number (or the same)" style="width: 300px;">
                <label for="PhoneNumberForExplorersAsContact">phone number for explorers as contact</label>

                <br><br>
                <input type="email" id="EmailForExplorersAsContact" name="EmailForExplorersAsContact" value="<?php echo htmlspecialchars($user['EmailForExplorersAsContact']); ?>" placeholder="something@like.this" style="width: 300px;">
                <label for="EmailForExplorersAsContact">email for explorers as contact</label>

                <input type="hidden" name="ShowAddressToExplorers" value="0">
                <br><br>
                <input type="checkbox" id="ShowAddressToExplorers" name="ShowAddressToExplorers" value="1" <?php echo ($user['ShowAddressToExplorers'] == 1) ? 'checked' : ''; ?>>
                <label for="ShowAddressToExplorers">check if you want your address to be shown to explorers</label>

                <input type="hidden" name="CanExplorersVisitYou" value="0">
                <br><br><br><br><br>
                <input type="checkbox" id="CanExplorersVisitYou" name="CanExplorersVisitYou" value="1" <?php echo ($user['CanExplorersVisitYou'] == 1) ? 'checked' : ''; ?> onclick="togglePhysicalShopOpeningHours()">
                <label for="CanExplorersVisitYou">check if explorers can visit you at your provided address</label>

<div id="PhysicalShopOpeningHours" style="display: <?php echo ($user['CanExplorersVisitYou'] == 1) ? 'block' : 'none'; ?>;">
    <div align=center><h3>OPENING HOURS</h3></div>
    <div style="opacity: 0.4;">(leave fields empty if your shop isn't open on the specific day)</div>

    <table>
        <thead>
            <tr>
                <th>day</th>
                <th>opening time</th>
                <th>closing time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop through each day to set opening and closing times
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'NationalHolidays'];
            foreach ($days as $day) {
                // Get opening and closing times from the user data
                $openingTime = !empty($user["OpeningHours{$day}Opening"]) ? date('H:i', strtotime($user["OpeningHours{$day}Opening"])) : '';
                $closingTime = !empty($user["OpeningHours{$day}Closing"]) ? date('H:i', strtotime($user["OpeningHours{$day}Closing"])) : '';

                echo "<tr>
                    <td>{$day}</td>
                    <td><input type='time' id='OpeningHours{$day}Opening' name='OpeningHours{$day}Opening' value='" . htmlspecialchars($openingTime) . "'></td>
                    <td><input type='time' id='OpeningHours{$day}Closing' name='OpeningHours{$day}Closing' value='" . htmlspecialchars($closingTime) . "'></td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
                <input type="hidden" name="CloseOnlineShopIfPhysicalShopIsClosed" value="0">
                <br><br>
                <input type="checkbox" id="CloseOnlineShopIfPhysicalShopIsClosed" name="CloseOnlineShopIfPhysicalShopIsClosed" value="1" <?php echo ($user['CloseOnlineShopIfPhysicalShopIsClosed'] == 1) ? 'checked' : ''; ?>>
                <label for="CloseOnlineShopIfPhysicalShopIsClosed">check if your online shop should close if your physical shop is closed</label>
</div>

                <br><br><br><br><br>
                <textarea id="ShortDescription" name="ShortDescription" rows="4" cols="50" placeholder="a short description of your business..."><?php echo htmlspecialchars($user['ShortDescription']); ?></textarea>
                <label for="ShortDescription">short description</label>

                <br><br>
                <textarea id="LongDescription" name="LongDescription" rows="6" cols="50" placeholder="a longer description of your business..."><?php echo htmlspecialchars($user['LongDescription']); ?></textarea>
                <label for="LongDescription">long description</label>

                <br><br>
                <!-- <textarea id="LinksToSocialMediaAndOtherSites" name="LinksToSocialMediaAndOtherSites" rows="4" cols="50" placeholder="social media links, websites, etc."><?php // echo htmlspecialchars($user['LinksToSocialMediaAndOtherSites']); ?></textarea> -->
<textarea id="LinksToSocialMediaAndOtherSites" name="LinksToSocialMediaAndOtherSites" rows="10" style="display: none; text-align: left;" oninput="updateDisplay()">
<?php echo htmlspecialchars(trim($user['LinksToSocialMediaAndOtherSites'] ?? '')); ?>
</textarea>

                <!-- Display Area for Clickable Links -->
                <div id="displayLinks" style="white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; text-align: left;"
                    onclick="handleDisplayClick(event)">
                    <!-- Display will be dynamically updated here -->
                </div>
                <label for="LinksToSocialMediaAndOtherSites">links to social media and other sites<br><div style="opacity: 0.4;">(click on the small field right above to start the editing mode)</div></label>

            </div>
        </div>
        <br><br><br><br><br>
        <a href="javascript:void(0);" class="mainbutton" onclick="submitForm()">↗️ SAVE</a>
    </form>
</div>


<br><br><br><br><br>
<div style="opacity: 0.2;">
    <a href="index.php?content=logout.php">🚪 LOGOUT</a>
</div>




















<?php
}
?>




<script>
function confirmRemoval() {
    const confirmation = confirm("Are you sure you want to remove your profile picture?");
    if (confirmation) {
        window.location.href = "index.php?content=account.php&action=deleteProfilePicture&idpk=<?php echo htmlspecialchars($idpk); ?>";
    }
}





// // Function to update the display area with clickable shortened links
// function updateDisplay() {
//     const text = document.getElementById("LinksToSocialMediaAndOtherSites").value;
// 
//     // Replace URLs with clickable shortened links (first 30 characters) and add a 'link' class for easier targeting
//     const linkedText = text.replace(/(https?:\/\/[^\s]+)/g, function(url) {
//         const displayText = url.length > 30 ? url.substring(0, 30) + "..." : url;
//         return `<a href="${url}" target="_blank" class="link">${displayText}</a>`;
//     });
// 
//     // Display parsed content with clickable shortened links
//     document.getElementById("displayLinks").innerHTML = linkedText;
// }

// Function to update the display area with clickable links showing important parts
function updateDisplay() {
    const text = document.getElementById("LinksToSocialMediaAndOtherSites").value;

    // Regular expression to match valid domain names with optional paths, queries, and fragments
    const urlRegex = /(?:https?:\/\/)?(?:www\.)?([a-zA-Z0-9-]+\.[a-zA-Z]{2,})([\/\w\-\.?&=]*)/g;

    // Replace URLs with clickable links and extract important parts for display
    const linkedText = text.replace(urlRegex, function(match, hostname, pathname) {
        // Create the full URL for the link
        const fullUrl = `https://${hostname}${pathname}`;

        // Remove 'www.' if present
        const displayDomain = hostname.replace('www.', '').toUpperCase(); // Convert domain to uppercase

        // Remove TLDs from hostname
        const domainParts = displayDomain.split('.');
        const cleanDomain = domainParts.length > 1 ? domainParts.slice(0, -1).join('.') : displayDomain; // Join parts except last

        // Get the last part of the pathname for the page name
        const pathParts = pathname.split('/').filter(part => part); 
        const pageName = pathParts.length > 0 ? pathParts[pathParts.length - 1].split(/[?#]/)[0] : ''; // Get the last part without query or fragment

        // Handle the full path for display
        const limitedDomain = cleanDomain.length > 20 ? cleanDomain.substring(0, 20) + '...' : cleanDomain;
        const limitedPageName = pageName.length > 20 ? pageName.substring(0, 20) + '...' : pageName;

        // Convert page name to uppercase if present
        const displayText = pageName ? `${limitedDomain} (${limitedPageName.toUpperCase()})` : limitedDomain;

        return `<a href="${fullUrl}" target="_blank" class="link">${displayText}</a>`;
    });

    // Display parsed content with clickable links
    document.getElementById("displayLinks").innerHTML = linkedText;
}

// Toggle visibility to edit in the textarea
function editContent() {
    const displayDiv = document.getElementById("displayLinks");
    const textarea = document.getElementById("LinksToSocialMediaAndOtherSites");
    
    displayDiv.style.display = "none";
    textarea.style.display = "block";
    textarea.focus();
}

// Handle clicks within displayLinks div
function handleDisplayClick(event) {
    // Check if the clicked element is a link
    if (!event.target.classList.contains('link')) {
        // If not a link, enable edit mode
        editContent();
    }
}

// Close edit mode if clicking outside
function closeEditMode(event) {
    const displayDiv = document.getElementById("displayLinks");
    const textarea = document.getElementById("LinksToSocialMediaAndOtherSites");

    // Check if click is outside both the textarea and displayLinks
    if (!textarea.contains(event.target) && !displayDiv.contains(event.target)) {
        // Hide the textarea and show the displayLinks div
        textarea.style.display = "none";
        displayDiv.style.display = "block";
        
        // Update the display to reflect any changes
        updateDisplay();
    }
}

// Initial load to show the parsed content
updateDisplay();

// Event listener for detecting clicks outside the textarea to close edit mode
document.addEventListener("click", closeEditMode);
</script>