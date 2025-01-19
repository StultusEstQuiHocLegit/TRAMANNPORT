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

// Helper function to safely round values
if (!function_exists('safe_round')) {
    function safe_round($value, $precision = 2) {
        // return is_numeric($value) ? round($value, $precision) : 0;
        return number_format((float) $value, $precision, '.', '');
    }
}

// Assuming the user's ID is already defined
$user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

$userRole = null; // Initialize user role

if ($user_id !== null) {
    try {
        // Prepare the SQL query to get the user role
        // $stmt = $pdo->prepare('SELECT ExplorerOrCreator FROM ExplorersAndCreators WHERE idpk = :id');
        $stmt = $pdo->prepare('SELECT idpk, ExplorerOrCreator, FirstName, LastName, CompanyName 
                               FROM ExplorersAndCreators 
                               WHERE idpk = :id');
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if a role was found
        if ($result) {
            $userRole = (int)$result['ExplorerOrCreator']; // Cast to integer if needed
        } else {
            // Handle case where user role is not found
            $userRole = null; // Or set to a default value
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Database error: " . $e->getMessage();
    }
}

$profilePictograms = [
    "🐶", "🐱", "🐭", "🐹", "🐰", "🦊", "🐻", "🐼", "🦁", "🐯",
    "🐨", "🐸", "🐵", "🐔", "🐧", "🐦", "🐥", "🦆", "🦅", "🦉",
    "🦇", "🐺", "🐗", "🐴", "🐝", "🐛", "🦋", "🐌", "🐞", "🐜",
    "🪲", "🪳", "🦟", "🦗", "🕷", "🐢", "🐍", "🦎", "🐙", "🦑",
    "🦐", "🦞", "🦀", "🐡", "🐠", "🐟", "🐬", "🐋", "🦈", "🐊",
    "🐅", "🐆", "🦓", "🦍", "🦧", "🐘", "🦛", "🦏", "🐪", "🐫",
    "🦒", "🦘", "🦬", "🐃", "🐂", "🐄", "🐖", "🐏", "🐑", "🦙",
    "🐐", "🦌", "🐓", "🦃", "🐿", "🦫", "🦔"
];

// Determine sender name
$senderName = $result['ExplorerOrCreator'] == 0 
? "{$result['FirstName']} {$result['LastName']} ({$result['idpk']})" 
: "{$result['CompanyName']} ({$result['idpk']})";
















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display product row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a product row
function displayProductRow($product, $highlight = false, $user_id = null, $userRole = null, $ContributionForTRAMANNPORT = null) {
    $truncatedName = truncateText($product['name'], 50);
    $truncatedDescription = truncateText($product['ShortDescription'], 100);
    // $shippingPrice = formatShippingPrice($product['SellingPricePackagingAndShippingInDollars']);
    
    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    // reduce the opacity for inactive (0) products or services
    $rowOpacity = ($product['state'] == 0) ? '0.4' : '1.0';

    echo "<tr style='opacity: $rowOpacity;'>";
    // add yellow block
    if ($canManage) {
        if ($product['OnlyForInternalPurposes'] == 0 || $product['OnlyForInternalPurposes'] === null) { // no
            echo "<td title='your product or service, also for external purposes' style='width: 1px; background-color: yellow;'></td>"; // Yellow block column
        } else { // yes
            echo "<td title='your product or service, only for internal purposes' style='width: 1px; background-color: lightyellow;'></td>"; // Yellow block column
        }
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
    echo "<td>";
        //echo "{$product['SellingPriceProductOrServiceInDollars']}$ $shippingPrice";
        // Main logic
        if ($canManage) {
            // Direct display without modification
            echo safe_round($product['SellingPriceProductOrServiceInDollars'], 2) . "$ (+"
                . safe_round($product['SellingPricePackagingAndShippingInDollars'], 2) . ")";
        } elseif ($userRole === 1) { // For creators (business accounts), apply contribution
            $sellingPriceWithContribution = $product['SellingPriceProductOrServiceInDollars'] * (1 + $ContributionForTRAMANNPORT / 100);
            $packagingAndShippingPriceWithContribution = $product['SellingPricePackagingAndShippingInDollars'] * (1 + $ContributionForTRAMANNPORT / 100);
            echo safe_round($sellingPriceWithContribution, 2) . "$ (+"
                . safe_round($packagingAndShippingPriceWithContribution, 2) . ")";
        } else { // For explorers, apply contribution and taxes
            $sellingPriceWithContribution = $product['SellingPriceProductOrServiceInDollars'] * (1 + $ContributionForTRAMANNPORT / 100);
            $packagingAndShippingPriceWithContribution = $product['SellingPricePackagingAndShippingInDollars'] * (1 + $ContributionForTRAMANNPORT / 100);
            $sellingPriceWithTaxes = $sellingPriceWithContribution * (1 + $product['TaxesInPercent'] / 100);
            $packagingAndShippingPriceWithTaxes = $packagingAndShippingPriceWithContribution * (1 + $product['TaxesInPercent'] / 100);
        
            echo safe_round($sellingPriceWithTaxes, 2) . "$ (+"
                . safe_round($packagingAndShippingPriceWithTaxes, 2) . ")";
        }
    echo "</td>";
    
    
    // Links
    // echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>👁️ MORE</a></td>";  // show link
    if ($canManage) {
        echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>✏️ EDIT</a></td>";  // manage link
    } else {
        echo "<td><a href='index.php?content=explore.php' onclick='addToCartGlow(event, {$product['idpk']})' class='mainbutton'>🛒 ADD TO CART</a></td>";  // add to cart link
    }
    echo "</tr>";
}















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////// display product row as in inventory.php
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a product row as in inventory.php
function displayProductRowManageInventory($product, $highlight = false, $user_id = null, $userRole = null) {
    $truncatedName = (strlen($product['name']) > 50) ? substr($product['name'], 0, 50) . '...' : $product['name'];
    $truncatedPersonalNotes = (strlen($product['PersonalNotes']) > 100) ? substr($product['PersonalNotes'], 0, 100) . '...' : $product['PersonalNotes'];
    
    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    // reduce the opacity for inactive (0) products or services
    $rowOpacity = ($product['state'] == 0) ? '0.4' : '1.0';

    if ($canManage) {
        echo "<tr style='opacity: $rowOpacity;>";
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
function displayProductRowManualSelling($product, $highlight = false, $user_id = null, $userRole = null) {
    $truncatedName = truncateText($product['name'], 50);
    $truncatedDescription = truncateText($product['ShortDescription'], 100);
    $shippingPrice = formatShippingPrice($product['SellingPricePackagingAndShippingInDollars']);
    
    // Calculate the total price with taxes (if applicable)
    $taxesInPercent = isset($product['TaxesInPercent']) ? $product['TaxesInPercent'] : 0;
    $taxMultiplier = 1 + ($taxesInPercent / 100);
    $priceWithTaxes = $product['SellingPriceProductOrServiceInDollars'] * $taxMultiplier;
    $shippingPriceWithTaxes = $product['SellingPricePackagingAndShippingInDollars'] * $taxMultiplier;

    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    // Check if the cookie for taxes is set
    $showPricesWithTaxes = isset($_COOKIE['manualSellingTaxes']) && $_COOKIE['manualSellingTaxes'] === 'added';

    if ($canManage && $product['state'] == 1) {
        echo "<tr>";
            // add yellow block
            if ($product['OnlyForInternalPurposes'] == 0 || $product['OnlyForInternalPurposes'] === null) { // no
                echo "<td title='your product or service, also for external purposes' style='width: 1px; background-color: yellow;'></td>"; // Yellow block column
            } else { // yes
                echo "<td title='your product or service, only for internal purposes' style='width: 1px; background-color: lightyellow;'></td>"; // Yellow block column
            }

            // add image here
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

            if (isset($imagePaths[0]) && file_exists($imagePaths[0])):
                // echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'><img src=\"" . htmlspecialchars($imagePaths[0]) . "\" style=\"height:100px;\"></a></td>";
                echo "<td>";
                    echo "<a href='javascript:void(0);' 
                        onclick='addToManualSelling(event, {$product['idpk']}, {
                            IdpkCreator: \"{$product['IdpkCreator']}\",
                            name: \"{$product['name']}\",
                            SellingPriceProductOrServiceInDollars: \"{$product['SellingPriceProductOrServiceInDollars']}\",
                            SellingPricePackagingAndShippingInDollars: \"{$product['SellingPricePackagingAndShippingInDollars']}\",
                            TaxesInPercent: \"{$product['TaxesInPercent']}\",
                            ManageInventory: \"{$product['ManageInventory']}\",
                            InventoryAvailable: \"{$product['InventoryAvailable']}\",
                            InventoryInProduction: \"{$product['InventoryInProduction']}\",
                            InventoryMinimumLevel: \"{$product['InventoryMinimumLevel']}\",
                            PersonalNotes: \"{$product['PersonalNotes']}\",
                            state: \"{$product['state']}\"
                        })'><img src=\"" . htmlspecialchars($imagePaths[0]) . "\" style=\"height:100px;\"></a>";
                echo "</td>";
            else:
                echo "<td></td>";
            endif;

            echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\">";
                // echo "<a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a>";
                echo "<a href='javascript:void(0);' 
                    onclick='addToManualSelling(event, {$product['idpk']}, {
                        IdpkCreator: \"{$product['IdpkCreator']}\",
                        name: \"{$product['name']}\",
                        SellingPriceProductOrServiceInDollars: \"{$product['SellingPriceProductOrServiceInDollars']}\",
                        SellingPricePackagingAndShippingInDollars: \"{$product['SellingPricePackagingAndShippingInDollars']}\",
                        TaxesInPercent: \"{$product['TaxesInPercent']}\",
                        ManageInventory: \"{$product['ManageInventory']}\",
                        InventoryAvailable: \"{$product['InventoryAvailable']}\",
                        InventoryInProduction: \"{$product['InventoryInProduction']}\",
                        InventoryMinimumLevel: \"{$product['InventoryMinimumLevel']}\",
                        PersonalNotes: \"{$product['PersonalNotes']}\",
                        state: \"{$product['state']}\"
                    })'>👉 ADD $truncatedName ({$product['idpk']})</a>";
                echo "<br><div title=\"" . htmlspecialchars($product['ShortDescription']) . "\" style=\"opacity: 0.5;\">$truncatedDescription</div>";
                echo "<div style=\"opacity: 0.5;\">available: {$product['InventoryAvailable']}, in production or reordered: {$product['InventoryInProduction']}</div>";
                echo "<div style=\"opacity: 0.5;\">{$product['PersonalNotes']}</div>";
            echo "</td>";

            // Display price based on the cookie
            echo "<td>";
            if ($showPricesWithTaxes) {
                echo number_format($priceWithTaxes, 2) . "$ + " . number_format($shippingPriceWithTaxes, 2) . "$";
                // echo "<br><span style='font-size: small; opacity: 0.5;'>(with taxes: {$taxesInPercent}%)</span>";
            } else {
                echo "{$product['SellingPriceProductOrServiceInDollars']}$ + $shippingPrice";
            }
            echo "</td>";

            // "ADD" button with tax-aware data
            echo "<td>";
                // echo "<a href='javascript:void(0);' 
                //     onclick='addToManualSelling(event, {$product['idpk']}, {
                //         IdpkCreator: \"{$product['IdpkCreator']}\",
                //         name: \"{$product['name']}\",
                //         SellingPriceProductOrServiceInDollars: \"{$product['SellingPriceProductOrServiceInDollars']}\",
                //         SellingPricePackagingAndShippingInDollars: \"{$product['SellingPricePackagingAndShippingInDollars']}\",
                //         TaxesInPercent: \"{$product['TaxesInPercent']}\",
                //         ManageInventory: \"{$product['ManageInventory']}\",
                //         InventoryAvailable: \"{$product['InventoryAvailable']}\",
                //         InventoryInProduction: \"{$product['InventoryInProduction']}\",
                //         InventoryMinimumLevel: \"{$product['InventoryMinimumLevel']}\",
                //         PersonalNotes: \"{$product['PersonalNotes']}\",
                //         state: \"{$product['state']}\"
                //     })'>👉 ADD</a>";
                echo "<a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}' style=\"opacity: 0.3;\" target='_blank'>👁️ MORE</a>";
            echo "</td>";
        echo "</tr>";
    }
}

















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display creators and explorers row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a creators and explorers row
function displayCreatorsAndExplorersRow($product, $highlight = false, $user_id = null, $userRole = null) {
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
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display customer relationships row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a customer relationships row
function displayCustomerRelationshipsRow($product, $highlight = false, $user_id = null, $userRole = null, $profilePictograms = null, $senderName = null) {
    global $pdo; // Make the $pdo variable accessible inside the function
    
    // Truncate the notes if they exceed 200 characters
    $truncatedNotes = (strlen($product['notes']) > 200) ? substr($product['notes'], 0, 200) . '...' : $product['notes'];

    // Fetch the ProfilePictogram value from $product
    $profilePictogramIndex = $product['ProfilePictogram'];

    // Get the matching emoji
    $profilePictogramEmoji = isset($profilePictograms[$profilePictogramIndex]) 
        ? $profilePictograms[$profilePictogramIndex] 
        : ''; // Default to nothing if the index is invalid

    echo "<tr>";
    

                // Display the table cell with the emoji
                // echo "<td title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\">
                //         <a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}' 
                //            style='font-size: 3rem;'>{$profilePictogramEmoji}</a>
                //       </td>";

                // Assuming $product contains the relevant data
                $state = $product['state'] ?? 0;
                $importance = $product['importance'] ?? 0;

                // Define dotted spacing based on importance (wider for lower importance, closer for higher importance)
                $dottedSpacingMap = [
                    0 => '10px', // Initial contact
                    1 => '8px',  // Emerging partner
                    2 => '6px',  // Partner
                    3 => '4px',  // Core partner
                    4 => '2px',  // Prime partner
                ];
                $dottedSpacing = $dottedSpacingMap[$importance] ?? '10px';

                // Determine shape and border style dynamically
                $borderStyle = ($state == 0) 
                    ? "border-style: dotted dotted dotted dashed; border-width: 2px 2px 2px 3px;" // potential customer
                    : "border-style: dotted dotted dotted solid; border-width: 2px 2px 2px 3px;";  // existing or former customer

                // Dynamic border color based on importance
                $borderColorMap = [
                    0 => 'rgba(50, 50, 50, 0.1)', // Initial contact
                    1 => 'rgba(50, 50, 50, 0.3)',      // Emerging partner
                    2 => 'rgba(50, 50, 50, 0.5)',     // Partner
                    3 => 'rgba(50, 50, 50, 0.7)',    // Core partner
                    4 => 'rgba(50, 50, 50, 1)',       // Prime partner
                ];
                $borderColor = $borderColorMap[$importance] ?? ''; // Default if not defined

                // Combine styles into a single definition
                $circleStyle = "{$borderStyle} border-color: {$borderColor}; padding: 0.5rem; display: inline-flex; align-items: center; justify-content: center; opacity: 1;";

                // Output the profile pictogram with dynamic styles
                echo "<td title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\" style='position: relative;'>
                        <a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}'
                           style='font-size: 3rem; position: relative;'>
                           <span style='{$circleStyle}'>{$profilePictogramEmoji}</span>
                        </a>
                      </td>";


    echo "<td>";
    echo "<a href='index.php?content=explore.php&action=ShowCustomerRelationships&idpk={$product['idpk']}' title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\">{$product['FirstName']} {$product['LastName']} ({$product['idpk']})</a>";
    echo "<span style=\"opacity: 0.8;\">";
        echo " {$product['title']}";
        if (!empty($product['CompanyName']) && !empty($product['title'])) {
            echo " at ";
        } elseif (!empty($product['CompanyName'])) {
            echo " from ";
        }
        // echo "{$product['CompanyName']}";

        // Assuming $product['CompanyName'] contains the input value (could be text or a number)
        $companyName = $product['CompanyName'];

        // Check if the CompanyName is numeric
        if (is_numeric($companyName)) {
            try {
                // Prepare and execute the query to find the company name based on the numeric ID
                $stmt = $pdo->prepare("SELECT CompanyName, idpk FROM ExplorersAndCreators WHERE idpk = :id AND ExplorerOrCreator = '1'");
                $stmt->bindParam(':id', $companyName, PDO::PARAM_INT);
                $stmt->execute();
            
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
                if ($result) {
                    // If a match is found, display the company name as a link
                    echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$result['idpk']}' title='{$result['CompanyName']} ({$result['idpk']})'>
                                {$result['CompanyName']}
                            </a>";
                } else {
                    // If no match is found, display the original numeric value
                    echo "{$companyName}";
                }
            } catch (PDOException $e) {
                // Handle database errors
                echo "Error fetching data: " . $e->getMessage();
            }
        } else {
            // If the CompanyName is not numeric, display it as plain text
            echo "{$companyName}";
        }
    echo "</span>";
    echo "<br><span style=\"opacity: 0.5;\">";

            // Determine recipient name
            $recipientName = $product['FirstName'];

            // sender name already determined earlier

            // Prepare email subject and body
            $emailSubject = "TRAMANN PORT - Hi from $senderName";
            $emailBody = "Hi" . ($recipientName ? " $recipientName" : "") . ",\n\n\n[ContentOfYourMessage]\n\n\n\nSincerely yours,\n$senderName";
            // URL-encode the subject and body
            $emailLink = "mailto:{$product['email']}?subject=" . rawurlencode($emailSubject) . "&body=" . rawurlencode($emailBody);
            // Generate tel link
            $telLink = "tel:{$product['PhoneNumber']}";
        if (!empty($product['email'])) {
            echo "<a href='$emailLink'>✉️ EMAIL</a> "; // open directly
        }
        if (!empty($product['PhoneNumber'])) {
            // echo "<a href='$telLink'>📞 PHONE</a> ";
            echo "<a href='#' onclick=\"confirmPhoneCall('$telLink')\">📞 PHONE</a> "; // Trigger confirmation
        }

        $text = htmlspecialchars(trim($product['LinksToSocialMediaAndOtherSites'] ?? ''));

        // Only process and display if there are links
        if (!empty($text)) {
            echo "<span class=\"LinksToSocialMediaAndOtherSites\" name=\"LinksToSocialMediaAndOtherSites\" style=\"text-align: left;\">";
        
            // Regular expression to match valid URLs
            $urlRegex = '/(?:https?:\/\/)?(?:www\.)?([a-zA-Z0-9-]+\.[a-zA-Z]{2,})([\/\w\-\.?&=]*)/';

            // Replace URLs with clickable links
            $formattedText = preg_replace_callback($urlRegex, function ($matches) {
                $hostname = $matches[1];
                $pathname = $matches[2] ?? '';
            
                // Create the full URL for the link
                $fullUrl = "https://$hostname$pathname";
            
                // Remove 'www.' if present
                $displayDomain = strtoupper(str_replace('www.', '', $hostname)); // Convert domain to uppercase
            
                // Remove TLDs from hostname
                $domainParts = explode('.', $displayDomain);
                $cleanDomain = count($domainParts) > 1 ? implode('.', array_slice($domainParts, 0, -1)) : $displayDomain;
            
                // Get the last part of the pathname for the page name
                $pathParts = array_filter(explode('/', $pathname));
                $pageName = end($pathParts) ? explode('?', end($pathParts))[0] : ''; // Get the last part without query or fragment
            
                // Limit lengths for display
                $limitedDomain = strlen($cleanDomain) > 20 ? substr($cleanDomain, 0, 20) . '...' : $cleanDomain;
                $limitedPageName = strlen($pageName) > 20 ? substr($pageName, 0, 20) . '...' : $pageName;
            
                // Convert page name to uppercase if present
                $displayText = $pageName ? "$limitedDomain ($limitedPageName)" : $limitedDomain;
            
                return "<a href=\"$fullUrl\" target=\"_blank\" class=\"link\">🔗 $displayText</a> ";
            }, $text);
        
            echo $formattedText;
            echo "</span>";
        }
    echo "</span>";
    // echo "<br><span style=\"opacity: 0.8;\">{$product['email']} {$product['PhoneNumber']} {$product['LinksToSocialMediaAndOtherSites']}</span>";
    echo "<br><span  title='{$product['notes']}' style=\"opacity: 0.5;\">$truncatedNotes</div>";
    echo "</td>";
    echo "</tr>";
}




















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////// display customer relationships row as in ShowCustomerRelationships.php
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a customer relationships row as in ShowCustomerRelationships.php
function displayCustomerRelationshipsRowManageCustomerRelationships($product, $highlight = false, $user_id = null, $userRole = null, $profilePictograms = null, $senderName = null) {
    global $pdo; // Make the $pdo variable accessible inside the function
    
    // Truncate the notes if they exceed 200 characters
    $truncatedNotes = (strlen($product['notes']) > 200) ? substr($product['notes'], 0, 200) . '...' : $product['notes'];

    // Fetch the ProfilePictogram value from $product
    $profilePictogramIndex = $product['ProfilePictogram'];

    // Get the matching emoji
    $profilePictogramEmoji = isset($profilePictograms[$profilePictogramIndex]) 
        ? $profilePictograms[$profilePictogramIndex] 
        : ''; // Default to nothing if the index is invalid

    // Check if the state is "former customer" (state = 2)
    $rowStyle = ($product['state'] == 2) ? ' style="opacity: 0.4;"' : '';

    echo "<tr{$rowStyle}>";

    
                // Display the table cell with the emoji
                // echo "<td title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\">
                //         <a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}' 
                //            style='font-size: 3rem;'>{$profilePictogramEmoji}</a>
                //       </td>";

                // Assuming $product contains the relevant data
                $state = $product['state'] ?? 0;
                $importance = $product['importance'] ?? 0;

                // Define dotted spacing based on importance (wider for lower importance, closer for higher importance)
                $dottedSpacingMap = [
                    0 => '10px', // Initial contact
                    1 => '8px',  // Emerging partner
                    2 => '6px',  // Partner
                    3 => '4px',  // Core partner
                    4 => '2px',  // Prime partner
                ];
                $dottedSpacing = $dottedSpacingMap[$importance] ?? '10px';

                // Determine shape and border style dynamically
                $borderStyle = ($state == 0) 
                    ? "border-style: dotted dotted dotted dashed; border-width: 2px 2px 2px 3px;" // potential customer
                    : "border-style: dotted dotted dotted solid; border-width: 2px 2px 2px 3px;";  // existing or former customer

                // Dynamic border color based on importance
                $borderColorMap = [
                    0 => 'rgba(50, 50, 50, 0.1)', // Initial contact
                    1 => 'rgba(50, 50, 50, 0.3)',      // Emerging partner
                    2 => 'rgba(50, 50, 50, 0.5)',     // Partner
                    3 => 'rgba(50, 50, 50, 0.7)',    // Core partner
                    4 => 'rgba(50, 50, 50, 1)',       // Prime partner
                ];
                $borderColor = $borderColorMap[$importance] ?? ''; // Default if not defined

                // Combine styles into a single definition
                $circleStyle = "{$borderStyle} border-color: {$borderColor}; padding: 0.5rem; display: inline-flex; align-items: center; justify-content: center; opacity: 1;";

                // Output the profile pictogram with dynamic styles
                echo "<td title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\" style='position: relative;'>
                        <a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}'
                           style='font-size: 3rem; position: relative;'>
                           <span style='{$circleStyle}'>{$profilePictogramEmoji}</span>
                        </a>
                      </td>";


    echo "<td>";
    echo "<a href='index.php?content=CustomerRelationships.php&action=update&idpk={$product['idpk']}' title=\"{$product['FirstName']} {$product['LastName']} ({$product['idpk']})\">{$product['FirstName']} {$product['LastName']} ({$product['idpk']})</a>";
    echo "<span style=\"opacity: 0.8;\">";
        echo " {$product['title']}";
        if (!empty($product['CompanyName']) && !empty($product['title'])) {
            echo " at ";
        } elseif (!empty($product['CompanyName'])) {
            echo " from ";
        }
        // echo "{$product['CompanyName']}";

        // Assuming $product['CompanyName'] contains the input value (could be text or a number)
        $companyName = $product['CompanyName'];

        // Check if the CompanyName is numeric
        if (is_numeric($companyName)) {
            try {
                // Prepare and execute the query to find the company name based on the numeric ID
                $stmt = $pdo->prepare("SELECT CompanyName, idpk FROM ExplorersAndCreators WHERE idpk = :id AND ExplorerOrCreator = '1'");
                $stmt->bindParam(':id', $companyName, PDO::PARAM_INT);
                $stmt->execute();
            
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
                if ($result) {
                    // If a match is found, display the company name as a link
                    echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$result['idpk']}' title='{$result['CompanyName']} ({$result['idpk']})'>
                                {$result['CompanyName']}
                            </a>";
                } else {
                    // If no match is found, display the original numeric value
                    echo "{$companyName}";
                }
            } catch (PDOException $e) {
                // Handle database errors
                echo "Error fetching data: " . $e->getMessage();
            }
        } else {
            // If the CompanyName is not numeric, display it as plain text
            echo "{$companyName}";
        }
    echo "</span>";
    echo "<br><span style=\"opacity: 0.5;\">";

            // Determine recipient name
            $recipientName = $product['FirstName'];

            // sender name already determined earlier

            // Prepare email subject and body
            $emailSubject = "TRAMANN PORT - Hi from $senderName";
            $emailBody = "Hi" . ($recipientName ? " $recipientName" : "") . ",\n\n\n[ContentOfYourMessage]\n\n\n\nSincerely yours,\n$senderName";
            // URL-encode the subject and body
            $emailLink = "mailto:{$product['email']}?subject=" . rawurlencode($emailSubject) . "&body=" . rawurlencode($emailBody);
            // Generate tel link
            $telLink = "tel:{$product['PhoneNumber']}";
        if (!empty($product['email'])) {
            echo "<a href='$emailLink'>✉️ EMAIL</a> "; // open directly
        }
        if (!empty($product['PhoneNumber'])) {
            // echo "<a href='$telLink'>📞 PHONE</a> ";
            echo "<a href='#' onclick=\"confirmPhoneCall('$telLink')\">📞 PHONE</a> "; // Trigger confirmation
        }

        $text = htmlspecialchars(trim($product['LinksToSocialMediaAndOtherSites'] ?? ''));

        // Only process and display if there are links
        if (!empty($text)) {
            echo "<span class=\"LinksToSocialMediaAndOtherSites\" name=\"LinksToSocialMediaAndOtherSites\" style=\"text-align: left;\">";
        
            // Regular expression to match valid URLs
            $urlRegex = '/(?:https?:\/\/)?(?:www\.)?([a-zA-Z0-9-]+\.[a-zA-Z]{2,})([\/\w\-\.?&=]*)/';

            // Replace URLs with clickable links
            $formattedText = preg_replace_callback($urlRegex, function ($matches) {
                $hostname = $matches[1];
                $pathname = $matches[2] ?? '';
            
                // Create the full URL for the link
                $fullUrl = "https://$hostname$pathname";
            
                // Remove 'www.' if present
                $displayDomain = strtoupper(str_replace('www.', '', $hostname)); // Convert domain to uppercase
            
                // Remove TLDs from hostname
                $domainParts = explode('.', $displayDomain);
                $cleanDomain = count($domainParts) > 1 ? implode('.', array_slice($domainParts, 0, -1)) : $displayDomain;
            
                // Get the last part of the pathname for the page name
                $pathParts = array_filter(explode('/', $pathname));
                $pageName = end($pathParts) ? explode('?', end($pathParts))[0] : ''; // Get the last part without query or fragment
            
                // Limit lengths for display
                $limitedDomain = strlen($cleanDomain) > 20 ? substr($cleanDomain, 0, 20) . '...' : $cleanDomain;
                $limitedPageName = strlen($pageName) > 20 ? substr($pageName, 0, 20) . '...' : $pageName;
            
                // Convert page name to uppercase if present
                $displayText = $pageName ? "$limitedDomain ($limitedPageName)" : $limitedDomain;
            
                return "<a href=\"$fullUrl\" target=\"_blank\" class=\"link\">🔗 $displayText</a> ";
            }, $text);
        
            echo $formattedText;
            echo "</span>";
        }
    echo "</span>";
    // echo "<br><span style=\"opacity: 0.8;\">{$product['email']} {$product['PhoneNumber']} {$product['LinksToSocialMediaAndOtherSites']}</span>";
    // echo "<br><span title='{$product['notes']}' style=\"opacity: 0.5;\">$truncatedNotes</div>";
    echo "<br><textarea id='notesConnectedToCustomerRelationship_{$product['idpk']}' class='notes-input' data-id='{$product['idpk']}' title='{$product['notes']}' style='opacity: 0.5; width: 100%;' rows='1'>{$product['notes']}</textarea>";
    echo "</td>";
    echo "</tr>";
}





















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display transactions row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a transactions row
function displayTransactionsRow($product, $highlight = false, $user_id = null, $userRole = null) {
    $transactionId = (int)$product['idpk'];

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
    $truncatedName = strlen($productName) > 50 ? substr($productName, 0, 47) . "..." : $productName;
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

    // // Set opacity based on conditions
    // $opacity = $isActive ? "1" : "0.6";
    // if ($TransactionState != 3 && $TransactionState != 4) {
    //     $opacity = "0.2";
    // }
    $opacity = "1";

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
        $truncatedTransactionPartnerName = $transactionPartnerName;
    } else {
        if ($isBuyer) {
            // Current user is buyer, partner is the creator
            $partnerRole = $details['CreatorRole'];
            $transactionPartnerId = $details['CreatorId'];
            if ($partnerRole == 1) { 
                // ExplorerOrCreator=1 means a Creator representing a Company
                $transactionPartnerName = $details['CreatorCompanyName'];
            } else {
                $transactionPartnerName = trim($details['CreatorFirstName'] . " " . $details['CreatorLastName']);
            }

            // Combine partner address details if needed
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

            // Combine partner address details if needed
            $transactionPartnerAddress = "{$details['BuyerCountry']}, {$details['BuyerCity']}, {$details['BuyerZIPCode']}, {$details['BuyerStreet']} {$details['BuyerHouseNumber']}";
        }

        $truncatedTransactionPartnerName = strlen($transactionPartnerName) > 50 ? substr($transactionPartnerName, 0, 47) . "..." : $transactionPartnerName;
    }

    echo "<tr style='opacity: {$opacity};'>";
    echo "<td style='font-weight: bold; font-size: 1.5rem;'>" . ($isBuyer ? "<span title='you bought' style='color:red;'>◀</span>" : "<span title='you sold' style='color:green;'>▶</span>") . "</td>";
    echo "<td title='TRANSACTION {$TransactionId}'><a href='index.php?content=explore.php&action=ShowTransaction&idpk={$TransactionId}'>TRANSACTION {$TransactionId}</a></td>";
    echo "<td title='CART {$CartId}'><a href='index.php?content=explore.php&action=ShowCarts&idpk={$CartId}'>(CART {$CartId})</a></td>";

    if ($details['manual'] == 1) {
        // Manual scenario
        $transactionPartnerFullName = $details['IfManualFurtherInformation'] . " (manual)";
        // Truncate the manual text if it's longer than 50 characters
        $truncatedManualText = (strlen($transactionPartnerFullName) > 30) 
            ? substr($transactionPartnerFullName, 0, 27) . '...' 
            : $transactionPartnerFullName;

        echo "<td title='{$transactionPartnerFullName}'>{$truncatedManualText}</td>";
    } else {
        // Non-manual scenario: show partner link and title with address
        echo "<td title='{$transactionPartnerName} ({$transactionPartnerId}), {$transactionPartnerAddress}'>
                <a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$transactionPartnerId}'>
                    $truncatedTransactionPartnerName ({$transactionPartnerId})
                </a>
              </td>";
    }

    if ($isBuyer and $details['manual'] == 1) {
        echo "<td></td>";
        echo "<td></td>";
    } else {
        echo "<td title='{$productName} ({$ProductId})'><a href='index.php?content=explore.php&action=ShowProduct&idpk={$ProductId}'>$truncatedName ({$ProductId})</a><br>{$commentsNotes}</td>";
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

    // Optionally, if you want to display partner address details in a second row (like in your provided snippet):
    // if ($details['manual'] == 1) {
    //     echo "<tr style='opacity: {$opacity}; font-weight: bold;'>";
    //     echo "<td></td><td></td><td colspan='5'>{$details['IfManualFurtherInformation']} (manual)</td><td></td>";
    //     echo "</tr>";
    // } else {
    //     echo "<tr style='opacity: {$opacity}; font-weight: bold;'>";
    //     echo "<td></td><td></td><td colspan='5'>{$transactionPartnerName} ({$transactionPartnerId}), {$transactionPartnerAddress}</td><td></td>";
    //     echo "</tr>";
    // }
}



















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// display carts row
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to display a carts row without transactions
function displayCartsRow($product, $highlight = false, $user_id = null, $userRole = null) {
    $cartId = (int)$product['idpk'];

    // Access global $pdo if not already defined
    global $pdo;

    // Fetch only cart-level details accessible by the given user
    $sql = "
        SELECT 
            c.idpk AS CartId,
            c.TimestampCreation AS CartCreationTimestamp,
            c.IdpkExplorerOrCreator AS BuyerId,
            c.manual,
            c.IfManualFurtherInformation,
            c.DeliveryType,
            c.WishedIdealDeliveryOrPickUpTime,

            SUM(COALESCE(ts.AmountInDollars, 0) + COALESCE(ts.ForTRAMANNPORTInDollars, 0) + COALESCE(ts.TaxesInDollars, 0)) AS TotalAmount,
            SUM(ts.AmountInDollars) AS TotalAmountIfSellingSide,

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

            -- Now aggregate creator info if multiple
            -- We'll group-concat their IDs
            GROUP_CONCAT(DISTINCT ps.IdpkCreator SEPARATOR ',') AS CreatorIds,
            GROUP_CONCAT(DISTINCT IF(ecCreator.ExplorerOrCreator=1, ecCreator.CompanyName, CONCAT(ecCreator.FirstName, ' ', ecCreator.LastName)) SEPARATOR ' | ') AS CreatorNames,
            GROUP_CONCAT(DISTINCT CONCAT(ecCreator.country, ', ', ecCreator.city, ', ', ecCreator.ZIPCode, ', ', ecCreator.street, ' ', ecCreator.HouseNumber) SEPARATOR ' | ') AS CreatorAddresses,
            GROUP_CONCAT(DISTINCT ecCreator.ExplorerOrCreator SEPARATOR ',') AS CreatorRoles

        FROM carts c
        LEFT JOIN transactions ts ON c.idpk = ts.IdpkCart
        LEFT JOIN ProductsAndServices ps ON ts.IdpkProductOrService = ps.idpk
        LEFT JOIN ExplorersAndCreators ecBuyer ON ecBuyer.idpk = c.IdpkExplorerOrCreator
        LEFT JOIN ExplorersAndCreators ecCreator ON ecCreator.idpk = ps.IdpkCreator
        WHERE
            c.idpk = :cartId
            AND (c.IdpkExplorerOrCreator = :user_id OR ps.IdpkCreator = :user_id)
        GROUP BY c.idpk
        LIMIT 1;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$details) {
        // No cart found or not accessible by this user
        return;
    }

    $CartId = $details['CartId'];
    $IfManualFurtherInformation = $details['IfManualFurtherInformation'];
    $cartTimestamp = date('Y-m-d H:i:s', $details['CartCreationTimestamp']);
    $deliveryType = isset($details['DeliveryType']) ? (int)$details['DeliveryType'] : 0;

    $formattedAmount = number_format($details['TotalAmount'] ?? 0, 2);
    $formattedAmountIfSellingSide = number_format($details['TotalAmountIfSellingSide'] ?? 0, 2);
    // Define a mapping for delivery type values
    $deliveryTypeMapping = [
        0 => 'standard',
        1 => 'express',
        2 => 'as soon as possible',
        3 => 'pick up in store',
        4 => 'best matching wished ideal delivery time'
    ];

    $formattedDeliveryType = $deliveryTypeMapping[$deliveryType] ?? 'unknown'; // Fallback to 'unknown' if the value is not in the mapping
    $cartWishedIdealDeliveryOrPickUpTime = date('Y-m-d H:i:s', $details['WishedIdealDeliveryOrPickUpTime']);

    $isBuyer = ($details['BuyerId'] == $user_id);

    // Determine transaction partner:
    // If manual, just use IfManualFurtherInformation
    // Otherwise, if current user is buyer, partner is the creator (seller)
    // If current user is seller (creator), partner is the buyer
    if ($details['manual'] == 1) {
        // Manual transaction partner information
        $transactionPartnerName = $details['IfManualFurtherInformation'] . " (manual)";
        $transactionPartnerId = "";
        $truncatedTransactionPartnerName = $transactionPartnerName;
    } else {
        if ($isBuyer) {
            // // Current user is buyer, partner is the creator
            // $partnerRole = $details['CreatorRole'];
            // $transactionPartnerId = $details['CreatorId'];
            // if ($partnerRole == 1) { 
            //     // ExplorerOrCreator=1 means a Creator representing a Company
            //     $transactionPartnerName = $details['CreatorCompanyName'];
            // } else {
            //     $transactionPartnerName = trim($details['CreatorFirstName'] . " " . $details['CreatorLastName']);
            // }
            //          // Combine partner address details if needed
            // $transactionPartnerAddress = "{$details['CreatorCountry']}, {$details['CreatorCity']}, {$details['CreatorZIPCode']}, {$details['CreatorStreet']} {$details['CreatorHouseNumber']}";
            
            
            $partnerRole = "";
            $transactionPartnerId = "";
            if ($partnerRole == 1) { 
                // ExplorerOrCreator=1 means a Creator representing a Company
                $transactionPartnerName = "";
            } else {
                $transactionPartnerName = "";
            }
                     // Combine partner address details if needed
            $transactionPartnerAddress = "";
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
                     // Combine partner address details if needed
            $transactionPartnerAddress = "{$details['BuyerCountry']}, {$details['BuyerCity']}, {$details['BuyerZIPCode']}, {$details['BuyerStreet']} {$details['BuyerHouseNumber']}";
        }
             $truncatedTransactionPartnerName = strlen($transactionPartnerName) > 50 ? substr($transactionPartnerName, 0, 47) . "..." : $transactionPartnerName;
    }

    $opacity = "1";

    // The arrow indicator:
    $arrow = $isBuyer ? "<span title='you bought' style='color:red;'>◀</span>" : "<span title='you sold' style='color:green;'>▶</span>";

    echo "<tr style='opacity: {$opacity};'>";
    // Arrow indicator column
    echo "<td style='font-weight: bold; font-size: 1.5rem;'>{$arrow}</td>";

    // Cart link column (similar to transaction link)
    echo "<td title='CART {$CartId}'>
            <a href='index.php?content=explore.php&action=ShowCarts&idpk={$CartId}'>CART {$CartId}</a>
          </td>";
    
    echo "<td style='width: 5px;'></td>";
    echo "<td>{$cartTimestamp}</td>";
    echo "<td style='width: 5px;'></td>";

    if ($details['manual'] == 1) {
        // Manual scenario
        $transactionPartnerFullName = $details['IfManualFurtherInformation'] . " (manual)";
        // Truncate the manual text if it's longer than 50 characters
        $truncatedManualText = (strlen($transactionPartnerFullName) > 30) 
            ? substr($transactionPartnerFullName, 0, 27) . '...' 
            : $transactionPartnerFullName;

        echo "<td title='{$transactionPartnerFullName}'>{$truncatedManualText}</td>";
    } else {
        if (!empty($partnerRole)) {
            // Non-manual scenario: show partner link and title with address
            echo "<td title='{$transactionPartnerName} ({$transactionPartnerId}), {$transactionPartnerAddress}'>
                    <a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$transactionPartnerId}'>
                        {$truncatedTransactionPartnerName} ({$transactionPartnerId})
                    </a>
                  </td>";
        } else {
            // $partnerRole is empty
            echo "<td></td>";
        }
    }
  
    echo "<td>" . (!$isBuyer ? "{$formattedAmountIfSellingSide}$" : "{$formattedAmount}$") . "</td>";
    echo "<td style='width: 5px;'></td>";
    if ($deliveryType == 3 || $deliveryType == 4) {
        echo "<td>{$formattedDeliveryType} ({$cartWishedIdealDeliveryOrPickUpTime})</td>";
    } else {
        echo "<td>{$formattedDeliveryType}</td>";
    }

    echo "</tr>";
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
        } elseif ($preselectedOption === 'your_customer_relationships') {
            // search only for the creators customer relationships
            $sql = <<<SQL
                SELECT DISTINCT cr.*
                FROM CustomerRelationships cr
                LEFT JOIN ExplorersAndCreators ec
                    ON cr.CompanyName = ec.idpk AND ec.ExplorerOrCreator = 1
                WHERE
                    cr.IdpkCreator = :user_id
                    AND (
                        cr.idpk LIKE :search OR
                        cr.FirstName LIKE :search OR
                        cr.LastName LIKE :search OR
                        cr.title LIKE :search OR
                        cr.email LIKE :search OR
                        cr.PhoneNumber LIKE :search OR
                        cr.LinksToSocialMediaAndOtherSites LIKE :search OR
                        cr.notes LIKE :search OR
                        cr.CompanyName LIKE :search OR
                        (
                            cr.CompanyName REGEXP '^[0-9]+$' AND
                            ec.CompanyName LIKE :search
                        )
                    );
                SQL;
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
            $sql = "
                SELECT t.*
                FROM transactions t
                LEFT JOIN ProductsAndServices ps ON t.IdpkProductOrService = ps.idpk
                LEFT JOIN ExplorersAndCreators ec1 ON t.IdpkExplorer = ec1.idpk
                LEFT JOIN ExplorersAndCreators ec2 ON ps.IdpkCreator = ec2.idpk
                WHERE 
                    (
                        t.IdpkExplorer = :user_id
                        OR ps.IdpkCreator = :user_id
                    )
                    AND
                    (
                        t.state >= 3
                    )
                    AND 
                    (
                        -- Match fields from transactions
                        t.idpk LIKE :search
                        OR t.IdpkProductOrService LIKE :search
                        OR t.IdpkCart LIKE :search
                        OR t.AmountInDollars LIKE :search
                        OR t.CommentsNotesSpecialRequests LIKE :search

                        -- Match fields from ProductsAndServices
                        OR ps.idpk LIKE :search
                        OR ps.KeywordsForSearch LIKE :search
                        OR ps.name LIKE :search
                        OR ps.ShortDescription LIKE :search
                        OR ps.LongDescription LIKE :search

                        -- Match fields from ExplorersAndCreators based on IdpkExplorer (ec1)
                        OR (
                            ec1.ExplorerOrCreator = 0
                            AND (
                                ec1.idpk LIKE :search
                                OR ec1.FirstName LIKE :search
                                OR ec1.LastName LIKE :search
                                OR ec1.street LIKE :search
                                OR ec1.ZIPCode LIKE :search
                                OR ec1.city LIKE :search
                                OR ec1.country LIKE :search
                                OR ec1.planet LIKE :search
                            )
                        )
                        OR (
                            ec1.ExplorerOrCreator = 1
                            AND (
                                ec1.idpk LIKE :search
                                OR ec1.CompanyName LIKE :search
                                OR ec1.VATID LIKE :search
                                OR ec1.street LIKE :search
                                OR ec1.ZIPCode LIKE :search
                                OR ec1.city LIKE :search
                                OR ec1.country LIKE :search
                                OR ec1.planet LIKE :search
                                OR ec1.PhoneNumberForExplorersAsContact LIKE :search
                                OR ec1.EmailForExplorersAsContact LIKE :search
                                OR ec1.ShortDescription LIKE :search
                                OR ec1.LongDescription LIKE :search
                            )
                        )

                        -- Match fields from ExplorersAndCreators based on IdpkCreator (ec2)
                        OR (
                            ec2.ExplorerOrCreator = 0
                            AND (
                                ec2.idpk LIKE :search
                                OR ec2.FirstName LIKE :search
                                OR ec2.LastName LIKE :search
                                OR ec2.street LIKE :search
                                OR ec2.ZIPCode LIKE :search
                                OR ec2.city LIKE :search
                                OR ec2.country LIKE :search
                                OR ec2.planet LIKE :search
                            )
                        )
                        OR (
                            ec2.ExplorerOrCreator = 1
                            AND (
                                ec2.idpk LIKE :search
                                OR ec2.CompanyName LIKE :search
                                OR ec2.VATID LIKE :search
                                OR ec2.street LIKE :search
                                OR ec2.ZIPCode LIKE :search
                                OR ec2.city LIKE :search
                                OR ec2.country LIKE :search
                                OR ec2.planet LIKE :search
                                OR ec2.PhoneNumberForExplorersAsContact LIKE :search
                                OR ec2.EmailForExplorersAsContact LIKE :search
                                OR ec2.ShortDescription LIKE :search
                                OR ec2.LongDescription LIKE :search
                            )
                        )
                    )
                ORDER BY t.idpk DESC
                ";
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } elseif ($preselectedOption === 'carts') {
            // search only for the creators carts
            $sql = "
                SELECT DISTINCT c.*
                FROM carts c
                LEFT JOIN transactions t ON t.IdpkCart = c.idpk
                LEFT JOIN ProductsAndServices ps ON ps.idpk = t.IdpkProductOrService
                -- Explorer/Creator references for carts, transactions, and products/services
                LEFT JOIN ExplorersAndCreators ecCart ON ecCart.idpk = c.IdpkExplorerOrCreator
                LEFT JOIN ExplorersAndCreators ecTrans ON ecTrans.idpk = t.IdpkExplorer
                LEFT JOIN ExplorersAndCreators ecPs ON ecPs.idpk = ps.IdpkCreator
                WHERE
                    (
                        c.IdpkExplorerOrCreator = :user_id
                        OR ps.IdpkCreator = :user_id
                    )
                    AND
                    (
                        -- Match in carts:
                        c.idpk LIKE :search
                        OR c.IfManualFurtherInformation LIKE :search
                        OR c.WishedIdealDeliveryOrPickUpTime LIKE :search

                        -- Match in transactions:
                        OR t.idpk LIKE :search
                        OR t.IdpkProductOrService LIKE :search
                        OR t.IdpkCart LIKE :search
                        OR t.AmountInDollars LIKE :search
                        OR t.CommentsNotesSpecialRequests LIKE :search

                        -- Match in ProductsAndServices:
                        OR ps.idpk LIKE :search
                        OR ps.KeywordsForSearch LIKE :search
                        OR ps.name LIKE :search
                        OR ps.ShortDescription LIKE :search
                        OR ps.LongDescription LIKE :search

                        -- Match in ExplorersAndCreators from carts (ecCart):
                        OR (
                            ecCart.ExplorerOrCreator = 0
                            AND (
                                ecCart.idpk LIKE :search
                                OR ecCart.FirstName LIKE :search
                                OR ecCart.LastName LIKE :search
                                OR ecCart.street LIKE :search
                                OR ecCart.ZIPCode LIKE :search
                                OR ecCart.city LIKE :search
                                OR ecCart.country LIKE :search
                                OR ecCart.planet LIKE :search
                            )
                        )
                        OR (
                            ecCart.ExplorerOrCreator = 1
                            AND (
                                ecCart.idpk LIKE :search
                                OR ecCart.CompanyName LIKE :search
                                OR ecCart.VATID LIKE :search
                                OR ecCart.street LIKE :search
                                OR ecCart.ZIPCode LIKE :search
                                OR ecCart.city LIKE :search
                                OR ecCart.country LIKE :search
                                OR ecCart.planet LIKE :search
                                OR ecCart.PhoneNumberForExplorersAsContact LIKE :search
                                OR ecCart.EmailForExplorersAsContact LIKE :search
                                OR ecCart.ShortDescription LIKE :search
                                OR ecCart.LongDescription LIKE :search
                            )
                        )

                        -- Match in ExplorersAndCreators from transactions (ecTrans) if needed:
                        OR (
                            ecTrans.ExplorerOrCreator = 0
                            AND (
                                ecTrans.idpk LIKE :search
                                OR ecTrans.FirstName LIKE :search
                                OR ecTrans.LastName LIKE :search
                                OR ecTrans.street LIKE :search
                                OR ecTrans.ZIPCode LIKE :search
                                OR ecTrans.city LIKE :search
                                OR ecTrans.country LIKE :search
                                OR ecTrans.planet LIKE :search
                            )
                        )
                        OR (
                            ecTrans.ExplorerOrCreator = 1
                            AND (
                                ecTrans.idpk LIKE :search
                                OR ecTrans.CompanyName LIKE :search
                                OR ecTrans.VATID LIKE :search
                                OR ecTrans.street LIKE :search
                                OR ecTrans.ZIPCode LIKE :search
                                OR ecTrans.city LIKE :search
                                OR ecTrans.country LIKE :search
                                OR ecTrans.planet LIKE :search
                                OR ecTrans.PhoneNumberForExplorersAsContact LIKE :search
                                OR ecTrans.EmailForExplorersAsContact LIKE :search
                                OR ecTrans.ShortDescription LIKE :search
                                OR ecTrans.LongDescription LIKE :search
                            )
                        )
                        
                        -- Match in ExplorersAndCreators from ProductsAndServices (ecPs):
                        OR (
                            ecPs.ExplorerOrCreator = 0
                            AND (
                                ecPs.idpk LIKE :search
                                OR ecPs.FirstName LIKE :search
                                OR ecPs.LastName LIKE :search
                                OR ecPs.street LIKE :search
                                OR ecPs.ZIPCode LIKE :search
                                OR ecPs.city LIKE :search
                                OR ecPs.country LIKE :search
                                OR ecPs.planet LIKE :search
                            )
                        )
                        OR (
                            ecPs.ExplorerOrCreator = 1
                            AND (
                                ecPs.idpk LIKE :search
                                OR ecPs.CompanyName LIKE :search
                                OR ecPs.VATID LIKE :search
                                OR ecPs.street LIKE :search
                                OR ecPs.ZIPCode LIKE :search
                                OR ecPs.city LIKE :search
                                OR ecPs.country LIKE :search
                                OR ecPs.planet LIKE :search
                                OR ecPs.PhoneNumberForExplorersAsContact LIKE :search
                                OR ecPs.EmailForExplorersAsContact LIKE :search
                                OR ecPs.ShortDescription LIKE :search
                                OR ecPs.LongDescription LIKE :search
                            )
                        )
                    )
                ORDER BY c.TimestampCreation DESC
                ";
            $stmt = $pdo->prepare($sql);
            
            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } else {
            // general search for all active products and services
            // $sql = "SELECT * FROM ProductsAndServices WHERE OnlyForInternalPurposes = 0 AND state = 1 AND (idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search)";
            // $sql = "SELECT * FROM ProductsAndServices 
            //             WHERE state = 1 
            //               AND (
            //                   (OnlyForInternalPurposes = 0) 
            //                   OR (OnlyForInternalPurposes = 1 AND IdpkCreator = :user_id)
            //               )
            //               AND (
            //                   idpk LIKE :search 
            //                   OR KeywordsForSearch LIKE :search 
            //                   OR name LIKE :search 
            //                   OR ShortDescription LIKE :search 
            //                   OR LongDescription LIKE :search
            //               )";
            $sql = "
                (
                    SELECT * 
                    FROM ProductsAndServices 
                    WHERE state = 1 
                      AND (
                          (OnlyForInternalPurposes = 0) 
                          OR (OnlyForInternalPurposes = 1 AND IdpkCreator = :user_id)
                      )
                      AND (
                          idpk LIKE :search 
                          OR KeywordsForSearch LIKE :search 
                          OR name LIKE :search 
                          OR ShortDescription LIKE :search 
                          OR LongDescription LIKE :search
                      )
                )
                UNION
                (
                    SELECT * 
                    FROM ProductsAndServices 
                    WHERE state = 0 
                      AND IdpkCreator = :user_id
                      AND (
                          idpk LIKE :search 
                          OR KeywordsForSearch LIKE :search 
                          OR name LIKE :search 
                          OR ShortDescription LIKE :search 
                          OR LongDescription LIKE :search
                      )
                )
                ORDER BY state DESC";
            $stmt = $pdo->prepare($sql);

            // Bind the :user_id parameter
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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
                echo "<table style='width: 100%; text-align: left;'>";
                if ($preselectedViewing === "manage_inventory") {
                    // Use displayProductRowManageInventory
                    displayProductRowManageInventory($exactMatch, true, $user_id, $userRole); // Highlight the exact match
                } elseif ($preselectedViewing === "manual_selling") {
                    // Use displayProductRowManualSelling
                    displayProductRowManualSelling($exactMatch, true, $user_id, $userRole); // Highlight the exact match
                } elseif ($preselectedViewing === "manage_customer_relationships") {
                    // Use displayCustomerRelationshipsRowManageCustomerRelationships
                    displayCustomerRelationshipsRowManageCustomerRelationships($exactMatch, true, $user_id, $userRole, $profilePictograms, $senderName); // Highlight the exact match
                } elseif ($preselectedOption === "creators_explorers" || $preselectedOption === "your_explorers_customers" || $preselectedOption === "your_creators_suppliers") {
                    // Use displayCreatorsAndExplorersRow
                    displayCreatorsAndExplorersRow($exactMatch, true, $user_id, $userRole); // Highlight the exact match
                } elseif ($preselectedOption === "your_customer_relationships") {
                    // Use displayCustomerRelationshipsRow
                    displayCustomerRelationshipsRow($exactMatch, true, $user_id, $userRole, $profilePictograms, $senderName); // Highlight the exact match
                } elseif ($preselectedOption === "transactions") {
                    // Use displayTransactionsRow
                    displayTransactionsRow($exactMatch, true, $user_id, $userRole); // Highlight the exact match
                } elseif ($preselectedOption === "carts") {
                    // Use displayCartsRow
                    displayCartsRow($exactMatch, true, $user_id, $userRole); // Highlight the exact match
                } else {
                    // Default to displayProductRow
                    displayProductRow($exactMatch, true, $user_id, $userRole, $ContributionForTRAMANNPORT); // Highlight the exact match
                }
                echo '</table>';
            echo "</div>";
            echo "<br><br><br><br><br>"; // Five additional line breaks
        }

        // Now display the other results
        echo "<table style='width: 100%; text-align: left;'>"; // Open the table for other results
        foreach ($otherResults as $product) {
            if ($preselectedViewing === "manage_inventory") {
                // Use displayProductRowManageInventory
                displayProductRowManageInventory($product, false, $user_id, $userRole);
            } elseif ($preselectedViewing === "manual_selling") {
                // Use displayProductRowManualSelling
                displayProductRowManualSelling($product, false, $user_id, $userRole);
            } elseif ($preselectedViewing === "manage_customer_relationships") {
                // Use displayCustomerRelationshipsRowManageCustomerRelationships
                displayCustomerRelationshipsRowManageCustomerRelationships($product, false, $user_id, $userRole, $profilePictograms, $senderName);
            } elseif ($preselectedOption === "creators_explorers" || $preselectedOption === "your_explorers_customers" || $preselectedOption === "your_creators_suppliers") {
                // Use displayCreatorsAndExplorersRow
                displayCreatorsAndExplorersRow($product, false, $user_id, $userRole);
            } elseif ($preselectedOption === "your_customer_relationships") {
                // Use displayCustomerRelationshipsRow
                displayCustomerRelationshipsRow($product, false, $user_id, $userRole, $profilePictograms, $senderName);
            } elseif ($preselectedOption === "transactions") {
                // Use displayTransactionsRow
                displayTransactionsRow($product, false, $user_id, $userRole);
            } elseif ($preselectedOption === "carts") {
                // Use displayCartsRow
                displayCartsRow($product, false, $user_id, $userRole);
            } else {
                // Default to displayProductRow
                displayProductRow($product, false, $user_id, $userRole, $ContributionForTRAMANNPORT);
            }
        }
        echo '</table>'; // Close the table

    } catch (PDOException $e) {
        // Handle SQL errors
        echo "Error in SQL execution: " . htmlspecialchars($e->getMessage());
    }
}
?>
