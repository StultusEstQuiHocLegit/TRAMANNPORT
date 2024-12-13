<?php
echo "<h1>👉 MANUAL SELLING</h1>";
?>
<script>
function setFormAction(actionType) {
    // Save the manual further information as a cookie before submitting
    saveManualFurtherInformation();

    // Set the action type in the hidden input field
    document.getElementById('formActionType').value = actionType;

    // Submit the form
    document.getElementById('manualSellingForm').submit();
}



















function confirmRemoval(productId, productName) {
    // Show confirmation popup
    const confirmation = confirm(`Are you sure you want to remove ${productName} (${productId})?`);
    if (confirmation) {
        // Remove the product and its quantity directly from the cookie
        deleteProductFromCookie(productId);
    }
}

function deleteProductFromCookie(productId) {
    // Retrieve the existing cookie data
    let addedItems = getCookie('manualSellingItems');
    addedItems = addedItems ? JSON.parse(addedItems) : [];

    // Filter out the product with the specified ID
    addedItems = addedItems.filter(item => item.idpk !== productId);

    // Remove the quantity for this product as well
    let quantities = getCookie('manualSellingQuantities');
    quantities = quantities ? JSON.parse(quantities) : [];

    quantities = quantities.filter(item => item.idpk !== productId);

    // Update the cookie
    setCookie('manualSellingItems', JSON.stringify(addedItems), 7);
    setCookie('manualSellingQuantities', JSON.stringify(quantities), 7);

    // Refresh the display
    displayAddedItems();
}



















// Add product or service to the cookie
function addToManualSelling(event, idpk, details) {
    event.preventDefault(); // Prevent default action

    // Retrieve the existing cookie data for products
    let addedItems = getCookie('manualSellingItems');
    addedItems = addedItems ? JSON.parse(addedItems) : [];

    // Retrieve the existing cookie data for quantities
    let quantities = getCookie('manualSellingQuantities');
    quantities = quantities ? JSON.parse(quantities) : [];

    // Check if the item already exists
    const existingItemIndex = addedItems.findIndex(item => item.idpk === idpk);
    if (existingItemIndex !== -1) {
        // Increment the quantity if the item exists
        const quantityIndex = quantities.findIndex(q => q.idpk === idpk);
        if (quantityIndex !== -1) {
            quantities[quantityIndex].quantity += 1;
        } else {
            // Fallback: add the quantity entry if it doesn't exist (rare case)
            quantities.push({
                idpk: idpk,
                quantity: 1
            });
        }
    } else {
        // Add the new item with all details
        addedItems.push({
            idpk: idpk,
            ...details
        });

        // Add the default quantity (e.g., 1)
        quantities.push({
            idpk: idpk,
            quantity: 1
        });
    }

    // Update the cookies
    setCookie('manualSellingItems', JSON.stringify(addedItems), 7);
    setCookie('manualSellingQuantities', JSON.stringify(quantities), 7);

    // Update the display
    displayAddedItems();
}

// Function to save the further information to the cookie
function saveManualFurtherInformation() {
    const furtherInfo = document.getElementById('IfManualFurtherInformation').value;
    setCookie('IfManualFurtherInformation', furtherInfo, 7); // Save for 7 days
}

function updateQuantity(productId, quantity) {
    // Retrieve the existing cookie data for quantities
    let quantities = getCookie('manualSellingQuantities');
    quantities = quantities ? JSON.parse(quantities) : [];

    // Update the quantity for the product
    const productIndex = quantities.findIndex(item => item.idpk === productId);
    if (productIndex !== -5) {
        quantities[productIndex].quantity = quantity;
    }

    // Update the cookie with the new quantities
    setCookie('manualSellingQuantities', JSON.stringify(quantities), 7);

    // Optionally refresh the display
    displayAddedItems();
}

function calculateTotals() {
    const addedItems = getCookie('manualSellingItems');
    const itemList = addedItems ? JSON.parse(addedItems) : [];

    const quantities = getCookie('manualSellingQuantities');
    const quantityList = quantities ? JSON.parse(quantities) : [];

    let totalSellingPrice = 0;
    let totalShippingPrice = 0;

    // Loop through items and calculate totals
    itemList.forEach(item => {
        const quantity = quantityList.find(q => q.idpk === item.idpk)?.quantity || 1;

        // Accumulate prices
        totalSellingPrice += (item.SellingPriceProductOrServiceInDollars || 0) * quantity;
        totalShippingPrice += (item.SellingPricePackagingAndShippingInDollars || 0) * quantity;
    });

    // Update totals on the page
    document.getElementById('TotalSellingPrice').textContent = totalSellingPrice.toFixed(2);
    document.getElementById('TotalShippingPrice').textContent = totalShippingPrice.toFixed(2);
}

// Update totals whenever the display is refreshed
function displayAddedItems() {
    const addedItems = getCookie('manualSellingItems');
    const itemList = addedItems ? JSON.parse(addedItems) : [];

    const quantities = getCookie('manualSellingQuantities');
    const quantityList = quantities ? JSON.parse(quantities) : [];

    const displayDiv = document.getElementById('ShowAddedProductsAnsServicesForManualSelling');
    displayDiv.innerHTML = ''; // Clear the div content

    if (itemList.length > 0) {
        const list = document.createElement('div');
        itemList.forEach(item => {
            const listItem = document.createElement('div');

            const link = document.createElement('a');
            link.href = `index.php?content=explore.php&action=ShowProduct&idpk=${item.idpk}`;
            link.title = `${item.name} (${item.idpk}), available: ${item.InventoryAvailable}, in production or reordered: ${item.InventoryInProduction}, ${item.PersonalNotes}`;
            const truncatedName = item.name.length > 50 ? item.name.substring(0, 50) + '...' : item.name;
            link.textContent = `${truncatedName} (${item.idpk})`;

            listItem.appendChild(link);

            // Get the quantity for this item
            const productQuantity = quantityList.find(q => q.idpk === item.idpk)?.quantity || 1;

            // Optionally, include other details in a separate div or span
            const detailsDiv = document.createElement('div');
            detailsDiv.style.fontSize = 'small';

            // Check prices and only display if they are greater than zero
            const productPrice = item.SellingPriceProductOrServiceInDollars > 0 
                ? `${item.SellingPriceProductOrServiceInDollars}$` 
                : '';
            const shippingPrice = item.SellingPricePackagingAndShippingInDollars > 0 
                ? `(+${item.SellingPricePackagingAndShippingInDollars}$)` 
                : '';

            // Construct the display string
            const priceDisplay = productPrice || shippingPrice 
                ? `${productPrice} ${shippingPrice}` 
                : '';

            detailsDiv.innerHTML = `
                ${priceDisplay}
                <br><input type="number" id="quantity_${item.idpk}" name="quantity_${item.idpk}" min="1" value="${productQuantity}" style="width: 80px;" onchange="updateQuantity(${item.idpk}, this.value)">
                <a href="javascript:void(0);" onclick="confirmRemoval(${item.idpk}, '${item.name}')" style="opacity: 0.5;">❌ REMOVE</a><br><br>
            `;
            listItem.appendChild(detailsDiv);

            list.appendChild(listItem);
        });
        displayDiv.appendChild(list);
    } else {
        displayDiv.textContent = 'Please select products and services using the search below.';
    }

    // Recalculate totals after updating the display
    calculateTotals();
}












// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize functionality on page load
    displayAddedItems();
    calculateTotals();

    const furtherInfoTextarea = document.getElementById('IfManualFurtherInformation');
    const suggestionsDiv = document.getElementById('ShowSuggestionsForIfManualFurtherInformation');

    if (furtherInfoTextarea) {
        // Save information to cookie on input
        furtherInfoTextarea.addEventListener('input', () => {
            saveManualFurtherInformation();
            loadSuggestions(furtherInfoTextarea.value);
            if (suggestionsDiv) {
                suggestionsDiv.style.display = 'block'; // Show suggestions when typing
            }
        });

        // Hide suggestions when clicking outside the textarea
        document.addEventListener('click', (event) => {
            if (suggestionsDiv && !furtherInfoTextarea.contains(event.target) && !suggestionsDiv.contains(event.target)) {
                suggestionsDiv.style.display = 'none'; // Hide suggestions
            }
        });
    }
});

// Function to load suggestions based on input
function loadSuggestions(query) {
    if (query.trim().length < 1) {
        document.getElementById('ShowSuggestionsForIfManualFurtherInformation').innerHTML = '';
        return;
    }

    fetch('SaveDataManualSellingOrBuying.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ query, type: 'ManualSelling' }) // Specify the type
    })
    .then(response => response.json())
    .then(data => {
        const suggestionsDiv = document.getElementById('ShowSuggestionsForIfManualFurtherInformation');
        suggestionsDiv.innerHTML = ''; // Clear previous suggestions

        if (data.success && data.suggestions.length > 0) {
            const list = document.createElement('div');
            list.style.listStyleType = 'none';

            data.suggestions.forEach(suggestion => {
                const listItem = document.createElement('div');
                const link = document.createElement('a');
                link.textContent = suggestion.text;
                link.href = 'javascript:void(0);';
                link.title = suggestion.fullText; // Full text on hover
                link.addEventListener('click', () => {
                    document.getElementById('IfManualFurtherInformation').value = suggestion.fullText;
                    saveManualFurtherInformation();
                    suggestionsDiv.innerHTML = ''; // Clear suggestions after selection
                });
                listItem.appendChild(link);
                listItem.appendChild(document.createElement('br'));
                listItem.appendChild(document.createElement('br'));
                list.appendChild(listItem);
            });

            suggestionsDiv.appendChild(list);
        } else {
            suggestionsDiv.innerHTML = '';
        }
    })
    .catch(error => {
        console.error('Error fetching suggestions:', error);
        document.getElementById('ShowSuggestionsForIfManualFurtherInformation').innerHTML = '';
    });
}





// Helper function to get a cookie value by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Helper function to set a cookie
function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value}; path=/; expires=${date.toUTCString()}`;
}
</script>








































<?php
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize an array to hold error messages
    $errors = [];

    // Get the user ID from the cookie
    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;



    // if there are cookies set, we continue (otherwise we don't)
    //
    // create an entry into the table carts (database connection already established ($stmt = $pdo->prepare("...))
    // TimestampCreation (integer) is set to the current: $timestampCreation = time(); // Current timestamp
    // manual is set to 1, IfManualFurtherInformation is set to the value from this field: IfManualFurtherInformation (if it exists), DeliveryType is set to 0
    //
    // now we create entries into the table transactions for each product or service (saved as cookies)
    // (but only if in the table ProductsAndServices, IdpkCreator = user idpk and state = 1 (active) (use the idpks saved in the cookies to check this before continuing))
    // we set: TimestampCreation = as handled above, IdpkExplorer = 0, IdpkProductOrService = idpk of the product or service (from the cookie),
    // IdpkCart = the idpk of the cart just created before, quantity = corrensponding quantity (from the cookie),
    //
    // AmountInDollars = get the sum of SellingPriceProductOrServiceInDollars + SellingPricePackagingAndShippingInDollars from the table ProductsAndServices
    // or, if the user entered something into this field: OverwrittenPrice, we take the OverwrittenPrice
    // and distribute it accordingly to all the products and services just sold
    // (for example: SellingPriceProductOrServiceInDollars + SellingPricePackagingAndShippingInDollars for product or service 1 -> 50$,
    // for 2, it is 30$ and for 3 it is 20$ and now the OverwrittenPrice is 50$, we distribute accordingly like this: 25$, 15$, 10$,
    // if the OverwrittenPrice is 200$, we distribute accordingly like this: 100$, 60$, 40$)
    //
    // state = 3 (if the user clicked on: ↗️ SAVE AS PAID) or 9 (if the user clicked on: ↗️ SAVE AS FINISHED)
    //
    // now we go to the table: ProductsAndServices and decrease the InventoryAvailable by the quantity we just sold



    // Get the current timestamp
    $timestampCreation = time();

    // Get the additional manual information and action type
    $ifManualFurtherInformation = $_POST['IfManualFurtherInformation'] ?? null;
    $formActionType = $_POST['formActionType'] ?? null;

    // Validate action type
    if (!in_array($formActionType, ['paid', 'finished'])) {
        $errors[] = "Invalid action type.";
    }

    // Parse cookies for added products and quantities
    $manualSellingItems = isset($_COOKIE['manualSellingItems']) ? json_decode($_COOKIE['manualSellingItems'], true) : [];
    $manualSellingQuantities = isset($_COOKIE['manualSellingQuantities']) ? json_decode($_COOKIE['manualSellingQuantities'], true) : [];

    // Validate product data
    if (empty($manualSellingItems) || empty($manualSellingQuantities)) {
        $errors[] = "No products or services selected.<br><br><a href=\"index.php?content=ManualSelling.php\">▶️ CONTINUE</a>";
    }

    // If no errors, proceed
    if (empty($errors)) {
        // Start database transactions
        try {
            $pdo->beginTransaction();

            // Insert into `carts`
            $stmt = $pdo->prepare("
                INSERT INTO carts (IdpkExplorerOrCreator, TimestampCreation, manual, IfManualFurtherInformation, DeliveryType)
                VALUES (0, :timestampCreation, 1, :ifManualFurtherInformation, 0)
            ");
            $stmt->execute([
                ':timestampCreation' => $timestampCreation,
                ':ifManualFurtherInformation' => $ifManualFurtherInformation,
            ]);
            $cartId = $pdo->lastInsertId();

            // Iterate through products and create transactions
            foreach ($manualSellingItems as $item) {
                $idpk = $item['idpk'];
                $quantity = 0;
                foreach ($manualSellingQuantities as $q) {
                    if ($q['idpk'] == $idpk) {
                        $quantity = $q['quantity'];
                        break;
                    }
                }

                // Validate product existence and state
                $stmt = $pdo->prepare("
                    SELECT SellingPriceProductOrServiceInDollars, SellingPricePackagingAndShippingInDollars, state
                    FROM ProductsAndServices
                    WHERE idpk = :idpk AND IdpkCreator = :user_id
                ");
                $stmt->execute([':idpk' => $idpk, ':user_id' => $user_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product || $product['state'] != 1) {
                    throw new Exception("Product with ID $idpk is not active or does not exist.");
                }

                // Calculate prices
                $sellingPrice = $product['SellingPriceProductOrServiceInDollars'] ?? 0;
                $shippingPrice = $product['SellingPricePackagingAndShippingInDollars'] ?? 0;
                $totalPrice = $sellingPrice + $shippingPrice;

                // Check for overwritten price
                $overwrittenPrice = $_POST['OverwrittenPrice'] ?? null;
                if ($overwrittenPrice) {
                    // Distribute the overwritten price proportionally
                    $totalSum = array_sum(array_map(function ($item) use ($manualSellingQuantities) {
                        $quantity = array_column($manualSellingQuantities, 'quantity', 'idpk')[$item['idpk']] ?? 1;
                        return ($item['SellingPriceProductOrServiceInDollars'] + $item['SellingPricePackagingAndShippingInDollars']) * $quantity;
                    }, $manualSellingItems));
                    $priceRatio = $totalPrice / $totalSum;
                    $totalPrice = $priceRatio * $overwrittenPrice;
                }

                // Insert into `transactions`
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (TimestampCreation, IdpkExplorer, IdpkProductOrService, IdpkCart, quantity, AmountInDollars, state)
                    VALUES (:timestampCreation, 0, :idpk, :cartId, :quantity, :amount, :state)
                ");
                $stmt->execute([
                    ':timestampCreation' => $timestampCreation,
                    ':idpk' => $idpk,
                    ':cartId' => $cartId,
                    ':quantity' => $quantity,
                    ':amount' => $totalPrice,
                    ':state' => $formActionType === 'paid' ? 3 : 9,
                ]);

                // Decrease inventory in `ProductsAndServices` only if `ManageInventory` = 1
                $stmt = $pdo->prepare("
                    UPDATE ProductsAndServices
                    SET InventoryAvailable = InventoryAvailable - :quantity
                    WHERE idpk = :idpk AND ManageInventory = 1
                ");
                $stmt->execute([':quantity' => $quantity, ':idpk' => $idpk]);
            }

            // Commit the transaction
            $pdo->commit();

            echo "Saved successfully.<br><br><a href=\"index.php?content=ManualSelling.php\">▶️ CONTINUE</a>";
            ?>
            <script>
                document.cookie = 'IfManualFurtherInformation=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                document.cookie = 'manualSellingItems=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                document.cookie = 'manualSellingQuantities=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                console.log('Cookies cleared');
            </script>
            <?php
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error: " . $e->getMessage();
        }
    } else {
        foreach ($errors as $error) {
            echo "Error: $error";
        }
    }

} else {























    echo "<div style=\"opacity: 0.5;\">Here you can directly save a manual sale for explorers (customers) outside our systeme where you already got the money.</div>";
    echo "<br><br>";



    echo "<form id=\"manualSellingForm\" onsubmit=\"return submitFormManualSelling()\" action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
        echo "<table>";
            echo "<tr>";
                echo "<td>";
                    $ifManualFurtherInformation = isset($_COOKIE['IfManualFurtherInformation']) ? $_COOKIE['IfManualFurtherInformation'] : '';
                    echo "<textarea id=\"IfManualFurtherInformation\" name=\"IfManualFurtherInformation\" rows=\"12\" style=\"width: 300px;\" placeholder=\"if you want to, you can insert further information about the explorer (customer) here, for example first name, last name, company name, street, house number, ZIP code, city, country, planet, VATID, email, further notes, ...\">" . htmlspecialchars($ifManualFurtherInformation) . "</textarea>";
                    echo "<br><br><br><div id=\"ShowSuggestionsForIfManualFurtherInformation\"></div>";
                    echo "</td>";
                echo "<td></td>";
                echo "<td>";
                    echo "<div id=\"ShowAddedProductsAnsServicesForManualSelling\"></div>";
                echo "</td>";    
                echo "<td></td>";
                echo "<td>";
                    echo "<strong><span id=\"TotalSellingPrice\">0.00</span>$ (+<span id=\"TotalShippingPrice\">0.00</span>$)</strong>";
                    echo "<br><br>";
                    echo "<input type=\"number\" id=\"OverwrittenPrice\" name=\"OverwrittenPrice\" placeholder=\"only if needed\" style=\"width: 200px;\">";
                    echo "<br><label for=\"OverwrittenPrice\">overwritten price</label>";
                    echo "<br><br><br>";
                    // hidden input field to store the action type (completely finished or just paid)
                    echo "<input type=\"hidden\" id=\"formActionType\" name=\"formActionType\" value=\"\">";
                    echo "<a href=\"javascript:void(0);\" class=\"mainbutton\" onclick=\"setFormAction('finished')\">↗️ SAVE AS FINISHED</a>";
                    echo "<br><br>";
                    echo "<a href=\"javascript:void(0);\" class=\"button\" onclick=\"setFormAction('paid')\">↗️ SAVE AS PAID</a>";
                echo "</td>";
            echo "</tr>";
        echo "</table>";
    echo "</form>";







    echo "<br><br>";

    $preselectedOption = "your_products_services"; // add preselected search option
    $preselectedViewing = "manual_selling"; // add preselected viewing

    include ("explore.php"); // include explore.php for exploring and searching



}
?>