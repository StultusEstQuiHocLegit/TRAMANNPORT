<script>
    // JavaScript function to trigger the glow animation, log idpk, prevent link navigation, and add a database entry
    function addToCartGlow(event, idpk) {
        event.preventDefault(); // Prevent the link from navigating
        
        console.log("Product idpk:", idpk); // Log the idpk to the console
    
        const glowElement = document.getElementById('glowEffect');
        
        // Reset the animation by setting it to 'none' and forcing a reflow
        glowElement.style.animation = 'none';
        void glowElement.offsetWidth; // Trigger a reflow, flushing the CSS changes
        
        // Reapply the animation
        glowElement.style.animation = 'glow 2.5s forwards';
        
        // Get the user_id from cookies
        const user_id = getCookie('user_id');
        if (!user_id) {
            console.error("User id not found. Cannot proceed.");
            return; // Exit if user_id is missing
        }
    
        // Send an AJAX request to add the transaction to the database
        fetch('SaveDataShowProduct.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                idpk: idpk,
                user_id: user_id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Transaction added successfully.");
            } else {
                console.error("Failed to add transaction:", data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    }
    
    // Helper function to get a cookie by name
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null; // Return null if cookie is not found
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






















<?php
echo "<h1>🛍️ PREVIOUS CARTS</h1>";

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
        WHERE c.IdpkExplorerOrCreator = :user_id AND t.state >= 2
        GROUP BY c.TimestampCreation, c.idpk, e.CompanyName, e.FirstName, e.LastName
        ORDER BY c.TimestampCreation DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($carts)) {
        echo "There are no previous carts available yet.";
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







            // Fetch products for the current cart
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
                    CONCAT('uploads/ProductPictures/', p.idpk, '_0.jpg') AS Image
                FROM transactions t
                INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
                WHERE t.IdpkCart = :cart_id
            ");
            $stmt->execute(['cart_id' => $cart['CartNumber']]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);









        
            // Add a horizontal line for separation
            echo "<tr><td colspan='8' style='padding: 0;'><hr style='border: 1px solid #505050; margin: 0; width: 100%;'></td></tr>";
            
            echo "<tr style='font-weight: bold;'>";
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
            echo "<td><a href='#' onclick='ShowCart(event, {$cart['CartNumber']}, this)' data-state='more'>👁️ MORE</a></td>";
            // echo "<td></td>";
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
                $opacity = $isActive ? "1" : "0.5";
        
                // Assign a class that ties the row to the cart
                echo "<tr class='cart-products cart-products-{$cart['CartNumber']}' style='opacity: {$opacity}; display: none;'>";
                echo "<td></td>";
                echo "<td>{$product['TransactionId']}</td>";
                echo "<td title='{$productName} ({$product['ProductId']})'><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['ProductId']}'>$truncatedName ({$product['ProductId']})</a><br>{$commentsNotes}</td>";
                echo "<td style='font-weight: bold; font-size: 1.5rem;'>{$product['Quantity']}x </td>";
                echo "<td>{$product['TotalPrice']}$</td>";
                echo "<td title='(0 = collecting, 1 = ordered, 2 = paid, 3 = orders transmitted to creators, 4 = creators producing or selecting, 5 = creators shipping, 6 = in customs, 7 = at distribution center, 8 = arriving, 9 = finished)'>{$translatedTransactionState}</td>";
                // echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['ProductId']}'>👁️ MORE</a></td>";
        
                if ($isActive) {
                    echo "<td><a href='index.php?content=PreviousCarts.php' onclick='addToCartGlow(event, {$product['ProductId']})' class='mainbutton'>🛒 REPICK</a></td>";
                } else {
                    echo "<td>inactive</td>";
                }
        
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
















