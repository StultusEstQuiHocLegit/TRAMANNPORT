<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $errors = [];

    // Check required fields
    $requiredFields = [
        'KeywordsForSearch', 
        'name', 
        'SellingPriceProductOrServiceInDollars', 
        'type'
    ];

    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[] = ucfirst($field) . " is required.";
        }
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
    $allowComments = isset($_POST['AllowComments']) ? $_POST['AllowComments'] : '0';
    $type = $_POST['type'];
    $sellingPriceProductOrServiceInDollars = $_POST['SellingPriceProductOrServiceInDollars'];
    $weightInKg = isset($_POST['WeightInKg']) ? $_POST['WeightInKg'] : '0';
    $dimensionsLengthInMm = isset($_POST['DimensionsLengthInMm']) ? $_POST['DimensionsLengthInMm'] : '0';
    $dimensionsWidthInMm = isset($_POST['DimensionsWidthInMm']) ? $_POST['DimensionsWidthInMm'] : '0';
    $dimensionsHeightInMm = isset($_POST['DimensionsHeightInMm']) ? $_POST['DimensionsHeightInMm'] : '0';
    $sellingPricePackagingAndShippingInDollars = isset($_POST['SellingPricePackagingAndShippingInDollars']) ? $_POST['SellingPricePackagingAndShippingInDollars'] : '0';
    $manageInventory = isset($_POST['ManageInventory']) ? $_POST['ManageInventory'] : '0';
    $inventoryAvailable = isset($_POST['InventoryAvailable']) ? $_POST['InventoryAvailable'] : '0';
    $inventoryInProduction = isset($_POST['InventoryInProduction']) ? $_POST['InventoryInProduction'] : '0';
    $personalNotes = $_POST['PersonalNotes'];

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
        SellingPricePackagingAndShippingInDollars, ManageInventory, InventoryAvailable, 
        InventoryInProduction, PersonalNotes, state
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt->execute([$timestampCreation, $idpkCreator, $keywordsForSearch, $name, $shortDescription, $longDescription, $allowComments, $type, $sellingPriceProductOrServiceInDollars, $weightInKg, $dimensionsLengthInMm, $dimensionsWidthInMm, $dimensionsHeightInMm, $sellingPricePackagingAndShippingInDollars, $manageInventory, $inventoryAvailable, $inventoryInProduction, $personalNotes, $state])) {
        echo "Error in SQL execution: " . implode(", ", $stmt->errorInfo());
        exit;
    }

    // Get the ID of the new product
    $productId = $pdo->lastInsertId();

    // Handle file uploads
    $uploadDir = "uploads/ProductPictures/$productId_";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    for ($i = 0; $i < 5; $i++) {
        if (!empty($_FILES["ProductPicture$i"]['name'])) {
            $fileName = basename($_FILES["ProductPicture$i"]['name']);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $validTypes = ['png', 'jpg', 'jpeg', 'gif'];

            // Check file type
            if (in_array($fileType, $validTypes)) {
                $targetFilePath = $uploadDir . $i . '.' . $fileType;
                if (move_uploaded_file($_FILES["ProductPicture$i"]['tmp_name'], $targetFilePath)) {
                    // File successfully uploaded
                } else {
                    echo "Error uploading file $i.";
                }
            } else {
                echo "Invalid file type for picture $i.";
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Product created successfully.']);
    exit; // Stop further processing
}
?>
























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
        document.getElementById('createNewProductButton').style.display = 'block';
        document.getElementById('listProductDiv').style.display = 'block';
        loadProductList();  // Load the list of products
    }








function submitFormCreateProduct() {
    event.preventDefault();  // Prevent the default form submission
    const form = document.getElementById('createProductForm');
    const formData = new FormData(form);
    
    // Add action to the form data
    formData.append('action', 'create');

    // Check for required fields
    const requiredFields = ['KeywordsForSearch', 'name', 'SellingPriceProductOrServiceInDollars', 'type'];
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

    // Proceed with the AJAX request
    fetch('products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product created successfully!');
            showProductList();  // Show list after product creation
        } else {
            alert('Error creating product: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}











    // Function to submit the update product form via AJAX
    function submitFormUpdateProduct() {
        event.preventDefault();  // Prevent the default form submission
        const form = document.getElementById('editProductForm');
        const formData = new FormData(form);

        formData.append('action', 'update');  // Add action to the form data

        fetch('products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product updated successfully!');
                showProductList();  // Show list after product update
            } else {
                alert('Error updating product: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }









    // Function to load the list of products dynamically
    function loadProductList() {
        fetch('products.php')  // Sends a GET request
        .then(response => response.json())
        .then(data => {
            let productListDiv = document.getElementById('productList');
            productListDiv.innerHTML = '';  // Clear the existing list

            data.products.forEach(product => {
                let productItem = document.createElement('div');
                let opacityStyle = product.state == 0 ? 'opacity: 0.4;' : '';  // Apply reduced opacity if inactive

                productItem.innerHTML = `<div style="${opacityStyle}">
                    ${product.name} (${product.id}) - ${product.shortDescription} - ${product.sellingPrice}$ (+${product.shippingPrice}$)
                    <a href="javascript:void(0)" onclick="showEditProduct(${product.id})">Edit</a>
                </div>`;
                productListDiv.appendChild(productItem);
            });
        })
        .catch(error => console.error('Error:', error));
    }





    








    // Function to show the edit form for a specific product
    function showEditProduct(productId) {
        // Fetch product details and fill the edit form
        fetch(`products.php?action=get&id=${productId}`)
        .then(response => response.json())
        .then(data => {
            let product = data.product;
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editProductName').value = product.name;
            document.getElementById('editProductPrice').value = product.sellingPrice;

            document.getElementById('editProductDiv').style.display = 'block';
            document.getElementById('listProductDiv').style.display = 'none';
            document.getElementById('createProductDiv').style.display = 'none';
            document.getElementById('createNewProductButton').style.display = 'none';
        })
        .catch(error => console.error('Error:', error));
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





















































<h1>PRODUCTS AND SERVICES</h1>

<!-- Button to toggle showing the create product form -->
<a href="javascript:void(0)" id="createNewProductButton" class="button" onclick="showCreateProduct()">CREATE NEW PRODUCT OR SERVICE</a>






<!-- Div for creating new products (hidden by default) -->
<div id="createProductDiv" class="steps" style="display: none;">
    <div align=center>
        <h3>CREATE</h3>
    </div>
    <form id="createProductForm" onsubmit="submitFormCreateProduct(event)" enctype="multipart/form-data">
        <!-- Product Keywords for Search -->
        <textarea id="KeywordsForSearch" name="KeywordsForSearch" rows="3" style="width: 100%;" placeholder="something for the bots" required></textarea>
        <label for="KeywordsForSearch">keywords for search*<br><div style="opacity: 0.4;">(* means that this field is required)</div></label>
        
        <!-- Product Name -->
        <br><br>
        <input type="text" id="name" name="name" placeholder="something short for the humans" style="width: 500px;" required>
        <label for="name">product name*</label>

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
            <input type="number" id="SellingPriceProductOrServiceInDollars" name="SellingPriceProductOrServiceInDollars" placeholder="price the explorer should pay" style="width: 300px;" required>
            <label for="SellingPriceProductOrServiceInDollars">selling price (in USD)*</label>
        <!-- Div for Selling Price (only for products/food) -->
        <div id="priceAttributes" style="display: none;">
            <br><br>
            <input type="number" id="SellingPricePackagingAndShippingInDollars" name="SellingPricePackagingAndShippingInDollars" placeholder="only if you want to separate" style="width: 300px;">
            <label for="SellingPricePackagingAndShippingInDollars">selling price of packaging and shipping (in USD)</label>
        </div>

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
            <input type="number" id="InventoryInProduction" name="InventoryInProduction" placeholder="and how much is in production" style="width: 300px;">
            <label for="InventoryInProduction">inventory in production</label>
        </div>

        <!-- Personal Notes -->
        <br><br><br><br><br>
        <textarea id="PersonalNotes" name="PersonalNotes" rows="6" style="width: 100%;" placeholder="only you can see these"></textarea>
        <label for="PersonalNotes">personal notes</label>

        <br><br><br><br><br>
        <div align=center>
            <!-- Submit button for creating product -->
            <a href="javascript:void(0);" class="mainbutton" onclick="submitFormCreateProduct()">SAVE</a>
        </div>
    </form>
</div>














<!-- Div for editing existing products (hidden by default) -->
<div id="editProductDiv" class="steps" style="display: none;">
    <div align=center>
        <h3>UPDATE</h3>
    </div>
    <form id="editProductForm" onsubmit="submitFormUpdateProduct(event)" enctype="multipart/form-data">
        <input type="hidden" id="editProductId" name="productId">
        <label for="editProductName">Product Name:</label>
        <input type="text" id="editProductName" name="productName" required><br>
        <label for="editProductPrice">Price:</label>
        <input type="number" id="editProductPrice" name="productPrice" required><br>
        <br><br><br><br><br>
        <div align=center>
            <a href="javascript:void(0);" class="mainbutton" onclick="submitFormUpdateProduct()">SAVE</a>
        </div>
    </form>
</div>














<!-- Div for listing all products -->
<div id="listProductDiv" class="steps">
    <br><br><br><br><br>
    <div id="productList">
        <!-- Products will be dynamically loaded here
         please use the following format for display from the database ProductsAndServices where the IdpkCreator is the same as the user idpk (// Assuming user_id is stored in a cookie
    $user_id = $_COOKIE['user_id'];
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);)
    'name' ('idpk') - 'ShortDescription' - 'SellingPriceProductOrServiceInDollars'$ (+'SellingPricePackagingAndShippingInDollars'$) - editLinkHere,
    display with only 0.4 opacity if 'state' = 0 (inactive)
        -->
    </div>
</div>
