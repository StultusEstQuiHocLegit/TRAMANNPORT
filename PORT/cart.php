<?php
include ("ExchangeRates.php"); // include ExchangeRates.php for recalculation of prices





if (isset($_GET['action']) && $_GET['action'] === 'buy') {

    // Get and sanitize inputs
    $deliveryType = isset($_GET['deliveryType']) ? htmlspecialchars($_GET['deliveryType']) : null;
    $wishedDeliveryTime = isset($_GET['wishedDeliveryTime']) ? htmlspecialchars($_GET['wishedDeliveryTime']) : null;

    // Retrieve all pending transactions for this user
    $stmt = $pdo->prepare("
        SELECT idpk, IdpkProductOrService, quantity, CommentsNotesSpecialRequests 
        FROM transactions 
        WHERE IdpkExplorer = ? AND state = 0 
        ORDER BY TimestampCreation DESC
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize the total amounts
    $TotalAmount = 0;
    $TotalTaxes = 0;
    $TotalNetPrice = 0;
    $ForTRAMANNPORT = 0;

    $cartProducts = [];
    $transactionIds = []; // To store IDs of the transactions being processed

    foreach ($transactions as $transaction) {
        $idpkTransaction = $transaction['idpk'];
        $idpkProductOrService = $transaction['IdpkProductOrService'];
        $quantity = $transaction['quantity'];

        // Fetch product details from ProductsAndServices table
        $productStmt = $pdo->prepare("
            SELECT SellingPriceProductOrServiceInDollars, SellingPricePackagingAndShippingInDollars, TaxesInPercent 
            FROM ProductsAndServices 
            WHERE idpk = ?
        ");
        $productStmt->execute([$idpkProductOrService]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $productPrice = $product['SellingPriceProductOrServiceInDollars'] * $quantity;
            $shippingPrice = $product['SellingPricePackagingAndShippingInDollars'] * $quantity;
            $amountInDollars = $productPrice + $shippingPrice;

            // Calculate TRAMANN PORT fees (3%)
            $forTRAMANNPORTInDollars = $amountInDollars * ($ContributionForTRAMANNPORT / 100);

            // Calculate taxes if applicable
            $taxesInDollars = 0;
            if ($userRole === 0) { // Apply taxes only for non-creator roles (explorers)
                $taxesInDollars = $amountInDollars * ($product['TaxesInPercent'] / 100);
            }

            // Calculate net price
            $netPrice = $amountInDollars - $taxesInDollars;

            // Update totals
            $TotalAmount += $amountInDollars + $taxesInDollars;
            $TotalTaxes += $taxesInDollars;
            $TotalNetPrice += $netPrice;
            $ForTRAMANNPORT += $forTRAMANNPORTInDollars;

            // Collect the transaction ID for updating later
            $transactionIds[] = $idpkTransaction;

            // Update the state of the transaction in the database
            $updateStmt = $pdo->prepare("
                UPDATE transactions 
                SET state = 1, AmountInDollars = ?, ForTRAMANNPORTInDollars = ?, TaxesInDollars = ? 
                WHERE idpk = ?
            ");
            $updateStmt->execute([$amountInDollars, $forTRAMANNPORTInDollars, $taxesInDollars, $idpkTransaction]);
        }
    }

    // Convert the wished delivery time to a timestamp (if provided)
    $wishedDeliveryTimestamp = $wishedDeliveryTime ? strtotime($wishedDeliveryTime) : null;

    if ($deliveryType !== null && $wishedDeliveryTimestamp !== null) {
        // Insert a new entry into the carts table
        $insertStmt = $pdo->prepare("
            INSERT INTO carts (TimestampCreation, IdpkExplorerOrCreator, manual, IfManualFurtherInformation, DeliveryType, WishedIdealDeliveryOrPickUpTime) 
            VALUES (?, ?, 0, 0, ?, ?)
        ");
        $timestampCreation = time(); // Current timestamp
        $insertStmt->execute([$timestampCreation, $user_id, $deliveryType, $wishedDeliveryTimestamp]);

        // Retrieve the last inserted cart ID
        $lastCartId = $pdo->lastInsertId();

        // Update transactions to associate them with the new cart ID
        $updateCartStmt = $pdo->prepare("
            UPDATE transactions 
            SET IdpkCart = ? 
            WHERE idpk IN (" . implode(',', array_fill(0, count($transactionIds), '?')) . ")
        ");
        $updateCartStmt->execute(array_merge([$lastCartId], $transactionIds));




        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // Fetch the inserted data for the email
        $cartDetails = [
            'TimestampCreation' => date('Y-m-d H:i:s', $timestampCreation),
            'IdpkExplorerOrCreator' => $user_id,
            'DeliveryType' => $deliveryType,
            'WishedIdealDeliveryOrPickUpTime' => date('Y-m-d H:i:s', $wishedDeliveryTimestamp),
        ];

        // Prepare email details
        $to = "hi@tramann-projects.com";
        $subject = "PORT: new order to be processed";
        $message = "New order has been added to the database:\n\n" . print_r($cartDetails, true);

        // Send email
        $headers = "From: hi@tramann-projects.com"; // Replace with your domain email
        if (mail($to, $subject, $message, $headers)) {
            // echo "Cart entry created successfully, and email sent with ID: " . $lastCartId;
        } else {
            // echo "Cart entry created successfully, but email failed to send.";
        }
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email
        // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// email




    } else {
        echo "Invalid delivery type or wished delivery time.";
    }








    echo "<h1>🏦 PAYMENT</h1>";

    echo "<div style=\"opacity: 0.5;\">Please use your phone or online banking and transfer the following total amount of</div>";
    echo "<br><br>";
    
    $exchangeRate = floatval($ExchangeRate['OneDollarIsEqualTo']);
    $currencyCode = htmlspecialchars($ExchangeRate['CurrencyCode']);
    $totalInOtherCurrency = ($TotalAmount + $ForTRAMANNPORT) * $exchangeRate;

    echo "<h3>" . number_format(($TotalAmount + $ForTRAMANNPORT) * 1.00, 2) . "$</h3>";

    if ($currencyCode !== "USD") {
        echo "<div style=\"opacity: 0.5;\"><h3>(OR " . number_format($totalInOtherCurrency * 1.01, 2) . " " . $currencyCode . ")</h3></div>";
    }

    // Display tax details if applicable
    if ($TotalTaxes > 0) {
        $taxesInOtherCurrency = $TotalTaxes * $exchangeRate;
        $netPriceInOtherCurrency = $TotalNetPrice * $exchangeRate;

        echo "<br><div style=\"opacity: 0.4;\">";
        echo "(thereof " . number_format($TotalTaxes, 2) . "$ taxes, total net price therefore: " . number_format($TotalNetPrice, 2) . "$)";
        if ($currencyCode !== "USD") {
            echo "<br><div style=\"opacity: 0.4;\">(thereof " . number_format($taxesInOtherCurrency, 2) . " " . $currencyCode . " taxes, total net price therefore: " . number_format($netPriceInOtherCurrency, 2) . " " . $currencyCode . ")</div>";
        }
        echo "</div>";
    }
    
    echo "<br>";
    echo "<div style=\"opacity: 0.5;\">from your bank account (IBAN: " . htmlspecialchars($user['IBAN']) . ") to the following IBAN</div>";
    echo "<br><br>";
    
    echo "<h3>DE95 1203 0000 1081 8677 47</h3>";

    echo "<br>";
    echo "<div style=\"opacity: 0.5;\">for further processing within the TRAMANN PORT payment system.</div>";

    echo "<br><br><br><br><br>";
    // echo "<a href=\"index.php\" class=\"mainbutton\">✔️ DONE</a>";
    if ($userRole === 0) { // explorer
        echo "<a href=\"index.php\" class=\"mainbutton\">✔️ DONE</a>";
    } else { // creator
        echo "<a href='index.php?content=explore.php&action=ShowCarts&idpk={$lastCartId}' title='CART {$lastCartId}' class=\"mainbutton\">✔️ DONE</a>";
    }

    echo "<br><br><br><br><br>";
    echo "<a href=\"#\" id=\"learnLink\" style=\"opacity: 0.4;\">LEARN HOW IT WORKS</a>";
    echo "<br><br>";
    echo "<div id='LearnHowItWorks' style='display: none;'>"; // initially hide the div
        echo "To ensure security in payment processing, we are hacking our way by using established banking infrastructure";
        echo " to send money into our TRAMANN PORT payment system and out again.";
        echo " This separation ensures that no external actors can manipulate our system.";
        echo " In the long term, we also want to create an even more user friendly interface for this purpose.";
        echo "<br><br>";
        echo "We will forward your orders to the corresponding creators immediately after we have received your payment,";
        echo " but we will hold the money for the next 30 days so that we can mediate in the event of complaints";
        echo " and also refund you if necessary (TRAMANN PORT trade assurance).";
        echo "<br><br>";
        echo "Either way, we got you covered, enabling secure trade.";
    echo "</div>";



    ?>
    <script>
        document.getElementById('learnLink').addEventListener('click', function(event) {
            event.preventDefault();  // Prevent the default link navigation
        
            // Toggle visibility of the div
            var learnDiv = document.getElementById('LearnHowItWorks');
            if (learnDiv.style.display === 'none') {
                learnDiv.style.display = 'block';  // Show the div
            } else {
                learnDiv.style.display = 'none';   // Hide the div if clicked again
            }
        
            // Change the opacity of the link
            var link = document.getElementById('learnLink');
            if (link.style.opacity === '0.5') {
                link.style.opacity = '1';  // Set opacity to 1 (no transparency)
            } else {
                link.style.opacity = '0.5';  // Revert opacity back to 0.5 (semi-transparent)
            }
        });
    </script>
    <?php



} else {
?>






























































<h1>🛒 CART</h1>

<?php
// Define utility functions
function truncateText($text, $maxLength) {
    return (strlen($text) > $maxLength) ? substr($text, 0, $maxLength) . '...' : $text;
}

// Function to format the shipping price
function formatShippingPrice($shippingPrice) {
    return (!empty($shippingPrice) && $shippingPrice != 0) ? "(+$shippingPrice\$)" : '';
}

$priceMultiplier = 1 + $ContributionForTRAMANNPORT / 100; // Calculate the multiplier

// Function to display a product row
function displayProductRow($product, $transaction = null, $user_id = null, $priceMultiplier = null, $userRole = null) {
    $truncatedName = truncateText($product['name'], 50);
    $truncatedDescription = truncateText($product['ShortDescription'], 100);
    $shippingPrice = formatShippingPrice($product['SellingPricePackagingAndShippingInDollars']);
    $commentsNotesSpecialRequests = isset($transaction['CommentsNotesSpecialRequests']) ? htmlspecialchars($transaction['CommentsNotesSpecialRequests']) : '';
    $quantity = isset($transaction['quantity']) ? htmlspecialchars($transaction['quantity']) : 1;
    $canManage = ($product['IdpkCreator'] == $user_id);

    // Start the table row
    echo "<tr>";

    // Display product image
    $uploadDir = "uploads/ProductPictures/" . htmlspecialchars($product['idpk']) . "_";
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imagePaths = [];

    for ($i = 0; $i < 5; $i++) {
        foreach ($validExtensions as $extension) {
            $filePath = "{$uploadDir}{$i}.{$extension}";
            if (file_exists($filePath)) {
                $imagePaths[] = $filePath;
                break;
            }
        }
    }



    if (isset($imagePaths[0]) && file_exists($imagePaths[0])) {
        echo "<td><img src=\"" . htmlspecialchars($imagePaths[0]) . "\" style=\"height:100px;\"></td>";
    } else {
        echo "<td></td>";
    }

    // Display product details with comment and quantity fields
    // echo "<td>$truncatedName ({$product['idpk']})<br><div style=\"opacity: 0.5;\">$truncatedDescription</div><br>";
    // echo "<input type=\"text\" id=\"CommentsNotesSpecialRequests\" name=\"CommentsNotesSpecialRequests\" value=\"$commentsNotesSpecialRequests\" placeholder=\"comments, notes, special requests\"></td>";
    // 
    // echo "<td>{$product['SellingPriceProductOrServiceInDollars']}$ $shippingPrice<br>";
    // echo "<input type=\"number\" id=\"quantity\" name=\"quantity\" value=\"$quantity\" placeholder=\"quantity\"></td>";

    echo "<td>";
    echo "<a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a><br><div style=\"opacity: 0.5;\">$truncatedDescription</div>";
    echo "<div style=\"position: relative; display: flex; align-items: flex-end;\">";
    // echo "<input type=\"text\" id=\"CommentsNotesSpecialRequests\" name=\"CommentsNotesSpecialRequests\" value=\"$commentsNotesSpecialRequests\" placeholder=\"comments, notes, special requests\" style=\"width: 90%;\" onchange=\"updateCartData({$product['idpk']}, 'CommentsNotesSpecialRequests', this.value)\">";
        // Check if AllowCommentsNotesSpecialRequests is set to 1 (yes)
        if ($product['AllowCommentsNotesSpecialRequests'] == 1) {
            // Only display the CommentsNotesSpecialRequests field if it's allowed
            echo "<input type=\"text\" id=\"CommentsNotesSpecialRequests\" name=\"CommentsNotesSpecialRequests\" value=\"$commentsNotesSpecialRequests\" placeholder=\"comments, notes, special requests\" style=\"width: 90%;\" onchange=\"updateCartData({$product['idpk']}, 'CommentsNotesSpecialRequests', this.value)\">";
        }
    echo "</div></td>";

    // Adjust priceMultiplier for explorers (userRole === 0)
    if ($userRole === 0) {
        $taxesMultiplier = 1 + ($product['TaxesInPercent'] / 100); // Calculate taxes multiplier
        $priceMultiplier *= $taxesMultiplier; // Apply taxes multiplier
    }
    
    echo "<td>";
        $productPrice = $product['SellingPriceProductOrServiceInDollars'] * $priceMultiplier; // Multiply by the factor
        $shippingPrice = $product['SellingPricePackagingAndShippingInDollars'] * $priceMultiplier; // Multiply by the factor
        
        // Calculate total price based on quantity
        $totalProductPrice = $productPrice * $quantity;
        $totalShippingPrice = $shippingPrice * $quantity;
        
        // Format the price for display
        $totalProductPriceFormatted = number_format($totalProductPrice, 2);
        $totalShippingPriceFormatted = number_format($totalShippingPrice, 2);
    // echo "{$product['SellingPriceProductOrServiceInDollars']}$ $shippingPrice";
    echo "<div id='totalProductPrice_{$product['idpk']}'>{$totalProductPriceFormatted}$</div> <div id='totalShippingPrice_{$product['idpk']}'>(+{$totalShippingPriceFormatted}$)</div>";    echo "<div style=\"position: relative; display: flex; align-items: flex-end;\">";
    echo "<input type=\"number\" id=\"quantity_{$product['idpk']}\" name=\"quantity\" value=\"$quantity\" placeholder=\"quantity\" style=\"width: 80px;\" data-product-id=\"{$product['idpk']}\" min=\"1\">";
    echo "</div></td>";

    // Links
    // echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>👁️ MORE</a></td>";
    echo "<td><a href=\"javascript:void(0);\" onclick=\"confirmRemoval({$product['idpk']}, '{$product['name']}')\" style='opacity: 0.5;'>❌ REMOVE</a></td>";
    
    // End the table row
    echo "</tr>";
    echo "<tr></tr><tr></tr>";
}

try {
    // Retrieve all pending transactions for this user
    $stmt = $pdo->prepare("SELECT IdpkProductOrService, quantity, CommentsNotesSpecialRequests FROM transactions WHERE IdpkExplorer = ? AND state = 0 ORDER BY TimestampCreation DESC");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize an array to store product details
    $cartProducts = [];

    foreach ($transactions as $transaction) {
        $idpkProductOrService = $transaction['IdpkProductOrService'];
        
        // Fetch product details from ProductsAndServices table
        $productStmt = $pdo->prepare("SELECT * FROM ProductsAndServices WHERE idpk = ?");
        $productStmt->execute([$idpkProductOrService]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Add the transaction data to the product array
            $product['transaction'] = $transaction;  // Store the transaction details with the product
            $cartProducts[] = $product;
        }
    }

    // Display cart products in a table
    if (!empty($cartProducts)) {
        // echo "<strong id=\"totalPrice\" style=\"font-size: 1.2em;\">0.00$ (+0.00$)</strong> <a href=\"index.php?content=cart.php&action=buy\" id=\"BuyButton\" class=\"mainbutton\" onclick=\"sendBuyRequest(event)\">🛒 BUY NOW</a>";
        echo "<div align='center'>";
            echo "<table>";
                echo "<tr>";
                    echo "<td style='font-size: 1.2em; text-align: left;'><strong id='totalPrice'>0.00$ (+0.00$)</strong><br><strong id='totalPriceInOtherCurrency' style='opacity: 0.5;'></strong></td>";
                    echo "<td style='text-align: right;'><a href='index.php?content=cart.php&action=buy' id='BuyButton' class='mainbutton' onclick='sendBuyRequest(event)'>🛒 BUY NOW</a></td>";
                echo "</tr>";
            echo "</table>";
        echo "</div>";
        echo "<br><br><br><br>";








        $isRestaurantFood = false;
        $deliveryOptions = [
            0 => 'standard', 
            1 => 'express', 
            2 => 'as soon as possible', 
            3 => 'pick up in store', 
            4 => 'best matching wished ideal delivery time'
        ];
        
        // Process cart products to determine if any are restaurant food
        foreach ($cartProducts as $product) {
            if ($product['type'] == 1) {
                // If we have at least one restaurant food item, we adjust the options
                $deliveryOptions = [
                    2 => 'as soon as possible', 
                    3 => 'pick up in store', 
                    4 => 'best matching wished ideal delivery time'
                ];
                $isRestaurantFood = true;
                break; // No need to check further, we already know there's a restaurant food product
            }
        }
        
        // If there is any restaurant food, adjust delivery options
        if ($isRestaurantFood) {
            $wishedTimeRequired = true; // Show the time field if DeliveryType is 4
        } else {
            // For non-restaurant food, show the time field if DeliveryType is 4
            $wishedTimeRequired = true; // Show the time field if DeliveryType is 4
        }
        
        // Display the dropdown for DeliveryType at the top of the page
        echo "<select name='deliveryType' id='deliveryType' style='width: 300px;'>";
        foreach ($deliveryOptions as $key => $value) {
            // Set '4' as the default selected option when there is restaurant food
            $selected = ($key == 4 && $isRestaurantFood) ? 'selected' : ''; 
            echo "<option value='$key' $selected>$value</option>";
        }
        echo "</select>";
        
        // Initially hide the wishedDeliveryTime input field, and show it later via JavaScript
        echo "<br><br><div id='wishedDeliveryTimeField' style='display: none;'>";
        // Get the current time
        $currentTime = new DateTime();
        $currentHour = (int)$currentTime->format('H');
        $currentMinute = (int)$currentTime->format('i');
            
        // Initialize default time
        $defaultTime = clone $currentTime;
            
        // Logic for setting the default time
        if ($currentHour >= 22) {
            // If it's after 22:00, set to 19:00 the next day
            $defaultTime->modify('+1 day')->setTime(19, 0);
        } elseif ($currentHour < 18) {
            // If it's before 18:00, set to 19:00 the same day
            $defaultTime->setTime(19, 0);
        } elseif ($currentHour >= 18 && $currentHour < 22) {
            // If it's between 18:00 and 22:00, add 60 minutes and round up to the next quarter hour
            $defaultTime->modify('+1 hour');
            $minute = (int)$defaultTime->format('i');
            $roundedMinute = ceil($minute / 15) * 15; // Round up to the nearest quarter hour
            if ($roundedMinute >= 60) {
                $defaultTime->modify('+1 hour')->setTime((int)$defaultTime->format('H'), 0);
            } else {
                $defaultTime->setTime((int)$defaultTime->format('H'), $roundedMinute);
            }
        }
        
        // Format the default time as 'Y-m-d\TH:i' for the datetime-local input field
        $defaultTimeFormatted = $defaultTime->format('Y-m-d\TH:i');
        // Output the input field with the calculated default value
        echo "<input type='datetime-local' id='wishedDeliveryTime' name='wishedDeliveryTime' style='width: 300px;' value='$defaultTimeFormatted'>";
        echo "</div>";












        echo "<br><br><br><br><br><br>";

        echo "<table style='width: 100%; text-align: left;'>";
        // echo "<tr><th></th><th></th><th></th><th></th><th></th></tr>";
        
        foreach ($cartProducts as $product) {
            // Display each product row
            displayProductRow($product, $product['transaction'], $user_id, $priceMultiplier, $userRole);
        }

        echo "</table>";
    } else {
        echo "Your cart is currently empty. You can find interesting products and services to add if you visit <a href=\"index.php?content=explore.php\">🔍 EXPLORE</a>.";
    }

} catch (PDOException $e) {
    echo "Error fetching cart items: " . $e->getMessage();
}
?>



































<script>
function confirmRemoval(productId, productName) {
    // Show confirmation popup
    const confirmation = confirm(`Are you sure you want to remove ${productName} (${productId}) from your cart?`);
    
    if (confirmation) {
        // If the user clicks "Yes", make the AJAX request to delete the product
        deleteProductFromCart(productId);
    }
}

function deleteProductFromCart(productId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "SaveDataCart.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const userId = getCookie("user_id");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            const response = xhr.responseText;

            if (response === "Product or service removed successfully.") {
                // Remove the product row from the DOM
                const productRow = document.querySelector(`#totalProductPrice_${productId}`).closest("tr");
                if (productRow) {
                    productRow.remove();
                }

                // Recalculate the total price for the cart
                calculateTotalPrice();
            } else {
                alert(response || "Error removing product.");
            }
        }
    };

    // Send the POST request to remove the product
    xhr.send(`productId=${productId}&userId=${userId}&action=remove`);
}

function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}

// Update the displayed price after changing the quantity
function updatePriceDisplay(productId, totalProductPrice, totalShippingPrice) {
    // Find the price elements by productId
    const totalProductPriceElement = document.querySelector(`#totalProductPrice_${productId}`);
    const totalShippingPriceElement = document.querySelector(`#totalShippingPrice_${productId}`);

    if (totalProductPriceElement && totalShippingPriceElement) {
        // Update the price elements with the new values
        totalProductPriceElement.innerHTML = `${totalProductPrice}$`;
        totalShippingPriceElement.innerHTML = `(+${totalShippingPrice}$)`;
    }
}

function calculateTotalPrice() {
    let totalProductPrice = 0;
    let totalShippingPrice = 0;

    // Iterate over all rows to calculate total prices
    document.querySelectorAll("tr").forEach((row) => {
        const productPriceElement = row.querySelector("[id^='totalProductPrice_']");
        const shippingPriceElement = row.querySelector("[id^='totalShippingPrice_']");

        if (productPriceElement && shippingPriceElement) {
            const productPrice = parseFloat(productPriceElement.innerHTML.replace("$", "")) || 0;
            const shippingPrice = parseFloat(shippingPriceElement.innerHTML.replace("(+", "").replace("$)", "")) || 0;

            totalProductPrice += productPrice;
            totalShippingPrice += shippingPrice;
        }
    });

    // Update the total price element
    const totalPriceElement = document.getElementById("totalPrice");
    if (totalPriceElement) {
        totalPriceElement.innerHTML = `${totalProductPrice.toFixed(2)}$ (+${totalShippingPrice.toFixed(2)}$)`;
    }

    // Update the total price in another currency (if needed)
    const totalPriceInOtherCurrencyElement = document.getElementById("totalPriceInOtherCurrency");
    if (totalPriceInOtherCurrencyElement) {
        // Fetch PHP variables
        const exchangeRate = parseFloat("<?php echo $ExchangeRateOneDollarIsEqualTo; ?>");
        const currencyCode = "<?php echo htmlspecialchars($ExchangeRateCurrencyCode); ?>";

        let totalProductPriceInOtherCurrency = totalProductPrice * exchangeRate;
        let totalShippingPriceInOtherCurrency = totalShippingPrice * exchangeRate;

        if (currencyCode !== "USD") {
            totalPriceInOtherCurrencyElement.innerHTML = `${(totalProductPriceInOtherCurrency * 1.01).toFixed(2)} ${currencyCode} (+${(totalShippingPriceInOtherCurrency * 1.01).toFixed(2)} ${currencyCode})`;
        } else {
            totalPriceInOtherCurrencyElement.innerHTML = `${(totalProductPrice * 1.01).toFixed(2)}$ (+${(totalShippingPrice * 1.01).toFixed(2)}$)`;
        }
    }
}

function updateCartData(productId, fieldName, value) {
    // Prevent quantities below 1
    if (fieldName === "quantity" && (isNaN(value) || value < 1)) {
        value = 1; // Correct invalid quantities to 1
        // Update the quantity input field to reflect the corrected value
        const quantityInput = document.querySelector(`#quantity_${productId}`);
        if (quantityInput) {
            quantityInput.value = value;
        }
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "SaveDataCart.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const userId = getCookie("user_id");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            const response = xhr.responseText;

            try {
                const data = JSON.parse(response);

                if (data.success) {
                    // Update the displayed prices for the product
                    updatePriceDisplay(productId, data.totalProductPrice, data.totalShippingPrice);

                    // Recalculate the total price for the cart
                    calculateTotalPrice();
                } else {
                    alert(data.message || "Error updating cart.");
                }
            } catch (e) {
                console.error("Error parsing server response:", e);
            }
        }
    };

    // Send the POST request to update the cart data
    xhr.send(`productId=${productId}&userId=${userId}&fieldName=${fieldName}&fieldValue=${value}&action=update`);
}

// Utility function to retrieve a cookie value by name
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}





function sendBuyRequest(event) {
    const deliveryType = document.getElementById('deliveryType').value;
    const wishedDeliveryTime = document.getElementById('wishedDeliveryTime').value;
    const buyButton = document.getElementById('BuyButton');

    // Check if wishedDeliveryTime is in the past
    const now = new Date(); // Current date and time
    const selectedDateTime = new Date(wishedDeliveryTime);

    if (selectedDateTime < now) {
        alert("We may be fast, but not this fast (yet  ; ) ). The selected delivery time is in the past, please choose a valid date and time.");
        event.preventDefault(); // Stop the default link behavior (page reload)
        return; // Stop further execution
    }

    // If the time is valid, set the URL
    buyButton.href = `index.php?content=cart.php&action=buy&deliveryType=${encodeURIComponent(deliveryType)}&wishedDeliveryTime=${encodeURIComponent(wishedDeliveryTime)}`;
}






document.addEventListener('DOMContentLoaded', function() {
    const deliveryTypeSelect = document.getElementById('deliveryType');
    const wishedDeliveryTimeField = document.getElementById('wishedDeliveryTimeField');
    
    // Function to handle the visibility of the wishedDeliveryTime input field
    function toggleWishedDeliveryTimeField() {
        const selectedDeliveryType = deliveryTypeSelect.value;
        
        // Show the datetime input if DeliveryType is 3 (pick up in store) or 4 (best matching wished ideal delivery time)
        if (selectedDeliveryType == '3' || selectedDeliveryType == '4') {
            wishedDeliveryTimeField.style.display = 'block';
        } else {
            wishedDeliveryTimeField.style.display = 'none';
        }
    }

    // Attach the event listener to the quantity input
    document.querySelectorAll('input[name="quantity"]').forEach(function(input) {
        input.addEventListener('change', function(event) {
            // Get the product ID from the data attribute
            const productId = event.target.getAttribute('data-product-id');
            let value = event.target.value;

            // Ensure the quantity is at least 1
            if (isNaN(value) || value < 1) {
                value = 1;
                event.target.value = value; // Correct the input value immediately
            }

            // Call the updateCartData function
            updateCartData(productId, 'quantity', value);
        });
    });

    // Add event listener to handle changes in deliveryType
    deliveryTypeSelect.addEventListener('change', toggleWishedDeliveryTimeField);

    // Call the function once to ensure correct initial state
    toggleWishedDeliveryTimeField();

    calculateTotalPrice();
});
</script>





<?php
}
?>