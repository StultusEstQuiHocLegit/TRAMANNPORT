<h1>🏰 INVENTORY</h1>

<?php
$preselectedOption = "your_products_services"; // add preselected search option
$preselectedViewing = "manage_inventory"; // add preselected viewing

include ("explore.php"); // include explore.php for exploring and searching
echo "<br><br><br><br><br>";
?>





















<!-- Div for listing all products -->
<div id="listProductDiv" class="steps">
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
        // $stmt = $pdo->prepare("SELECT idpk, name, ManageInventory, InventoryAvailable, InventoryInProduction, InventoryMinimumLevel, PersonalNotes, state FROM ProductsAndServices WHERE IdpkCreator = :id ORDER BY name ASC");
        // sort alphabetically, but show products and services where InventoryAvailable < InventoryMinimumLevel first
        $stmt = $pdo->prepare("SELECT idpk, name, ManageInventory, InventoryAvailable, InventoryInProduction, InventoryMinimumLevel, PersonalNotes, state FROM ProductsAndServices WHERE IdpkCreator = :id ORDER BY CASE WHEN InventoryAvailable < InventoryMinimumLevel THEN 0 ELSE 1 END, name ASC");
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

        // Display active products
        if (!empty($activeProducts)) {
            // Start the table structure
            echo "<table style='width: 100%; text-align: left;'>";
            echo "<tr>";
                echo "<th>name (idpk)</th>";
                echo "<th>available</th>";
                echo "<th>in production<br>or reordered</th>";
                echo "<th>minimum</th>";
                echo "<th>personal notes</th>";
            echo "</tr>";
            echo "<tr></tr><tr></tr>";
            
            foreach ($activeProducts as $product) {
                // Truncate the name and description
                $truncatedName = (strlen($product['name']) > 50) ? substr($product['name'], 0, 50) . '...' : $product['name'];
                $truncatedPersonalNotes = (strlen($product['PersonalNotes']) > 100) ? substr($product['PersonalNotes'], 0, 100) . '...' : $product['PersonalNotes'];
            
                echo "<tr>";
                // Check if inventory is managed
                if ($product['ManageInventory'] == 0) {
                    echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\" style=\"opacity: 0.6;\"><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
                    echo "<td id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" placeholder=\"0\" style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" placeholder=\"0\" style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td id=\"InventoryMinimumLevel{$product['idpk']}\" name=\"InventoryMinimumLevel\" value=\"" . (isset($product['InventoryMinimumLevel']) ? htmlspecialchars($product['InventoryMinimumLevel']) : '0') . "\" placeholder=\"0\" style=\"opacity: 0.4;\">inventory not managed</td>";
                    // echo "<td style=\"opacity: 0.6;\"><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
                    echo "<td><input type=\"text\" id=\"PersonalNotes_{$product['idpk']}\" title=\"" . htmlspecialchars($product['PersonalNotes']) . "\" name=\"PersonalNotes\" value=\"" . htmlspecialchars($product['PersonalNotes']) . "\" style=\"opacity: 0.3; width: 100%;\" onchange=\"updateInventory({$product['idpk']}, 'PersonalNotes', this.value)\"></td>";
                } else {
                    echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\"><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
                    echo "<td><input type=\"number\" id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryAvailable', this.value)\"></td>";
                    echo "<td><input type=\"number\" id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryInProduction', this.value)\"></td>";
                    echo "<td><input type=\"number\" id=\"InventoryMinimumLevel{$product['idpk']}\" name=\"InventoryMinimumLevel\" value=\"" . (isset($product['InventoryMinimumLevel']) ? htmlspecialchars($product['InventoryMinimumLevel']) : '0') . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryMinimumLevel', this.value)\" style=\"opacity: 0.6;\"></td>";
                    // echo "<td><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
                    echo "<td><input type=\"text\" id=\"PersonalNotes_{$product['idpk']}\" title=\"" . htmlspecialchars($product['PersonalNotes']) . "\" name=\"PersonalNotes\" value=\"" . htmlspecialchars($product['PersonalNotes']) . "\" style=\"opacity: 0.4; width: 100%;\" onchange=\"updateInventory({$product['idpk']}, 'PersonalNotes', this.value)\"></td>";
                }
                echo "</tr>";
                echo "<tr></tr><tr></tr>"; // additional space after each product or service
            }
        }

        // Add spacing rows between active and inactive products
        echo "<tr></tr><tr></tr><tr></tr><tr></tr><tr></tr>";

        // Display inactive products with opacity
        if (!empty($inactiveProducts)) {
            foreach ($inactiveProducts as $product) {
                $opacityStyle = 'style="opacity: 0.4;"';
                $truncatedName = (strlen($product['name']) > 50) ? substr($product['name'], 0, 50) . '...' : $product['name'];
                $truncatedPersonalNotes = (strlen($product['PersonalNotes']) > 100) ? substr($product['PersonalNotes'], 0, 100) . '...' : $product['PersonalNotes'];
            
                echo "<tr $opacityStyle>";
                // Check if inventory is managed
                if ($product['ManageInventory'] == 0) {
                    echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\" style=\"opacity: 0.6;\"><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
                    echo "<td id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" placeholder=\"0\" style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" placeholder=\"0\" style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td id=\"InventoryMinimumLevel_{$product['idpk']}\" name=\"InventoryMinimumLevel\" value=\"" . (isset($product['InventoryMinimumLevel']) ? htmlspecialchars($product['InventoryMinimumLevel']) : '0') . "\" placeholder=\"0\" style=\"opacity: 0.4;\">inventory not managed</td>";
                    // echo "<td style=\"opacity: 0.6;\"><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
                    echo "<td><input type=\"text\" id=\"PersonalNotes_{$product['idpk']}\" title=\"" . htmlspecialchars($product['PersonalNotes']) . "\" name=\"PersonalNotes\" value=\"" . htmlspecialchars($product['PersonalNotes']) . "\" style=\"opacity: 0.3; width: 100%;\" onchange=\"updateInventory({$product['idpk']}, 'PersonalNotes', this.value)\"></td>";
                } else {
                    echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\"><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
                    echo "<td><input type=\"number\" id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryAvailable', this.value)\"></td>";
                    echo "<td><input type=\"number\" id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryInProduction', this.value)\"></td>";
                    echo "<td><input type=\"number\" id=\"InventoryMinimumLevel_{$product['idpk']}\" name=\"InventoryMinimumLevel\" value=\"" . (isset($product['InventoryMinimumLevel']) ? htmlspecialchars($product['InventoryMinimumLevel']) : '0') . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryMinimumLevel', this.value)\" style=\"opacity: 0.6;\"></td>";
                    // echo "<td><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
                    echo "<td><input type=\"text\" id=\"PersonalNotes_{$product['idpk']}\" title=\"" . htmlspecialchars($product['PersonalNotes']) . "\" name=\"PersonalNotes\" value=\"" . htmlspecialchars($product['PersonalNotes']) . "\" style=\"opacity: 0.4; width: 100%;\" onchange=\"updateInventory({$product['idpk']}, 'PersonalNotes', this.value)\"></td>";
                }
                echo "</tr>";
                echo "<tr></tr><tr></tr>"; // additional space after each product or service
            }
        }

        // Display message only if there are no products at all
        if (empty($activeProducts) && empty($inactiveProducts)) {
            echo "<tr><td colspan='5' style='text-align: center;'>please create new <a href=\"index.php?content=products.php\">📦 PRODUCTS AND SERVICES</a> so they can be shown here</td></tr>";
        }

        echo '</table>';
    }
    ?>
    </div>
</div>




















<script>
document.addEventListener("DOMContentLoaded", function() {
    // // Select all InventoryAvailable and InventoryInProduction inputs
    // const inputs = document.querySelectorAll("input[type='number'][id^='InventoryAvailable_'], input[type='number'][id^='InventoryInProduction_']");

    // // Loop through each input and check its value
    // inputs.forEach(input => {
    //     applyOutlineBasedOnValue(input, parseFloat(input.value));
    // });

    // Select all InventoryAvailable inputs
    const availableInputs = document.querySelectorAll("input[type='number'][id^='InventoryAvailable_']");

    // Loop through each InventoryAvailable input and apply outline based on value
    availableInputs.forEach(input => {
        applyOutlineBasedOnValue(input, parseFloat(input.value));
    });

    // // Specifically for Inventory In Production inputs
    // const inProductionInputs = document.querySelectorAll("input[type='number'][id^='InventoryInProduction_']");
    // inProductionInputs.forEach(input => {
    //     applyOutlineBasedOnValue(input, parseFloat(input.value));
    // });

    // Add event listener for changes in InventoryMinimumLevel to adjust the outline of InventoryAvailable
    const minLevelInputs = document.querySelectorAll("input[type='number'][id^='InventoryMinimumLevel']");
    minLevelInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.id.replace('InventoryMinimumLevel', ''); // Extract productId from input ID
            adjustInventoryOutlineBasedOnMinimumLevel(productId);
        });
    });

    // Assuming search results are dynamically loaded, add a function to trigger highlighting
    const searchResults = document.getElementById("search-results"); // Assuming you have a search results container with this ID
    if (searchResults) {
        // Wait for the search results to be shown (or use a custom event if necessary)
        new MutationObserver(function() {
            const products = searchResults.querySelectorAll('.product'); // Assuming each product has the 'product' class
            products.forEach(product => {
                const productId = product.getAttribute("data-product-id"); // Assuming each product has a 'data-product-id' attribute
                adjustInventoryOutlineBasedOnMinimumLevel(productId);
            });
        }).observe(searchResults, { childList: true, subtree: true });
    }
});

function updateInventory(productId, field, value) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "SaveDataInventory.php", true); // Adjust the path to your PHP update script
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log(xhr.responseText); // Optional: Log the server response for debugging

            // After updating, re-apply the outlines for both fields
            // applyOutlineBasedOnValue(inventoryAvailableField, parseFloat(inventoryAvailableField.value));
            // applyOutlineBasedOnValue(inventoryInProductionField, parseFloat(inventoryInProductionField.value));

            // Update the corresponding fields dynamically
            const targetInputId = `${field}_${productId}`;
            const targetInput = document.getElementById(targetInputId);

            // if (targetInput) {
            //     targetInput.value = value;
            //     applyOutlineBasedOnValue(targetInput, parseFloat(value)); // Re-apply styles based on the value
            // }

            if (targetInput) {
                targetInput.value = value;

                // Apply outline only to InventoryAvailable field
                if (field === 'InventoryAvailable') {
                    applyOutlineBasedOnValue(targetInput, parseFloat(value));
                }
            }

            // Update the additional list (e.g., in search results or inventory list)
            const mirroredInput = document.querySelectorAll(`[id='${field}_${productId}']`);
            mirroredInput.forEach(input => {
                if (input !== targetInput) { // Prevent updating the input being modified
                    input.value = value;
                    // applyOutlineBasedOnValue(input, parseFloat(value)); // Apply styling
                    // Apply outline only to InventoryAvailable field
                    if (field === 'InventoryAvailable') {
                        applyOutlineBasedOnValue(input, parseFloat(value));
                    }
                }
            });
        }
    };

    // Select the input fields by their IDs
    const inventoryAvailableField = document.getElementById("InventoryAvailable_" + productId);
    const inventoryInProductionField = document.getElementById("InventoryInProduction_" + productId);

    const inventoryInProductionValue = parseFloat(value);
    const currentInventoryAvailableValue = parseFloat(inventoryAvailableField.value);

    if (field === 'InventoryInProduction') {
        // Calculate the change in production
        const previousInProductionValue = parseFloat(inventoryInProductionField.defaultValue);
        const changeInProduction = inventoryInProductionValue - previousInProductionValue; // Change in Inventory In Production

        // Only update Inventory Available if production decreases
        if (changeInProduction < 0) {
            // Increase Inventory Available by the absolute value of the decrease
            inventoryAvailableField.value = currentInventoryAvailableValue - changeInProduction; // Subtracting a negative is adding

            // Update the default value to the new value for future calculations
            inventoryInProductionField.defaultValue = inventoryInProductionValue;

            // Apply the outline based on the new value
            applyOutlineBasedOnValue(inventoryAvailableField, parseFloat(inventoryAvailableField.value));

            // Update mirrored input fields for Inventory Available
            const mirroredInventoryAvailableInput = document.querySelectorAll(`[id='InventoryAvailable_${productId}']`);
            mirroredInventoryAvailableInput.forEach(input => {
                if (input !== inventoryAvailableField) { // Prevent updating the input being modified
                    input.value = inventoryAvailableField.value;
                    applyOutlineBasedOnValue(input, parseFloat(input.value)); // Apply styling
                }
            });
        } else {
            // If inventory in production increases, do nothing to inventory available
            inventoryInProductionField.defaultValue = inventoryInProductionValue; // Just update the default
        }
    } else {
        // Apply the appropriate outline based on the new value of Inventory Available
        if (field === 'InventoryAvailable') {
            applyOutlineBasedOnValue(inventoryAvailableField, parseFloat(value));
        }

        // Update mirrored input fields for Inventory Available
        const mirroredInventoryAvailableInput = document.querySelectorAll(`[id='InventoryAvailable_${productId}']`);
        mirroredInventoryAvailableInput.forEach(input => {
            if (input !== inventoryAvailableField) { // Prevent updating the input being modified
                input.value = value;
                applyOutlineBasedOnValue(input, parseFloat(input.value)); // Apply styling
            }
        });
    }

    // Send updated values to the PHP script
    const updatedAvailableValue = inventoryAvailableField.value; // Get the current value of Inventory Available

    // Send data to the PHP script
    xhr.send("id=" + productId + "&field=" + field + "&value=" + value + "&updatedAvailable=" + updatedAvailableValue);
}

function applyOutlineBasedOnValue(input, value) {
    // Get the product ID from the input's ID
    const productId = input.id.match(/\d+$/); // Extract numeric ID at the end of the input ID
    if (!productId) return; // Exit if no product ID is found

    // Get the corresponding InventoryMinimumLevel input
    const minLevelInput = document.getElementById(`InventoryMinimumLevel${productId}`);
    const minLevel = minLevelInput ? parseFloat(minLevelInput.value) || 0 : 0; // Default to 0 if not set

    // Apply the outline based on value ranges
    if (value <= -10 || value < minLevel) { // Check both conditions
        input.style.outline = "3px solid yellow";
    } else if (value <= -5) {
        input.style.outline = "2px solid yellow";
    } else if (value <= 0) {
        input.style.outline = "1px solid yellow";
    } else if (value <= 5) {
        input.style.outline = "1px solid white";
    } else {
        input.style.outline = "none"; // Reset outline if no condition is met
    }
}

function adjustInventoryOutlineBasedOnMinimumLevel(productId) {
    // Get the values of InventoryAvailable and InventoryMinimumLevel for the specific product
    const inventoryAvailableField = document.getElementById(`InventoryAvailable_${productId}`);
    const inventoryMinimumLevelField = document.getElementById(`InventoryMinimumLevel${productId}`);

    if (!inventoryAvailableField || !inventoryMinimumLevelField) return;

    const availableValue = parseFloat(inventoryAvailableField.value);
    const minimumLevelValue = parseFloat(inventoryMinimumLevelField.value);

    // Check if the InventoryAvailable is below or above InventoryMinimumLevel
    if (availableValue < minimumLevelValue) {
        inventoryAvailableField.style.outline = "3px solid yellow"; // Highlight if available is lower than minimum level
    } else if (availableValue > minimumLevelValue) {
    } else {
        inventoryAvailableField.style.outline = "none"; // Remove outline if they are equal
    }
}
</script>