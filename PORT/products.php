<h1>📦 PRODUCTS AND SERVICES</h1>

<?php
include ("ExchangeRates.php"); // include ExchangeRates.php for recalculation of prices





$preselectedOption = "your_products_services"; // add preselected search option

include ("explore.php"); // include explore.php for exploring and searching
echo "<br><br><br><br><br>";
?>










<?php
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// delete product picture
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (
    isset($_GET['action']) && $_GET['action'] === 'deleteProductPicture' &&
    isset($_GET['idpk']) && isset($_GET['slot'])
) {
    $idpk = htmlspecialchars($_GET['idpk']);
    $slot = (int) $_GET['slot']; // Ensure slot is an integer
    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Verify user ownership via the database
    try {
        $dsn = "mysql:host=$mysqlDbServer;dbname=$mysqlDbName;charset=utf8";
        $pdo = new PDO($dsn, $mysqlDbUser, $mysqlDbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query the database to ensure the user owns the product
        $stmt = $pdo->prepare("SELECT * FROM ProductsAndServices WHERE idpk = ? AND IdpkCreator = ?");
        $stmt->execute([$idpk, $user_id]);

        // Check if the product exists and is owned by the user
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            echo "You are not authorized to delete this product picture.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    }

    // Define the valid extensions and upload directory
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $uploadDir = "uploads/ProductPictures/" . $idpk . "_";

    // Try to delete the specified slot's picture
    $deleted = false;
    foreach ($validExtensions as $extension) {
        $filePath = "{$uploadDir}{$slot}.{$extension}";
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                $deleted = true;
                break;
            }
        }
    }

    if ($deleted) {
    } else {
        // echo "The product picture couldn't be found or deleted.";
    }
}

























// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// update product in database
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'updateDatabase' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Example array of required fields
    $requiredFields = [
        'name', 
        'SellingPriceProductOrServiceInDollars', 
        'type'
    ];

    // Initialize an array to hold any errors
    $errors = [];

    // Loop through each required field and check if it's set
    foreach ($requiredFields as $field) {
        // Use isset() for strict checking, especially for 'type' field
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $errors[] = "$field is required.";
        }
    }

    // Special check for the 'type' field to allow zero as a valid value
    if (array_key_exists('type', $_POST) && $_POST['type'] === '') {
        $errors[] = "type is required.";
    }

    // If there are errors, stop the script and display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        exit; // Stop further processing
    }

    // Extract form data
    $keywordsForSearch = $_POST['KeywordsForSearch'];
    $name = $_POST['name'];
    $shortDescription = $_POST['ShortDescription'];
    $longDescription = $_POST['LongDescription'];
    $allowComments = isset($_POST['AllowComments']) ? (int) $_POST['AllowComments'] : 1; // Default to yes (1)
    $type = $_POST['type'];
    $sellingPriceProductOrServiceInDollars = $_POST['SellingPriceProductOrServiceInDollars'];
    $weightInKg = isset($_POST['WeightInKg']) ? (float) str_replace(',', '.', $_POST['WeightInKg']) : null;
    $dimensionsLengthInMm = isset($_POST['DimensionsLengthInMm']) ? round((float) str_replace(',', '.', $_POST['DimensionsLengthInMm']), 2) : null;
    $dimensionsWidthInMm = isset($_POST['DimensionsWidthInMm']) ? round((float) str_replace(',', '.', $_POST['DimensionsWidthInMm']), 2) : null;
    $dimensionsHeightInMm = isset($_POST['DimensionsHeightInMm']) ? round((float) str_replace(',', '.', $_POST['DimensionsHeightInMm']), 2) : null;
    $sellingPricePackagingAndShippingInDollars = isset($_POST['SellingPricePackagingAndShippingInDollars']) ? round((float) str_replace(',', '.', $_POST['SellingPricePackagingAndShippingInDollars']), 2) : null;
    $taxesInPercent = isset($_POST['TaxesInPercent']) ? round((float) str_replace(',', '.', $_POST['TaxesInPercent']), 2) : null;
    $manageInventory = isset($_POST['ManageInventory']) ? (int) $_POST['ManageInventory'] : 1; // Default to yes (1)
    $inventoryAvailable = isset($_POST['InventoryAvailable']) ? (int) $_POST['InventoryAvailable'] : null;
    $inventoryInProduction = isset($_POST['InventoryInProduction']) ? (int) $_POST['InventoryInProduction'] : null;
    $inventoryMinimumLevel = isset($_POST['InventoryMinimumLevel']) ? (int) $_POST['InventoryMinimumLevel'] : null;
    $personalNotes = $_POST['PersonalNotes'];
    $state = $_POST['state'];

    // Check conditions for setting inventory values
    if ($manageInventory == 0 || $type == 3 || $type == 4) {
        $inventoryAvailable = 0; // Set InventoryAvailable to 0
        $inventoryInProduction = 0; // Set InventoryInProduction to 0
        $inventoryMinimumLevel = 0; // Set InventoryMinimumLevel to 0
    }

    // Prepare your update query with IdpkCreator security check
    $stmt = $pdo->prepare("
        UPDATE ProductsAndServices SET 
            name = :name,
            KeywordsForSearch = :keywords,
            ShortDescription = :shortDescription,
            LongDescription = :longDescription,
            AllowCommentsNotesSpecialRequests = :allowComments,
            type = :type,
            WeightInKg = :weight,
            DimensionsLengthInMm = :dimensionsLength,
            DimensionsWidthInMm = :dimensionsWidth,
            DimensionsHeightInMm = :dimensionsHeight,
            SellingPriceProductOrServiceInDollars = :sellingPrice,
            SellingPricePackagingAndShippingInDollars = :shippingPrice,
            TaxesInPercent = :taxesInPercent,
            ManageInventory = :manageInventory,
            InventoryAvailable = :inventoryAvailable,
            InventoryInProduction = :inventoryInProduction,
            InventoryMinimumLevel = :inventoryMinimumLevel,
            PersonalNotes = :personalNotes,
            state = :state
        WHERE idpk = $idpk AND IdpkCreator = $user_id
    ");

    // Bind parameters
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':keywords', $keywordsForSearch);
    $stmt->bindParam(':shortDescription', $shortDescription);
    $stmt->bindParam(':longDescription', $longDescription);
    $stmt->bindParam(':allowComments', $allowComments, PDO::PARAM_INT);
    $stmt->bindParam(':type', $type, PDO::PARAM_INT);
    $stmt->bindParam(':weight', $weightInKg, PDO::PARAM_STR);
    $stmt->bindParam(':dimensionsLength', $dimensionsLengthInMm, PDO::PARAM_STR);
    $stmt->bindParam(':dimensionsWidth', $dimensionsWidthInMm, PDO::PARAM_STR);
    $stmt->bindParam(':dimensionsHeight', $dimensionsHeightInMm, PDO::PARAM_STR);
    $stmt->bindParam(':sellingPrice', $sellingPriceProductOrServiceInDollars, PDO::PARAM_STR);
    $stmt->bindParam(':shippingPrice', $sellingPricePackagingAndShippingInDollars, PDO::PARAM_STR);
    $stmt->bindParam(':taxesInPercent', $taxesInPercent, PDO::PARAM_STR);
    $stmt->bindParam(':manageInventory', $manageInventory, PDO::PARAM_INT);
    $stmt->bindParam(':inventoryAvailable', $inventoryAvailable, PDO::PARAM_INT);
    $stmt->bindParam(':inventoryInProduction', $inventoryInProduction, PDO::PARAM_INT);
    $stmt->bindParam(':inventoryMinimumLevel', $inventoryMinimumLevel, PDO::PARAM_INT);
    $stmt->bindParam(':personalNotes', $personalNotes);
    $stmt->bindParam(':state', $state);

    // Execute the update
    if ($stmt->execute()) {
        // Optionally redirect or notify user of success
        // echo "Updated successfully.";
    } else {
        // echo "Error updating.";
    }

    // Get the ID of the new product
    $productId = $idpk;

    // Define the upload directory based on the product ID
    $uploadDir = "uploads/ProductPictures/{$productId}_";
    

    // Initialize a sequential counter for image files
    $imageCounter = 0;

    // Loop through each file input (ProductPicture0 to ProductPicture4)
    for ($i = 0; $i < 5; $i++) {
        $inputName = "ProductPicture$i";

        // Check if a file was uploaded for this input
        if (!empty($_FILES[$inputName]['name'])) {
            $fileName = basename($_FILES[$inputName]['name']);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $validTypes = ['png', 'jpg', 'jpeg', 'gif'];

            // Validate the file type
            if (in_array($fileType, $validTypes)) {
                // Define the target file path using the sequential counter
                $targetFilePath = "{$uploadDir}{$imageCounter}.{$fileType}";

                // Attempt to move the uploaded file to the target location
                if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetFilePath)) {
                    // Increment the counter only if the file was successfully uploaded
                    $imageCounter++;
                } else {
                    echo "Error uploading file from input $i.<br>";
                }
            } else {
                echo "Invalid file type for picture $i.<br>";
            }
        }
    }
    echo "<script>window.location.href = 'index.php?content=products.php';</script>";
    exit();
}
































// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// user interface for updating product
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['idpk'])) {
    
    ?>
    <script>
        // Function to submit the update product form via AJAX
        function submitFormUpdateProduct() {
            const form = document.getElementById('editProductForm');
            const formData = new FormData(form);
        
            // Check for required fields
            const requiredFields = ['name', 'SellingPriceProductOrServiceInDollars', 'type'];
            let isValid = true;
        
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
                return; // Stop submission if validation fails
            }
            if (!isValid) {
                alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
            } else {
                document.getElementById('editProductForm').submit(); // Submit the form if all required fields are filled
            }
        }
        
        // Function to toggle visibility of fields based on product type
        function toggleFields() {
            const type = document.getElementById('type').value;
            const physicalAttributesDiv = document.getElementById('physicalAttributes');
            const priceAttributesDiv = document.getElementById('priceAttributes');
        
            if (type == '0' || type == '1' || type == '2') { // Show for products and food types
                physicalAttributesDiv.style.display = 'block';
                priceAttributesDiv.style.display = 'block';
            } else { // Hide for services
                physicalAttributesDiv.style.display = 'none';
                priceAttributesDiv.style.display = 'none';
            }
        }
    
        // Function to toggle inventory fields based on Manage Inventory checkbox
        function toggleInventoryFields() {
            const manageInventory = document.getElementById('ManageInventory').checked;
            const inventoryFieldsDiv = document.getElementById('inventoryFields');
            const type = document.getElementById('type').value;
        
            // Show inventory fields only if Manage Inventory is checked and type is not service
            if (manageInventory && (type == '0' || type == '1' || type == '2')) {
                inventoryFieldsDiv.style.display = 'block';
            } else {
                inventoryFieldsDiv.style.display = 'none';
            }
        }

        // Call toggle functions on page load
        window.onload = function() {
            toggleFields();
            toggleInventoryFields();
        }
    </script>
    <?php




    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Query the database to get product details
    $query = "SELECT * FROM ProductsAndServices WHERE idpk = ? AND IdpkCreator = $user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idpk]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the product exists
    if ($product) {
        ?>
        <!-- Div for editing existing products -->
        <div id="editProductDiv" class="steps">
            <div align=center>
                <h3>✏️ EDIT</h3>
            </div>
            <form id="editProductForm" action="index.php?content=products.php&action=updateDatabase&idpk=<?php echo $product['idpk']; ?>" method="post" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitFormUpdateProduct();">
                <!-- tell that this is the form for creating -->
                    <input type="hidden" name="action" value="update">
                <!-- tell the idpk of the product or service -->
                    <input type="hidden" name="productId" value="<?php echo htmlspecialchars($product['idpk']); ?>">
        
        
                <!-- Product Name -->
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" placeholder="something short for the humans" style="width: 500px;" required>
                <label for="name">product name*<br><div style="opacity: 0.4;">(* means that this field is required)</div></label>

                <!-- Product Keywords for Search -->
                <br>
                <textarea id="KeywordsForSearch" name="KeywordsForSearch" rows="3" style="width: 100%;" placeholder="something for the bots (highest search priority)" required><?php echo htmlspecialchars($product['KeywordsForSearch']); ?></textarea>
                <label for="KeywordsForSearch">keywords for search</label>

                <br><br>
                idpk: <?php echo htmlspecialchars($product['idpk']); ?>

                <?php
                // Define the base path for the uploaded images
                $uploadDir = "uploads/ProductPictures/" . htmlspecialchars($product['idpk']) . "_";
                    
                // Define possible file extensions
                $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                // Initialize array to store the paths of the existing images
                $imagePaths = [];

                // Check each slot (0 to 4) for the available extensions
                for ($i = 0; $i < 5; $i++) {
                    foreach ($validExtensions as $extension) {
                        $filePath = "{$uploadDir}{$i}.{$extension}";
                        if (file_exists($filePath)) {
                            $imagePaths[] = $filePath;  // Add to array if file exists
                            break;  // Stop checking other extensions if a file is found
                        }
                    }
                }
                ?>
                
                <br><br><br><br><br>
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <?php if (isset($imagePaths[$i])): ?>
                        <br><br>
                        <img src="<?php echo $imagePaths[$i]; ?>" style="height:300px;"><br>
                        <br><br><a href="index.php?content=products.php&action=deleteProductPicture&idpk=<?php echo htmlspecialchars($product['idpk']); ?>&slot=<?php echo $i; ?>" onclick="return confirm('Are you sure you want to delete this picture?');" style="opacity: 0.5;">❌ REMOVE</a>
                        <br><br>
                        <?php endif; ?>
                    <input type="file" name="ProductPicture<?php echo $i; ?>" id="ProductPicture<?php echo $i; ?>" accept="image/*">
                    <label for="ProductPicture<?php echo $i; ?>">
                        <br><?php echo ($i === 0) ? "upload main product picture" : "upload additional product picture"; ?>
                    </label>
                    <br><br>
                <?php endfor; ?>

                <!-- Short Description -->
                <br><br><br><br><br>
                <textarea id="ShortDescription" name="ShortDescription" rows="3" style="width: 100%;" placeholder="keep it short and simple"><?php echo htmlspecialchars($product['ShortDescription']); ?></textarea>
                <label for="ShortDescription">short description</label>
        
                <!-- Long Description -->
                <br><br>
                <textarea id="LongDescription" name="LongDescription" rows="6" style="width: 100%;" placeholder="if there is more to say"><?php echo htmlspecialchars($product['LongDescription']); ?></textarea>
                <label for="LongDescription">long description</label>
        
                <!-- Checkbox for Allow Comments -->
                    <input type="hidden" name="AllowComments" value="0">
                <br><br><br><br><br>
                <input type="checkbox" id="AllowComments" name="AllowComments" value="1" <?php echo ($product['AllowCommentsNotesSpecialRequests'] == 1) ? 'checked' : ''; ?>>
                <label for="AllowComments">check if you want to allow explorers to add comments, notes, special requests, ...</label>
        
                <!-- Dropdown for Product Type -->
                    <input type="hidden" name="type" value="0">
                <br><br><br><br><br>
                <select id="type" name="type" onchange="toggleFields()">
                    <option value="0" <?php echo ($product['type'] == 0) ? 'selected' : ''; ?>>product</option>
                    <option value="1" <?php echo ($product['type'] == 1) ? 'selected' : ''; ?>>restaurant food</option>
                    <option value="2" <?php echo ($product['type'] == 2) ? 'selected' : ''; ?>>other food products</option>
                    <option value="3" <?php echo ($product['type'] == 3) ? 'selected' : ''; ?>>physical service</option>
                    <option value="4" <?php echo ($product['type'] == 4) ? 'selected' : ''; ?>>digital service</option>
                </select>
                <label for="type">type*</label>
        
                <!-- Div for Weight, Dimensions (only for products/food) -->
                <div id="physicalAttributes" style="display: none;">
                    <br><br><br><br><br>
                    <input type="number" id="WeightInKg" name="WeightInKg" value="<?php echo htmlspecialchars(str_replace(',', '.', $product['WeightInKg'])); ?>" placeholder="we love the metric system" style="width: 300px;">
                    <label for="WeightInKg">weight (in kg)</label>
        
                    <br><br>
                    <input type="number" id="DimensionsLengthInMm" name="DimensionsLengthInMm" value="<?php echo htmlspecialchars(str_replace(',', '.', $product['DimensionsLengthInMm'])); ?>" placeholder="it's easier to calculate" style="width: 300px;">
                    <label for="DimensionsLengthInMm">length (in mm)</label>
        
                    <br><br>
                    <input type="number" id="DimensionsWidthInMm" name="DimensionsWidthInMm" value="<?php echo htmlspecialchars(str_replace(',', '.', $product['DimensionsWidthInMm'])); ?>" placeholder="nearly all of the world uses it" style="width: 300px;">
                    <label for="DimensionsWidthInMm">width (in mm)</label>
        
                    <br><br>
                    <input type="number" id="DimensionsHeightInMm" name="DimensionsHeightInMm" value="<?php echo htmlspecialchars(str_replace(',', '.', $product['DimensionsHeightInMm'])); ?>" placeholder="it's totally logical" style="width: 300px;">
                    <label for="DimensionsHeightInMm">height (in mm)</label>
                </div>
        
                    <br><br><br><br><br>
                    <input type="number" id="SellingPriceProductOrServiceInDollars" name="SellingPriceProductOrServiceInDollars" value="<?php echo htmlspecialchars(str_replace(',', '.', $product['SellingPriceProductOrServiceInDollars'])); ?>" placeholder="price the explorer should pay" style="width: 300px;" oninput="updatePriceCurrency('SellingPriceProductOrServiceInDollars')" required>
                    <label for="SellingPriceProductOrServiceInDollars">selling price (in USD)*</label>
                    <?php
                        if ($ExchangeRateCurrencyCode !== "USD") {
                            echo "<br><br>";
                            echo "<input type=\"number\" id=\"SellingPriceProductOrServiceInDollarsInOtherCurrency\" name=\"SellingPriceProductOrServiceInDollarsInOtherCurrency\" placeholder=\"price the explorer should pay\" style=\"width: 300px; opacity: 0.3;\" oninput=\"updatePriceCurrency('SellingPriceProductOrServiceInDollarsInOtherCurrency')\">";
                            echo "<label for=\"SellingPriceProductOrServiceInDollarsInOtherCurrency\" style='opacity: 0.3;'>selling price (in $ExchangeRateCurrencyCode)</label>";
                        }
                    ?>
                <!-- Div for Selling Price (only for products/food) -->
                <div id="priceAttributes" style="display: none;">
                    <br><br>
                    <input type="number" id="SellingPricePackagingAndShippingInDollars" name="SellingPricePackagingAndShippingInDollars" value="<?php echo htmlspecialchars(str_replace(',', '.', $product['SellingPricePackagingAndShippingInDollars'])); ?>" placeholder="only if you want to separate" style="width: 300px;" oninput="updatePriceCurrency('SellingPricePackagingAndShippingInDollars')">
                    <label for="SellingPricePackagingAndShippingInDollars">selling price of packaging and shipping (in USD)</label>
                    <?php
                        if ($ExchangeRateCurrencyCode !== "USD") {
                            echo "<br><br>";
                            echo "<input type=\"number\" id=\"SellingPricePackagingAndShippingInDollarsInOtherCurrency\" name=\"SellingPricePackagingAndShippingInDollarsInOtherCurrency\" placeholder=\"only if you want to separate\" style=\"width: 300px; opacity: 0.3;\" oninput=\"updatePriceCurrency('SellingPricePackagingAndShippingInDollarsInOtherCurrency')\">";
                            echo "<label for=\"SellingPricePackagingAndShippingInDollarsInOtherCurrency\" style='opacity: 0.3;'>selling price of packaging and shipping (in $ExchangeRateCurrencyCode)</label>";
                        }
                    ?>
                </div>

                <br><br>
                <input type="number" id="TaxesInPercent" name="TaxesInPercent" value="<?php echo htmlspecialchars(str_replace(',', '.', $product['TaxesInPercent'])); ?>" placeholder="% for uncle sam" style="width: 300px;" oninput="updateLiveCalculations()">
                <label for="TaxesInPercent">taxes (in %)<br><div id="LiveCalculations" style="opacity: 0.4;"></div></label>
        
                <!-- Checkbox for Manage Inventory -->
                    <input type="hidden" name="ManageInventory" value="0">
                <br><br><br><br><br>
                <input type="checkbox" id="ManageInventory" name="ManageInventory" value="1" <?php echo ($product['ManageInventory'] == 1) ? 'checked' : ''; ?> onclick="toggleInventoryFields()">
                <label for="ManageInventory">check if you want to manage the inventory</label>
        
                <!-- Inventory Fields -->
                <div id="inventoryFields" style="display: none;">
                    <br><br>
                    <input type="number" id="InventoryAvailable" name="InventoryAvailable" value="<?php echo htmlspecialchars($product['InventoryAvailable']); ?>" placeholder="how much do you have right now" style="width: 300px;">
                    <label for="InventoryAvailable">inventory available</label>
        
                    <br><br>
                    <input type="number" id="InventoryInProduction" name="InventoryInProduction" value="<?php echo htmlspecialchars($product['InventoryInProduction']); ?>" placeholder="and how much is in production or reordered" style="width: 300px;" oninput="updateInventoryAvailable()">
                    <label for="InventoryInProduction">inventory in production or reordered</label>

                    <br><br>
                    <input type="number" id="InventoryMinimumLevel" name="InventoryMinimumLevel" value="<?php echo htmlspecialchars($product['InventoryMinimumLevel']); ?>" placeholder="get warnings if it drops below" style="width: 300px;">
                    <label for="InventoryMinimumLevel">inventory minimum level</label>
                </div>
        
                <!-- Personal Notes -->
                <br><br><br><br><br>
                <textarea id="PersonalNotes" name="PersonalNotes" rows="6" style="width: 100%;" placeholder="only you can see these"><?php echo htmlspecialchars($product['PersonalNotes']); ?></textarea>
                <label for="PersonalNotes">personal notes</label>

                <!-- Checkbox for Manage Inventory -->
                    <input type="hidden" name="state" value="0">
                <br><br><br><br><br>
                <input type="checkbox" id="state" name="state" value="1" <?php echo ($product['state'] == 1) ? 'checked' : ''; ?>>
                <label for="state">state active/inactive (uncheck if you don't want to present and sell this anymore)</label>
        
                <br><br><br><br><br>
                <div align=center>
                    <!-- Submit button for updating product -->
                    <!-- <a href="javascript:void(0);" class="mainbutton" onclick="submitFormUpdateProduct()">↗️ SAVE</a> -->
                    <?php
                        // echo "<a href='index.php?content=products.php&action=updateDatabase&idpk={$product['idpk']}' class='mainbutton' onclick='submitFormUpdateProduct()'>↗️ SAVE</a>";
                        echo "<a href='javascript:void(0);' class='mainbutton' onclick='submitFormUpdateProduct()'>↗️ SAVE</a>";
                    ?>
                </div>
            </form>
        </div>
    <script>
        // Variable to store the previous value of InventoryInProduction
        var previousInventoryInProduction = parseInt(document.getElementById('InventoryInProduction').value) || 0;

        function updateInventoryAvailable() {
            var inventoryAvailableInput = document.getElementById('InventoryAvailable');
            var inventoryInProductionInput = document.getElementById('InventoryInProduction');

            // Parse current value of InventoryInProduction
            var currentInventoryInProduction = parseInt(inventoryInProductionInput.value) || 0;

            // Check if the current value is less than the previous value
            if (currentInventoryInProduction < previousInventoryInProduction) {
                // Calculate the difference and update InventoryAvailable
                var difference = previousInventoryInProduction - currentInventoryInProduction;
                var currentInventoryAvailable = parseInt(inventoryAvailableInput.value) || 0;

                // Increase InventoryAvailable by the difference
                inventoryAvailableInput.value = currentInventoryAvailable + difference;
            }

            // Update the previous value for the next input
            previousInventoryInProduction = currentInventoryInProduction;
        }
    </script>
    <?php
    } else {
        // echo "Product or service with idpk $idpk not found.";
    }
} else {






















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// create product in database
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    // Example array of required fields
    $requiredFields = [
        'name', 
        'SellingPriceProductOrServiceInDollars', 
        'type'
    ];

    // Initialize an array to hold any errors
    $errors = [];

    // Loop through each required field and check if it's set
    foreach ($requiredFields as $field) {
        // Use isset() for strict checking, especially for 'type' field
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $errors[] = "$field is required.";
        }
    }

    // Special check for the 'type' field to allow zero as a valid value
    if (array_key_exists('type', $_POST) && $_POST['type'] === '') {
        $errors[] = "type is required.";
    }

    // If there are errors, stop the script and display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        exit; // Stop further processing
    }

    // Extract form data
    $keywordsForSearch = $_POST['KeywordsForSearch'];
    $name = $_POST['name'];
    $shortDescription = $_POST['ShortDescription'];
    $longDescription = $_POST['LongDescription'];
    $allowComments = isset($_POST['AllowComments']) ? (int) $_POST['AllowComments'] : 1; // Default to yes (1)
    $type = $_POST['type'];
    $sellingPriceProductOrServiceInDollars = $_POST['SellingPriceProductOrServiceInDollars'];
    $weightInKg = isset($_POST['WeightInKg']) ? (float) str_replace(',', '.', $_POST['WeightInKg']) : null;
    $dimensionsLengthInMm = isset($_POST['DimensionsLengthInMm']) ? round((float) str_replace(',', '.', $_POST['DimensionsLengthInMm']), 2) : null;
    $dimensionsWidthInMm = isset($_POST['DimensionsWidthInMm']) ? round((float) str_replace(',', '.', $_POST['DimensionsWidthInMm']), 2) : null;
    $dimensionsHeightInMm = isset($_POST['DimensionsHeightInMm']) ? round((float) str_replace(',', '.', $_POST['DimensionsHeightInMm']), 2) : null;
    $sellingPricePackagingAndShippingInDollars = isset($_POST['SellingPricePackagingAndShippingInDollars']) ? round((float) str_replace(',', '.', $_POST['SellingPricePackagingAndShippingInDollars']), 2) : null;
    $taxesInPercent = isset($_POST['TaxesInPercent']) ? round((float) str_replace(',', '.', $_POST['TaxesInPercent']), 2) : null;
    $manageInventory = isset($_POST['ManageInventory']) ? (int) $_POST['ManageInventory'] : 1; // Default to yes (1)
    $inventoryAvailable = isset($_POST['InventoryAvailable']) ? (int) $_POST['InventoryAvailable'] : null;
    $inventoryInProduction = isset($_POST['InventoryInProduction']) ? (int) $_POST['InventoryInProduction'] : null;
    $inventoryMinimumLevel = isset($_POST['InventoryMinimumLevel']) ? (int) $_POST['InventoryMinimumLevel'] : null;
    $personalNotes = $_POST['PersonalNotes'];

    // Check conditions for setting inventory values
    if ($manageInventory == 0 || $type == 3 || $type == 4) {
        $inventoryAvailable = 0; // Set InventoryAvailable to 0
        $inventoryInProduction = 0; // Set InventoryInProduction to 0
        $inventoryMinimumLevel = 0; // Set InventoryMinimumLevel to 0
    }

    // New fields
    $timestampCreation = time();  // Get the current time as UNIX timestamp
    $idpkCreator = htmlspecialchars($user['idpk']);  // Fetch the creator's idpk from user session or passed variable

    // Default state: active (1)
    $state = 1;

    // Prepare SQL statement to insert product data
    $stmt = $pdo->prepare("INSERT INTO ProductsAndServices (
        TimestampCreation, IdpkCreator, KeywordsForSearch, name, ShortDescription, LongDescription, 
        AllowCommentsNotesSpecialRequests, type, SellingPriceProductOrServiceInDollars, WeightInKg, 
        DimensionsLengthInMm, DimensionsWidthInMm, DimensionsHeightInMm, 
        SellingPricePackagingAndShippingInDollars, TaxesInPercent, ManageInventory, InventoryAvailable, 
        InventoryInProduction, InventoryMinimumLevel, PersonalNotes, state
    ) VALUES (
        :timestampCreation, :idpkCreator, :keywordsForSearch, :name, :shortDescription, :longDescription, 
        :allowComments, :type, :sellingPriceProductOrServiceInDollars, :weightInKg, 
        :dimensionsLengthInMm, :dimensionsWidthInMm, :dimensionsHeightInMm, 
        :sellingPricePackagingAndShippingInDollars, :taxesInPercent, :manageInventory, :inventoryAvailable, 
        :inventoryInProduction, :inventoryMinimumLevel, :personalNotes, :state
    )");
    
    // Bind the parameters
    $stmt->bindParam(':timestampCreation', $timestampCreation);
    $stmt->bindParam(':idpkCreator', $idpkCreator);
    $stmt->bindParam(':keywordsForSearch', $keywordsForSearch);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':shortDescription', $shortDescription);
    $stmt->bindParam(':longDescription', $longDescription);
    $stmt->bindParam(':allowComments', $allowComments);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':sellingPriceProductOrServiceInDollars', $sellingPriceProductOrServiceInDollars);
    $stmt->bindParam(':weightInKg', $weightInKg);
    $stmt->bindParam(':dimensionsLengthInMm', $dimensionsLengthInMm);
    $stmt->bindParam(':dimensionsWidthInMm', $dimensionsWidthInMm);
    $stmt->bindParam(':dimensionsHeightInMm', $dimensionsHeightInMm);
    $stmt->bindParam(':sellingPricePackagingAndShippingInDollars', $sellingPricePackagingAndShippingInDollars);
    $stmt->bindParam(':taxesInPercent', $taxesInPercent);
    $stmt->bindParam(':manageInventory', $manageInventory);
    $stmt->bindParam(':inventoryAvailable', $inventoryAvailable);
    $stmt->bindParam(':inventoryInProduction', $inventoryInProduction);
    $stmt->bindParam(':inventoryMinimumLevel', $inventoryMinimumLevel);
    $stmt->bindParam(':personalNotes', $personalNotes);
    $stmt->bindParam(':state', $state);
    
    // Execute the statement
    if (!$stmt->execute()) {
        echo "Error in SQL execution: " . implode(", ", $stmt->errorInfo());
        exit;
    }

    // Get the ID of the new product
    $productId = $pdo->lastInsertId();

    // Define the upload directory based on the product ID
    $uploadDir = "uploads/ProductPictures/{$productId}_";
    

    // Initialize a sequential counter for image files
    $imageCounter = 0;

    // Loop through each file input (ProductPicture0 to ProductPicture4)
    for ($i = 0; $i < 5; $i++) {
        $inputName = "ProductPicture$i";

        // Check if a file was uploaded for this input
        if (!empty($_FILES[$inputName]['name'])) {
            $fileName = basename($_FILES[$inputName]['name']);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $validTypes = ['png', 'jpg', 'jpeg', 'gif'];

            // Validate the file type
            if (in_array($fileType, $validTypes)) {
                // Define the target file path using the sequential counter
                $targetFilePath = "{$uploadDir}{$imageCounter}.{$fileType}";

                // Attempt to move the uploaded file to the target location
                if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetFilePath)) {
                    // Increment the counter only if the file was successfully uploaded
                    $imageCounter++;
                } else {
                    echo "Error uploading file from input $i.<br>";
                }
            } else {
                echo "Invalid file type for picture $i.<br>";
            }
        }
    }

    // echo "<br><br><br>Created successfully!<br><br><a href=\"index.php?content=products.php\">GO BACK</a>";
    // exit; // Stop further processing
    echo "<script>window.location.href = 'index.php?content=products.php';</script>";
    exit();
}

?>























<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- // /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// some java script stuff -->
<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<script>
    // Function to show the create product form and hide others
    function showCreateProduct() {
        document.getElementById('createProductDiv').style.display = 'block';
        document.getElementById('listProductDiv').style.display = 'none';
        document.getElementById('editProductDiv').style.display = 'none';
        document.getElementById('createNewProductButton').style.display = 'none';
    }

    // Function to hide all forms and show the product list
    function showProductList() {
        document.getElementById('createProductDiv').style.display = 'none';
        document.getElementById('editProductDiv').style.display = 'none';
        document.getElementById('createNewProductButton').style.display = '';
        document.getElementById('listProductDiv').style.display = 'block';
        loadProductList();  // Load the list of products
    }








    function submitFormCreateProduct() {
        const form = document.getElementById('createProductForm');
        const formData = new FormData(form);

        // Check for required fields
        const requiredFields = ['name', 'SellingPriceProductOrServiceInDollars', 'type'];
        let isValid = true;

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
            return; // Stop submission if validation fails
        }
        if (!isValid) {
            alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
        } else {
            document.getElementById('createProductForm').submit(); // Submit the form if all required fields are filled
        }

        // // Proceed with the AJAX request
        // fetch('products.php', {
        //     method: 'POST',
        //     body: formData
        // })
        // .then(response => response.json())
        // .then(data => {
        //     if (data.success) {
        //         alert('Product created successfully!');
        //         showProductList();  // Show list after product creation
        //     } else {
        //         alert('Error creating product: ' + data.message);
        //     }
        // })
        // .catch(error => console.error('Error:', error));
    }











    // Function to submit the update product form via AJAX
    function submitFormUpdateProduct() {
        const form = document.getElementById('editProductForm');
        const formData = new FormData(form);

        // Check for required fields
        const requiredFields = ['name', 'SellingPriceProductOrServiceInDollars', 'type'];
        let isValid = true;

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
            return; // Stop submission if validation fails
        }
        if (!isValid) {
            alert('Please fill out all required fields and mind the correct format.'); // Show alert if any required field is empty
        } else {
            document.getElementById('editProductForm').submit(); // Submit the form if all required fields are filled
        }
    }









    // Function to load the list of products dynamically
    function loadProductList() {
        fetch('products.php')  // Sends a GET request to products.php
        .then(response => response.json())  // Expect a JSON response
        // .then(data => {
            // let productListDiv = document.getElementById('productList');
            // productListDiv.innerHTML = '';  // Clear the existing list

            // Iterate through the products and create the HTML structure for each product
            // data.products.forEach(product => {
            //     let productItem = document.createElement('div');
            //     let opacityStyle = product.state == 0 ? 'opacity: 0.4;' : '';  // Apply reduced opacity if inactive
// 
            //     // Create the HTML structure for each product
            //     productItem.innerHTML = `
            //         <div style="${opacityStyle}">
            //             ${product.name} (${product.idpk}) - ${product.ShortDescription} - ${product.SellingPriceProductOrServiceInDollars}$ (+${product.SellingPricePackagingAndShippingInDollars}$)
            //             <a href="javascript:void(0)" onclick="showEditProduct(${product.idpk})">edit</a>
            //         </div>
            //     `;
// 
            //     // Append the product to the list
            //     productListDiv.appendChild(productItem);
            // });
        // })
        .catch(error => console.error('error:', error));
    }





    




    // // Function to show the edit form for a specific product
    // function showEditProduct(productId) {
    //     // Show the edit form and hide others
    //     document.getElementById('editProductDiv').style.display = 'block';
    //     document.getElementById('listProductDiv').style.display = 'none';
    //     document.getElementById('createProductDiv').style.display = 'none';
    //     document.getElementById('createNewProductButton').style.display = 'none';
// 
    //     // Fetch product details using AJAX
    //     fetch(`SaveDataFetchProductIdpk.php?productId=${productId}`)
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.error) {
    //                 alert(data.error); // Handle error (e.g., product not found)
    //             } else {
    //                 // Populate the edit form with product details
    //                 document.getElementById('editProductId').value = data.idpk;
    //                 document.getElementById('KeywordsForSearch').value = data.KeywordsForSearch;
    //                 document.getElementById('name').value = data.name;
    //                 // Populate any additional fields as needed
    //             }
    //         })
    //         .catch(error => console.error('Error fetching product details:', error));
    // }




    // Function to show the edit form for a specific product
    function showEditProduct(productId) {
        // You can directly use the productId passed
        // Or, if you want to get it from the hidden input field
        // const productId = document.querySelector(`input[value='${productId}']`).value;
        
        // Show the edit form and hide others
        document.getElementById('editProductDiv').style.display = 'block';
        document.getElementById('listProductDiv').style.display = 'none';
        document.getElementById('createProductDiv').style.display = 'none';
        document.getElementById('createNewProductButton').style.display = 'none';
        
        // Optionally, set the value in a hidden input in the edit form
        document.getElementById('editProductId').value = productId;
    }

    window.onload = function() {
        showProductList();  // Automatically load product list when the page loads
    }





    



    // Function to toggle visibility of fields based on product type
    function toggleFields() {
        const type = document.getElementById('type').value;
        const physicalAttributesDiv = document.getElementById('physicalAttributes');
        const priceAttributesDiv = document.getElementById('priceAttributes');

        if (type == '0' || type == '1' || type == '2') { // Show for products and food types
            physicalAttributesDiv.style.display = 'block';
            priceAttributesDiv.style.display = 'block';
        } else { // Hide for services
            physicalAttributesDiv.style.display = 'none';
            priceAttributesDiv.style.display = 'none';
        }
    }

    // Function to toggle inventory fields based on Manage Inventory checkbox
    function toggleInventoryFields() {
        const manageInventory = document.getElementById('ManageInventory').checked;
        const inventoryFieldsDiv = document.getElementById('inventoryFields');
        const type = document.getElementById('type').value;

        // Show inventory fields only if Manage Inventory is checked and type is not service
        if (manageInventory && (type == '0' || type == '1' || type == '2')) {
            inventoryFieldsDiv.style.display = 'block';
        } else {
            inventoryFieldsDiv.style.display = 'none';
        }
    }
    
    // Initial setup on page load
    window.onload = function() {
        toggleFields();  // Set initial visibility of fields
        toggleInventoryFields();  // Set initial visibility of inventory fields
    }
</script>






















































<!-- Button to toggle showing the create product form -->
<a href="javascript:void(0)" id="createNewProductButton" class="button" onclick="showCreateProduct()">📦 CREATE NEW PRODUCT OR SERVICE</a>




<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// user interface for creating product -->
<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<!-- Div for creating new products (hidden by default) -->
<div id="createProductDiv" class="steps" style="display: none;">
    <div align=center>
        <h3>📦 CREATE</h3>
    </div>
    <form id="createProductForm" onsubmit="submitFormCreateProduct()" action="" method="post" enctype="multipart/form-data">
        <!-- tell that this is the form for creating -->
            <input type="hidden" name="action" value="create">

        
        <!-- Product Name -->
        <input type="text" id="name" name="name" placeholder="something short for the humans" style="width: 500px;" required>
        <label for="name">product name*<br><div style="opacity: 0.4;">(* means that this field is required)</div></label>

        <!-- Product Keywords for Search -->
        <br>
        <textarea id="KeywordsForSearch" name="KeywordsForSearch" rows="3" style="width: 100%;" placeholder="something for the bots (highest search priority)" required></textarea>
        <label for="KeywordsForSearch">keywords for search</label>

        <br><br><br><br><br>
        <input type="file" name="ProductPicture0" id="ProductPicture0" accept="image/*">
        <label for="ProductPicture0"><br>upload main product picture</label>

        <br><br>
        <input type="file" name="ProductPicture1" id="ProductPicture1" accept="image/*">
        <label for="ProductPicture1"><br>upload additional product picture</label>

        <br><br>
        <input type="file" name="ProductPicture2" id="ProductPicture2" accept="image/*">
        <label for="ProductPicture2"><br>upload additional product picture</label>

        <br><br>
        <input type="file" name="ProductPicture3" id="ProductPicture3" accept="image/*">
        <label for="ProductPicture3"><br>upload additional product picture</label>

        <br><br>
        <input type="file" name="ProductPicture4" id="ProductPicture4" accept="image/*">
        <label for="ProductPicture4"><br>upload additional product picture</label>

        <!-- Short Description -->
        <br><br><br><br><br>
        <textarea id="ShortDescription" name="ShortDescription" rows="3" style="width: 100%;" placeholder="keep it short and simple"></textarea>
        <label for="ShortDescription">short description</label>

        <!-- Long Description -->
        <br><br>
        <textarea id="LongDescription" name="LongDescription" rows="6" style="width: 100%;" placeholder="if there is more to say"></textarea>
        <label for="LongDescription">long description</label>

        <!-- Checkbox for Allow Comments -->
            <input type="hidden" name="AllowComments" value="0">
        <br><br><br><br><br>
        <input type="checkbox" id="AllowComments" name="AllowComments" value="1" checked>
        <label for="AllowComments">check if you want to allow explorers to add comments, notes, special requests, ...</label>

        <!-- Dropdown for Product Type -->
            <input type="hidden" name="type" value="0">
        <br><br><br><br><br>
        <select id="type" name="type" onchange="toggleFields()">
            <option value="0">product</option>
            <option value="1">restaurant food</option>
            <option value="2">other food products</option>
            <option value="3">physical service</option>
            <option value="4">digital service</option>
        </select>
        <label for="type">type*</label>

        <!-- Div for Weight, Dimensions (only for products/food) -->
        <div id="physicalAttributes" style="display: none;">
            <br><br><br><br><br>
            <input type="number" id="WeightInKg" name="WeightInKg" placeholder="we love the metric system" style="width: 300px;">
            <label for="WeightInKg">weight (in kg)</label>
            
            <br><br>
            <input type="number" id="DimensionsLengthInMm" name="DimensionsLengthInMm" placeholder="it's easier to calculate" style="width: 300px;">
            <label for="DimensionsLengthInMm">length (in mm)</label>

            <br><br>
            <input type="number" id="DimensionsWidthInMm" name="DimensionsWidthInMm" placeholder="nearly all of the world uses it" style="width: 300px;">
            <label for="DimensionsWidthInMm">width (in mm)</label>

            <br><br>
            <input type="number" id="DimensionsHeightInMm" name="DimensionsHeightInMm" placeholder="it's totally logical" style="width: 300px;">
            <label for="DimensionsHeightInMm">height (in mm)</label>
        </div>

            <br><br><br><br><br>
            <input type="number" id="SellingPriceProductOrServiceInDollars" name="SellingPriceProductOrServiceInDollars" placeholder="price the explorer should pay" style="width: 300px;" oninput="updatePriceCurrency('SellingPriceProductOrServiceInDollars')" required>
            <label for="SellingPriceProductOrServiceInDollars">selling price (in USD)*</label>
            <?php
                if ($ExchangeRateCurrencyCode !== "USD") {
                    echo "<br><br>";
                    echo "<input type=\"number\" id=\"SellingPriceProductOrServiceInDollarsInOtherCurrency\" name=\"SellingPriceProductOrServiceInDollarsInOtherCurrency\" placeholder=\"price the explorer should pay\" style=\"width: 300px; opacity: 0.3;\" oninput=\"updatePriceCurrency('SellingPriceProductOrServiceInDollarsInOtherCurrency')\">";
                    echo "<label for=\"SellingPriceProductOrServiceInDollarsInOtherCurrency\" style='opacity: 0.3;'>selling price (in $ExchangeRateCurrencyCode)</label>";
                }
            ?>
        <!-- Div for Selling Price (only for products/food) -->
        <div id="priceAttributes" style="display: none;">
            <br><br>
            <input type="number" id="SellingPricePackagingAndShippingInDollars" name="SellingPricePackagingAndShippingInDollars" placeholder="only if you want to separate" style="width: 300px;" oninput="updatePriceCurrency('SellingPricePackagingAndShippingInDollars')">
            <label for="SellingPricePackagingAndShippingInDollars">selling price of packaging and shipping (in USD)</label>
            <?php
                if ($ExchangeRateCurrencyCode !== "USD") {
                    echo "<br><br>";
                    echo "<input type=\"number\" id=\"SellingPricePackagingAndShippingInDollarsInOtherCurrency\" name=\"SellingPricePackagingAndShippingInDollarsInOtherCurrency\" placeholder=\"only if you want to separate\" style=\"width: 300px; opacity: 0.3;\" oninput=\"updatePriceCurrency('SellingPricePackagingAndShippingInDollarsInOtherCurrency')\">";
                    echo "<label for=\"SellingPricePackagingAndShippingInDollarsInOtherCurrency\" style='opacity: 0.3;'>selling price of packaging and shipping (in $ExchangeRateCurrencyCode)</label>";
                }
            ?>
        </div>

            <br><br>
            <input type="number" id="TaxesInPercent" name="TaxesInPercent" placeholder="% for uncle sam" style="width: 300px;" oninput="updateLiveCalculations()">
            <label for="TaxesInPercent">taxes (in %)<br><div id="LiveCalculations" style="opacity: 0.4;"></div></label>

        <!-- Checkbox for Manage Inventory -->
            <input type="hidden" name="ManageInventory" value="0">
        <br><br><br><br><br>
        <input type="checkbox" id="ManageInventory" name="ManageInventory" value="1" checked onchange="toggleInventoryFields()">
        <label for="ManageInventory">check if you want to manage the inventory</label>

        <!-- Inventory Fields -->
        <div id="inventoryFields" style="display: none;">
            <br><br>
            <input type="number" id="InventoryAvailable" name="InventoryAvailable" placeholder="how much do you have right now" style="width: 300px;">
            <label for="InventoryAvailable">inventory available</label>

            <br><br>
            <input type="number" id="InventoryInProduction" name="InventoryInProduction" placeholder="and how much is in production or reordered" style="width: 300px;">
            <label for="InventoryInProduction">inventory in production or reordered</label>

            <br><br>
            <input type="number" id="InventoryMinimumLevel" name="InventoryMinimumLevel" placeholder="get warnings if it drops below" style="width: 300px;">
            <label for="InventoryMinimumLevel">inventory minimum level</label>
        </div>

        <!-- Personal Notes -->
        <br><br><br><br><br>
        <textarea id="PersonalNotes" name="PersonalNotes" rows="6" style="width: 100%;" placeholder="only you can see these"></textarea>
        <label for="PersonalNotes">personal notes</label>

        <br><br><br><br><br>
        <div align=center>
            <!-- Submit button for creating product -->
            <a href="javascript:void(0);" class="mainbutton" onclick="submitFormCreateProduct()">↗️ SAVE</a>
        </div>
    </form>
</div>
















<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// listing products -->
<!-- // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<!-- Div for listing all products -->
<div id="listProductDiv" class="steps">
    <br><br>
    <div id="productList">
        <!-- Products will be dynamically loaded here
         please use the following format for display from the database ProductsAndServices where the IdpkCreator is the same as the user idpk (// Assuming user_id is stored in a cookie
    $user_id = $_COOKIE['user_id'];
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);)
    'name' ('idpk') - 'ShortDescription' - 'SellingPriceProductOrServiceInDollars'$ (+'SellingPricePackagingAndShippingInDollars'$) - editLinkHere,
    display with only 0.4 opacity if 'state' = 0 (inactive)
        -->
    <?php
    // Get the user ID from the cookie
    if (isset($_COOKIE['user_id'])) {
        $user_id = $_COOKIE['user_id'];

        // Query to get all products for the specific user, ordered alphabetically
        $stmt = $pdo->prepare("SELECT idpk, name, ShortDescription, SellingPriceProductOrServiceInDollars, SellingPricePackagingAndShippingInDollars, PersonalNotes, state FROM ProductsAndServices WHERE IdpkCreator = :id ORDER BY name ASC");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all products as an associative array
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Separate active and inactive products
        $activeProducts = [];
        $inactiveProducts = [];

        foreach ($products as $product) {
            if ($product['state'] == 1) {
                $activeProducts[] = $product;
            } else {
                $inactiveProducts[] = $product;
            }
        }

        // Start the table structure
        echo '<table>';

        // Display active products
        if (!empty($activeProducts)) {
            foreach ($activeProducts as $product) {
                // Truncate the name and description
                $truncatedName = (strlen($product['name']) > 50) ? substr($product['name'], 0, 50) . '...' : $product['name'];
                $truncatedDescription = (strlen($product['ShortDescription']) > 100) ? substr($product['ShortDescription'], 0, 100) . '...' : $product['ShortDescription'];
                $truncatedPersonalNotes = (strlen($product['PersonalNotes']) > 100) ? substr($product['PersonalNotes'], 0, 100) . '...' : $product['PersonalNotes'];

                // Display Shipping Price if not 0 or null
                $shippingPrice = (!empty($product['SellingPricePackagingAndShippingInDollars']) && $product['SellingPricePackagingAndShippingInDollars'] != 0)
                                ? '(+' . $product['SellingPricePackagingAndShippingInDollars'] . '$)'
                                : '';

                echo "<tr>";
                echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
                echo "<td><div style=\"opacity: 0.5;\">$truncatedDescription</div></td>";
                echo "<td>{$product['SellingPriceProductOrServiceInDollars']}$</td>";
                echo "<td>$shippingPrice</td>";
                // echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>✏️ EDIT</a></td>";
                // echo "<td><a href='javascript:void(0)' onclick='showEditProduct({$product['idpk']})'>✏️ EDIT</a></td>";
                // hidden field to transfer the idpk of the product or service
                    echo "<input type='hidden' class='editProductId' id='editProductId' value='{$product['idpk']}'>";
                echo "<td><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
                echo "</tr>";
                echo "<tr></tr>"; // additional line after each product or service
            }
        }

        // Add spacing rows between active and inactive products
        echo "<tr></tr><tr></tr><tr></tr><tr></tr><tr></tr>";

        // Display inactive products with opacity
        if (!empty($inactiveProducts)) {
            foreach ($inactiveProducts as $product) {
                $opacityStyle = 'style="opacity: 0.4;"';
                $truncatedName = (strlen($product['name']) > 50) ? substr($product['name'], 0, 50) . '...' : $product['name'];
                $truncatedDescription = (strlen($product['ShortDescription']) > 100) ? substr($product['ShortDescription'], 0, 100) . '...' : $product['ShortDescription'];
                $truncatedPersonalNotes = (strlen($product['PersonalNotes']) > 100) ? substr($product['PersonalNotes'], 0, 100) . '...' : $product['PersonalNotes'];
                
                // Display Shipping Price if not 0 or null
                $shippingPrice = (!empty($product['SellingPricePackagingAndShippingInDollars']) && $product['SellingPricePackagingAndShippingInDollars'] != 0)
                                ? '(+' . $product['SellingPricePackagingAndShippingInDollars'] . '$)'
                                : '';

                echo "<tr $opacityStyle>";
                echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
                echo "<td><div style=\"opacity: 0.5;\">$truncatedDescription</div></td>";
                echo "<td>{$product['SellingPriceProductOrServiceInDollars']}$</td>";
                echo "<td>$shippingPrice</td>";
                // echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>✏️ EDIT</a></td>";
                // echo "<td><a href='javascript:void(0)' onclick='showEditProduct({$product['idpk']})'>✏️ EDIT</a></td>";
                // hidden field to transfer the idpk of the product or service
                    echo "<input type='hidden' class='editProductId' id='editProductId' value='{$product['idpk']}'>";
                echo "<td><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
                echo "</tr>";
                echo "<tr></tr>"; // additional line after each product or service
            }
        }

        // Display message only if there are no products at all
        if (empty($activeProducts) && empty($inactiveProducts)) {
            echo "<tr><td colspan='5' align='center'>please create new products and services so they can be shown here by clicking on the button above</td></tr>";
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
    // JavaScript variables for PHP values
    const exchangeRateCurrencyCode = "<?php echo $ExchangeRateCurrencyCode; ?>";
    const exchangeRate = parseFloat("<?php echo $ExchangeRateOneDollarIsEqualTo; ?>");

    let isUpdating = false; // Prevent circular updates

    // Initialize fields with calculated values based on existing database data
    document.addEventListener('DOMContentLoaded', () => {
        const sellingPriceUSD = document.getElementById('SellingPriceProductOrServiceInDollars');
        const sellingPriceOther = document.getElementById('SellingPriceProductOrServiceInDollarsInOtherCurrency');
        const shippingPriceUSD = document.getElementById('SellingPricePackagingAndShippingInDollars');
        const shippingPriceOther = document.getElementById('SellingPricePackagingAndShippingInDollarsInOtherCurrency');

        if (exchangeRateCurrencyCode !== "USD") {
            // Initialize selling price fields
            if (sellingPriceUSD && !isNaN(parseFloat(sellingPriceUSD.value))) {
                sellingPriceOther.value = (parseFloat(sellingPriceUSD.value) * exchangeRate).toFixed(2);
            } else if (sellingPriceOther && !isNaN(parseFloat(sellingPriceOther.value))) {
                sellingPriceUSD.value = (parseFloat(sellingPriceOther.value) / exchangeRate).toFixed(2);
            }

            // Initialize shipping price fields
            if (shippingPriceUSD && !isNaN(parseFloat(shippingPriceUSD.value))) {
                shippingPriceOther.value = (parseFloat(shippingPriceUSD.value) * exchangeRate).toFixed(2);
            } else if (shippingPriceOther && !isNaN(parseFloat(shippingPriceOther.value))) {
                shippingPriceUSD.value = (parseFloat(shippingPriceOther.value) / exchangeRate).toFixed(2);
            }
        }

        updateLiveCalculations(); // Perform initial live calculations
    });

    function updatePriceCurrency(changedField) {
        if (isUpdating) return; // Prevent circular updates
        isUpdating = true;

        const sellingPriceUSD = document.getElementById('SellingPriceProductOrServiceInDollars');
        const sellingPriceOther = document.getElementById('SellingPriceProductOrServiceInDollarsInOtherCurrency');
        const shippingPriceUSD = document.getElementById('SellingPricePackagingAndShippingInDollars');
        const shippingPriceOther = document.getElementById('SellingPricePackagingAndShippingInDollarsInOtherCurrency');

        if (changedField === 'SellingPriceProductOrServiceInDollars') {
            const usdValue = parseFloat(sellingPriceUSD.value);
            if (!isNaN(usdValue)) {
                sellingPriceOther.value = (usdValue * exchangeRate).toFixed(2);
            } else {
                sellingPriceOther.value = '';
            }
        } else if (changedField === 'SellingPriceProductOrServiceInDollarsInOtherCurrency') {
            const otherCurrencyValue = parseFloat(sellingPriceOther.value);
            if (!isNaN(otherCurrencyValue)) {
                sellingPriceUSD.value = (otherCurrencyValue / exchangeRate).toFixed(2);
            } else {
                sellingPriceUSD.value = '';
            }
        } else if (changedField === 'SellingPricePackagingAndShippingInDollars') {
            const usdValue = parseFloat(shippingPriceUSD.value);
            if (!isNaN(usdValue)) {
                shippingPriceOther.value = (usdValue * exchangeRate).toFixed(2);
            } else {
                shippingPriceOther.value = '';
            }
        } else if (changedField === 'SellingPricePackagingAndShippingInDollarsInOtherCurrency') {
            const otherCurrencyValue = parseFloat(shippingPriceOther.value);
            if (!isNaN(otherCurrencyValue)) {
                shippingPriceUSD.value = (otherCurrencyValue / exchangeRate).toFixed(2);
            } else {
                shippingPriceUSD.value = '';
            }
        }

        updateLiveCalculations(); // Update live calculations after price update
        isUpdating = false;
    }

    function updateLiveCalculations() {
        const sellingPriceInput = document.getElementById('SellingPriceProductOrServiceInDollars');
        const shippingPriceInput = document.getElementById('SellingPricePackagingAndShippingInDollars');
        const taxesInput = document.getElementById('TaxesInPercent');
        const liveCalculationsDiv = document.getElementById('LiveCalculations');

        if (!liveCalculationsDiv) {
            console.error('LiveCalculations element not found.');
            return;
        }

        const sellingPrice = parseFloat(sellingPriceInput?.value) || 0;
        const shippingPrice = parseFloat(shippingPriceInput?.value) || 0;
        const taxesPercent = parseFloat(taxesInput?.value) || 0;

        const combinedValue = sellingPrice + shippingPrice;
        const contributionPercent = <?php echo isset($ContributionForTRAMANNPORT) ? json_encode($ContributionForTRAMANNPORT) : json_encode($ContributionForTRAMANNPORT); ?>;
        const contributionValue = (combinedValue * contributionPercent) / 100;
        const netPrice = combinedValue + contributionValue;
        const taxesValue = (netPrice * taxesPercent) / 100;
        const grossPrice = netPrice + taxesValue;

        const isUSD = exchangeRateCurrencyCode === "USD";
        const convertValue = (value) => (isUSD ? value : value * exchangeRate);

        const combinedValueConverted = convertValue(combinedValue);
        const contributionValueConverted = convertValue(contributionValue);
        const netPriceConverted = convertValue(netPrice);
        const taxesValueConverted = convertValue(taxesValue);
        const grossPriceConverted = convertValue(grossPrice);

        liveCalculationsDiv.innerHTML = `
            <br>(combined value for you: ${combinedValue.toFixed(2)}$, 
            adding ${contributionValue.toFixed(2)}$ (${contributionPercent}%) contribution for TRAMANN PORT, 
            total net price therefore: ${netPrice.toFixed(2)}$, 
            adding ${taxesValue.toFixed(2)}$ (${taxesPercent}%) taxes, 
            total gross price therefore: ${grossPrice.toFixed(2)}$)
            ${!isUSD ? `
            <br><br><div style="opacity: 0.5;">(in ${exchangeRateCurrencyCode}: combined value for you: ${combinedValueConverted.toFixed(2)}, 
            adding ${contributionValueConverted.toFixed(2)} (${contributionPercent}%) contribution for TRAMANN PORT, 
            total net price therefore: ${netPriceConverted.toFixed(2)}, 
            adding ${taxesValueConverted.toFixed(2)} (${taxesPercent}%) taxes, 
            total gross price therefore: ${grossPriceConverted.toFixed(2)})</div>` : ''}
        `;
    }
</script>