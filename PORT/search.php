<?php
// Include configuration file
include_once '../config.php';

// Function to truncate text based on specified limits
function truncateText($text, $limit) {
    return (strlen($text) > $limit) ? substr($text, 0, $limit) . '...' : $text;
}

// Function to format the shipping price
function formatShippingPrice($shippingPrice) {
    return (!empty($shippingPrice) && $shippingPrice != 0) ? "(+$shippingPrice\$)" : '';
}

// Assuming the user's ID is already defined
$user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;
















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display product row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a product row
function displayProductRow($product, $highlight = false, $user_id = null) {
    $truncatedName = truncateText($product['name'], 50);
    $truncatedDescription = truncateText($product['ShortDescription'], 100);
    $shippingPrice = formatShippingPrice($product['SellingPricePackagingAndShippingInDollars']);
    
    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    // reduce the opacity for inactive (0) products or services
    $rowOpacity = ($product['state'] == 0) ? '0.4' : '1.0';

    echo "<tr style='opacity: $rowOpacity;'>";
    // add yellow block
    if ($canManage) {
        echo "<td style='width: 1px; background-color: yellow;'></td>"; // Yellow block column
    } else {
        echo "<td></td>";
    }
        // add image here
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

        if (isset($imagePaths[0]) && file_exists($imagePaths[0])):
            echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'><img src=\"" . htmlspecialchars($imagePaths[0]) . "\" style=\"height:100px;\"></a></td>";
        else:
            echo "<td></td>";
        endif;
    echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\"><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a><br><div title=\"" . htmlspecialchars($product['ShortDescription']) . "\" style=\"opacity: 0.5;\">$truncatedDescription</div></td>";
    // echo "<td>$truncatedDescription</td>";
    echo "<td>{$product['SellingPriceProductOrServiceInDollars']}$ $shippingPrice</td>";
    
    // Links
    // echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>👁️ MORE</a></td>";  // show link
    if ($canManage) {
        echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>✏️ EDIT</a></td>";  // manage link
    } else {
        echo "<td><a href='index.php?content=explore.php' onclick='addToCartGlow(event, {$product['idpk']})'>🛒 ADD TO CART</a></td>";  // add to cart link
    }
    echo "</tr>";
}















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////// display product row as in inventory.php
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a product row as in inventory.php
function displayProductRowManageInventory($product, $highlight = false, $user_id = null) {
    $truncatedName = (strlen($product['name']) > 50) ? substr($product['name'], 0, 50) . '...' : $product['name'];
    $truncatedPersonalNotes = (strlen($product['PersonalNotes']) > 100) ? substr($product['PersonalNotes'], 0, 100) . '...' : $product['PersonalNotes'];
    
    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    // reduce the opacity for inactive (0) products or services
    $rowOpacity = ($product['state'] == 0) ? '0.4' : '1.0';

    if ($canManage) {
        echo "<tr style='opacity: $rowOpacity;>";

        // add yellow block
        echo "<td style='width: 1px; background-color: yellow;'></td>"; // Yellow block column

        // Check if inventory is managed
        if ($product['ManageInventory'] == 0) {
            echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\" data-context=\"SearchResults\" style=\"opacity: 0.6;\"><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
            echo "<td id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" data-context=\"SearchResults\" placeholder=\"0\" style=\"opacity: 0.6;\">inventory not managed</td>";
            echo "<td id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" data-context=\"SearchResults\" placeholder=\"0\" style=\"opacity: 0.6;\">inventory not managed</td>";
            echo "<td id=\"InventoryMinimumLevel_{$product['idpk']}\" name=\"InventoryMinimumLevel\" value=\"" . (isset($product['InventoryMinimumLevel']) ? htmlspecialchars($product['InventoryMinimumLevel']) : '0') . "\" data-context=\"SearchResults\" placeholder=\"0\" style=\"opacity: 0.4;\">inventory not managed</td>";
            // echo "<td style=\"opacity: 0.6;\"><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
            echo "<td><input type=\"text\" id=\"PersonalNotes_{$product['idpk']}\" title=\"" . htmlspecialchars($product['PersonalNotes']) . "\" name=\"PersonalNotes\" value=\"" . htmlspecialchars($product['PersonalNotes']) . "\" data-context=\"SearchResults\" style=\"opacity: 0.3; width: 100%;\" onchange=\"updateInventory({$product['idpk']}, 'PersonalNotes', this.value)\"></td>";
        } else {
            echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\" data-context=\"SearchResults\"><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a></td>";
            echo "<td><input type=\"number\" id=\"InventoryAvailable_{$product['idpk']}\" name=\"InventoryAvailable\" value=\"" . htmlspecialchars($product['InventoryAvailable']) . "\" data-context=\"SearchResults\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryAvailable', this.value)\"></td>";
            echo "<td><input type=\"number\" id=\"InventoryInProduction_{$product['idpk']}\" name=\"InventoryInProduction\" value=\"" . htmlspecialchars($product['InventoryInProduction']) . "\" data-context=\"SearchResults\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryInProduction', this.value)\"></td>";
            echo "<td><input type=\"number\" id=\"InventoryMinimumLevel_{$product['idpk']}\" name=\"InventoryMinimumLevel\" value=\"" . (isset($product['InventoryMinimumLevel']) ? htmlspecialchars($product['InventoryMinimumLevel']) : '0') . "\" data-context=\"SearchResults\" placeholder=\"0\" style=\"width: 100px;\" onchange=\"updateInventory({$product['idpk']}, 'InventoryMinimumLevel', this.value)\" style=\"opacity: 0.6;\"></td>";
            // echo "<td><div style=\"opacity: 0.5;\">$truncatedPersonalNotes</div></td>";
            echo "<td><input type=\"text\" id=\"PersonalNotes_{$product['idpk']}\" title=\"" . htmlspecialchars($product['PersonalNotes']) . "\" name=\"PersonalNotes\" value=\"" . htmlspecialchars($product['PersonalNotes']) . "\" data-context=\"SearchResults\" style=\"opacity: 0.4; width: 100%;\" onchange=\"updateInventory({$product['idpk']}, 'PersonalNotes', this.value)\"></td>";
        }

        echo "</tr>";
    }
}



















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////// display product row for ManualSelling.php
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a product row for ManualSelling.php
function displayProductRowManualSelling($product, $highlight = false, $user_id = null) {
    $truncatedName = truncateText($product['name'], 50);
    $truncatedDescription = truncateText($product['ShortDescription'], 100);
    $shippingPrice = formatShippingPrice($product['SellingPricePackagingAndShippingInDollars']);
    
    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    if ($canManage && $product['state'] == 1) {
        echo "<tr>";
            echo "<td style='width: 1px; background-color: yellow;'></td>"; // add yellow block

            // add image here
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

            if (isset($imagePaths[0]) && file_exists($imagePaths[0])):
                echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'><img src=\"" . htmlspecialchars($imagePaths[0]) . "\" style=\"height:100px;\"></a></td>";
            else:
                echo "<td></td>";
            endif;

            echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\">";
                echo "<a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a>";
                echo "<br><div title=\"" . htmlspecialchars($product['ShortDescription']) . "\" style=\"opacity: 0.5;\">$truncatedDescription</div>";
                echo "<div style=\"opacity: 0.5;\">available: {$product['InventoryAvailable']}, in production or reordered: {$product['InventoryInProduction']}</div>";
                echo "<div style=\"opacity: 0.5;\">{$product['PersonalNotes']}</div>";
            echo "</td>";
            // echo "<td>$truncatedDescription</td>";
            echo "<td>{$product['SellingPriceProductOrServiceInDollars']}$ $shippingPrice</td>";

            // "ADD" button that triggers the JavaScript function
            // echo "<td><a href='javascript:void(0);' onclick='addToManualSelling(event, {$product['idpk']})'>👉 ADD</a></td>";
            echo "<td>";
            echo "<a href='javascript:void(0);' 
                onclick='addToManualSelling(event, {$product['idpk']}, {
                    IdpkCreator: \"{$product['IdpkCreator']}\",
                    name: \"{$product['name']}\",
                    SellingPriceProductOrServiceInDollars: \"{$product['SellingPriceProductOrServiceInDollars']}\",
                    SellingPricePackagingAndShippingInDollars: \"{$product['SellingPricePackagingAndShippingInDollars']}\",
                    ManageInventory: \"{$product['ManageInventory']}\",
                    InventoryAvailable: \"{$product['InventoryAvailable']}\",
                    InventoryInProduction: \"{$product['InventoryInProduction']}\",
                    InventoryMinimumLevel: \"{$product['InventoryMinimumLevel']}\",
                    PersonalNotes: \"{$product['PersonalNotes']}\",
                    state: \"{$product['state']}\"
                })'>👉 ADD</a>";
            echo "</td>";
        echo "</tr>";
    }
}
















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display creators and explorers row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a creators and explorers row
function displayCreatorsAndExplorersRow($product, $highlight = false, $user_id = null) {
    $truncatedShortDescription = (strlen($product['ShortDescription']) > 100) ? substr($product['ShortDescription'], 0, 100) . '...' : $product['ShortDescription'];
    $address = "{$product['country']}, {$product['city']}, {$product['ZIPCode']}, {$product['street']} {$product['HouseNumber']}";

    echo "<tr>";

    if ($product['idpk'] == $user_id) { // creator
    // add yellow block
    echo "<td style='width: 1px; background-color: yellow;'></td>"; // Yellow block column
    } else {
        echo "<td></td>";
    }

    // Check if inventory is managed
    if ($product['ExplorerOrCreator'] == 1) { // creator
        echo "<td>";
            // Get the idpk
            $idpk = htmlspecialchars($product['idpk']);
            // Define the possible image file extensions
            $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];
            // Base directory for profile pictures
            $uploadDir = './uploads/AccountPictures/';
            // Initialize a variable to hold the profile picture path (if found)
            $profilePicturePath = null;
            // Iterate through the possible extensions and check if the file exists
            foreach ($imageExtensions as $ext) {
                $potentialPath = $uploadDir . $idpk . '.' . $ext;
                if (file_exists($potentialPath)) {
                    $profilePicturePath = $potentialPath;
                    break; // Exit the loop once we find the file
                }
            }
            // Display the profile picture if it exists
            if ($profilePicturePath) {
                // Output the image tag for the found profile picture
                echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$product['idpk']}'><img src=\"$profilePicturePath\" style=\"height:50px;\"></a>";
            } else {
                // If no profile picture is found, display nothing
            }
        echo "</td>";
        echo "<td><a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$product['idpk']}'>{$product['CompanyName']} ({$product['idpk']})</a></td>";
        echo "<td style=\"opacity: 0.6;\"><div title=\"" . htmlspecialchars($product['ShortDescription']) . "\">$truncatedShortDescription</div><br>$address</td>";
    } else { // explorer
        echo "<td></td>";
        echo "<td><a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$product['idpk']}'>{$product['FirstName']} {$product['LastName']} ({$product['idpk']})</a></td>";
        echo "<td style=\"opacity: 0.6;\">$address</td>";
    }
    
    // echo "<td><a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$product['idpk']}'>👁️ MORE</a></td>";  // show link

    echo "</tr>";
}

// in another part of my code i got that:
// idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search

// can we now write something for that:
// $sql = "SELECT * FROM ExplorersAndCreators WHERE
// -> look for where search matches FirstName, LastName, street, ZIPCode, city, country, planet (if ExplorerOrCreator = 0)
//     -> but only show it, if in the table transactions there is an entry where field IdpkProductOrService, then look in table ProductsAndServices for field idpk to match that, then look on ProductsAndServices for field IdpkCreator to match :user_id OR the idpk of the ExplorersAndCreators just searched
//     -> and in the table transactions the field IdpkExplorer has to match accodingly the idpk of the ExplorersAndCreators just searched OR :user_id (the other way round)
//     -> meaning: there has to have been a transaction between the two
// -> look for where search matches CompanyName, VATID, street, ZIPCode, city, country, planet, PhoneNumberForExplorersAsContact, EmailForExplorersAsContact, ShortDescription, LongDescription (if ExplorerOrCreator = 1)
// idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search





















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display transactions row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a transactions row
function displayTransactionsRow($product, $highlight = false, $user_id = null) {
    echo "displayTransactionsRow - currently under construction, if you need this function urgently, tell Lasse to hurry up   ; )";
}



















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display carts row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a carts row
function displayCartsRow($product, $highlight = false, $user_id = null) {
    echo "displayCartsRow - currently under construction, if you need this function urgently, tell Lasse to hurry up   ; )";
}



















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// main search functionality
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_POST['query']) && isset($_POST['preselectedOption']) && isset($_POST['preselectedViewing'])) {
    $searchQuery = trim(htmlspecialchars($_POST['query'])); // Sanitize the search query
    $preselectedOption = trim(htmlspecialchars($_POST['preselectedOption'])); // Retrieve and sanitize the preselected option
    $preselectedViewing = trim(htmlspecialchars($_POST['preselectedViewing'])); // Retrieve and sanitize the preselected viewing

    // Prepare and sanitize the input
    $searchQuery = trim($searchQuery);
    $searchQuery = htmlspecialchars($searchQuery);

    try {
        if ($preselectedOption === 'your_products_services') {
            // search only for the creators products and services
            $sql = "SELECT * FROM ProductsAndServices WHERE IdpkCreator = :user_id AND (idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search)";
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } elseif ($preselectedOption === 'creators_explorers') {
            // search for creators and explorers
            $sql = <<<SQL
                SELECT DISTINCT ec.*
                FROM ExplorersAndCreators ec
                LEFT JOIN transactions t1
                    ON t1.IdpkExplorer = ec.idpk
                LEFT JOIN transactions t2
                    ON t2.IdpkExplorer = :user_id
                LEFT JOIN ProductsAndServices ps
                    ON ps.idpk = t1.IdpkProductOrService
                WHERE
                    (
                        (ec.ExplorerOrCreator = 0 AND (
                            ec.idpk LIKE :search OR
                            ec.FirstName LIKE :search OR
                            ec.LastName LIKE :search OR
                            ec.street LIKE :search OR
                            ec.ZIPCode LIKE :search OR
                            ec.city LIKE :search OR
                            ec.country LIKE :search OR
                            ec.planet LIKE :search
                        )
                        AND (
                            (ps.IdpkCreator = :user_id OR ec.idpk = ps.IdpkCreator)
                            AND (t1.IdpkExplorer = :user_id OR t2.IdpkExplorer = ec.idpk)
                        ))
                        OR
                        (ec.ExplorerOrCreator = 1 AND (
                            ec.idpk LIKE :search OR
                            ec.CompanyName LIKE :search OR
                            ec.VATID LIKE :search OR
                            ec.street LIKE :search OR
                            ec.ZIPCode LIKE :search OR
                            ec.city LIKE :search OR
                            ec.country LIKE :search OR
                            ec.planet LIKE :search OR
                            ec.PhoneNumberForExplorersAsContact LIKE :search OR
                            ec.EmailForExplorersAsContact LIKE :search OR
                            ec.ShortDescription LIKE :search OR
                            ec.LongDescription LIKE :search
                        ))
                    );
                SQL;
            // ////////////////////////////////////////////////////////////////////////////////////// logic
            // $sql = "SELECT * FROM ExplorersAndCreators WHERE
            // -> look for where search matches FirstName, LastName, street, ZIPCode, city, country, planet (if ExplorerOrCreator = 0)
            //     -> but only show it, if in the table transactions there is an entry where field IdpkProductOrService, then look in table ProductsAndServices for field idpk to match that, then look on ProductsAndServices for field IdpkCreator to match :user_id OR the idpk of the ExplorersAndCreators just searched
            //     -> and in the table transactions the field IdpkExplorer has to match accodingly the idpk of the ExplorersAndCreators just searched OR :user_id (the other way round)
            //     -> meaning: there has to have been a transaction between the two
            // -> look for where search matches CompanyName, VATID, street, ZIPCode, city, country, planet, PhoneNumberForExplorersAsContact, EmailForExplorersAsContact, ShortDescription, LongDescription (if ExplorerOrCreator = 1)
            // idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } elseif ($preselectedOption === 'your_explorers_customers') {
            // search only for the creators explorers (customers)
            $sql = <<<SQL
                SELECT DISTINCT ec.*
                FROM ExplorersAndCreators ec
                INNER JOIN transactions t
                    ON t.IdpkExplorer = ec.idpk
                INNER JOIN ProductsAndServices ps
                    ON ps.idpk = t.IdpkProductOrService
                WHERE
                    ps.IdpkCreator = :user_id
                    AND (
                        (ec.ExplorerOrCreator = 0 AND (
                            ec.idpk LIKE :search OR
                            ec.FirstName LIKE :search OR
                            ec.LastName LIKE :search OR
                            ec.street LIKE :search OR
                            ec.ZIPCode LIKE :search OR
                            ec.city LIKE :search OR
                            ec.country LIKE :search OR
                            ec.planet LIKE :search
                        ))
                        OR
                        (ec.ExplorerOrCreator = 1 AND (
                            ec.idpk LIKE :search OR
                            ec.CompanyName LIKE :search OR
                            ec.VATID LIKE :search OR
                            ec.street LIKE :search OR
                            ec.ZIPCode LIKE :search OR
                            ec.city LIKE :search OR
                            ec.country LIKE :search OR
                            ec.planet LIKE :search OR
                            ec.PhoneNumberForExplorersAsContact LIKE :search OR
                            ec.EmailForExplorersAsContact LIKE :search OR
                            ec.ShortDescription LIKE :search OR
                            ec.LongDescription LIKE :search
                        ))
                    );
                SQL;
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } elseif ($preselectedOption === 'your_creators_suppliers') {
            // search only for the creators creators (suppliers)
            $sql = <<<SQL
                SELECT DISTINCT ec.*
                FROM ExplorersAndCreators ec
                INNER JOIN transactions t
                    ON t.IdpkExplorer = :user_id
                INNER JOIN ProductsAndServices ps
                    ON ps.idpk = t.IdpkProductOrService
                WHERE
                    ps.IdpkCreator = ec.idpk
                    AND (
                        (ec.ExplorerOrCreator = 0 AND (
                            ec.idpk LIKE :search OR
                            ec.FirstName LIKE :search OR
                            ec.LastName LIKE :search OR
                            ec.street LIKE :search OR
                            ec.ZIPCode LIKE :search OR
                            ec.city LIKE :search OR
                            ec.country LIKE :search OR
                            ec.planet LIKE :search
                        ))
                        OR
                        (ec.ExplorerOrCreator = 1 AND (
                            ec.idpk LIKE :search OR
                            ec.CompanyName LIKE :search OR
                            ec.VATID LIKE :search OR
                            ec.street LIKE :search OR
                            ec.ZIPCode LIKE :search OR
                            ec.city LIKE :search OR
                            ec.country LIKE :search OR
                            ec.planet LIKE :search OR
                            ec.PhoneNumberForExplorersAsContact LIKE :search OR
                            ec.EmailForExplorersAsContact LIKE :search OR
                            ec.ShortDescription LIKE :search OR
                            ec.LongDescription LIKE :search
                        ))
                    );
                SQL;
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } elseif ($preselectedOption === 'transactions') {
            // search only for the creators transactions
            $sql = "SELECT * FROM ProductsAndServices WHERE IdpkCreator = :user_id AND (idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search)";
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } elseif ($preselectedOption === 'carts') {
            // search only for the creators carts
            $sql = "SELECT * FROM ProductsAndServices WHERE IdpkCreator = :user_id AND (idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search)";
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } else {
            // general search for all active products and services
            $sql = "SELECT * FROM ProductsAndServices WHERE state = 1 AND (idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search)";
            $stmt = $pdo->prepare($sql);
        }

        // Use wildcards for partial matches
        $param = '%' . $searchQuery . '%';
        
        // Bind the parameter
        $stmt->bindParam(':search', $param, PDO::PARAM_STR);

        // Execute the statement
        $stmt->execute();

        $exactMatch = null; // Variable to store the exact match, if found
        $otherResults = []; // Array to store other results

        // Check if any results were found
        if ($stmt->rowCount() > 0) {
            while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Check for exact match on idpk
                if ($product['idpk'] == $searchQuery) {
                    $exactMatch = $product; // Store exact match
                } else {
                    $otherResults[] = $product; // Store other results
                }
            }
        } else {
            echo "We are very sorry, but we could not find any results for '" . htmlspecialchars($searchQuery) . "', please check the spelling or try other similar keywords.";
            exit; // Exit if no results
        }

        // Display the exact match if it exists
        if ($exactMatch) {
            echo "<div style='border: 1px solid #505050;'>";
                echo '<table>';
                if ($preselectedViewing === "manage_inventory") {
                    // Use displayProductRowManageInventory
                    displayProductRowManageInventory($exactMatch, true, $user_id); // Highlight the exact match
                } elseif ($preselectedViewing === "manual_selling") {
                    // Use displayProductRowManualSelling
                    displayProductRowManualSelling($exactMatch, true, $user_id); // Highlight the exact match
                } elseif ($preselectedOption === "creators_explorers" || $preselectedOption === "your_explorers_customers" || $preselectedOption === "your_creators_suppliers") {
                    // Use displayCreatorsAndExplorersRow
                    displayCreatorsAndExplorersRow($exactMatch, true, $user_id); // Highlight the exact match
                } elseif ($preselectedOption === "transactions") {
                    // Use displayTransactionsRow
                    displayTransactionsRow($exactMatch, true, $user_id); // Highlight the exact match
                } elseif ($preselectedOption === "carts") {
                    // Use displayCartsRow
                    displayCartsRow($exactMatch, true, $user_id); // Highlight the exact match
                } else {
                    // Default to displayProductRow
                    displayProductRow($exactMatch, true, $user_id); // Highlight the exact match
                }
                echo '</table>';
            echo "</div>";
            echo "<br><br><br><br><br>"; // Five additional line breaks
        }

        // Now display the other results
        echo '<table>'; // Open the table for other results
        foreach ($otherResults as $product) {
            if ($preselectedViewing === "manage_inventory") {
                // Use displayProductRowManageInventory
                displayProductRowManageInventory($product, false, $user_id);
            } elseif ($preselectedViewing === "manual_selling") {
                // Use displayProductRowManualSelling
                displayProductRowManualSelling($product, false, $user_id);
            } elseif ($preselectedOption === "creators_explorers" || $preselectedOption === "your_explorers_customers" || $preselectedOption === "your_creators_suppliers") {
                // Use displayCreatorsAndExplorersRow
                displayCreatorsAndExplorersRow($product, false, $user_id);
            } elseif ($preselectedOption === "transactions") {
                // Use displayTransactionsRow
                displayTransactionsRow($product, false, $user_id);
            } elseif ($preselectedOption === "carts") {
                // Use displayCartsRow
                displayCartsRow($product, false, $user_id);
            } else {
                // Default to displayProductRow
                displayProductRow($product, false, $user_id);
            }
        }
        echo '</table>'; // Close the table

    } catch (PDOException $e) {
        // Handle SQL errors
        echo "Error in SQL execution: " . htmlspecialchars($e->getMessage());
    }
}
?>
