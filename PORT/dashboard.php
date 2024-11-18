<?php
$user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

// Helper function to execute SQL queries
function fetchData($query) {
    global $pdo;  // Assuming $pdo is the database connection
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

// Get current timestamp
$current_timestamp = time();



// Calculate Revenue for Last 24 hours
$revenue_24h_query = "
    SELECT SUM(t.AmountInDollars) AS total_revenue
    FROM transactions t
    JOIN carts c ON t.IdpkCart = c.Idpk
    WHERE c.TimestampCreation > :time_24h
    AND t.IdpkProductOrService IN (
        SELECT Idpk FROM ProductsAndServices WHERE IdpkCreator = :user_id
    )
";
$stmt = $pdo->prepare($revenue_24h_query);
$stmt->execute([
    'time_24h' => $current_timestamp - 86400,  // 24 hours ago (86400 seconds)
    'user_id' => $user_id
]);
$revenue_24h = $stmt->fetchColumn();
$revenue_24h = $revenue_24h ? $revenue_24h : 0; // Default to 0 if null



// Calculate Revenue for Last 30 days
$revenue_30d_query = "
    SELECT SUM(t.AmountInDollars) AS total_revenue
    FROM transactions t
    JOIN carts c ON t.IdpkCart = c.Idpk
    WHERE c.TimestampCreation > :time_30d
    AND t.IdpkProductOrService IN (
        SELECT Idpk FROM ProductsAndServices WHERE IdpkCreator = :user_id
    )
";
$stmt = $pdo->prepare($revenue_30d_query);
$stmt->execute([
    'time_30d' => $current_timestamp - 2592000,  // 30 days ago (2592000 seconds)
    'user_id' => $user_id
]);
$revenue_30d = $stmt->fetchColumn();
$revenue_30d = $revenue_30d ? $revenue_30d : 0; // Default to 0 if null



// Calculate Revenue for Last 24 hours (Previous Period)
$previous_revenue_24h_query = "
    SELECT SUM(t.AmountInDollars) AS total_revenue
    FROM transactions t
    JOIN carts c ON t.IdpkCart = c.Idpk
    WHERE c.TimestampCreation BETWEEN :previous_time_24h_start AND :previous_time_24h_end
    AND t.IdpkProductOrService IN (
        SELECT Idpk FROM ProductsAndServices WHERE IdpkCreator = :user_id
    )
";
$stmt = $pdo->prepare($previous_revenue_24h_query);
$stmt->execute([
    'previous_time_24h_start' => $current_timestamp - 172800,  // 48 hours ago (172800 seconds)
    'previous_time_24h_end' => $current_timestamp - 86400,  // 24 hours ago
    'user_id' => $user_id
]);
$previous_revenue_24h = $stmt->fetchColumn();
$previous_revenue_24h = $previous_revenue_24h ? $previous_revenue_24h : 0; // Default to 0 if null



// Calculate Revenue for Last 30 days (Previous Period)
$previous_revenue_30d_query = "
    SELECT SUM(t.AmountInDollars) AS total_revenue
    FROM transactions t
    JOIN carts c ON t.IdpkCart = c.Idpk
    WHERE c.TimestampCreation BETWEEN :previous_time_30d_start AND :previous_time_30d_end
    AND t.IdpkProductOrService IN (
        SELECT Idpk FROM ProductsAndServices WHERE IdpkCreator = :user_id
    )
";
$stmt = $pdo->prepare($previous_revenue_30d_query);
$stmt->execute([
    'previous_time_30d_start' => $current_timestamp - 5184000,  // 60 days ago (5184000 seconds)
    'previous_time_30d_end' => $current_timestamp - 2592000,  // 30 days ago
    'user_id' => $user_id
]);
$previous_revenue_30d = $stmt->fetchColumn();
$previous_revenue_30d = $previous_revenue_30d ? $previous_revenue_30d : 0; // Default to 0 if null



// Calculate Explorers made happy in the last 30 days
$explorers_query = "
    SELECT COUNT(DISTINCT c.IdpkExplorerOrCreator) AS explorers_count
    FROM carts c
    WHERE c.TimestampCreation > :time_30d
    AND c.IdpkExplorerOrCreator != :user_id
";
$stmt = $pdo->prepare($explorers_query);
$stmt->execute([
    'time_30d' => $current_timestamp - 2592000,  // 30 days ago (2592000 seconds)
    'user_id' => $user_id
]);
$explorers_count = $stmt->fetchColumn();
$explorers_count = $explorers_count ? $explorers_count : 0; // Default to 0 if null

// Calculate Explorers made happy in the previous 30 days (60 to 30 days ago)
$previous_explorers_query = "
    SELECT COUNT(DISTINCT c.IdpkExplorerOrCreator) AS explorers_count
    FROM carts c
    WHERE c.TimestampCreation BETWEEN :previous_time_30d_start AND :previous_time_30d_end
    AND c.IdpkExplorerOrCreator != :user_id
";
$stmt = $pdo->prepare($previous_explorers_query);
$stmt->execute([
    'previous_time_30d_start' => $current_timestamp - 5184000,  // 60 days ago (5184000 seconds)
    'previous_time_30d_end' => $current_timestamp - 2592000,  // 30 days ago (2592000 seconds)
    'user_id' => $user_id
]);
$previous_explorers_count = $stmt->fetchColumn();
$previous_explorers_count = $previous_explorers_count ? $previous_explorers_count : 0; // Default to 0 if null

// Calculate Percentage Change for Explorers
$explorers_percentage = 0; // Default to 0 if no previous count
if ($previous_explorers_count > 0) {
    $explorers_percentage = (($explorers_count - $previous_explorers_count) / $previous_explorers_count) * 100;
}



// Calculate Percentage Changes
$revenue_24h_percentage = ($previous_revenue_24h > 0) ? (($revenue_24h - $previous_revenue_24h) / $previous_revenue_24h) * 100 : 0;
$revenue_30d_percentage = ($previous_revenue_30d > 0) ? (($revenue_30d - $previous_revenue_30d) / $previous_revenue_30d) * 100 : 0;

// Format as strings with "+" or "-" sign (rounded to the nearest whole number)
$revenue_24h_percentage = number_format($revenue_24h_percentage, 0) . "%";
$revenue_30d_percentage = number_format($revenue_30d_percentage, 0) . "%";
$explorers_percentage = number_format($explorers_percentage, 0) . "%";
?>


















<h1>⭐ DASHBOARD</h1>
<div class="stats-container">
    <div class="stat-item">
        <span class="stat-number"><?= $revenue_24h ?>$ (<?= $revenue_24h_percentage ?>)</span>
        <div style="opacity: 0.4;">revenue value created last 24h</div>
    </div>
    <div class="vertical-line"></div>
    <div class="stat-item">
        <span class="stat-number"><?= $revenue_30d ?>$ (<?= $revenue_30d_percentage ?>)</span>
        <div style="opacity: 0.4;">revenue value created last 30 days</div>
    </div>
    <div class="vertical-line"></div>
    <div class="stat-item">
        <span class="stat-number"><?= $explorers_count ?> (<?= $explorers_percentage ?>)</span>
        <div style="opacity: 0.4;">explorers made happy last 30 days</div>
    </div>
</div>




























<?php
// Fetch the newest orders with their associated details
$stmt = $pdo->prepare("
    SELECT 
        c.TimestampCreation AS CartTimestamp,
        c.idpk AS CartNumber,
        e.FirstName AS BuyerFirstName,
        e.LastName AS BuyerLastName,
        e.CompanyName AS CompanyName,
        e.idpk AS ExplorerOrCreatorId,  -- Add this line to get the idpk of Explorer/Creator
        p.name AS ProductName,
        t.quantity AS Quantity,
        t.AmountInDollars AS TotalAmount,
        t.CommentsNotesSpecialRequests AS Comments,
        c.DeliveryType AS DeliveryType,
        c.WishedIdealDeliveryOrPickUpTime AS WishedTime
    FROM transactions t
    INNER JOIN carts c ON t.IdpkCart = c.idpk
    INNER JOIN ExplorersAndCreators e ON c.IdpkExplorerOrCreator = e.idpk
    INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
    WHERE t.state = 3 OR t.state = 4 -- orders transmitted to creators OR creators producing or selecting
    ORDER BY c.TimestampCreation DESC
    LIMIT 8 -- Adjust this to show the number of orders you want
");
$stmt->execute();
$newestOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize order list output
$orderList = "";

// Check if there are any orders in the database
if (empty($newestOrders)) {
    $orderList = "There are no orders yet, scroll down to your to do list and pick some tasks or do more advertising."; // Display message if no orders are found
} else {
    // Set the total number of orders to display
    $totalOrders = 8;

    // Loop through each order and display the details with adjusted opacity behavior
    for ($i = 0; $i < $totalOrders; $i++) {
        $order = isset($newestOrders[$i]) ? $newestOrders[$i] : null; // Ensure $order is set
    
        if ($order === null) {
            continue; // Skip to the next iteration if order is not available
        }
    
        // Display buyer name (company or individual)
        $buyerName = isset($order['CompanyName']) && !empty($order['CompanyName']) ? $order['CompanyName'] . " (" . $order['ExplorerOrCreatorId'] . ")" : 
                     (isset($order['BuyerFirstName']) && isset($order['BuyerLastName']) ? $order['BuyerFirstName'] . " " . $order['BuyerLastName'] . " (" . $order['ExplorerOrCreatorId'] . ")" : 'Unknown Buyer');
        
        // Format delivery type
        $deliveryType = '';
        if (isset($order['DeliveryType'])) {
            switch ($order['DeliveryType']) {
                case 0: $deliveryType = 'standard'; break;
                case 1: $deliveryType = 'express'; break;
                case 2: $deliveryType = 'as soon as possible'; break;
                case 3: $deliveryType = 'pick up in store'; break;
                case 4: $deliveryType = 'best matching wished ideal delivery time'; break;
                default: $deliveryType = 'standard'; break;
            }
        }
    
        // Format delivery or pickup time
        $wishedDeliveryTime = isset($order['WishedTime']) && $order['WishedTime'] ? date('Y-m-d H:i:s', $order['WishedTime']) : 'Not specified';
    
        // Adjust opacity based on the order position
        if ($i < 6) {
            $opacity = 1.0; // Full opacity for the first 6 orders
        } elseif ($i < 7) {
            $opacity = 0.6; // Opacity for the 7th order
        } else {
            $opacity = 0.3; // Opacity for the 8th order
        }
    
        // Handle potential null value for TotalAmount
        $formattedAmount = isset($order['TotalAmount']) ? number_format($order['TotalAmount'], 2) : '0.00';
    
        // Build the order display
        $orderList .= "<div style=\"opacity: {$opacity};\">";
        $orderList .= htmlspecialchars($buyerName ?? 'Unknown Buyer') . " bought ";
        $orderList .= htmlspecialchars($order['ProductName'] ?? 'Unknown Product') . " (" . intval($order['Quantity']) . "x) ";
        $orderList .= "in cart " . intval($order['CartNumber']) . " on ";
        $orderList .= date('Y-m-d H:i:s', $order['CartTimestamp']) . " ";
        $orderList .= "for combined " . $formattedAmount . "$";
        
        // Check if comments exist before displaying them
        if (isset($order['Comments']) && !empty($order['Comments'])) {
            $orderList .= " [" . htmlspecialchars($order['Comments']) . "]";
        }
        
        $orderList .= ", delivery type: " . $deliveryType . "";
        
        // Check if wished delivery time exists before displaying
        if (isset($wishedDeliveryTime) && $wishedDeliveryTime !== 'Not specified') {
            $orderList .= " (" . $wishedDeliveryTime . ")";
        }
        
        $orderList .= "</div><br>";
    }

    // Add the "VIEW ALL ORDERS" link at the end
    $orderList .= '<a href="index.php?content=orders.php">📝 VIEW ALL ORDERS AND MANAGE THEM</a>';
}
?>



<br><br><br><br><br>
<h3>📝 NEWEST ORDERS</h3>
<!-- <div class="steps">
    MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere
    <br><br>MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere
    <br><br>MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere
    <br><br><div style="opacity: 0.6;">MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere</div>
    <br><div style="opacity: 0.3;">MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere</div>
    <br><a href="index.php?content=orders.php">VIEW ALL ORDERS</a>
</div> -->
<div class="steps">
    <?php echo $orderList; ?>
</div>







<br><br><br><br><br>
<h3>📋 TO DO LIST</h3>
<textarea id="PersonalToDoList" rows="16" style="width: 100%;" oninput="saveData('PersonalToDoList', this.value)"><?php echo htmlspecialchars(trim($user['PersonalToDoList'] ?? '')); ?></textarea>
<br>
<br>
<h3>🔗 COLLECTION OF LINKS</h3>
<!-- <textarea id="PersonalCollectionOfLinks" rows="10" style="width: 100%;" oninput="saveData('PersonalCollectionOfLinks', this.value)"><?php // echo htmlspecialchars(trim($user['PersonalCollectionOfLinks'] ?? '')); ?></textarea> -->
<!-- Hidden Textarea for Input -->
<!-- <div style="width: 50%; max-width: 50%;"> -->
<textarea id="PersonalCollectionOfLinks" rows="10" style="display: none; text-align: left;"
    oninput="updateDisplay(); saveData('PersonalCollectionOfLinks', this.value)">
<?php echo htmlspecialchars(trim($user['PersonalCollectionOfLinks'] ?? '')); ?>
</textarea>

<!-- Display Area for Clickable Links -->
<div id="displayLinks" style="white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; text-align: left;"
    onclick="handleDisplayClick(event)">
    <!-- Display will be dynamically updated here -->
</div>
<!-- </div> -->
<br>
<br>
<h3>📒 NOTES</h3>
<textarea id="PersonalNotes" rows="16" style="width: 100%;" oninput="saveData('PersonalNotes', this.value)"><?php echo htmlspecialchars(trim($user['PersonalNotes'] ?? '')); ?></textarea>
<br>
<br>
<h3>📊 STRATEGIC PLANNING NOTES</h3>
<textarea id="PersonalStrategicPlaningNotes" rows="10" style="width: 100%;" oninput="saveData('PersonalStrategicPlaningNotes', this.value)"><?php echo htmlspecialchars(trim($user['PersonalStrategicPlaningNotes'] ?? '')); ?></textarea>




<?php
echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
echo "<h3>🧭 MENU</h3>";
include ("menu.php"); // Include the menu
?>



























<script>
// // Function to update the display area with clickable shortened links
// function updateDisplay() {
//     const text = document.getElementById("PersonalCollectionOfLinks").value;
// 
//     // Replace URLs with clickable shortened links (first 30 characters) and add a 'link' class for easier targeting
//     const linkedText = text.replace(/(https?:\/\/[^\s]+)/g, function(url) {
//         const displayText = url.length > 30 ? url.substring(0, 30) + "..." : url;
//         return `<a href="${url}" target="_blank" class="link">${displayText}</a>`;
//     });
// 
//     // Display parsed content with clickable shortened links
//     document.getElementById("displayLinks").innerHTML = linkedText;
// }

// Function to update the display area with clickable links showing important parts
function updateDisplay() {
    const text = document.getElementById("PersonalCollectionOfLinks").value;

    // Regular expression to match valid domain names with optional paths, queries, and fragments
    const urlRegex = /(?:https?:\/\/)?(?:www\.)?([a-zA-Z0-9-]+\.[a-zA-Z]{2,})([\/\w\-\.?&=]*)/g;

    // Replace URLs with clickable links and extract important parts for display
    const linkedText = text.replace(urlRegex, function(match, hostname, pathname) {
        // Create the full URL for the link
        const fullUrl = `https://${hostname}${pathname}`;

        // Remove 'www.' if present
        const displayDomain = hostname.replace('www.', '').toUpperCase(); // Convert domain to uppercase

        // Remove TLDs from hostname
        const domainParts = displayDomain.split('.');
        const cleanDomain = domainParts.length > 1 ? domainParts.slice(0, -1).join('.') : displayDomain; // Join parts except last

        // Get the last part of the pathname for the page name
        const pathParts = pathname.split('/').filter(part => part); 
        const pageName = pathParts.length > 0 ? pathParts[pathParts.length - 1].split(/[?#]/)[0] : ''; // Get the last part without query or fragment

        // Handle the full path for display
        const limitedDomain = cleanDomain.length > 20 ? cleanDomain.substring(0, 20) + '...' : cleanDomain;
        const limitedPageName = pageName.length > 20 ? pageName.substring(0, 20) + '...' : pageName;

        // Convert page name to uppercase if present
        const displayText = pageName ? `${limitedDomain} (${limitedPageName.toUpperCase()})` : limitedDomain;

        return `<a href="${fullUrl}" target="_blank" class="link">${displayText}</a>`;
    });

    // Display parsed content with clickable links
    document.getElementById("displayLinks").innerHTML = linkedText;
}

// Toggle visibility to edit in the textarea
function editContent() {
    const displayDiv = document.getElementById("displayLinks");
    const textarea = document.getElementById("PersonalCollectionOfLinks");
    
    displayDiv.style.display = "none";
    textarea.style.display = "block";
    textarea.focus();
}

// Handle clicks within displayLinks div
function handleDisplayClick(event) {
    // Check if the clicked element is a link
    if (!event.target.classList.contains('link')) {
        // If not a link, enable edit mode
        editContent();
    }
}

// Close edit mode if clicking outside
function closeEditMode(event) {
    const displayDiv = document.getElementById("displayLinks");
    const textarea = document.getElementById("PersonalCollectionOfLinks");

    // Check if click is outside both the textarea and displayLinks
    if (!textarea.contains(event.target) && !displayDiv.contains(event.target)) {
        // Hide the textarea and show the displayLinks div
        textarea.style.display = "none";
        displayDiv.style.display = "block";
        
        // Update the display to reflect any changes
        updateDisplay();
    }
}

// Initial load to show the parsed content
updateDisplay();

// Event listener for detecting clicks outside the textarea to close edit mode
document.addEventListener("click", closeEditMode);






// JavaScript function to handle saving data via AJAX
function saveData(fieldName, value) {
    // Trim the value to remove any extra spaces or line breaks
    var trimmedValue = value.trim();

    // Create an AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'SaveDataDashboard.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Send the field name and the trimmed value to the server
    xhr.send('fieldName=' + encodeURIComponent(fieldName) + '&value=' + encodeURIComponent(trimmedValue));

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log('Data saved successfully for ' + fieldName);
        } else if (xhr.readyState == 4 && xhr.status != 200) {
            console.error('Failed to save data for ' + fieldName);
        }
    };
}
</script>

