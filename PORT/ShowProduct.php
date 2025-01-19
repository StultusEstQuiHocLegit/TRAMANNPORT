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
</script>





























<?php
// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'ShowProduct' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Query the database to get product details
    $query = "SELECT * FROM ProductsAndServices 
          WHERE idpk = :idpk
          AND (state = 1 OR (state = 0 AND IdpkCreator = :user_id))";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['idpk' => $idpk, 'user_id' => $user_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    // Check if the product exists
    if ($product) {
        echo "<h3>$product[name] ($product[idpk])</h3>";
























        
        // Initialize the main image and thumbnail images array
        $uploadDir = "uploads/ProductPictures/" . htmlspecialchars($product['idpk']) . "_";
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $images = [];
        
        // Loop through images and store all existing images in $images array
        for ($i = 0; $i < 5; $i++) {
            foreach ($validExtensions as $extension) {
                $filePath = "{$uploadDir}{$i}.{$extension}";
                if (file_exists($filePath)) {
                    $images[] = $filePath;
                    break;
                }
            }
        }
        
        // Set the first image as the default main image
        $mainImage = $images[0] ?? null;
        
        // Display the main image and table in a container with controlled width
        if ($mainImage):
        ?>
            <div id="imageContainer" style="text-align: center; width: fit-content; margin: auto;">
                <!-- Main image display -->
                <img id="mainImage" src="<?= htmlspecialchars($mainImage); ?>" style="height: 500px; display: block; margin: auto;">
                
                <!-- Display thumbnails in a table with the same width as the main image -->
                <table style="width: 100%; margin-top: 10px;">
                    <tr>
                        <?php
                        // Calculate thumbnail width based on the number of images and main image width
                        $thumbnailWidth = 100 / count($images) . "%";
                        
                        // Loop through all images and display them as thumbnails
                        foreach ($images as $index => $thumb):
                            // Add an identifier to each thumbnail for JavaScript access
                            $isMain = ($thumb === $mainImage) ? " selected" : "";
                            echo "<td style='text-align: center; width: $thumbnailWidth;'>";
                            echo "<img id='thumb$index' src='" . htmlspecialchars($thumb) . "' class='thumbnail$isMain' style='height: 100px; cursor: pointer;' onclick='changeMainImage(this.src, $index)'>";
                            echo "</td>";
                        endforeach;
                        ?>
                    </tr>
                </table>
            </div>
        
            <!-- JavaScript to change main image when a thumbnail is clicked -->
            <script>
                function changeMainImage(selectedSrc, selectedIndex) {
                    // Update the main image with the selected thumbnail
                    const mainImage = document.getElementById('mainImage');
                    mainImage.src = selectedSrc;
        
                    // Remove 'selected' class from all thumbnails and add it to the current one
                    const thumbnails = document.querySelectorAll('.thumbnail');
                    thumbnails.forEach((thumb, index) => {
                        thumb.classList.remove('selected');
                        if (index === selectedIndex) {
                            thumb.classList.add('selected');
                        }
                    });
                }
            </script>
        
            <style>
                /* Thumbnail styling */
                .thumbnail {
                    border: 1px solid transparent; /* Default border */
                }
        
                /* Highlight selected thumbnail */
                .thumbnail.selected {
                    /* border: 1px solid yellow; */
                    opacity: 0.3;
                }
            </style>
        <?php
        endif;
        




















        echo "<br><br><br>";
        echo "$product[ShortDescription]";
        echo "<br><br><br><br>";
























        // Display the table with product details
        echo "<table style='width: 100%;'>";
        echo "<tr>";
        // Top left cell: Selling price and packaging/shipping price
            // Function to format the shipping price
            function formatShippingPriceForDisplay($shippingPrice) {
                return (!empty($shippingPrice) && $shippingPrice != 0) ? "(+$shippingPrice\$)" : '';
            }
            $shippingPrice = formatShippingPriceForDisplay($product['SellingPricePackagingAndShippingInDollars']);

            // Helper function to safely round values
            if (!function_exists('safe_round')) {
                function safe_round($value, $precision = 2) {
                    // return is_numeric($value) ? round($value, $precision) : 0;
                    return number_format((float) $value, $precision, '.', '');
                }
            }
            
        echo "<td style='text-align: center;'>";
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

        // Top right cell: Buy/Edit button
        echo "<td style='text-align: center;'>";
        if ($canManage) {
            echo "<a href='index.php?content=products.php&action=update&idpk=" . htmlspecialchars($product['idpk']) . "' class='mainbutton'>✏️ EDIT</a>";
        } else {
            echo "<a href='index.php?content=explore.php' onclick='addToCartGlow(event, {$product['idpk']})' class='mainbutton'>🛒 ADD TO CART</a>";
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr>";



        // Bottom left cell: Creator ID
        // Assuming $product['IdpkCreator'] contains the creator's ID
        $creatorId = $product['IdpkCreator'];

        // Query to get CompanyName and level from ExplorersAndCreators
        $query = "SELECT CompanyName, level FROM ExplorersAndCreators WHERE idpk = :creatorId LIMIT 1";
        $stmt = $pdo->prepare($query); // Assuming $pdo is your database connection
        $stmt->bindParam(':creatorId', $creatorId, PDO::PARAM_INT);
        $stmt->execute();
        $creator = $stmt->fetch(PDO::FETCH_ASSOC);

        // Define levels and their descriptions
        $levelDescriptions = [
            0 => "new",
            1 => "experienced",
            2 => "experts",
            3 => "checked experts",
            4 => "official partners"
        ];

        // Display the creator information in the table cell
        echo "<tr>";
        // Bottom left cell: CompanyName, Idpk, and level
        echo "<td style='text-align: center;'><a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk=" . $creatorId . "'>";

                // Define the possible image file extensions
                $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];

                // Base directory for profile pictures
                $uploadDir = './uploads/AccountPictures/';

                // Initialize a variable to hold the profile picture path (if found)
                $profilePicturePath = null;

                // Iterate through the possible extensions and check if the file exists
                foreach ($imageExtensions as $ext) {
                    $potentialPath = $uploadDir . $creatorId . '.' . $ext;
                    if (file_exists($potentialPath)) {
                        $profilePicturePath = $potentialPath;
                        break; // Exit the loop once we find the file
                    }
                }

                // Display the profile picture if it exists
                if ($profilePicturePath) {
                    // Output the image tag for the found profile picture
                    echo "<img src=\"$profilePicturePath\" style=\"height:50px;\"><br><br>";
                } else {
                    // If no profile picture is found, display nothing
                }

        // Display CompanyName and IdpkCreator
        echo htmlspecialchars($creator['CompanyName']) . " (" . htmlspecialchars($product['IdpkCreator']) . ")</a><br>";
        // Display level with description
        $level = $creator['level'];
        $levelText = isset($levelDescriptions[$level]) ? $levelDescriptions[$level] : "unknown";
        echo "<span style='opacity: 0.5;'>(level: $level ($levelText))</span>";

        echo "</td>";



        // Bottom right cell: Inventory information
        echo "<td style='text-align: center; opacity: 0.5;'>";
        if ($product['state'] == 0 || $product['state'] === null || $product['OnlyForInternalPurposes'] == 1) {
            if ($product['state'] == 0 || $product['state'] === null) {
                echo "inactive";
            }
            if (($product['state'] == 0 || $product['state'] === null) AND ($product['OnlyForInternalPurposes'] == 1)) {
                echo ", ";
            }
            if ($product['OnlyForInternalPurposes'] == 1) {
                echo "only for internal purposes";
            }
            echo "<br><br>";
        }
        if ($product['ManageInventory'] == 1) {
            echo ($product['InventoryAvailable'] > 0 ? "available: " . htmlspecialchars($product['InventoryAvailable']) : "can be produced") . "<br>";
            echo ($product['InventoryInProduction'] > 0 ? "in production or reordered: " . htmlspecialchars($product['InventoryInProduction']) : "can be produced");
        } else {
            echo "can be produced";
        }
                // Query the transactions table to count sales of the selected product
                $productId = $product['idpk'];
                $query = "SELECT COUNT(*) as sale_count FROM transactions WHERE IdpkProductOrService = :productId";
                $stmt = $pdo->prepare($query); // Assuming $pdo is your database connection
                $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
                $stmt->execute();
                $salesData = $stmt->fetch(PDO::FETCH_ASSOC);

                // Display the sales count if the product has been sold at least once
                if ($salesData && $salesData['sale_count'] > 0) {
                    echo "<br><br>already sold: " . htmlspecialchars($salesData['sale_count']);
                }
        echo "</td>";
        echo "</tr>";
        echo "</table>";

        echo "<br><div style='opacity: 0.5;'>";
            // Initialize an array to hold dimensions if they exist
            $details = [];
                
            // Add each dimension to the array if it exists, is not null, and is greater than 0
            if (!empty($product['WeightInKg']) && $product['WeightInKg'] > 0) {
                $details[] = "weight: {$product['WeightInKg']}kg";
            }
            if (!empty($product['DimensionsLengthInMm']) && $product['DimensionsLengthInMm'] > 0) {
                $details[] = "length: {$product['DimensionsLengthInMm']}mm";
            }
            if (!empty($product['DimensionsWidthInMm']) && $product['DimensionsWidthInMm'] > 0) {
                $details[] = "width: {$product['DimensionsWidthInMm']}mm";
            }
            if (!empty($product['DimensionsHeightInMm']) && $product['DimensionsHeightInMm'] > 0) {
                $details[] = "height: {$product['DimensionsHeightInMm']}mm";
            }
        
            // Output the details if any exist
            if (!empty($details)) {
                echo "(" . implode(", ", $details) . ")</div>";
            }
        echo "</div>";



















        echo "<br><br><br><br>";
        echo "$product[LongDescription]";

        echo "<br><br><br><br><br><br><br><br><br><br>";
    }
}


?>