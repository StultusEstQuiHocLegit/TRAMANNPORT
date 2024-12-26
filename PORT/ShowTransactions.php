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
</script>





















































<?php
// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'ShowTransaction' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $transactionId = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Access global $pdo if not already defined
    global $pdo;

    // perform a second query to fetch all the necessary details
    // we also add the user check conditions again for security
    $sql = "
        SELECT 
            t.idpk AS TransactionId,
            t.IdpkExplorer AS BuyerId,
            t.IdpkProductOrService AS ProductId,
            t.IdpkCart AS CartId,
            COALESCE(t.AmountInDollars, 0) + COALESCE(t.ForTRAMANNPORTInDollars, 0) + COALESCE(t.TaxesInDollars, 0) AS TotalPrice,
            t.AmountInDollars AS TotalPriceIfSellingSide,
            t.CommentsNotesSpecialRequests,
            t.state,
            t.quantity,
            ps.name AS ProductName,
            ps.state AS ProductState,
            ps.IdpkCreator AS CreatorId,

            c.manual,
            c.IfManualFurtherInformation,

            -- Buyer info
            ecBuyer.ExplorerOrCreator AS BuyerRole,
            ecBuyer.FirstName AS BuyerFirstName,
            ecBuyer.LastName AS BuyerLastName,
            ecBuyer.CompanyName AS BuyerCompanyName,
            ecBuyer.country AS BuyerCountry,
            ecBuyer.city AS BuyerCity,
            ecBuyer.ZIPCode AS BuyerZIPCode,
            ecBuyer.street AS BuyerStreet,
            ecBuyer.HouseNumber AS BuyerHouseNumber,

            -- Creator (Seller) info
            ecCreator.ExplorerOrCreator AS CreatorRole,
            ecCreator.FirstName AS CreatorFirstName,
            ecCreator.LastName AS CreatorLastName,
            ecCreator.CompanyName AS CreatorCompanyName,
            ecCreator.country AS CreatorCountry,
            ecCreator.city AS CreatorCity,
            ecCreator.ZIPCode AS CreatorZIPCode,
            ecCreator.street AS CreatorStreet,
            ecCreator.HouseNumber AS CreatorHouseNumber

        FROM transactions t
        LEFT JOIN ProductsAndServices ps ON t.IdpkProductOrService = ps.idpk
        LEFT JOIN carts c ON t.IdpkCart = c.idpk
        LEFT JOIN ExplorersAndCreators ecBuyer ON t.IdpkExplorer = ecBuyer.idpk
        LEFT JOIN ExplorersAndCreators ecCreator ON ps.IdpkCreator = ecCreator.idpk
        WHERE 
            t.idpk = :transactionId
            AND (t.IdpkExplorer = :user_id OR ps.IdpkCreator = :user_id)
            AND (t.state >= 3)
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':transactionId', $transactionId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$details) {
        // No transaction found or not accessible by this user
        return;
    }

    $productName = htmlspecialchars($details['ProductName'] ?? '');
    $commentsNotes = !empty($details['CommentsNotesSpecialRequests']) ? " ({$details['CommentsNotesSpecialRequests']})" : "";

    $isActive = isset($details['ProductState']) && $details['ProductState'] == 1;

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

    $TransactionState = isset($details['state']) ? (int)$details['state'] : 0;
    $translatedTransactionState = $transactionStateMapping[$TransactionState] ?? 'unknown';

    // Set opacity based on conditions
    $opacity = "1";
    if ($TransactionState != 3 && $TransactionState != 4) {
        $opacity = "1";
    }

    $isBuyer = ($details['BuyerId'] == $user_id);

    $TransactionId = $details['TransactionId'];
    $ProductId = $details['ProductId'];
    $Quantity = isset($details['quantity']) ? $details['quantity'] : 1;
    $TotalPrice = isset($details['TotalPrice']) ? $details['TotalPrice'] : 0;
    $TotalPriceIfSellingSide = isset($details['TotalPriceIfSellingSide']) ? $details['TotalPriceIfSellingSide'] : 0;
    $CartId = isset($details['CartId']) ? $details['CartId'] : 0;

    // Determine transaction partner:
    // If manual, just use IfManualFurtherInformation
    // Otherwise, if current user is buyer, partner is the creator (seller)
    // If current user is seller (creator), partner is the buyer
    if ($details['manual'] == 1) {
        // Manual transaction partner information
        $transactionPartnerName = $details['IfManualFurtherInformation'] . " (manual)";
        $transactionPartnerId = "";
        $transactionPartnerAddress = "";
        $partnerRole = null; // no company or explorer info
    } else {
        if ($isBuyer) {
            // Current user is buyer, partner is the creator (seller)
            $partnerRole = $details['CreatorRole'];
            $transactionPartnerId = $details['CreatorId'];
            if ($partnerRole == 1) { 
                // Creator representing a Company
                $transactionPartnerName = $details['CreatorCompanyName'];
            } else {
                $transactionPartnerName = trim($details['CreatorFirstName'] . " " . $details['CreatorLastName']);
            }
            $transactionPartnerAddress = "{$details['CreatorCountry']}, {$details['CreatorCity']}, {$details['CreatorZIPCode']}, {$details['CreatorStreet']} {$details['CreatorHouseNumber']}";
        } else {
            // Current user is seller, partner is the buyer
            $partnerRole = $details['BuyerRole'];
            $transactionPartnerId = $details['BuyerId'];
            if ($partnerRole == 1) {
                // Buyer is a company (Creator account type)
                $transactionPartnerName = $details['BuyerCompanyName'];
            } else {
                $transactionPartnerName = trim($details['BuyerFirstName'] . " " . $details['BuyerLastName']);
            }
            $transactionPartnerAddress = "{$details['BuyerCountry']}, {$details['BuyerCity']}, {$details['BuyerZIPCode']}, {$details['BuyerStreet']} {$details['BuyerHouseNumber']}";
        }
    }

    // Fetch product picture(s)
    $uploadDirProduct = "uploads/ProductPictures/" . htmlspecialchars($ProductId) . "_";
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imagePaths = [];
    for ($i = 0; $i < 5; $i++) {
        foreach ($validExtensions as $extension) {
            $filePath = "{$uploadDirProduct}{$i}.{$extension}";
            if (file_exists($filePath)) {
                $imagePaths[] = $filePath;  // Add to array if file exists
                break;  // Stop checking other extensions if a file is found
            }
        }
    }

    // Fetch company logo if partner is a company and not manual
    $profilePicturePath = null;
    if ($details['manual'] != 1 && $partnerRole == 1 && !empty($transactionPartnerId)) {
        // Partner is a company (Creator)
        $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];
        $uploadDirCompany = './uploads/AccountPictures/';
        foreach ($imageExtensions as $ext) {
            $potentialPath = $uploadDirCompany . $transactionPartnerId . '.' . $ext;
            if (file_exists($potentialPath)) {
                $profilePicturePath = $potentialPath;
                break;
            }
        }
    }




























    
    echo "<h3>"
    . ($isBuyer ? "<span title='you bought' style='color:red;'>◀</span>" : "<span title='you sold' style='color:green;'>▶</span>")
    . " TRANSACTION {$TransactionId}</h3>";

    echo "<table style='width: 100%; text-align: left;'>";
    echo "<tr style='opacity: {$opacity};'>";
    echo "<td title='CART {$CartId}'><a href='index.php?content=explore.php&action=ShowCarts&idpk={$CartId}'>(CART {$CartId})</a></td>";

    if ($details['manual'] == 1) {
        // Manual scenario
        $transactionPartnerFullName = $details['IfManualFurtherInformation'] . " (manual)";
        echo "<td title='{$transactionPartnerFullName}' style='text-align:center;'>" . htmlspecialchars($transactionPartnerFullName) . "</td>";
    } else {
        // Non-manual scenario: show partner link and title with address
        echo "<td title='{$transactionPartnerName} ({$transactionPartnerId}), {$transactionPartnerAddress}' style='text-align:center;'>";
        if ($partnerRole == 1 && $profilePicturePath) {
            // Display company logo above the name
            echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$transactionPartnerId}'>
                    <img src=\"" . htmlspecialchars($profilePicturePath) . "\" style=\"height:50px; display:block; margin: 0 auto;\">
                  </a>";
        }
        echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$transactionPartnerId}'>"
        . htmlspecialchars($transactionPartnerName) . " ({$transactionPartnerId})</a>";
        echo "</td>";
    }

    if ($isBuyer and $details['manual'] == 1) {
        // Product column with image
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
    } else {
        // Product column with image
        echo "<td title='{$productName} ({$ProductId})' style='text-align:right;'>";
        if (isset($imagePaths[0]) && file_exists($imagePaths[0])) {
            echo "<a href='index.php?content=explore.php&action=ShowProduct&idpk={$ProductId}'>
                    <img src=\"" . htmlspecialchars($imagePaths[0]) . "\" style=\"height:100px;\">
                  </a>";
        }
        echo "</td><td title='{$productName} ({$ProductId})'><a href='index.php?content=explore.php&action=ShowProduct&idpk={$ProductId}'>$productName ({$ProductId})</a><br>{$commentsNotes}</td>";
        echo "<td style='font-weight: bold; font-size: 1.5rem;'>{$Quantity}x</td>";
    }

    echo "<td>" . (!$isBuyer ? "{$TotalPriceIfSellingSide}$" : "{$TotalPrice}$") . "</td>";
    echo "<td title='(0 = collecting, 1 = ordered, 2 = paid, 3 = orders transmitted to creators, 4 = creators producing or selecting, 5 = creators shipping, 6 = in customs, 7 = at distribution center, 8 = arriving, 9 = finished)'>{$translatedTransactionState}</td>";
    
    if ($isBuyer and $details['manual'] != 1) { // the user is the buyer
        if ($isActive) {
            echo "<td><a href='index.php?content=explore.php' onclick='addToCartGlow(event, {$ProductId})' class='mainbutton'>🛒 REPICK</a></td>";
        } else {
            echo "<td>inactive</td>";
        }
    } elseif ($isBuyer and $details['manual'] == 1) {
        echo "<td></td>";
    } elseif ($TransactionState == 3 or $TransactionState == 4) { // here starts the point where the user is the seller
        echo "<td>";
            if ($TransactionState == 3) {
                echo "<a href='javascript:void(0);' id='4-link-{$TransactionId}' class='mainbutton' onclick='updateTransactionState(event, {$TransactionId}, \"4\")'>🛠️ WORKING</a>";
                echo "<a href='javascript:void(0);' id='5-link-{$TransactionId}' class='mainbutton' style='display:none;' onclick='updateTransactionState(event, {$TransactionId}, \"5\")'>🚚 SHIPPING</a>";
                echo "<br><a href='javascript:void(0);' id='3-link-{$TransactionId}' style='opacity: 0.4; display:none;' onclick='updateTransactionState(event, {$TransactionId}, \"3\")'>⬅️ BACK</a>";
            } elseif ($TransactionState == 4) {
                echo "<a href='javascript:void(0);' id='4-link-{$TransactionId}' class='mainbutton' style='display:none;' onclick='updateTransactionState(event, {$TransactionId}, \"4\")'>🛠️ WORKING</a>";
                echo "<a href='javascript:void(0);' id='5-link-{$TransactionId}' class='mainbutton' onclick='updateTransactionState(event, {$TransactionId}, \"5\")' >🚚 SHIPPING</a>";
                echo "<br><a href='javascript:void(0);' id='3-link-{$TransactionId}' style='opacity: 0.4;' onclick='updateTransactionState(event, {$TransactionId}, \"3\")'>⬅️ BACK</a>";
            }
        echo "</td>";
    } else {
        echo "<td></td>";
    }


    echo "</tr>";
    echo "</table>";

    echo "<br><br><br><br><br><br><br><br><br><br>";
}


?>