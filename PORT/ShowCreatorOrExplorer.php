<?php
// Function to truncate text based on specified limits
function truncateText($text, $limit) {
    return (strlen($text) > $limit) ? substr($text, 0, $limit) . '...' : $text;
}

// Function to format the shipping price
function formatShippingPrice($shippingPrice) {
    return (!empty($shippingPrice) && $shippingPrice != 0) ? "(+$shippingPrice\$)" : '';
}





// Function to display a product row
function displayCreatorOrExplorerProductRow($product, $highlight = false) {
    $truncatedName = truncateText($product['name'], 50);
    $truncatedDescription = truncateText($product['ShortDescription'], 100);
    $shippingPrice = formatShippingPrice($product['SellingPricePackagingAndShippingInDollars']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Determine if the user can manage this product
    $canManage = ($product['IdpkCreator'] == $user_id);

    // Reduce the opacity for inactive (0) products or services
    $rowOpacity = ($product['state'] == 0) ? '0.4' : '1.0';

    echo "<tr style='opacity: $rowOpacity;'>";
    if ($canManage) {
        echo "<td style='width: 1px; background-color: yellow;'></td>"; // Yellow block column
    } else {
        echo "<td></td>";
    }

    // Image handling
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
        echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'><img src=\"" . htmlspecialchars($imagePaths[0]) . "\" style=\"height:100px;\"></a></td>";
    } else {
        echo "<td></td>";
    }

    echo "<td title=\"" . htmlspecialchars($product['name']) . " ({$product['idpk']})\"><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>$truncatedName ({$product['idpk']})</a><br><div title=\"" . htmlspecialchars($product['ShortDescription']) . "\" style=\"opacity: 0.5;\">$truncatedDescription</div></td>";
    echo "<td>{$product['SellingPriceProductOrServiceInDollars']}$ $shippingPrice</td>";
    
    // Links
    // echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$product['idpk']}'>👁️ MORE</a></td>";
    if ($canManage) {
        echo "<td><a href='index.php?content=products.php&action=update&idpk={$product['idpk']}'>✏️ EDIT</a></td>";
    } else {
        echo "<td><a href='index.php?content=explore.php' onclick='addToCartGlow(event, {$product['idpk']})'>🛒 ADD TO CART</a></td>";
    }
    echo "</tr>";
}




















// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'ShowCreatorOrExplorerProductsAndServices' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);
    
    try {
        // Check if the user with this idpk is a Creator
        $query = "SELECT ExplorerOrCreator FROM ExplorersAndCreators WHERE idpk = :idpk";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify if the user exists and is a Creator (ExplorerOrCreator = 1)
        if ($user && intval($user['ExplorerOrCreator']) === 1) {
            // Fetch active Products and Services for the Creator
            $sql = "SELECT * 
                    FROM ProductsAndServices 
                    WHERE IdpkCreator = :idpk AND state = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch all matching records
            $productsAndServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($productsAndServices) {
                echo '<table>'; // Open the table for results
                
                foreach ($productsAndServices as $product) {
                    displayCreatorOrExplorerProductRow($product, false, $idpk);
                }

                echo '</table>'; // Close the table
            } else {
                echo "We are very sorry, but we couldn't find any active products or services from this creator.";
            }
        }
    } catch (PDOException $e) {
        // Handle database errors (e.g., log the error and display a user-friendly message)
    }






































// Check if action and idpk are set
} elseif (isset($_GET['action']) && $_GET['action'] === 'ShowCreatorOrExplorer' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);

    // Get user_id from cookies (if set)
    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Query the database to get ExplorersAndCreators details
    $query = "
        SELECT 
            ec.idpk, 
            ec.TimestampCreation, 
            ec.email, 
            ec.PhoneNumber, 
            ec.FirstName, 
            ec.LastName, 
            ec.street, 
            ec.HouseNumber, 
            ec.ZIPCode, 
            ec.city, 
            ec.country, 
            ec.planet, 
            ec.IBAN, 
            ec.ExplorerOrCreator, 
            ec.level, 
            ec.CompanyName, 
            ec.VATID, 
            ec.PhoneNumberForExplorersAsContact, 
            ec.EmailForExplorersAsContact, 
            ec.ShowAddressToExplorers, 
            ec.CanExplorersVisitYou, 
            ec.OpeningHoursMondayOpening, 
            ec.OpeningHoursMondayClosing, 
            ec.OpeningHoursTuesdayOpening, 
            ec.OpeningHoursTuesdayClosing, 
            ec.OpeningHoursWednesdayOpening, 
            ec.OpeningHoursWednesdayClosing, 
            ec.OpeningHoursThursdayOpening, 
            ec.OpeningHoursThursdayClosing, 
            ec.OpeningHoursFridayOpening, 
            ec.OpeningHoursFridayClosing, 
            ec.OpeningHoursSaturdayOpening, 
            ec.OpeningHoursSaturdayClosing, 
            ec.OpeningHoursSundayOpening, 
            ec.OpeningHoursSundayClosing, 
            ec.OpeningHoursNationalHolidaysOpening, 
            ec.OpeningHoursNationalHolidaysClosing, 
            ec.CloseOnlineShopIfPhysicalShopIsClosed, 
            ec.PhysicalShopClosedBecauseOfHolidaysClosing, 
            ec.PhysicalShopClosedBecauseOfHolidaysOpening, 
            ec.ShortDescription, 
            ec.LongDescription, 
            ec.LinksToSocialMediaAndOtherSites, 
            ec.Heading1, 
            ec.Text1, 
            ec.Heading2, 
            ec.Text2, 
            ec.Heading3, 
            ec.Text3, 
            ec.Heading4, 
            ec.Text4, 
            ec.Heading5, 
            ec.Text5, 
            ec.Heading6, 
            ec.Text6, 
            ec.Heading7, 
            ec.Text7, 
            ec.Heading8, 
            ec.Text8, 
            ec.Heading9, 
            ec.Text9, 
            ec.Heading10, 
            ec.Text10
        FROM ExplorersAndCreators AS ec
        LEFT JOIN transactions AS t ON t.IdpkExplorer = ec.idpk OR t.IdpkExplorer = :user_id
        LEFT JOIN ProductsAndServices AS ps ON ps.idpk = t.IdpkProductOrService
        WHERE ec.idpk = :idpk
        AND (
            ec.ExplorerOrCreator = 1  -- Creator
            OR (
                ec.ExplorerOrCreator = 0  -- Explorer
                AND EXISTS (
                    SELECT 1
                    FROM transactions AS t2
                    WHERE t2.IdpkExplorer = :idpk OR t2.IdpkExplorer = :user_id
                    AND t2.IdpkProductOrService = ps.idpk
                    AND (ps.IdpkCreator = :user_id OR ps.IdpkCreator = ec.idpk)
                )
            )
        )
    ";
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();
    
    // Fetch the result
    $ExplorersAndCreators = $stmt->fetch(PDO::FETCH_ASSOC);


























    // Check if the ExplorersAndCreators exists
    if ($ExplorersAndCreators) {

        // Get the idpk
        $idpk = htmlspecialchars($ExplorersAndCreators['idpk']);
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
            echo "<img src=\"$profilePicturePath\" style=\"height:200px;\"><br><br>";
        } else {
            // If no profile picture is found, display nothing
        }

        $address = "{$ExplorersAndCreators['country']}, {$ExplorersAndCreators['city']}, {$ExplorersAndCreators['ZIPCode']}, {$ExplorersAndCreators['street']} {$ExplorersAndCreators['HouseNumber']}";

        // Check if the ExplorerOrCreator value is 1 (Creator) or 0 (Explorer)
        if ($ExplorersAndCreators['ExplorerOrCreator'] == 1) {  // creator
            echo "<h3>{$ExplorersAndCreators['CompanyName']} ({$ExplorersAndCreators['idpk']})</h3>";
            echo "<br><strong>{$ExplorersAndCreators['ShortDescription']}</strong><br><br><br><br><br>";
            echo "<table>";
                echo "<tr>";
                    echo "<td>$address";
                        $levelDescription = $ExplorersAndCreators['level'] === 0 ? "new" : ($ExplorersAndCreators['level'] === 1 ? "experienced" : ($ExplorersAndCreators['level'] === 2 ? "experts" : ($ExplorersAndCreators['level'] === 3 ? "checked experts" : ($ExplorersAndCreators['level'] === 4 ? "official partners" : "unknown"))));
                        echo "<br><br>level: {$ExplorersAndCreators['level']} ($levelDescription)";
                        if (!empty($ExplorersAndCreators['VATID'])) {
                            echo "<br>VATID: {$ExplorersAndCreators['VATID']}";
                        }
                    echo "</td>";
                    echo "<td style='width: 20px;'></td>";
                        // Determine recipient name
                        $recipientName = $ExplorersAndCreators['ExplorerOrCreator'] == 0 
                        ? $ExplorersAndCreators['FirstName'] 
                        : '';
                        // Determine sender name
                        $senderName = $user['ExplorerOrCreator'] == 0 
                        ? "{$user['FirstName']} {$user['LastName']} ({$user['idpk']})" 
                        : "{$user['CompanyName']} ({$user['idpk']})";
                        // Prepare email subject and body
                        $emailSubject = "TRAMANN PORT - Hi from $senderName";
                        $emailBody = "Hi" . ($recipientName ? " $recipientName" : "") . ",\n\n\n[ContentOfYourMessage]\n\n\n\nSincerely yours,\n$senderName";
                        // URL-encode the subject and body
                        $emailLink = "mailto:{$ExplorersAndCreators['EmailForExplorersAsContact']}?subject=" . rawurlencode($emailSubject) . "&body=" . rawurlencode($emailBody);                        // Generate tel link
                        $telLink = "tel:{$ExplorersAndCreators['PhoneNumberForExplorersAsContact']}";
                    echo "<td><a href='$emailLink'>{$ExplorersAndCreators['EmailForExplorersAsContact']}</a><br><br><a href='$telLink'>{$ExplorersAndCreators['PhoneNumberForExplorersAsContact']}</a></td>";
                echo "</tr>";
            echo "</table>";

            // echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorerProductsAndServices&idpk={$ExplorersAndCreators['idpk']}' class='mainbutton'>👁️ OUR PRODUCTS AND SERVICES</a>";

            // Check if the user is a Creator and has entries in ProductsAndServices
            try {
                // Query to check if the user is a Creator and has active Products or Services
                $sql = "SELECT COUNT(*) AS productCount 
                        FROM ProductsAndServices 
                        WHERE IdpkCreator = :idpk AND state = 1";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idpk', $idpk, PDO::PARAM_INT);
                $stmt->execute();
            
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
                // Display the link if there are active Products or Services
                if ($result && intval($result['productCount']) > 0) {
                    echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorerProductsAndServices&idpk={$idpk}' class='mainbutton'>👁️ OUR PRODUCTS AND SERVICES</a>";
                }
            } catch (PDOException $e) {
                // Handle database errors (e.g., log the error and display a user-friendly message)
            }




            if ($ExplorersAndCreators['CanExplorersVisitYou'] == 1) {
                echo "<br><br><br>";
            
                // Extract relevant opening and closing time data
                $openingHours = [
                    'national holidays' => ['Opening' => 'OpeningHoursNationalHolidaysOpening', 'Closing' => 'OpeningHoursNationalHolidaysClosing'],
                    'monday' => ['Opening' => 'OpeningHoursMondayOpening', 'Closing' => 'OpeningHoursMondayClosing'],
                    'tuesday' => ['Opening' => 'OpeningHoursTuesdayOpening', 'Closing' => 'OpeningHoursTuesdayClosing'],
                    'wednesday' => ['Opening' => 'OpeningHoursWednesdayOpening', 'Closing' => 'OpeningHoursWednesdayClosing'],
                    'thursday' => ['Opening' => 'OpeningHoursThursdayOpening', 'Closing' => 'OpeningHoursThursdayClosing'],
                    'friday' => ['Opening' => 'OpeningHoursFridayOpening', 'Closing' => 'OpeningHoursFridayClosing'],
                    'saturday' => ['Opening' => 'OpeningHoursSaturdayOpening', 'Closing' => 'OpeningHoursSaturdayClosing'],
                    'sunday' => ['Opening' => 'OpeningHoursSundayOpening', 'Closing' => 'OpeningHoursSundayClosing'],
                ];
            
                // Check if holiday-specific closures exist
                $holidayOpening = $ExplorersAndCreators['PhysicalShopClosedBecauseOfHolidaysOpening'] ?? null;
                $holidayClosing = $ExplorersAndCreators['PhysicalShopClosedBecauseOfHolidaysClosing'] ?? null;
            
                // Display holiday-specific closures if values exist
                if ($holidayOpening && $holidayClosing) {
                    echo "<br><br><strong>🧳 PHYSICAL SHOP IS CLOSED FOR HOLIDAYS";
                    echo "<br>from: " . htmlspecialchars($holidayOpening);
                    echo " to: " . htmlspecialchars($holidayClosing) . "</strong>";
                }

                echo "<br><br><strong>🕒 OPENING HOURS</strong>";
            
                // Start the table
                echo "<br><br><table style=\"border-collapse: collapse;\">";
                echo "<tr><th></th><th>day</th><th></th><th>opening</th><th></th><th>closing</th></tr>";

                $currentDay = strtolower(date('l')); // Get the current day in full text format (e.g., "monday"), Convert the current day to lowercase
            
                // Loop through the days and populate the table
                foreach ($openingHours as $day => $times) {
                    $openingTime = htmlspecialchars($ExplorersAndCreators[$times['Opening']] ?? '');
                    $closingTime = htmlspecialchars($ExplorersAndCreators[$times['Closing']] ?? '');

                    // Add an arrow if the current day matches
                    $arrow = (trim($day) == trim($currentDay)) ? '➡️' : '';
            
                    // Only add rows for days with valid opening and closing times
                    if ($openingTime || $closingTime) {
                        echo "<tr><td>$arrow</td><td>$day</td><td width='30px'></td><td>$openingTime</td><td width='30px'></td><td>$closingTime</td></tr>";
                    }
                    
            
                    // Add extra space after national holidays
                    if ($day == 'national holidays') {
                        echo "<tr><td colspan=\"5\" style=\"height: 10px;\"></td></tr>";
                    }
                }
            
                // Close the table
                echo "</table>";
            }
            






            echo "<br><br><br><br>{$ExplorersAndCreators['LongDescription']}";



            $text = htmlspecialchars(trim($ExplorersAndCreators['LinksToSocialMediaAndOtherSites'] ?? ''));

            // Only process and display if there are links
            if (!empty($text)) {
                echo "<br><br><br><br><br><strong>🔗 LINKS</strong>";
                echo "<div id=\"LinksToSocialMediaAndOtherSites\" name=\"LinksToSocialMediaAndOtherSites\" style=\"text-align: left;\">";
            
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
                
                    return "<a href=\"$fullUrl\" target=\"_blank\" class=\"link\">$displayText</a><br>";
                }, $text);

                echo $formattedText;
                echo "</div>";
            }

















            // Define the base path for uploaded images
            $uploadDir = "uploads/CreatorWebsitePictures/";
            $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            // User ID
            $explorersAndCreatorsId = htmlspecialchars($ExplorersAndCreators['idpk']);

            // Flag to check if any website content exists
            $hasWebsiteContent = false;

            // Check for content in slots 1 to 10
            for ($i = 1; $i <= 10; $i++) {
                $heading = $ExplorersAndCreators['Heading' . $i] ?? null;
                $text = $ExplorersAndCreators['Text' . $i] ?? null;
            
                // Check if an image exists for this slot
                foreach ($validExtensions as $ext) {
                    $filePath = "{$uploadDir}{$explorersAndCreatorsId}_{$i}.{$ext}";
                    if (file_exists($filePath)) {
                        $hasWebsiteContent = true;
                        break 2; // Exit both loops if content is found
                    }
                }
            
                // Check for heading or text
                if ($heading || $text) {
                    $hasWebsiteContent = true;
                    break; // Exit loop if content is found
                }
            }

            // Display the website section only if content exists
            if ($hasWebsiteContent) {
                echo "<br><br><br><br><br>";
                echo "<br><br><br><br><br><h3>🌐 WEBSITE</h3><br><br>";
            
                // Loop through slots 1 to 10 to display content
                for ($i = 1; $i <= 10; $i++) {
                    $heading = $ExplorersAndCreators['Heading' . $i] ?? null;
                    $text = $ExplorersAndCreators['Text' . $i] ?? null;
                
                    // Check if an image exists for this slot
                    $imagePath = '';
                    foreach ($validExtensions as $ext) {
                        $filePath = "{$uploadDir}{$explorersAndCreatorsId}_{$i}.{$ext}";
                        if (file_exists($filePath)) {
                            $imagePath = $filePath;
                            break; // Stop checking other extensions
                        }
                    }
                
                    // Display content if available
                    if ($imagePath || $heading || $text) {
                        echo "<div>";
                        if ($imagePath) {
                            echo "<img src='$imagePath' style='width:100%;'><br><br>";
                        }
                    
                        if ($heading) {
                            echo "<h3>" . htmlspecialchars($heading) . "</h3>";
                        }
                    
                        if ($text) {
                            echo "" . nl2br(htmlspecialchars($text)) . "<br><br><br><br><br>";
                        }
                        echo "</div>";
                    }
                }
            }











            


























        } else { // explorer
            echo "<h3>{$ExplorersAndCreators['FirstName']} {$ExplorersAndCreators['LastName']} ({$ExplorersAndCreators['idpk']})</h3>";
            echo "<br><br><br><br><br>";
            echo "<table>";
                echo "<tr>";
                    echo "<td>$address";
                        $levelDescription = $ExplorersAndCreators['level'] === 0 ? "new" : ($ExplorersAndCreators['level'] === 1 ? "experienced" : ($ExplorersAndCreators['level'] === 2 ? "experts" : ($ExplorersAndCreators['level'] === 3 ? "checked experts" : ($ExplorersAndCreators['level'] === 4 ? "official partners" : "unknown"))));
                        echo "<br><br>level: {$ExplorersAndCreators['level']} ($levelDescription)";
                    echo "</td>";
                    echo "<td style='width: 20px;'></td>";
                        // Determine recipient name
                        $recipientName = $ExplorersAndCreators['ExplorerOrCreator'] == 0 
                        ? $ExplorersAndCreators['FirstName'] 
                        : '';
                        // Determine sender name
                        $senderName = $user['ExplorerOrCreator'] == 0 
                        ? "{$user['FirstName']} {$user['LastName']} ({$user['idpk']})" 
                        : "{$user['CompanyName']} ({$user['idpk']})";
                        // Prepare email subject and body
                        $emailSubject = "TRAMANN PORT - Hi from $senderName";
                        $emailBody = "Hi" . ($recipientName ? " $recipientName" : "") . ",\n\n\n[ContentOfYourMessage]\n\n\n\nSincerely yours,\n$senderName";
                        // URL-encode the subject and body
                        $emailLink = "mailto:{$ExplorersAndCreators['EmailForExplorersAsContact']}?subject=" . rawurlencode($emailSubject) . "&body=" . rawurlencode($emailBody);                        // Generate tel link
                        $telLink = "tel:{$ExplorersAndCreators['PhoneNumber']}";
                    echo "<td><a href='$emailLink'>{$ExplorersAndCreators['email']}</a><br><br><a href='$telLink'>{$ExplorersAndCreators['PhoneNumber']}</a></td>";
                echo "</tr>";
            echo "</table>";
        }
    }    

    echo "<br><br><br><br><br><br><br><br><br><br>";
}
?>
