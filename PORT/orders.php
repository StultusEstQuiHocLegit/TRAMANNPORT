<?php
echo "<h1>📝 ORDERS</h1>";

try {
    // Database connection assumed as $pdo
    if (!isset($pdo)) {
        throw new Exception("Database connection is not established.");
    }

    // Get the user ID from the cookie
    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Handle AJAX request for cart details
    if (isset($_GET['cart_id'])) {
        $cartId = (int)$_GET['cart_id'];
    
        try {
            // Fetch the products in the specified cart
            $stmt = $pdo->prepare("
                SELECT 
                    p.idpk AS ProductId,
                    p.name AS ProductName,
                    t.quantity AS Quantity,
                    t.AmountInDollars AS Price,
                    CONCAT('uploads/ProductPictures/', p.idpk, '_0.jpg') AS Image
                FROM transactions t
                INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
                WHERE t.IdpkCart = :cart_id
            ");
            $stmt->execute(['cart_id' => $cartId]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // If no products found, return an empty array
            if (!$products) {
                $products = [];
            }
    
            // Return a clean JSON response
            header('Content-Type: application/json');
            echo json_encode(['products' => $products]);
        } catch (Exception $e) {
            // Handle errors gracefully and return a JSON error
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Error fetching cart details.']);
        }
        exit;
    }

    // Fetch main page with cart summaries if no cart_id is provided
    $stmt = $pdo->prepare("
        SELECT 
            c.idpk AS CartNumber,
            c.TimestampCreation AS CartTimestamp,
            c.DeliveryType AS deliveryType,
            c.WishedIdealDeliveryOrPickUpTime AS wishedIdealDeliveryOrPickUpTime,
            SUM(COALESCE(t.AmountInDollars, 0)) AS TotalAmount,
            e.CompanyName AS CompanyName,
            e.FirstName AS BuyerFirstName,
            e.LastName AS BuyerLastName
        FROM carts c
        INNER JOIN transactions t ON t.IdpkCart = c.idpk
        INNER JOIN ExplorersAndCreators e ON c.IdpkExplorerOrCreator = e.idpk
        INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
        WHERE p.IdpkCreator = :user_id AND t.state >= 3
        GROUP BY c.TimestampCreation, c.idpk, e.CompanyName, e.FirstName, e.LastName
        ORDER BY c.TimestampCreation DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($carts)) {
        echo "There are no previous orders available yet.";
    } else {
        echo "<table>";
        // echo "<thead>";
        // echo "<tr>";
        // echo "<th>date and time</th>";
        // echo "<th>idpk</th>";
        // echo "<th>total amount</th>";
        // echo "<th>explorer (or creator)</th>";
        // echo "<th></th>";
        // echo "</tr>";
        // echo "</thead>";
        echo "<tbody>";
        // echo "<tr></tr><tr></tr>";

        foreach ($carts as $cart) {
            $formattedAmount = number_format($cart['TotalAmount'], 2);
            // Define a mapping for delivery type values
            $deliveryTypeMapping = [
                0 => 'standard',
                1 => 'express',
                2 => 'as soon as possible',
                3 => 'pick up in store',
                4 => 'best matching wished ideal delivery time'
            ];
            
            // Retrieve and format the delivery type from the database
            $deliveryType = isset($cart['deliveryType']) ? (int)$cart['deliveryType'] : 0;
            $formattedDeliveryType = $deliveryTypeMapping[$deliveryType] ?? 'unknown'; // Fallback to 'unknown' if the value is not in the mapping
            $cartTimestamp = date('Y-m-d H:i:s', $cart['CartTimestamp']);
            $cartWishedIdealDeliveryOrPickUpTime = date('Y-m-d H:i:s', $cart['wishedIdealDeliveryOrPickUpTime']);
            $buyerName = !empty($cart['CompanyName']) 
                ? "{$cart['CompanyName']} ({$cart['CartNumber']})" 
                : "{$cart['BuyerFirstName']} {$cart['BuyerLastName']} ({$cart['CartNumber']})";




            
            
            // Fetch products along with customer details for the current cart
            $stmt = $pdo->prepare("
            SELECT 
                p.idpk AS ProductId,
                p.name AS ProductName,
                t.idpk AS TransactionId,
                t.quantity AS Quantity,
                t.AmountInDollars AS TotalPrice,
                t.CommentsNotesSpecialRequests AS commentsNotesSpecialRequests,
                t.state AS TransactionState,
                p.state AS ProductState,
                CONCAT('uploads/ProductPictures/', p.idpk, '_0.jpg') AS Image,
                -- Fetch customer information
                ec.idpk AS BuyerId,
                ec.CompanyName,
                ec.FirstName,
                ec.LastName,
                c.IdpkExplorerOrCreator,
                ec.ExplorerOrCreator,
                -- Fetch address details
                ec.country,
                ec.city,
                ec.ZIPCode,
                ec.street,
                ec.HouseNumber
            FROM transactions t
            INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
            INNER JOIN carts c ON t.IdpkCart = c.idpk
            INNER JOIN ExplorersAndCreators ec ON c.IdpkExplorerOrCreator = ec.idpk
            WHERE t.IdpkCart = :cart_id
            ");
            $stmt->execute(['cart_id' => $cart['CartNumber']]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
            // Check if all transactions are in states other than 3 (working) and 4 (shipping)
            $allOtherStates = true; // Flag to track if any transaction is in state 3 or 4
                    
            foreach ($products as $product) {
            if (in_array($product['TransactionState'], [3, 4])) {
                $allOtherStates = false; // Set flag to false if state is 3 or 4
                break;
            }
            }
            
            // Now pass this flag to the frontend
            $cartOpacityClass = $allOtherStates ? "0.2" : "1"; // If all are other states, reduce opacity

            






            // Add a horizontal line for separation
            echo "<tr><td colspan='8' style='padding: 0;'><hr style='border: 1px solid #505050; margin: 0; width: 100%;'></td></tr>";
        
            // echo "<tr style='font-weight: bold;'>";
            // Add the dynamic opacity class based on the flag
            // echo "<tr class='cart-details cart-{$cart['CartNumber']}' style='opacity: {$cartOpacityClass}; font-weight: bold;'>";
            // echo "<tr class='cart-details cart-{$cart['CartNumber']}' style='opacity: {$cartOpacityClass}; font-weight: bold;' data-cart-number='{$cart['CartNumber']}'>";
            echo "<tr id='cart-details cart-{$cart['CartNumber']}' style='opacity: {$cartOpacityClass}; font-weight: bold;'>";
            echo "<td>cart {$cart['CartNumber']} from</td>";
            echo "<td></td>";
            echo "<td>{$cartTimestamp}</td>";
            echo "<td></td>";
            echo "<td>{$formattedAmount}$</td>";
            if ($deliveryType == 3 || $deliveryType == 4) {
                echo "<td>{$formattedDeliveryType} ({$cartWishedIdealDeliveryOrPickUpTime})</td>";
            } else {
                echo "<td>{$formattedDeliveryType}</td>";
            }            
            echo "<td></td>";
            echo "<td><a href='#' onclick='ShowCart(event, {$cart['CartNumber']}, this)' data-state='more'>👁️ MORE</a></td>";
            echo "</tr>";









            // Determine the customer display name
            $customerDisplayName = ($product['ExplorerOrCreator'] == 1) 
                ? $product['CompanyName'] 
                : $product['FirstName'] . ' ' . $product['LastName'];
            // Combine the address details
            $customerAddress = "{$product['country']}, {$product['city']}, {$product['ZIPCode']}, {$product['street']} {$product['HouseNumber']}";
            echo "<tr id='cart-buyer-details cart-{$cart['CartNumber']}' style='opacity: {$cartOpacityClass}; font-weight: bold;'>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td colspan='5'>" . $customerDisplayName . " (" . $product['BuyerId'] . "), " . $customerAddress . "</td>";
            // echo "<td></td>";
            // echo "<td></td>";
            // echo "<td colspan='4'>{$customerAddress}</td>";          
            // echo "<td></td>";
            echo "<td></td>";
            echo "</tr>";


        

        






            foreach ($products as $product) {
                $productName = htmlspecialchars($product['ProductName']);
                $truncatedName = strlen($productName) > 50 ? substr($productName, 0, 47) . "..." : $productName;
                $commentsNotes = !empty($product['commentsNotesSpecialRequests']) ? " ({$product['commentsNotesSpecialRequests']})" : "";
                $isActive = $product['ProductState'] == 1;
                // Define a mapping for product state values
                $transactionStateMapping = [
                    0 => 'collecting',
                    1 => 'ordered',
                    2 => 'paid',
                    3 => 'orders transmitted to creators',
                    4 => 'creators producing or selecting',
                    5 => 'creators shipping',
                    6 => 'in customs',
                    7 => 'at distribution center',
                    8 => 'arriving',
                    9 => 'finished'
                ];
                
                // Retrieve and translate the product state
                $TransactionState = isset($product['TransactionState']) ? (int)$product['TransactionState'] : 0;
                $translatedTransactionState = $transactionStateMapping[$TransactionState] ?? 'unknown'; // Fallback to 'unknown'
                
                // set the opacity to 0.6 if the product isn't active anymore
                $opacity = $isActive ? "1" : "0.6";
                // Set opacity to 0.2 if the state is not 3 or 4
                $opacity = ($TransactionState == 3 || $TransactionState == 4) ? "1" : "0.2";
        
                // Assign a class that ties the row to the cart
                echo "</tr></tr><tr id='cart-products-{$product['TransactionId']}' class='cart-products cart-products-{$cart['CartNumber']}' style='opacity: {$opacity}; display: none;'>";
                echo "<td></td>";
                echo "<td>{$product['TransactionId']}</td>";
                echo "<td title='{$productName} ({$product['ProductId']})'>{$truncatedName} ({$product['ProductId']}){$commentsNotes}</td>";
                echo "<td style='font-weight: bold; font-size: 1.5rem;'>{$product['Quantity']}x </td>";
                echo "<td>{$product['TotalPrice']}$</td>";
                echo "<td title='(0 = collecting, 1 = ordered, 2 = paid, 3 = orders transmitted to creators, 4 = creators producing or selecting, 5 = creators shipping, 6 = in customs, 7 = at distribution center, 8 = arriving, 9 = finished)'>{$translatedTransactionState}</td>";
                
                echo "<td>";
                    // Show links based on the transaction state
                    if ($TransactionState == 3) {
                        echo "<a href='javascript:void(0);' id='4-link-{$product['TransactionId']}' class='mainbutton' onclick='updateTransactionState(event, {$product['TransactionId']}, \"4\")'>🛠️ WORKING</a>";
                        echo "<a href='javascript:void(0);' id='5-link-{$product['TransactionId']}' class='mainbutton' style='display:none;' onclick='updateTransactionState(event, {$product['TransactionId']}, \"5\")'>🚚 SHIPPING</a>";
                        echo "<br><a href='javascript:void(0);' id='3-link-{$product['TransactionId']}' style='opacity: 0.4; display:none;' onclick='updateTransactionState(event, {$product['TransactionId']}, \"3\")'>⬅️ BACK</a>";
                    } elseif ($TransactionState == 4) {
                        echo "<a href='javascript:void(0);' id='4-link-{$product['TransactionId']}' class='mainbutton' style='display:none;' onclick='updateTransactionState(event, {$product['TransactionId']}, \"4\")'>🛠️ WORKING</a>";
                        echo "<a href='javascript:void(0);' id='5-link-{$product['TransactionId']}' class='mainbutton' onclick='updateTransactionState(event, {$product['TransactionId']}, \"5\")' >🚚 SHIPPING</a>";
                        echo "<br><a href='javascript:void(0);' id='3-link-{$product['TransactionId']}' style='opacity: 0.4;' onclick='updateTransactionState(event, {$product['TransactionId']}, \"3\")'>⬅️ BACK</a>";
                    }
                echo "</td>";

                echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['ProductId']}'>👁️ MORE</a></td>";
        
                echo "</tr>";
            }
            echo "<tr></tr><tr></tr><tr></tr><tr></tr><tr></tr>";
        }
        
        
    
        echo "</tbody>";
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage()) . "";
}
?>




































<script>
    function updateTransactionState(event, TransactionId, action) {
        event.preventDefault(); // Prevent the link from navigating

        // Define the state values based on the action
        let newState;
        if (action === '4') {
            newState = 4; // 'working' maps to 4
        } else if (action === '3') {
            newState = 3; // 'back' maps to 3
        } else if (action === '5') {
            newState = 5; // 'shipping' maps to 5
        }

        // Send an AJAX request to update the transaction state
        fetch('SaveDataOrders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                TransactionId: TransactionId,
                state: newState
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data.message);

                // Pass the updated state value to the function
                toggleLinksState(TransactionId, newState); // Use newState here
                updateRowOpacity(TransactionId, newState); // Update opacity dynamically
            } else {
                console.error(data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function toggleLinksState(TransactionId, state) {    
        // Show the relevant link based on the state
        if (state === 4) {  // Check against numeric state (since newState is an integer)
            document.getElementById('4-link-' + TransactionId).style.display = 'none';
            document.getElementById('5-link-' + TransactionId).style.display = 'inline-block';
            document.getElementById('3-link-' + TransactionId).style.display = 'inline-block';
        } else if (state === 3) {
            document.getElementById('4-link-' + TransactionId).style.display = 'inline-block';
            document.getElementById('5-link-' + TransactionId).style.display = 'none';
            document.getElementById('3-link-' + TransactionId).style.display = 'none';
        } else {
            document.getElementById('4-link-' + TransactionId).style.display = 'none';
            document.getElementById('5-link-' + TransactionId).style.display = 'none';
            document.getElementById('3-link-' + TransactionId).style.display = 'none';
        }
    }

    function updateRowOpacity(TransactionId, state) {
        // Adjust opacity of the product row
        const productRow = document.getElementById('cart-products-' + TransactionId); // Use the correct ID format
        
        if (state !== 3 && state !== 4) {
            productRow.style.opacity = '0.2'; // Dim the row if state is not 3 or 4
        } else {
            productRow.style.opacity = '1'; // Reset opacity for valid states
        }
    
        // Adjust opacity of the cart if all products in the cart are inactive
        adjustCartOpacity(TransactionId);
    }
    
    function adjustCartOpacity(TransactionId) {
        // Determine the cart number from the product row's class
        const productRow = document.getElementById('cart-products-' + TransactionId);
        const cartClass = Array.from(productRow.classList).find(cls => cls.startsWith('cart-products-'));
        const cartNumber = cartClass.split('-').pop(); // Extract cart number from the class
    
        // Select all product rows in the same cart
        const cartProductRows = document.querySelectorAll('.cart-products-' + cartNumber);
        let allInactive = true;
    
        cartProductRows.forEach(row => {
            const rowOpacity = row.style.opacity || '1'; // Check the opacity (default to 1 if not set)
            if (rowOpacity === '1') {
                allInactive = false; // If any row is active, the cart should remain fully visible
            }
        });
    
        // Adjust opacity of the cart row
        const cartRow = document.getElementById('cart-details cart-' + cartNumber);
        const cartBuyerRow = document.getElementById('cart-buyer-details cart-' + cartNumber);
        if (allInactive) {
            cartRow.style.opacity = '0.2'; // Dim the cart if all rows are inactive
            cartBuyerRow.style.opacity = '0.2';
        } else {
            cartRow.style.opacity = '1'; // Reset cart opacity if at least one row is active
            cartBuyerRow.style.opacity = '1';
        }
    }


















    function ShowCart(event, cartId, link) {
        event.preventDefault();
        
        // Select all product rows for the specific cart
        const productRows = document.querySelectorAll(`.cart-products-${cartId}`);
        const currentState = link.getAttribute("data-state");
        
        if (currentState === "more") {
            // Show the product rows
            productRows.forEach(row => row.style.display = "table-row");
        
            // Update the link text and state
            link.innerHTML = "👁️ HIDE";
            link.setAttribute("data-state", "hide");
        } else {
            // Hide the product rows
            productRows.forEach(row => row.style.display = "none");
        
            // Update the link text and state
            link.innerHTML = "👁️ MORE";
            link.setAttribute("data-state", "more");
        }
    }
</script>