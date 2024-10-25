<h1>INVENTORY</h1>

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
        $stmt = $pdo->prepare("SELECT idpk, name, ManageInventory, InventoryAvailable, InventoryInProduction, PersonalNotes, state FROM ProductsAndServices WHERE IdpkCreator = :id ORDER BY name ASC");
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
            echo '<table>';
            echo "<tr>";
                echo "<th>name (idpk)</th>";
                echo "<th>available</th>";
                echo "<th>in production</th>";
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
                    echo "<td style=\"opacity: 0.6;\">$truncatedName ({$product['idpk']})</td>";
                    echo "<td style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td style=\"opacity: 0.6;\">$truncatedPersonalNotes</td>";
                } else {
                    echo "<td>$truncatedName ({$product['idpk']})</td>";
                    echo "<td><input type=\"number\" id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryAvailable', this.value)\"></td>";
                    echo "<td><input type=\"number\" id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryInProduction', this.value)\"></td>";
                    echo "<td>$truncatedPersonalNotes</td>";
                }
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
                $truncatedPersonalNotes = (strlen($product['PersonalNotes']) > 100) ? substr($product['PersonalNotes'], 0, 100) . '...' : $product['PersonalNotes'];
            
                echo "<tr $opacityStyle>";
                // Check if inventory is managed
                if ($product['ManageInventory'] == 0) {
                    echo "<td style=\"opacity: 0.6;\">$truncatedName ({$product['idpk']})</td>";
                    echo "<td style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td style=\"opacity: 0.6;\">inventory not managed</td>";
                    echo "<td style=\"opacity: 0.6;\">$truncatedPersonalNotes</td>";
                } else {
                    echo "<td>$truncatedName ({$product['idpk']})</td>";
                    echo "<td><input type=\"number\" id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryAvailable', this.value)\"></td>";
                    echo "<td><input type=\"number\" id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryInProduction', this.value)\"></td>";
                    echo "<td>$truncatedPersonalNotes</td>";
                }
                echo "</tr>";
                echo "<tr></tr>"; // additional line after each product or service
            }
        } else {
            // If no products are found, display a message
            echo "<tr><td colspan='5'>please <a href=\"index.php?content=products.php\">CREATE NEW PRODUCTS</a> so they can be shown here</td></tr>";
        }

        echo '</table>';
    }
    ?>
    </div>
</div>












<script>
document.addEventListener("DOMContentLoaded", function() {
    // Select all InventoryAvailable and InventoryInProduction inputs
    const inputs = document.querySelectorAll("input[type='number'][id^='InventoryAvailable_'], input[type='number'][id^='InventoryInProduction_']");

    // Loop through each input and check its value
    inputs.forEach(input => {
        applyOutlineBasedOnValue(input, parseFloat(input.value));
    });
});

function updateInventory(productId, field, value) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "SaveDataInventory.php", true); // Change to the actual path of your PHP update script
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log(xhr.responseText); // Optional: Log the server response for debugging
        }
    };

    // Send data to the PHP script
    xhr.send("id=" + productId + "&field=" + field + "&value=" + value);

    // Select the input field by its ID
    const inputField = document.getElementById(field + "_" + productId);
    
    // Apply the appropriate outline based on the new value
    applyOutlineBasedOnValue(inputField, parseFloat(value));
}

function applyOutlineBasedOnValue(input, value) {
    // Apply the outline based on value ranges
    if (value <= -10) {
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
</script>
