<?php
// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'ShowCustomerRelationships' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $idpk = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Access global $pdo if not already defined
    global $pdo;

    // perform a second query to fetch all the necessary details
    // we also add the user check conditions again for security
    // Query the database to get customer relationships details
    $query = "SELECT * FROM CustomerRelationships WHERE idpk = ? AND IdpkCreator = $user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idpk]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        // No customer relationship found or not accessible by this user
        return;
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

    // Fetch the ProfilePictogram value from $product
    $profilePictogramIndex = $product['ProfilePictogram'];

    // Get the matching emoji
    $profilePictogramEmoji = isset($profilePictograms[$profilePictogramIndex]) 
        ? $profilePictograms[$profilePictogramIndex] 
        : ''; // Default to nothing if the index is invalid




























    
    if (!empty($profilePictogramEmoji)) {
        echo "<span style='font-size: 5rem;'>{$profilePictogramEmoji}</span><br>";
    }
    echo "<h3>{$product['FirstName']} {$product['LastName']} ({$product['idpk']})</h3>";

                    echo "{$product['title']}";
                    if (!empty($product['CompanyName']) && !empty($product['title'])) {
                        echo " at ";
                    } elseif (!empty($product['CompanyName'])) {
                        echo "from ";
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
                echo "<br><br><br><br><br>";

                        // Determine recipient name
                        $recipientName = $product['FirstName'];
                        // Determine sender name
                        $senderName = $user['ExplorerOrCreator'] == 0 
                        ? "{$user['FirstName']} {$user['LastName']} ({$user['idpk']})" 
                        : "{$user['CompanyName']} ({$user['idpk']})";
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
                        echo "<span id=\"LinksToSocialMediaAndOtherSites\" name=\"LinksToSocialMediaAndOtherSites\" style=\"text-align: left;\">";
                    
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

                echo "<br><br><br><a href='index.php?content=CustomerRelationships.php&action=update&idpk=" . htmlspecialchars($product['idpk']) . "' class='mainbutton'>✏️ EDIT</a>";

                // echo "<br><br><br>({$product['state']}, {$product['importance']})";
                // Mappings for importance and state
                $importanceMap = [
                    0 => 'initial contact',
                    1 => 'emerging partner',
                    2 => 'partner',
                    3 => 'core partner',
                    4 => 'prime partner'
                ];
                $stateMap = [
                    0 => 'potential customer',
                    1 => 'existing customer',
                    2 => 'former customer'
                ];
                // Assuming $product['state'] and $product['importance'] contain the numeric values
                $stateDescription = $stateMap[$product['state']] ?? 'unknown';
                $importanceDescription = $importanceMap[$product['importance']] ?? 'unknown';
                // Output the mapped values
                echo "<br><br><br>({$stateDescription}, {$importanceDescription})";

                // echo "<br><span style=\"opacity: 0.8;\">{$product['email']} {$product['PhoneNumber']} {$product['LinksToSocialMediaAndOtherSites']}</span>";
                echo "<br><br><br><br><br>{$product['notes']}";

    

    echo "<br><br><br><br><br><br><br><br><br><br>";
}


?>