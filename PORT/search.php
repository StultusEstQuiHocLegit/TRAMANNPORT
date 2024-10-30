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

// Function to display a product row
function displayProductRow($product, $highlight = false, $user_id = null) {
    $truncatedName = truncateText($product['name'], 50);
    $truncatedDescription = truncateText($product['ShortDescription'], 100);
    $shippingPrice = formatShippingPrice($product['SellingPricePackagingAndShippingInDollars']);
    
    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    echo "<tr>";
    // add yellow block
    if ($canManage) {
        echo "<td style='width: 1px; background-color: yellow;'></td>"; // Yellow block column
    } else {
        echo "<td></td>";
    }
    echo "<td>$truncatedName ({$product['idpk']})</td>";
    echo "<td>$truncatedDescription</td>";
    echo "<td>{$product['SellingPriceProductOrServiceInDollars']}$</td>";
    echo "<td>$shippingPrice</td>";
    
    // Links
    echo "<td><a href='index.php?content=ShowProduct.php&idpk={$product['idpk']}'>more</a></td>";
    if ($canManage) {
        echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>edit</a></td>";  // manage link
    }
    echo "</tr>";
}

if (isset($_POST['query'])) {
    $searchQuery = $_POST['query'];

    // Prepare and sanitize the input
    $searchQuery = trim($searchQuery);
    $searchQuery = htmlspecialchars($searchQuery);

    try {
        // Prepare the SQL statement with a condition for active state
        $sql = "SELECT * FROM ProductsAndServices WHERE state = 1 AND (idpk LIKE :search OR KeywordsForSearch LIKE :search OR name LIKE :search OR ShortDescription LIKE :search OR LongDescription LIKE :search)";
        $stmt = $pdo->prepare($sql);

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
            echo "<div style='border: 1px solid white;'>";
                echo '<table>';
                    displayProductRow($exactMatch, true, $user_id); // Highlight the exact match
                echo '</table>';
            echo "</div>";
            echo "<br><br><br><br><br>"; // Five additional line breaks
        }

        // Now display the other results
        echo '<table>'; // Open the table for other results
        foreach ($otherResults as $product) {
            displayProductRow($product, false, $user_id);
        }
        echo '</table>'; // Close the table

    } catch (PDOException $e) {
        // Handle SQL errors
        echo "Error in SQL execution: " . htmlspecialchars($e->getMessage());
    }
}
?>
