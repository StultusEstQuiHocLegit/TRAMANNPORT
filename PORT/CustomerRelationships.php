<h1>❤️ BUSINESS RELATIONSHIPS</h1>

<?php
$preselectedOption = "your_customer_relationships"; // add preselected search option
$preselectedViewing = "manage_customer_relationships"; // add preselected viewing

include ("explore.php"); // include explore.php for exploring and searching
echo "<br><br><br><br><br>";
?>





<?php
$profilePictograms = [
    "🐶", "🐱", "🐭", "🐹", "🐰", "🦊", "🐻", "🐼", "🦁", "🐯",
    "🐨", "🐸", "🐵", "🐔", "🐧", "🐦", "🐥", "🦆", "🦅", "🦉",
    "🦇", "🐺", "🐗", "🐴", "🐝", "🐛", "🦋", "🐌", "🐞", "🐜",
    "🪲", "🪳", "🦟", "🦗", "🕷", "🐢", "🐍", "🦎", "🐙", "🦑",
    "🦐", "🦞", "🦀", "🐡", "🐠", "🐟", "🐬", "🐋", "🦈", "🐊",
    "🐅", "🐆", "🦓", "🦍", "🦧", "🐘", "🦛", "🦏", "🐪", "🐫",
    "🦒", "🦘", "🦬", "🐃", "🐂", "🐄", "🐖", "🐏", "🐑", "🦙",
    "🐐", "🦌", "🐓", "🦃", "🐿", "🦫", "🦔"
];


















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////// remove customer relationship from database
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && $_GET['action'] === 'deleteCustomerRelationship' && isset($_GET['idpk'])) {
    // Handle the request to delete the customer relationship
    $idpk = intval($_GET['idpk']);
    $user_id = isset($_COOKIE['user_id']) ? intval($_COOKIE['user_id']) : null; // Retrieve user_id from cookies

    if (!$idpk || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        // Prepare and execute the delete query
        $query = "DELETE FROM CustomerRelationships WHERE idpk = ? AND IdpkCreator = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$idpk, $user_id]);

    } catch (Exception $e) {
        // Handle database errors
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    echo "<script>window.location.href = 'index.php?content=CustomerRelationships.php';</script>";
    exit;
}
















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////// update customer relationship in database
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'updateDatabase' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Initialize an array to hold any errors
    $errors = [];

    // If there are errors, stop the script and display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        exit; // Stop further processing
    }

    // Validate and sanitize input fields
    $profilePictogram = isset($_POST['ProfilePictogram']) ? (int)$_POST['ProfilePictogram'] : 0;
    $firstName = htmlspecialchars(trim($_POST['FirstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['LastName'] ?? ''));
    $title = htmlspecialchars(trim($_POST['title'] ?? ''));
    $companyName = htmlspecialchars(trim($_POST['CompanyName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phoneNumber = filter_var($_POST['PhoneNumber'] ?? '', FILTER_SANITIZE_NUMBER_INT);
    $linksToSocialMedia = htmlspecialchars(trim($_POST['LinksToSocialMediaAndOtherSites'] ?? ''));
    $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));
    $importance = isset($_POST['importance']) ? (int)$_POST['importance'] : 0;
    $state = isset($_POST['state']) ? (int)$_POST['state'] : 0;

    // Prepare and execute the SQL statement for updating the record
    try {
        $stmt = $pdo->prepare(
            "UPDATE CustomerRelationships SET
                ProfilePictogram = :ProfilePictogram,
                FirstName = :FirstName,
                LastName = :LastName,
                title = :title,
                CompanyName = :CompanyName,
                email = :email,
                PhoneNumber = :PhoneNumber,
                LinksToSocialMediaAndOtherSites = :LinksToSocialMediaAndOtherSites,
                notes = :notes,
                importance = :importance,
                state = :state
             WHERE idpk = :idpk AND IdpkCreator = :user_id"
        );

        // Bind parameters
        $stmt->bindParam(':ProfilePictogram', $profilePictogram, PDO::PARAM_INT);
        $stmt->bindParam(':FirstName', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':LastName', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':CompanyName', $companyName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':PhoneNumber', $phoneNumber, PDO::PARAM_INT);
        $stmt->bindParam(':LinksToSocialMediaAndOtherSites', $linksToSocialMedia, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindParam(':importance', $importance, PDO::PARAM_INT);
        $stmt->bindParam(':state', $state, PDO::PARAM_INT);
        $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Redirect upon success
        echo "<script>window.location.href = 'index.php?content=CustomerRelationships.php';</script>";
        exit();
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
        exit();
    }
}
































// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////// user interface for updating customer relationship
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['idpk'])) {
    ?>
    <script>
        // Function to submit the update product form via AJAX
        function submitFormUpdateCustomerRelationship() {
            const form = document.getElementById('editCustomerRelationshipForm');
            const formData = new FormData(form);

            let isValid = true;

            if (!isValid) {
                alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
                return; // Stop submission if validation fails
            }
            if (!isValid) {
                alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
            } else {
                document.getElementById('editCustomerRelationshipForm').submit(); // Submit the form if all required fields are filled
            }
        }
    </script>
    <?php

    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Query the database to get customer relationships details
    $query = "SELECT * FROM CustomerRelationships WHERE idpk = ? AND IdpkCreator = $user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idpk]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the product exists
    if ($product) {
        ?>
        <!-- Div for editing existing customer relationships -->
        <div id="editCustomerRelationshipDiv" class="steps">
            <div align=center>
                <h3>✏️ EDIT</h3>
            </div>
            <form id="editCustomerRelationshipForm" action="index.php?content=CustomerRelationships.php&action=updateDatabase&idpk=<?php echo $product['idpk']; ?>" method="post" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitFormUpdateCustomerRelationship();">
                <!-- tell that this is the form for updating -->
                <input type="hidden" name="action" value="update">
                <!-- tell the idpk of the product or service -->
                <input type="hidden" name="productId" value="<?php echo htmlspecialchars($product['idpk']); ?>">

                <!-- Dropdown for ProfilePictogram -->
                <input type="hidden" name="ProfilePictogram" value="0">
                <select id="ProfilePictogram" name="ProfilePictogram" style="width: 100px; font-size: 3rem;">
                    <?php foreach ($profilePictograms as $key => $emoji): ?>
                        <option value="<?= $key ?>" <?= ($key == $product['ProfilePictogram']) ? 'selected' : '' ?>>
                            <?= $emoji ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="ProfilePictogram">profile pictogram</label>
                    
                <br><br>
                <input type="text" id="FirstName" name="FirstName" value="<?php echo htmlspecialchars($product['FirstName']); ?>" placeholder="how you call him" style="width: 500px;">
                <label for="FirstName">first name</label>
                    
                <br><br>
                <input type="text" id="LastName" name="LastName" value="<?php echo htmlspecialchars($product['LastName']); ?>" placeholder="how the government calls him" style="width: 500px;">
                <label for="LastName">last name</label>
                    
                <br><br>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" placeholder="for example: emperor" style="width: 500px;">
                <label for="title">title</label>
                    
                <br><br>
                <input type="text" id="CompanyName" name="CompanyName" value="<?php echo htmlspecialchars($product['CompanyName']); ?>" placeholder="his workplace" style="width: 500px;">
                <label for="CompanyName">company name <span id="ShowMatchingCompanyName"></span><br><div style="opacity: 0.4;">(you can also enter an idpk of a creator from within TRAMANN PORT to connect automatically)</div></label>

                <!-- Trigger the JavaScript functionality -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const inputField = document.getElementById('CompanyName');
                        if (inputField) {
                            inputField.dispatchEvent(new Event('input')); // Triggers the input event on the field
                        }
                    });
                </script>
                    
                <br><br><br><br>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($product['email']); ?>" placeholder="Or do you use homing pigeons?" style="width: 500px;">
                <label for="email">email</label>
                    
                <br><br>
                <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($product['PhoneNumber']); ?>" placeholder="ring, ring, ring   ; )" style="width: 500px;">
                <label for="PhoneNumber">phone number</label>
                    
                <br><br>
                <!-- <input type="text" id="LinksToSocialMediaAndOtherSites" name="LinksToSocialMediaAndOtherSites" value="<?php // echo htmlspecialchars($product['LinksToSocialMediaAndOtherSites']); ?>" placeholder="other ways to connect" style="width: 500px;"> -->
<textarea id="LinksToSocialMediaAndOtherSites" name="LinksToSocialMediaAndOtherSites" rows="10" style="display: none; text-align: left;" oninput="updateDisplay()">
<?php echo htmlspecialchars(trim($product['LinksToSocialMediaAndOtherSites'] ?? '')); ?>
</textarea>
                <!-- Display Area for Clickable Links -->
                <div id="displayLinks" style="white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; text-align: left;"
                    onclick="handleDisplayClick(event)">
                    <!-- Display will be dynamically updated here -->
                </div>
                <label for="LinksToSocialMediaAndOtherSites">links to social media and other sites<br><div style="opacity: 0.4;">(click on the small field right above to start the editing mode)</div></label>
                    
                <br><br><br><br>
                <textarea id="notes" name="notes" rows="10" style="width: 100%;" placeholder="enter some information you want to save in association with this customer"><?php echo htmlspecialchars($product['notes']); ?></textarea>
                <label for="notes">notes</label>

                <!-- Dropdown for state -->
                <br><br><br><br><br>
                <input type="hidden" name="state" value="0">
                <select id="state" name="state" style="width: 200px;">
                    <option value="0" <?= ($product['state'] == 0) ? 'selected' : '' ?>>potential customer</option>
                    <option value="1" <?= ($product['state'] == 1) ? 'selected' : '' ?>>existing customer</option>
                    <option value="2" <?= ($product['state'] == 2) ? 'selected' : '' ?>>former customer</option>
                </select>
                <label for="state">state</label>

                <!-- Dropdown for importance -->
                <br><br>
                <input type="hidden" name="importance" value="0">
                <select id="importance" name="importance" style="width: 200px;">
                    <option value="0" <?= ($product['importance'] == 0) ? 'selected' : '' ?>>initial contact</option>
                    <option value="1" <?= ($product['importance'] == 1) ? 'selected' : '' ?>>emerging partner</option>
                    <option value="2" <?= ($product['importance'] == 2) ? 'selected' : '' ?>>partner</option>
                    <option value="3" <?= ($product['importance'] == 3) ? 'selected' : '' ?>>core partner</option>
                    <option value="4" <?= ($product['importance'] == 4) ? 'selected' : '' ?>>prime partner</option>
                </select>
                <label for="importance">importance</label>
                
                <br><br><br><br><br>
                <div align=center>
                    <?php
                        echo "<a href='javascript:void(0);' class='mainbutton' onclick='submitFormUpdateCustomerRelationship()'>↗️ SAVE</a>";
                    ?>
                </div>
            </form>

            <br><br><br><br><br>
            <div style="text-align: center;"><?php
                // echo "<a href=\"javascript:void(0);\" onclick=\"confirmRemoval({$product['idpk']})\" style='opacity: 0.2;'>❌ REMOVE</a>";
                // echo "<a href='index.php?content=CustomerRelationships.php&action=deleteCustomerRelationship&idpk={$product['idpk']} style='opacity: 0.2;'>❌ REMOVE</a>";
                echo "<a href='#' onclick='confirmDeletion(\"index.php?content=CustomerRelationships.php&action=deleteCustomerRelationship&idpk={$product['idpk']}\")' style='opacity: 0.2;'>❌ REMOVE</a>";

                echo "<script>
                        function confirmDeletion(url) {
                            if (confirm(\"Do you really want to remove the customer relationship?\")) {
                                window.location.href = url;
                            }
                        }
                        </script>";
            ?></div>
        </div>
    <?php
    } else {
        // echo "Product or service with idpk $idpk not found.";
    }
} else {






















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////// create customer relationship in database
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    // Initialize an array to hold any errors
    $errors = [];

    // Validate and sanitize input fields
    $profilePictogram = isset($_POST['ProfilePictogram']) ? (int) $_POST['ProfilePictogram'] : 0;
    $firstName = htmlspecialchars(trim($_POST['FirstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['LastName'] ?? ''));
    $title = htmlspecialchars(trim($_POST['title'] ?? ''));
    $companyName = htmlspecialchars(trim($_POST['CompanyName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phoneNumber = filter_var($_POST['PhoneNumber'] ?? '', FILTER_SANITIZE_NUMBER_INT);
    $linksToSocialMedia = htmlspecialchars(trim($_POST['LinksToSocialMediaAndOtherSites'] ?? ''));
    $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));
    $importance = isset($_POST['importance']) ? (int) $_POST['importance'] : 0;
    $state = isset($_POST['state']) ? (int) $_POST['state'] : 0;

    if ($importance === false || $importance < 0 || $importance > 4) {
        $errors[] = 'Invalid importance value.';
    }
    if ($state === false || $state < 0 || $state > 2) {
        $errors[] = 'Invalid state value.';
    }

    // If there are errors, stop the script and display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        exit; // Stop further processing
    }

    $timestampCreation = time();  // Get the current time as UNIX timestamp
    $idpkCreator = htmlspecialchars($user['idpk']);  // Fetch the creator's idpk from user session or passed variable

    // Prepare and execute the SQL statement
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO CustomerRelationships  (
                ProfilePictogram, 
                FirstName, 
                LastName, 
                title, 
                CompanyName, 
                email, 
                PhoneNumber, 
                LinksToSocialMediaAndOtherSites, 
                notes, 
                importance, 
                state, 
                TimestampCreation, 
                IdpkCreator
            ) VALUES (
                :ProfilePictogram, 
                :FirstName, 
                :LastName, 
                :title, 
                :CompanyName, 
                :email, 
                :PhoneNumber, 
                :LinksToSocialMediaAndOtherSites, 
                :notes, 
                :importance, 
                :state, 
                :TimestampCreation, 
                :IdpkCreator
            )"
        );

        // Bind parameters
        $stmt->bindParam(':ProfilePictogram', $profilePictogram, PDO::PARAM_STR);
        $stmt->bindParam(':FirstName', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':LastName', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':CompanyName', $companyName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':PhoneNumber', $phoneNumber, PDO::PARAM_INT);
        $stmt->bindParam(':LinksToSocialMediaAndOtherSites', $linksToSocialMedia, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindParam(':importance', $importance, PDO::PARAM_INT);
        $stmt->bindParam(':state', $state, PDO::PARAM_INT);
        $stmt->bindValue(':TimestampCreation', time(), PDO::PARAM_INT);
        $stmt->bindValue(':IdpkCreator', $idpkCreator, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Redirect upon success
        echo "<script>window.location.href = 'index.php?content=CustomerRelationships.php';</script>";
        exit();
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
        exit();
    }
}

?>























<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- // /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// some java script stuff -->
<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<script>
    // Function to show the create customer relationships form and hide others
    function showCreateCustomerRelationship() {
        document.getElementById('createCustomerRelationshipDiv').style.display = 'block';
        document.getElementById('listCustomerRelationshipsDiv').style.display = 'none';
        document.getElementById('editCustomerRelationshipDiv').style.display = 'none';
        document.getElementById('createNewCustomerRelationship').style.display = 'none';
    }

    // Function to hide all forms and show the customer relationships list
    function showCustomerRelationshipsList() {
        document.getElementById('createCustomerRelationshipDiv').style.display = 'none';
        document.getElementById('editCustomerRelationshipDiv').style.display = 'none';
        document.getElementById('createNewCustomerRelationship').style.display = '';
        document.getElementById('listCustomerRelationshipsDiv').style.display = 'block';
        loadCustomerRelationshipsList();  // Load the list of customer relationships
    }








    function submitFormCreateCustomerRelationship() {
        const form = document.getElementById('createCustomerRelationshipForm');
        const formData = new FormData(form);

        let isValid = true;

        if (!isValid) {
            alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
            return; // Stop submission if validation fails
        }
        if (!isValid) {
            alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
        } else {
            document.getElementById('createCustomerRelationshipForm').submit(); // Submit the form if all required fields are filled
        }
    }











    // Function to submit the update product form via AJAX
    function submitFormUpdateCustomerRelationship() {
        const form = document.getElementById('editCustomerRelationshipForm');
        const formData = new FormData(form);

        let isValid = true;

        if (!isValid) {
            alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
            return; // Stop submission if validation fails
        }
        if (!isValid) {
            alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
        } else {
            document.getElementById('editCustomerRelationshipForm').submit(); // Submit the form if all required fields are filled
        }
    }









    // Function to load the list of the customer relationships dynamically
    function loadCustomerRelationshipsList() {
        fetch('CustomerRelationships.php')  // Sends a GET request to CustomerRelationships.php
        .then(response => response.json())  // Expect a JSON response
        .catch(error => console.error('error:', error));
    }





    







    window.onload = function() {
        showCustomerRelationshipsList();  // Automatically load customer relationships list when the page loads
    }









    function confirmPhoneCall(telLink) {
        const userConfirmed = confirm("Do you want to start a phone call?");
        if (userConfirmed) {
            window.location.href = telLink; // Proceed to call
        }
    }
</script>






















































<!-- Button to toggle showing the create customer relationship form -->
<a href="javascript:void(0)" id="createNewCustomerRelationship" class="button" onclick="showCreateCustomerRelationship()">❤️ CREATE NEW BUSINESS RELATIONSHIP</a>




<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- // //////////////////////////////////////////////////////////////////////////////////////////////////// user interface for creating customer relationship -->
<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<!-- Div for creating new customer relationships (hidden by default) -->
<div id="createCustomerRelationshipDiv" class="steps" style="display: none;">
    <div align=center>
        <h3>❤️ CREATE</h3>
    </div>
    <form id="createCustomerRelationshipForm" onsubmit="submitFormCreateCustomerRelationship()" action="" method="post" enctype="multipart/form-data">
        <!-- tell that this is the form for creating -->
        <input type="hidden" name="action" value="create">

        <!-- Dropdown for ProfilePictogram -->
        <input type="hidden" name="ProfilePictogram" value="0">
        <select id="ProfilePictogram" name="ProfilePictogram" style="width: 100px; font-size: 3rem;">
            <?php foreach ($profilePictograms as $key => $emoji): ?>
                <option value="<?= $key ?>"><?= $emoji ?></option>
            <?php endforeach; ?>
        </select>
        <label for="ProfilePictogram">profile pictogram</label>

        <br><br>
        <input type="text" id="FirstName" name="FirstName" placeholder="how you call him" style="width: 500px;">
        <label for="FirstName">first name</label>

        <br><br>
        <input type="text" id="LastName" name="LastName" placeholder="how the government calls him" style="width: 500px;">
        <label for="LastName">last name</label>

        <br><br>
        <input type="text" id="title" name="title" placeholder="for example: emperor" style="width: 500px;">
        <label for="title">title</label>

        <br><br>
        <input type="text" id="CompanyName" name="CompanyName" placeholder="his workplace" style="width: 500px;"> <span id="ShowMatchingCompanyName"></span>
        <label for="CompanyName">company name<br><div style="opacity: 0.4;">(you can also enter an idpk of a creator from within TRAMANN PORT to connect automatically)</div></label>

        <!-- Trigger the JavaScript functionality -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const inputField = document.getElementById('CompanyName');
                if (inputField) {
                    inputField.dispatchEvent(new Event('input')); // Triggers the input event on the field
                }
            });
        </script>

        <br><br><br><br>
        <input type="text" id="email" name="email" placeholder="Or do you use homing pigeons?" style="width: 500px;">
        <label for="email">email</label>

        <br><br>
        <input type="text" id="PhoneNumber" name="PhoneNumber" placeholder="ring, ring, ring   ; )" style="width: 500px;">
        <label for="PhoneNumber">phone number</label>

        <br><br>
        <!-- <input type="text" id="LinksToSocialMediaAndOtherSites" name="LinksToSocialMediaAndOtherSites" placeholder="other ways to connect" style="width: 500px;"> -->
<textarea id="LinksToSocialMediaAndOtherSites" name="LinksToSocialMediaAndOtherSites" rows="10" style="display: none; text-align: left;" oninput="updateDisplay()">
</textarea>
        <!-- Display Area for Clickable Links -->
        <div id="displayLinks" style="white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; text-align: left;"
            onclick="handleDisplayClick(event)">
            <!-- Display will be dynamically updated here -->
        </div>
        <label for="LinksToSocialMediaAndOtherSites">links to social media and other sites<br><div style="opacity: 0.4;">(click on the small field right above to start the editing mode)</div></label>

        <br><br><br><br>
        <textarea id="notes" name="notes" rows="10" style="width: 100%;" placeholder="enter some information you want to save in association with this customer"></textarea>
        <label for="notes">notes</label>

        <!-- Dropdown for state -->
        <br><br>
        <input type="hidden" name="state" value="0">
        <select id="state" name="state" style="width: 200px;">
            <option value="0">potential customer</option>
            <option value="1">existing customer</option>
            <option value="2">former custome</option>
        </select>
        <label for="state">state</label>

        <!-- Dropdown for importance -->
        <br><br><br><br><br>
        <input type="hidden" name="importance" value="0">
        <select id="importance" name="importance" style="width: 200px;">
            <option value="0">initial contact</option>
            <option value="1">emerging partner</option>
            <option value="2">partner</option>
            <option value="3">core partner</option>
            <option value="4">prime partner</option>
        </select>
        <label for="importance">importance</label>
        

        <br><br><br><br><br>
        <div align=center>
            <!-- Submit button for creating customer relationship -->
            <a href="javascript:void(0);" class="mainbutton" onclick="submitFormCreateCustomerRelationship()">↗️ SAVE</a>
        </div>
    </form>
</div>
















<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- // /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// listing customer relationships -->
<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<!-- Div for listing all customer relationships -->
<div id="listCustomerRelationshipsDiv" class="steps">
    <br><br>
    <div id="customerRelationshipsList">
    <?php
    // Get the user ID from the cookie
    if (isset($_COOKIE['user_id'])) {
        $user_id = $_COOKIE['user_id'];

        // Query to get all customer relationships for the specific user, ordered alphabetically
        $stmt = $pdo->prepare("SELECT * FROM CustomerRelationships WHERE IdpkCreator = :id ORDER BY FirstName ASC, LastName ASC");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all customer relationships as an associative array
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Start the table structure
        echo "<table style='width: 100%; text-align: left;'>";

        // Check if there are any customer relationships
        if (!empty($products)) {
            foreach ($products as $product) {
                // Truncate the notes if they exceed 200 characters
                $truncatedNotes = (strlen($product['notes']) > 200) ? substr($product['notes'], 0, 200) . '...' : $product['notes'];

                // Fetch the ProfilePictogram value from $product
                $profilePictogramIndex = $product['ProfilePictogram'];

                // Get the matching emoji
                $profilePictogramEmoji = isset($profilePictograms[$profilePictogramIndex]) 
                    ? $profilePictograms[$profilePictogramIndex] 
                    : ''; // Default to nothing if the index is invalid

                // Check if the state is "former customer" (state = 2)
                $rowStyle = ($product['state'] == 2) ? ' style="opacity: 0.4;"' : '';

                echo "<tr{$rowStyle}>";


                // Display the table cell with the emoji
                // echo "<td title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\">
                //         <a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}' 
                //            style='font-size: 3rem;'>{$profilePictogramEmoji}</a>
                //       </td>";

                // Assuming $product contains the relevant data
                $state = $product['state'] ?? 0;
                $importance = $product['importance'] ?? 0;

                // Define dotted spacing based on importance (wider for lower importance, closer for higher importance)
                $dottedSpacingMap = [
                    0 => '10px', // Initial contact
                    1 => '8px',  // Emerging partner
                    2 => '6px',  // Partner
                    3 => '4px',  // Core partner
                    4 => '2px',  // Prime partner
                ];
                $dottedSpacing = $dottedSpacingMap[$importance] ?? '10px';

                // Determine shape and border style dynamically
                $borderStyle = ($state == 0) 
                    ? "border-style: dotted dotted dotted dashed; border-width: 2px 2px 2px 3px;" // potential customer
                    : "border-style: dotted dotted dotted solid; border-width: 2px 2px 2px 3px;";  // existing or former customer

                // Dynamic border color based on importance
                $borderColorMap = [
                    0 => 'rgba(50, 50, 50, 0.1)', // Initial contact
                    1 => 'rgba(50, 50, 50, 0.3)',      // Emerging partner
                    2 => 'rgba(50, 50, 50, 0.5)',     // Partner
                    3 => 'rgba(50, 50, 50, 0.7)',    // Core partner
                    4 => 'rgba(50, 50, 50, 1)',       // Prime partner
                ];
                $borderColor = $borderColorMap[$importance] ?? ''; // Default if not defined

                // Combine styles into a single definition
                $circleStyle = "{$borderStyle} border-color: {$borderColor}; padding: 0.5rem; display: inline-flex; align-items: center; justify-content: center; opacity: 1;";

                // Output the profile pictogram with dynamic styles
                echo "<td title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\" style='position: relative;'>
                        <a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}'
                           style='font-size: 3rem; position: relative;'>
                           <span style='{$circleStyle}'>{$profilePictogramEmoji}</span>
                        </a>
                      </td>";


                echo "<td>";
                echo "<a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}' title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\">{$product['FirstName']} {$product['LastName']} ({$product['idpk']})</a>";
                echo "<span style=\"opacity: 0.8;\">";
                    echo " {$product['title']}";
                    if (!empty($product['CompanyName']) && !empty($product['title'])) {
                        echo " at ";
                    } elseif (!empty($product['CompanyName'])) {
                        echo " from ";
                    }
                    // echo "{$product['CompanyName']}";

                    // Assuming $product['CompanyName'] contains the input value (could be text or a number)
                    $companyName = $product['CompanyName'];

                    // Check if the CompanyName is numeric
                    if (is_numeric($companyName)) {
                        try {
                            // Prepare and execute the query to find the company name based on the numeric ID
                            $stmt = $pdo->prepare("SELECT CompanyName, idpk FROM ExplorersAndCreators WHERE idpk = :id AND ExplorerOrCreator = '1'");
                            $stmt->bindParam(':id', $companyName, PDO::PARAM_INT);
                            $stmt->execute();
                        
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                            if ($result) {
                                // If a match is found, display the company name as a link
                                echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$result['idpk']}' title='{$result['CompanyName']} ({$result['idpk']})'>
                                            {$result['CompanyName']}
                                        </a>";
                            } else {
                                // If no match is found, display the original numeric value
                                echo "{$companyName}";
                            }
                        } catch (PDOException $e) {
                            // Handle database errors
                            echo "Error fetching data: " . $e->getMessage();
                        }
                    } else {
                        // If the CompanyName is not numeric, display it as plain text
                        echo "{$companyName}";
                    }
                echo "</span>";
                echo "<br><span style=\"opacity: 0.5;\">";

                        // Determine recipient name
                        $recipientName = $product['FirstName'];
                        // Determine sender name
                        $senderName = $user['ExplorerOrCreator'] == 0 
                        ? "{$user['FirstName']} {$user['LastName']} ({$user['idpk']})" 
                        : "{$user['CompanyName']} ({$user['idpk']})";
                        // Prepare email subject and body
                        $emailSubject = "TRAMANN PORT - Hi from $senderName";
                        $emailBody = "Hi" . ($recipientName ? " $recipientName" : "") . ",\n\n\n[ContentOfYourMessage]\n\n\n\nSincerely yours,\n$senderName";
                        // URL-encode the subject and body
                        $emailLink = "mailto:{$product['email']}?subject=" . rawurlencode($emailSubject) . "&body=" . rawurlencode($emailBody);
                        // Generate tel link
                        $telLink = "tel:{$product['PhoneNumber']}";
                    if (!empty($product['email'])) {
                        echo "<a href='$emailLink'>✉️ EMAIL</a> "; // open directly
                    }
                    if (!empty($product['PhoneNumber'])) {
                        // echo "<a href='$telLink'>📞 PHONE</a> ";
                        echo "<a href='#' onclick=\"confirmPhoneCall('$telLink')\">📞 PHONE</a> "; // Trigger confirmation
                    }

                    $text = htmlspecialchars(trim($product['LinksToSocialMediaAndOtherSites'] ?? ''));

                    // Only process and display if there are links
                    if (!empty($text)) {
                        echo "<span id=\"LinksToSocialMediaAndOtherSites\" name=\"LinksToSocialMediaAndOtherSites\" style=\"text-align: left;\">";
                    
                        // Regular expression to match valid URLs
                        $urlRegex = '/(?:https?:\/\/)?(?:www\.)?([a-zA-Z0-9-]+\.[a-zA-Z]{2,})([\/\w\-\.?&=]*)/';

                        // Replace URLs with clickable links
                        $formattedText = preg_replace_callback($urlRegex, function ($matches) {
                            $hostname = $matches[1];
                            $pathname = $matches[2] ?? '';
                        
                            // Create the full URL for the link
                            $fullUrl = "https://$hostname$pathname";
                        
                            // Remove 'www.' if present
                            $displayDomain = strtoupper(str_replace('www.', '', $hostname)); // Convert domain to uppercase
                        
                            // Remove TLDs from hostname
                            $domainParts = explode('.', $displayDomain);
                            $cleanDomain = count($domainParts) > 1 ? implode('.', array_slice($domainParts, 0, -1)) : $displayDomain;
                        
                            // Get the last part of the pathname for the page name
                            $pathParts = array_filter(explode('/', $pathname));
                            $pageName = end($pathParts) ? explode('?', end($pathParts))[0] : ''; // Get the last part without query or fragment
                        
                            // Limit lengths for display
                            $limitedDomain = strlen($cleanDomain) > 20 ? substr($cleanDomain, 0, 20) . '...' : $cleanDomain;
                            $limitedPageName = strlen($pageName) > 20 ? substr($pageName, 0, 20) . '...' : $pageName;
                        
                            // Convert page name to uppercase if present
                            $displayText = $pageName ? "$limitedDomain ($limitedPageName)" : $limitedDomain;
                        
                            return "<a href=\"$fullUrl\" target=\"_blank\" class=\"link\">🔗 $displayText</a> ";
                        }, $text);
                    
                        echo $formattedText;
                        echo "</span>";
                    }
                echo "</span>";
                // echo "<br><span style=\"opacity: 0.8;\">{$product['email']} {$product['PhoneNumber']} {$product['LinksToSocialMediaAndOtherSites']}</span>";
                // echo "<br><span title='{$product['notes']}' style=\"opacity: 0.5;\">$truncatedNotes</div>";
                echo "<br><textarea id='notesConnectedToCustomerRelationship_{$product['idpk']}' class='notes-input' data-id='{$product['idpk']}' title='{$product['notes']}' style='opacity: 0.5; width: 100%;' rows='1'>{$product['notes']}</textarea>";
                echo "</td>";
                echo "</tr>";
                echo "<tr></tr>"; // additional line after each customer relationship
            }
        } else {
            // Display a message when no customer relationships are found
            echo "<tr><td colspan='2' style='text-align: center;'>please create new customer relationships so they can be shown here by clicking on the button above</td></tr>";
        }

        echo '</table>';
    }
    ?>
    </div>
</div>














<?php
}
?>










<script>
// // Function to update the display area with clickable shortened links
// function updateDisplay() {
//     const text = document.getElementById("LinksToSocialMediaAndOtherSites").value;
// 
//     // Replace URLs with clickable shortened links (first 30 characters) and add a 'link' class for easier targeting
//     const linkedText = text.replace(/(https?:\/\/[^\s]+)/g, function(url) {
//         const displayText = url.length > 30 ? url.substring(0, 30) + "..." : url;
//         return `<a href="${url}" target="_blank" class="link">🔗 ${displayText}</a>`;
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

        return `<a href="${fullUrl}" target="_blank" class="link">🔗 ${displayText}</a>`;
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





























// JavaScript to handle searching and updating the span with results
document.getElementById('CompanyName').addEventListener('input', function (event) {
    const inputField = event.target;
    const inputValue = inputField.value;
    const spanElement = document.getElementById('ShowMatchingCompanyName');

    // Only proceed if the input contains only a number
    if (/^\d+$/.test(inputValue)) {
        // Create an AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'SaveDataCustomerRelationshipsFetchCompanyName.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Handle the response
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = xhr.responseText;

                if (response === "Creator wasn't found.") {
                    spanElement.textContent = response;
                    spanElement.title = response;
                    spanElement.innerHTML = '';
                } else {
                    // Parse the JSON response
                    const { CompanyName, idpk } = JSON.parse(response);
                    const link = `<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk=${idpk}' target='_blank'>${CompanyName} (${idpk})</a>`;

                    // Update the span with the result
                    spanElement.innerHTML = link;
                    spanElement.title = `${CompanyName} (${idpk})`;
                }
            } else {
                console.error('Error fetching data:', xhr.statusText);
            }
        };

        // Send the data
        xhr.send(`id=${encodeURIComponent(inputValue)}`);
    } else {
        spanElement.textContent = '';
        spanElement.title = '';
    }
});

































document.addEventListener('input', function (event) {
    if (event.target && event.target.classList.contains('notes-input')) {
        const textarea = event.target;
        const notes = textarea.value;
        const id = textarea.getAttribute('data-id');

        // Send AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'SaveDataCustomerRelationships.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (xhr.status === 200) {
                console.log(xhr.responseText);
            } else {
                console.error('An error occurred while updating notes.');
            }
        };

        xhr.send(`id=${encodeURIComponent(id)}&notes=${encodeURIComponent(notes)}`);
    }
});

// Functionality to increase textarea size on click and revert when clicking elsewhere
document.addEventListener('click', function (event) {
    const textareas = document.querySelectorAll('.notes-input');
    
    textareas.forEach(textarea => {
        if (textarea === event.target) {
            // Increase rows to 10 when textarea is clicked
            textarea.rows = 10;
        } else {
            // Revert rows to 1 when clicking elsewhere
            textarea.rows = 1;
        }
    });
});

</script>