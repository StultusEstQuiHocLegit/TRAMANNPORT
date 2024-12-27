<?php
include ("ExchangeRates.php"); // include ExchangeRates.php for recalculation of prices





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
    setCookie('manualSellingItems', JSON.stringify(addedItems), 3650);
    setCookie('manualSellingQuantities', JSON.stringify(quantities), 3650);

    // Refresh the display
    displayAddedItems();
}
























// PHP variables passed via script tag
const exchangeRateCurrencyCode = "<?php echo $ExchangeRateCurrencyCode; ?>";
const exchangeRate = parseFloat("<?php echo $ExchangeRateOneDollarIsEqualTo; ?>");

function updateOtherCurrency(changedField) {
    if (exchangeRateCurrencyCode === "USD") {
        return; // Do nothing if the currency is USD
    }

    const overwrittenPriceUSD = document.getElementById('OverwrittenPrice');
    const overwrittenPriceOther = document.getElementById('OverwrittenPriceInOtherCurrency');

    if (changedField === 'USD') {
        // Update the other currency when USD field is changed
        const usdValue = parseFloat(overwrittenPriceUSD.value);
        if (!isNaN(usdValue)) {
            overwrittenPriceOther.value = (usdValue * exchangeRate).toFixed(2);
        } else {
            overwrittenPriceOther.value = ''; // Clear the field if input is invalid
        }
    } else if (changedField === exchangeRateCurrencyCode) {
        // Update the USD field when the other currency field is changed
        const otherCurrencyValue = parseFloat(overwrittenPriceOther.value);
        if (!isNaN(otherCurrencyValue)) {
            overwrittenPriceUSD.value = (otherCurrencyValue / exchangeRate).toFixed(2);
        } else {
            overwrittenPriceUSD.value = ''; // Clear the field if input is invalid
        }
    }
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
    setCookie('manualSellingItems', JSON.stringify(addedItems), 3650);
    setCookie('manualSellingQuantities', JSON.stringify(quantities), 3650);

    // Update the display
    displayAddedItems();
}

// Function to save the further information to the cookie
function saveManualFurtherInformation() {
    const furtherInfo = document.getElementById('IfManualFurtherInformation').value;
    setCookie('IfManualFurtherInformation', furtherInfo, 3650); // Save for 10 years
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
    setCookie('manualSellingQuantities', JSON.stringify(quantities), 3650);

    // Optionally refresh the display
    displayAddedItems();
}

function calculateTotals() {
    const addedItems = getCookie('manualSellingItems');
    const itemList = addedItems ? JSON.parse(addedItems) : [];

    const quantities = getCookie('manualSellingQuantities');
    const quantityList = quantities ? JSON.parse(quantities) : [];

    const taxesState = getCookie('manualSellingTaxes') === 'added';

    let totalSellingPrice = 0;
    let totalShippingPrice = 0;

    // Loop through items and calculate totals
    itemList.forEach(item => {
        const quantity = quantityList.find(q => q.idpk === item.idpk)?.quantity || 1;

        // Base prices
        let sellingPrice = item.SellingPriceProductOrServiceInDollars || 0;
        let shippingPrice = item.SellingPricePackagingAndShippingInDollars || 0;

        // Apply taxes if toggled
        if (taxesState) {
            const taxesInPercent = item.TaxesInPercent || 0; // Default to 0% if not defined
            const taxMultiplier = 1 + taxesInPercent / 100;
            sellingPrice *= taxMultiplier;
            shippingPrice *= taxMultiplier;
        }

        // Accumulate prices
        totalSellingPrice += sellingPrice * quantity;
        totalShippingPrice += shippingPrice * quantity;
    });

    // Update totals on the page
    document.getElementById('TotalSellingPrice').textContent = totalSellingPrice.toFixed(2);
    document.getElementById('TotalShippingPrice').textContent = totalShippingPrice.toFixed(2);

    // PHP variables passed via script tag
    const exchangeRateCurrencyCode = "<?php echo $ExchangeRateCurrencyCode; ?>";
    const exchangeRate = parseFloat("<?php echo $ExchangeRateOneDollarIsEqualTo; ?>");

    // If the currency code is not USD, calculate the equivalent values
    if (exchangeRateCurrencyCode !== "USD") {
        const totalSellingPriceInOtherCurrency = totalSellingPrice * exchangeRate;
        const totalShippingPriceInOtherCurrency = totalShippingPrice * exchangeRate;

        // Update the display for the selling and shipping prices in the other currency
        document.getElementById('TotalSellingPriceInOtherCurrency').textContent = totalSellingPriceInOtherCurrency.toFixed(2);
        document.getElementById('TotalShippingPriceInOtherCurrency').textContent = totalShippingPriceInOtherCurrency.toFixed(2);
    }
}

// Update totals whenever the display is refreshed
function displayAddedItems() {
    const addedItems = getCookie('manualSellingItems');
    const itemList = addedItems ? JSON.parse(addedItems) : [];

    const quantities = getCookie('manualSellingQuantities');
    const quantityList = quantities ? JSON.parse(quantities) : [];

    const displayDiv = document.getElementById('ShowAddedProductsAnsServicesForManualSelling');
    displayDiv.innerHTML = ''; // Clear the div content

    // Check if the taxes are enabled
    const taxesEnabled = getCookie('manualSellingTaxes') === 'added';

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

            // Calculate prices based on taxes
            const taxMultiplier = 1 + (item.TaxesInPercent / 100);
            const baseProductPrice = item.SellingPriceProductOrServiceInDollars || 0;
            const baseShippingPrice = item.SellingPricePackagingAndShippingInDollars || 0;

            const productPrice = taxesEnabled
                ? `${(baseProductPrice * taxMultiplier).toFixed(2)}$`
                : `${baseProductPrice}$`;
            const shippingPrice = taxesEnabled
                ? `(+${(baseShippingPrice * taxMultiplier).toFixed(2)}$)`
                : `(+${baseShippingPrice}$)`;

            // Construct the display string
            const priceDisplay = (baseProductPrice > 0 || baseShippingPrice > 0)
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














function toggleTaxes(event) {
    event.preventDefault(); // Prevent the default action of the link

    // Retrieve the current state of taxes from the cookie
    let taxesState = getCookie('manualSellingTaxes');
    taxesState = taxesState === 'added' ? 'removed' : 'added'; // Toggle the state

    // Update the cookie with the new state
    setCookie('manualSellingTaxes', taxesState, 3650); // Save for 10 years

    // Update the link text based on the new state
    const link = event.target;
    if (taxesState === 'added') {
        link.textContent = '❌ REMOVE TAXES';
    } else {
        link.textContent = '➕ ADD TAXES';
    }

    // Recalculate totals to reflect taxes
    calculateTotals();

    displayAddedItems();
}













// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize functionality on page load
    displayAddedItems();
    calculateTotals();

    const furtherInfoTextarea = document.getElementById('IfManualFurtherInformation');
    const suggestionsDiv = document.getElementById('ShowSuggestionsForIfManualFurtherInformation');

    const taxesState = getCookie('manualSellingTaxes');
    const taxesLink = document.querySelector('a[onclick="toggleTaxes(event)"]');

    if (taxesState === 'added' && taxesLink) {
        taxesLink.textContent = '❌ REMOVE TAXES';
    }

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
    $taxesEnabled = isset($_COOKIE['manualSellingTaxes']) && $_COOKIE['manualSellingTaxes'] === 'added';

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

            // Fetch original prices and taxes for all items
            $totalOriginalPrice = 0;
            $products = [];
            foreach ($manualSellingItems as $item) {
                $stmt = $pdo->prepare("
                    SELECT SellingPriceProductOrServiceInDollars, SellingPricePackagingAndShippingInDollars, TaxesInPercent, state
                    FROM ProductsAndServices
                    WHERE idpk = :idpk AND IdpkCreator = :user_id
                ");
                $stmt->execute([':idpk' => $item['idpk'], ':user_id' => $user_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product || $product['state'] != 1) {
                    throw new Exception("Product with ID {$item['idpk']} is not active or does not exist.");
                }

                $product['quantity'] = array_column($manualSellingQuantities, 'quantity', 'idpk')[$item['idpk']] ?? 1;
                $product['idpk'] = $item['idpk'];

                $products[] = $product;
                $totalOriginalPrice += ($product['SellingPriceProductOrServiceInDollars'] + $product['SellingPricePackagingAndShippingInDollars']) * $product['quantity'];
            }

            // if want to (if overwritten price was entered) calculate: take the whole amount paid over all items
            // (including taxes if necessary) (TotalSellingPrice + TotalShippingPrice) and then calculate for every item the relative proportion the item represents,
            // separated into the proportion of price (selling price + shipping price combined, multiplied witht he quantity) to the overall total
            // and the proportion of the taxes (if necessary, also multiplied withthe quantity of course) to the overall total
            // and then I want to take these proportions (sum over all should be = 1) and multiply them with the new, overwritten price
            // and insert them into the databse accordinlgy into the fields  amount and taxes,
            // like in the other, normal case without the additional overwritting of the price

            // Check for overwritten price and distribute proportionally
            $overwrittenPrice = $_POST['OverwrittenPrice'] ?? null;
            $totalNetPriceSum = 0;
            $totalGrossPriceSum = 0;

            // Calculate the total net price (including quantities) for all products
            foreach ($products as &$product) {
                $quantity = $product['quantity'];
                $netPrice = $product['SellingPriceProductOrServiceInDollars'] + $product['SellingPricePackagingAndShippingInDollars'];
                $totalNetPriceSum += $netPrice * $quantity;

                // Apply taxes if enabled
                $taxMultiplier = $taxesEnabled ? 1 + ($product['TaxesInPercent'] / 100) : 1;

                $grossPrice = $netPrice * $taxMultiplier;
                $totalGrossPriceSum += $grossPrice * $quantity;
            }

            foreach ($products as &$product) {
                $sellingPrice = $product['SellingPriceProductOrServiceInDollars'] ?? 0;
                $shippingPrice = $product['SellingPricePackagingAndShippingInDollars'] ?? 0;
                $totalPricePerUnit = $sellingPrice + $shippingPrice;
                $totalPrice = $totalPricePerUnit * $product['quantity']; // Calculate total price for the quantity

                // Apply taxes if enabled
                $taxMultiplier = $taxesEnabled ? 1 + ($product['TaxesInPercent'] / 100) : 1;
                $taxAmount = $taxesEnabled ? ($totalPrice * $product['TaxesInPercent'] / 100) : 0;

                // Distribute overwritten price
                if ($overwrittenPrice) {
                    $quantity = $product['quantity'];
                    $netPrice = $product['SellingPriceProductOrServiceInDollars'] + $product['SellingPricePackagingAndShippingInDollars'];
                    
                    // Proportional ratio based on net price and quantity
                    $priceRatio = ($netPrice * $quantity) / $totalGrossPriceSum;
                    
                    // Calculate proportional price from the overwritten price
                    $proportionalPriceTotal = $priceRatio * $overwrittenPrice; // Total price for this product
                    $proportionalPricePerUnit = $proportionalPriceTotal / $quantity; // Per-unit price
                    
                    if ($taxesEnabled) {
                        // Adjust for taxes if enabled
                        $taxMultiplier = 1 + ($product['TaxesInPercent'] / 100);
                        $grossPrice = $netPrice * $taxMultiplier;
                        $taxAmountPerUnit = $grossPrice - $netPrice;

                        // Proportional ratio of taxes based on gross price and quantity
                        $taxRatio = ($taxAmountPerUnit * $quantity) / $totalGrossPriceSum;

                        // Calculate proportional taxes from the overwritten price
                        $proportionalTaxTotal = $taxRatio * $overwrittenPrice; // Total taxes for this product
                        $proportionalTaxPerUnit = $proportionalTaxTotal / $quantity; // Per-unit taxes
                        
                        // Total for this product
                        $totalPrice = $proportionalPricePerUnit * $quantity;
                        $taxAmount = $proportionalTaxPerUnit * $quantity;
                    } else {
                        // No taxes, use proportional price as-is
                        $totalPrice = $proportionalPricePerUnit * $quantity;
                        $taxAmount = 0;
                    }
                }

                // Insert into `transactions`
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (TimestampCreation, IdpkExplorer, IdpkProductOrService, IdpkCart, quantity, AmountInDollars, ForTRAMANNPORTInDollars, TaxesInDollars, state)
                    VALUES (:timestampCreation, 0, :idpk, :cartId, :quantity, :amount, 0, :taxes, :state)
                ");
                $stmt->execute([
                    ':timestampCreation' => $timestampCreation,
                    ':idpk' => $product['idpk'],
                    ':cartId' => $cartId,
                    ':quantity' => $product['quantity'],
                    ':amount' => $totalPrice,
                    ':taxes' => $taxAmount,
                    ':state' => $formActionType === 'paid' ? 3 : 9,
                ]);

                // **Conditional Inventory Reduction**
                if ($formActionType === 'finished') {
                    $stmt = $pdo->prepare("
                        UPDATE ProductsAndServices
                        SET InventoryAvailable = InventoryAvailable - :quantity
                        WHERE idpk = :idpk AND ManageInventory = 1
                    ");
                    $stmt->execute([':quantity' => $product['quantity'], ':idpk' => $product['idpk']]);
                }
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
                    if ($ExchangeRateCurrencyCode !== "USD") {
                        echo "<br><strong id='totalPriceInOtherCurrency' style='opacity: 0.5;'><span id=\"TotalSellingPriceInOtherCurrency\">0.00</span> (+<span id=\"TotalShippingPriceInOtherCurrency\">0.00</span>) (in $ExchangeRateCurrencyCode)</strong>";
                    }
                    echo "<br><br>";
                    echo "<input type=\"number\" id=\"OverwrittenPrice\" name=\"OverwrittenPrice\" placeholder=\"only if needed\" style=\"width: 200px;\" oninput=\"updateOtherCurrency('USD')\">";
                    echo "<br><label for=\"OverwrittenPrice\">overwritten price (in USD)</label>";
                    if ($ExchangeRateCurrencyCode !== "USD") {
                        echo "<br><br>";
                        echo "<input type=\"number\" id=\"OverwrittenPriceInOtherCurrency\" name=\"OverwrittenPriceInOtherCurrency\" placeholder=\"only if needed\" style=\"width: 200px; opacity: 0.3;\" oninput=\"updateOtherCurrency('$ExchangeRateCurrencyCode')\">";
                        echo "<br><label for=\"OverwrittenPriceInOtherCurrency\" style=\"opacity: 0.3;\">overwritten price (in $ExchangeRateCurrencyCode)</label>";
                    }
                    echo "<br><br>";
                    echo "<a href=\"javascript:void(0);\" onclick=\"toggleTaxes(event)\">➕ ADD TAXES</a>";
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

