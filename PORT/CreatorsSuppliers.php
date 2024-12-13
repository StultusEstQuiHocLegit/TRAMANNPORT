<h1>🏭 CREATORS (SUPPLIERS)</h1>

<?php
$preselectedOption = "your_creators_suppliers"; // add preselected search option

include ("explore.php"); // include explore.php for exploring and searching
echo "<br><br><br><br><br>";
?>




















<?php
$sql = <<<SQL
SELECT DISTINCT ec.*
FROM ExplorersAndCreators ec
INNER JOIN transactions t
    ON t.IdpkExplorer = :user_id
INNER JOIN ProductsAndServices ps
    ON ps.idpk = t.IdpkProductOrService
WHERE ps.IdpkCreator = ec.idpk
    AND (
        (ec.ExplorerOrCreator = 0) -- Explorer
        OR
        (ec.ExplorerOrCreator = 1) -- Creator
    )
ORDER BY 
    CASE
        WHEN ec.ExplorerOrCreator = 1 THEN ec.CompanyName
        ELSE ec.FirstName
    END,
    CASE
        WHEN ec.ExplorerOrCreator = 1 THEN NULL
        ELSE ec.LastName
    END;
SQL;
$stmt = $pdo->prepare($sql);

// Bind the :user_id parameter
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

// Execute the statement
$stmt->execute();





























// Div for listing all creators (suppliers)
echo "<div id=\"listCreatorsSuppliersDiv\" class=\"steps\">";

// Function to display a creators and explorers row
function displayCreatorsSuppliersRow($product, $highlight = false, $user_id = null) {
    $truncatedShortDescription = (strlen($product['ShortDescription']) > 100) ? substr($product['ShortDescription'], 0, 100) . '...' : $product['ShortDescription'];
    $address = "{$product['country']}, {$product['city']}, {$product['ZIPCode']}, {$product['street']} {$product['HouseNumber']}";

    echo "<tr>";

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


echo '<table>';
// Assuming $stmt is already executed and fetches all products
while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
    displayCreatorsSuppliersRow($product, false, $user_id);
}
echo '</table>';


echo "</div>";
?>