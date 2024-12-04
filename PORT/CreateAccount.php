<?php
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
        // Check if the field is missing or only contains spaces
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = ucfirst($field) . " is required.";
        }
    }
    
    // If there are errors, stop the script and display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        echo "<br><br><a href=\"index.php?content=CreateAccount.php\">GO BACK</a>";
        exit; // Stop further processing
    }





    // Sanitize and validate input data
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $firstName = trim($_POST['FirstName']);
    $lastName = trim($_POST['LastName']);
    $street = trim($_POST['street']);
    $houseNumber = (int) trim($_POST['HouseNumber']); // Cast to int
    $zipCode = trim($_POST['ZIPCode']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $planet = trim($_POST['planet']);
    $iban = trim($_POST['IBAN']);
    $phoneNumber = (int) trim($_POST['PhoneNumber']); // Cast to int
    $capitalInAccountInDollars = 0;
    $darkmode = 0; // Default to no (0)
    $level = 0; // Default to new (0)

    // For creators
    $explorerOrCreator = isset($_POST['ExplorerOrCreator']) ? (int) $_POST['ExplorerOrCreator'] : 0; // Default to 0 for Explorer
    $companyName = isset($_POST['CompanyName']) ? trim($_POST['CompanyName']) : null;
    $vatId = isset($_POST['VATID']) ? trim($_POST['VATID']) : null;
    $phoneNumberForExplorersAsContact = isset($_POST['PhoneNumberForExplorersAsContact']) ? (int) trim($_POST['PhoneNumberForExplorersAsContact']) : null;
    $emailForExplorersAsContact = isset($_POST['EmailForExplorersAsContact']) ? trim($_POST['EmailForExplorersAsContact']) : null;
    $showAddressToExplorers = isset($_POST['ShowAddressToExplorers']) ? (int) $_POST['ShowAddressToExplorers'] : 0; // Default to no
    $canExplorersVisitYou = isset($_POST['CanExplorersVisitYou']) ? (int) $_POST['CanExplorersVisitYou'] : 0; // Default to no

    // Opening hours for each day
    $openingHoursMondayOpening = isset($_POST['OpeningHoursMondayOpening']) ? $_POST['OpeningHoursMondayOpening'] : null;
    $openingHoursMondayClosing = isset($_POST['OpeningHoursMondayClosing']) ? $_POST['OpeningHoursMondayClosing'] : null;
    $openingHoursTuesdayOpening = isset($_POST['OpeningHoursTuesdayOpening']) ? $_POST['OpeningHoursTuesdayOpening'] : null;
    $openingHoursTuesdayClosing = isset($_POST['OpeningHoursTuesdayClosing']) ? $_POST['OpeningHoursTuesdayClosing'] : null;
    $openingHoursWednesdayOpening = isset($_POST['OpeningHoursWednesdayOpening']) ? $_POST['OpeningHoursWednesdayOpening'] : null;
    $openingHoursWednesdayClosing = isset($_POST['OpeningHoursWednesdayClosing']) ? $_POST['OpeningHoursWednesdayClosing'] : null;
    $openingHoursThursdayOpening = isset($_POST['OpeningHoursThursdayOpening']) ? $_POST['OpeningHoursThursdayOpening'] : null;
    $openingHoursThursdayClosing = isset($_POST['OpeningHoursThursdayClosing']) ? $_POST['OpeningHoursThursdayClosing'] : null;
    $openingHoursFridayOpening = isset($_POST['OpeningHoursFridayOpening']) ? $_POST['OpeningHoursFridayOpening'] : null;
    $openingHoursFridayClosing = isset($_POST['OpeningHoursFridayClosing']) ? $_POST['OpeningHoursFridayClosing'] : null;
    $openingHoursSaturdayOpening = isset($_POST['OpeningHoursSaturdayOpening']) ? $_POST['OpeningHoursSaturdayOpening'] : null;
    $openingHoursSaturdayClosing = isset($_POST['OpeningHoursSaturdayClosing']) ? $_POST['OpeningHoursSaturdayClosing'] : null;
    $openingHoursSundayOpening = isset($_POST['OpeningHoursSundayOpening']) ? $_POST['OpeningHoursSundayOpening'] : null;
    $openingHoursSundayClosing = isset($_POST['OpeningHoursSundayClosing']) ? $_POST['OpeningHoursSundayClosing'] : null;
    $openingHoursNationalHolidaysOpening = isset($_POST['OpeningHoursNationalHolidaysOpening']) ? $_POST['OpeningHoursNationalHolidaysOpening'] : null;
    $openingHoursNationalHolidaysClosing = isset($_POST['OpeningHoursNationalHolidaysClosing']) ? $_POST['OpeningHoursNationalHolidaysClosing'] : null;
    $closeOnlineShopIfPhysicalShopIsClosed = isset($_POST['CloseOnlineShopIfPhysicalShopIsClosed']) ? (int) $_POST['CloseOnlineShopIfPhysicalShopIsClosed'] : 0; // Default to no
    $physicalShopClosedBecauseOfHolidaysClosing = (int) (isset($_POST['PhysicalShopClosedBecauseOfHolidaysClosing']) ? $_POST['PhysicalShopClosedBecauseOfHolidaysClosing'] : 0);
    $physicalShopClosedBecauseOfHolidaysOpening = (int) (isset($_POST['PhysicalShopClosedBecauseOfHolidaysOpening']) ? $_POST['PhysicalShopClosedBecauseOfHolidaysOpening'] : 0);
 
     // Add the validation logic here
     if ($canExplorersVisitYou == 0) {
         // If CanExplorersVisitYou is '0', set CloseOnlineShopIfPhysicalShopIsClosed to '0'
         $closeOnlineShopIfPhysicalShopIsClosed = 0;
     }

    // Descriptions and notes
    $shortDescription = isset($_POST['ShortDescription']) ? trim($_POST['ShortDescription']) : null;
    $longDescription = isset($_POST['LongDescription']) ? trim($_POST['LongDescription']) : null;
    $linksToSocialMediaAndOtherSites = isset($_POST['LinksToSocialMediaAndOtherSites']) ? trim($_POST['LinksToSocialMediaAndOtherSites']) : null;

    // Password hashing for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $timestampCreation = time(); // Current timestamp

    // adding notes and texts for the starting of the dashboard for the first time if the user is a creator
    if ($explorerOrCreator === 1) {
        $personalNotes = "make personal notes for yourself and your colleagues," . 
                         " you could for example insert the notes from your last meeting here," . 
                         " so everybody in your team can see them";

        $personalStrategicPlaningNotes = "plan your long-term business strategy,\n" . 
                                         "which new products you want to introduce,\n" . 
                                         "which new explorers you want to reach, ...";

        $personalToDoList = "Welcome on board, we are very happy to have you with us   : )\n\n" . 
                            "please mind the white TRAMANN logo in the top left corner of your screen," . 
                            " by clicking on it, you can open the menu\n\n" . 
                            "your first things to do are:\n" .
                            "- click through the pages in the menu and get familiar with TRAMANN PORT\n" . 
                            "- visit - ACCOUNT - and upload your logo\n" . 
                            "- head over to - PRODUCTS AND SERVICES - and start adding them\n" . 
                            "- go to - YOUR WEBSITE - and customize it\n" . 
                            "- tell your friends about your new account and website\n" . 
                            "- replace these things to do with your new own ones";

        $personalCollectionOfLinks = "first, click on this field to start the editing mode," .
                                     " then add links to other distribution channels," . 
                                     " mail programs, social media, editing tools, ...," . 
                                     " so you can quickly access everything from your dashboard.";
        } else {
            // insert NULL for explorers
            $personalNotes = null;
            $personalStrategicPlaningNotes = null;
            $personalToDoList = null;
            $personalCollectionOfLinks = null;
        }

    // Prepare the SQL statement for inserting data into ExplorersAndCreators table
    $stmt = $pdo->prepare("INSERT INTO ExplorersAndCreators (TimestampCreation, email, password, PhoneNumber, FirstName, LastName, street, HouseNumber, ZIPCode, city, country, planet, IBAN, CapitalInAccountInDollars, darkmode, ExplorerOrCreator, level, CompanyName, VATID, PhoneNumberForExplorersAsContact, EmailForExplorersAsContact, ShowAddressToExplorers, CanExplorersVisitYou, OpeningHoursMondayOpening, OpeningHoursMondayClosing, OpeningHoursTuesdayOpening, OpeningHoursTuesdayClosing, OpeningHoursWednesdayOpening, OpeningHoursWednesdayClosing, OpeningHoursThursdayOpening, OpeningHoursThursdayClosing, OpeningHoursFridayOpening, OpeningHoursFridayClosing, OpeningHoursSaturdayOpening, OpeningHoursSaturdayClosing, OpeningHoursSundayOpening, OpeningHoursSundayClosing, OpeningHoursNationalHolidaysOpening, OpeningHoursNationalHolidaysClosing, CloseOnlineShopIfPhysicalShopIsClosed, PhysicalShopClosedBecauseOfHolidaysClosing, PhysicalShopClosedBecauseOfHolidaysOpening, ShortDescription, LongDescription, LinksToSocialMediaAndOtherSites, PersonalNotes, PersonalStrategicPlaningNotes, PersonalToDoList, PersonalCollectionOfLinks) 
                            VALUES (:timestampCreation, :email, :password, :phoneNumber, :firstName, :lastName, :street, :houseNumber, :zipCode, :city, :country, :planet, :iban, :capitalInAccountInDollars, :darkmode, :explorerOrCreator, :level, :companyName, :vatId, :phoneNumberForExplorersAsContact, :emailForExplorersAsContact, :showAddressToExplorers, :canExplorersVisitYou, :openingHoursMondayOpening, :openingHoursMondayClosing, :openingHoursTuesdayOpening, :openingHoursTuesdayClosing, :openingHoursWednesdayOpening, :openingHoursWednesdayClosing, :openingHoursThursdayOpening, :openingHoursThursdayClosing, :openingHoursFridayOpening, :openingHoursFridayClosing, :openingHoursSaturdayOpening, :openingHoursSaturdayClosing, :openingHoursSundayOpening, :openingHoursSundayClosing, :openingHoursNationalHolidaysOpening, :openingHoursNationalHolidaysClosing, :closeOnlineShopIfPhysicalShopIsClosed, :physicalShopClosedBecauseOfHolidaysClosing, :physicalShopClosedBecauseOfHolidaysOpening, :shortDescription, :longDescription, :linksToSocialMediaAndOtherSites, :personalNotes, :personalStrategicPlaningNotes, :personalToDoList, :personalCollectionOfLinks)");

    // Bind parameters
    $stmt->bindParam(':timestampCreation', $timestampCreation);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':phoneNumber', $phoneNumber);
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':street', $street);
    $stmt->bindParam(':houseNumber', $houseNumber);
    $stmt->bindParam(':zipCode', $zipCode);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':planet', $planet);
    $stmt->bindParam(':iban', $iban);
    $stmt->bindParam(':capitalInAccountInDollars', $capitalInAccountInDollars);
    $stmt->bindParam(':darkmode', $darkmode);
    $stmt->bindParam(':explorerOrCreator', $explorerOrCreator);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':companyName', $companyName);
    $stmt->bindParam(':vatId', $vatId);
    $stmt->bindParam(':phoneNumberForExplorersAsContact', $phoneNumberForExplorersAsContact);
    $stmt->bindParam(':emailForExplorersAsContact', $emailForExplorersAsContact);
    $stmt->bindParam(':showAddressToExplorers', $showAddressToExplorers);
    $stmt->bindParam(':canExplorersVisitYou', $canExplorersVisitYou);
    $stmt->bindParam(':openingHoursMondayOpening', $openingHoursMondayOpening);
    $stmt->bindParam(':openingHoursMondayClosing', $openingHoursMondayClosing);
    $stmt->bindParam(':openingHoursTuesdayOpening', $openingHoursTuesdayOpening);
    $stmt->bindParam(':openingHoursTuesdayClosing', $openingHoursTuesdayClosing);
    $stmt->bindParam(':openingHoursWednesdayOpening', $openingHoursWednesdayOpening);
    $stmt->bindParam(':openingHoursWednesdayClosing', $openingHoursWednesdayClosing);
    $stmt->bindParam(':openingHoursThursdayOpening', $openingHoursThursdayOpening);
    $stmt->bindParam(':openingHoursThursdayClosing', $openingHoursThursdayClosing);
    $stmt->bindParam(':openingHoursFridayOpening', $openingHoursFridayOpening);
    $stmt->bindParam(':openingHoursFridayClosing', $openingHoursFridayClosing);
    $stmt->bindParam(':openingHoursSaturdayOpening', $openingHoursSaturdayOpening);
    $stmt->bindParam(':openingHoursSaturdayClosing', $openingHoursSaturdayClosing);
    $stmt->bindParam(':openingHoursSundayOpening', $openingHoursSundayOpening);
    $stmt->bindParam(':openingHoursSundayClosing', $openingHoursSundayClosing);
    $stmt->bindParam(':openingHoursNationalHolidaysOpening', $openingHoursNationalHolidaysOpening);
    $stmt->bindParam(':openingHoursNationalHolidaysClosing', $openingHoursNationalHolidaysClosing);
    $stmt->bindParam(':closeOnlineShopIfPhysicalShopIsClosed', $closeOnlineShopIfPhysicalShopIsClosed);
    $stmt->bindParam(':physicalShopClosedBecauseOfHolidaysClosing', $physicalShopClosedBecauseOfHolidaysClosing);
    $stmt->bindParam(':physicalShopClosedBecauseOfHolidaysOpening', $physicalShopClosedBecauseOfHolidaysOpening);
    $stmt->bindParam(':shortDescription', $shortDescription);
    $stmt->bindParam(':longDescription', $longDescription);
    $stmt->bindParam(':linksToSocialMediaAndOtherSites', $linksToSocialMediaAndOtherSites);
    $stmt->bindParam(':personalNotes', $personalNotes);
    $stmt->bindParam(':personalStrategicPlaningNotes', $personalStrategicPlaningNotes);
    $stmt->bindParam(':personalToDoList', $personalToDoList);
    $stmt->bindParam(':personalCollectionOfLinks', $personalCollectionOfLinks);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<br><br><br>Account created successfully!<br><br><a href=\"index.php?content=login.php\">LOGIN</a>";
    } else {
        echo "<br><br><br>There was an unespected error creating your account. Please try again or contact an administrator so we can fix the problem.";
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
            alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
        } else {
            document.getElementById('createAccountForm').submit(); // Submit the form if all required fields are filled
        }
    }
</script>



<div class="registration-container">
    <h1>⚙️ CREATE ACCOUNT</h1> <!-- Main heading -->

    <div> <!-- link for login instead -->
        Already have an account? <a href="index.php?content=login.php">🗝️ GO TO LOGIN!</a>
        <br><br><br><br><br>
    </div>

    <form id="createAccountForm" action="" method="post"> <!-- Form for creating an account -->
        <div class="steps">
            <input type="email" id="email" name="email" placeholder="it's also your login and has to be legit" style="width: 300px;" required>
            <label for="email">email*<br><div style="opacity: 0.4;">(* means that this field is required)</div></label>

            <br>
            <input type="text" id="password" name="password" placeholder="something easy to remember, but hard to guess" style="width: 300px;" required>
            <label for="password">password*</label>

            <br><br>
            <input type="number" id="PhoneNumber" name="PhoneNumber" placeholder="not required, but it increases your security" style="width: 300px;">
            <label for="PhoneNumber">phone number</label>

            <br><br><br><br><br>
            <input type="text" id="FirstName" name="FirstName" placeholder="how your friends call you" style="width: 300px;" required>
            <label for="FirstName">first name*</label>

            <br><br>
            <input type="text" id="LastName" name="LastName" placeholder="how the government calls you" style="width: 300px;" required>
            <label for="LastName">last name*</label>

            <br><br>
            <input type="text" id="street" name="street" placeholder="so we can send you packages" style="width: 300px;" required>
            <label for="street">street*</label>

            <br><br>
            <input type="number" id="HouseNumber" name="HouseNumber" placeholder="42" style="width: 300px;" required>
            <label for="HouseNumber">house number*</label>

            <br><br>
            <input type="number" id="ZIPCode" name="ZIPCode" placeholder="introduced in 1963" style="width: 300px;" required>
            <label for="ZIPCode">ZIP code*</label>

            <br><br>
            <input type="text" id="city" name="city" placeholder="big city life ..." style="width: 300px;" required>
            <label for="city">city*</label>

            <br><br>
            <input type="text" id="country" name="country" placeholder="Anybody from Wakanda?" style="width: 300px;" required>
            <label for="country">country*</label>

            <br><br>
            <input type="text" id="planet" name="planet" placeholder="waiting for the first people from mars" style="width: 300px;">
            <label for="planet">planet</label>

            <br><br><br><br><br>
            <input type="text" id="IBAN" name="IBAN" placeholder="a long number on your banking card" style="width: 300px;" required>
            <label for="IBAN">IBAN*</label>
            
            <br><br><br><br><br>
                <!-- Hidden input to always send "Explorer" (0) if the checkbox is unchecked -->
                <input type="hidden" name="ExplorerOrCreator" value="0">
            <!-- Explorer or Creator Checkbox -->
            <input type="checkbox" id="ExplorerOrCreator" name="ExplorerOrCreator" value="1" onclick="toggleCreatorFields()"> <!-- Checkbox for role selection -->
            <label for="ExplorerOrCreator"><strong>business account</strong> (check if you<br>want to create and sell products too)</label>

            <!-- Additional fields for creators (hidden by default) -->
            <div id="creatorFields" style="display: none;">
                <br><br><br><br><br>
                <input type="text" id="CompanyName" name="CompanyName" placeholder="for example ACME Corporation" style="width: 300px;">
                <label for="CompanyName">company name</label>

                <br><br>
                <input type="text" id="VATID" name="VATID" placeholder="for uncle sam" style="width: 300px;">
                <label for="VATID">VAT ID</label>

                <br><br>
                <input type="text" id="PhoneNumberForExplorersAsContact" name="PhoneNumberForExplorersAsContact" placeholder="another phone number (or the same)" style="width: 300px;">
                <label for="PhoneNumberForExplorersAsContact">phone number for explorers as contact</label>

                <br><br>
                <input type="email" id="EmailForExplorersAsContact" name="EmailForExplorersAsContact" placeholder="something@like.this" style="width: 300px;">
                <label for="EmailForExplorersAsContact">email for explorers as contact</label>

                    <!-- Hidden input to always send "no" (0) if the checkbox is unchecked -->
                    <input type="hidden" name="ShowAddressToExplorers" value="0">
                <br><br>
                <input type="checkbox" id="ShowAddressToExplorers" name="ShowAddressToExplorers" value="1">
                <label for="ShowAddressToExplorers">check if you want your address to be shown to explorers</label>

                    <!-- Hidden input to always send "no" (0) if the checkbox is unchecked -->
                    <input type="hidden" name="CanExplorersVisitYou" value="0">
                <br><br><br><br><br>
                <input type="checkbox" id="CanExplorersVisitYou" name="CanExplorersVisitYou" value="1" onclick="togglePhysicalShopOpeningHours()">
                <label for="CanExplorersVisitYou">check if explorers can visit you at your provided address</label>

<!-- Additional fields for physical shops (hidden by default) -->
<div id="PhysicalShopOpeningHours" style="display: none;">
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
            <!-- Monday -->
            <tr>
                <td>Monday</td>
                <td><input type="time" id="OpeningHoursMondayOpening" name="OpeningHoursMondayOpening"></td>
                <td><input type="time" id="OpeningHoursMondayClosing" name="OpeningHoursMondayClosing"></td>
            </tr>
            <!-- Tuesday -->
            <tr>
                <td>Tuesday</td>
                <td><input type="time" id="OpeningHoursTuesdayOpening" name="OpeningHoursTuesdayOpening"></td>
                <td><input type="time" id="OpeningHoursTuesdayClosing" name="OpeningHoursTuesdayClosing"></td>
            </tr>
            <!-- Wednesday -->
            <tr>
                <td>Wednesday</td>
                <td><input type="time" id="OpeningHoursWednesdayOpening" name="OpeningHoursWednesdayOpening"></td>
                <td><input type="time" id="OpeningHoursWednesdayClosing" name="OpeningHoursWednesdayClosing"></td>
            </tr>
            <!-- Thursday -->
            <tr>
                <td>Thursday</td>
                <td><input type="time" id="OpeningHoursThursdayOpening" name="OpeningHoursThursdayOpening"></td>
                <td><input type="time" id="OpeningHoursThursdayClosing" name="OpeningHoursThursdayClosing"></td>
            </tr>
            <!-- Friday -->
            <tr>
                <td>Friday</td>
                <td><input type="time" id="OpeningHoursFridayOpening" name="OpeningHoursFridayOpening"></td>
                <td><input type="time" id="OpeningHoursFridayClosing" name="OpeningHoursFridayClosing"></td>
            </tr>
            <!-- Saturday -->
            <tr>
                <td>Saturday</td>
                <td><input type="time" id="OpeningHoursSaturdayOpening" name="OpeningHoursSaturdayOpening"></td>
                <td><input type="time" id="OpeningHoursSaturdayClosing" name="OpeningHoursSaturdayClosing"></td>
            </tr>
            <!-- Sunday -->
            <tr>
                <td>Sunday</td>
                <td><input type="time" id="OpeningHoursSundayOpening" name="OpeningHoursSundayOpening"></td>
                <td><input type="time" id="OpeningHoursSundayClosing" name="OpeningHoursSundayClosing"></td>
            </tr>
            <!-- National Holidays -->
            <tr>
                <td>National Holidays</td>
                <td><input type="time" id="OpeningHoursNationalHolidaysOpening" name="OpeningHoursNationalHolidaysOpening"></td>
                <td><input type="time" id="OpeningHoursNationalHolidaysClosing" name="OpeningHoursNationalHolidaysClosing"></td>
            </tr>
        </tbody>
    </table>
                    <!-- Hidden input to always send "no" (0) if the checkbox is unchecked -->
                    <input type="hidden" name="CloseOnlineShopIfPhysicalShopIsClosed" value="0">                
                <br><br>
                <input type="checkbox" id="CloseOnlineShopIfPhysicalShopIsClosed" name="CloseOnlineShopIfPhysicalShopIsClosed" value="1">
                <label for="CloseOnlineShopIfPhysicalShopIsClosed">check if you want your online shop to be closed when your physical shop is closed, for example if you have a restaurant</label>
</div>

                <br><br><br><br><br>
                <textarea id="ShortDescription" name="ShortDescription" placeholder="Manufacturing the best explosive tennis balls in the world." style="width: 500px; height: 50px;"></textarea>
                <br><label for="ShortDescription">short description</label>

                <br><br>
                <textarea id="LongDescription" name="LongDescription" placeholder="Based in Toledo, Ohio, we manufacture the best explosive tennis balls in the world. We are carefully crafting every piece individually by hand. Therefore we are ... ." style="width: 500px; height: 300px;"></textarea>
                <br><label for="LongDescription">long description</label>


                <br><br><br><br><br>
                <textarea id="LinksToSocialMediaAndOtherSites" name="LinksToSocialMediaAndOtherSites" placeholder="One link to rule them all, one link to find them, one link to bring them all, and in the darkness bind them. Or maybe also some more links ...   ; )" style="width: 500px; height: 200px;"></textarea>
                <br><label for="LinksToSocialMediaAndOtherSites">links to social media and other sites</label>
            </div>
        </div>
        
        <br><br><br><br><br>
        <a href="javascript:void(0);" class="mainbutton" onclick="submitForm()">↗️ SAVE</a>
    </form>
</div>



<?php
}
?>