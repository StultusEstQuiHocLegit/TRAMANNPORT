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


















<h1>⭐ DASHBOARD <span id="additionalIcon"></span></h1>
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
        e.idpk AS ExplorerOrCreatorId,
        p.name AS ProductName,
        p.idpk AS ProductIdpk,
        t.quantity AS Quantity,
        t.AmountInDollars AS TotalAmount,
        t.CommentsNotesSpecialRequests AS Comments,
        c.manual AS manual,
        c.IfManualFurtherInformation AS IfManualFurtherInformation,
        c.DeliveryType AS DeliveryType,
        c.WishedIdealDeliveryOrPickUpTime AS WishedTime
    FROM transactions t
    INNER JOIN carts c ON t.IdpkCart = c.idpk
    LEFT JOIN ExplorersAndCreators e ON c.IdpkExplorerOrCreator = e.idpk
    INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
    WHERE (p.IdpkCreator = :user_id AND (t.state = 3 OR t.state = 4))
    ORDER BY c.TimestampCreation DESC
    LIMIT 5
");
$stmt->execute(['user_id' => $user_id]);
$newestOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize order list output
$orderList = "";

// Check if there are any orders in the database
if (empty($newestOrders)) {
    $orderList = "<div style=\"opacity: 0.4;\">There are no orders yet, you can scroll down to your to do list and pick some tasks from there.</div>"; // Display message if no orders are found
    $orderList .= '<br><a href="index.php?content=orders.php">📝 VIEW ALL ORDERS AND MANAGE THEM</a>';
} else {
    // Set the total number of orders to display
    $totalOrders = 5;

    // Loop through each order and display the details with adjusted opacity behavior
    for ($i = 0; $i < $totalOrders; $i++) {
        $order = isset($newestOrders[$i]) ? $newestOrders[$i] : null; // Ensure $order is set
    
        if ($order === null) {
            continue; // Skip to the next iteration if order is not available
        }
    
        // Display buyer name (company or individual)
        // $buyerName = isset($order['CompanyName']) && !empty($order['CompanyName']) ? $order['CompanyName'] . " (" . $order['ExplorerOrCreatorId'] . ")" : 
                    //  (isset($order['BuyerFirstName']) && isset($order['BuyerLastName']) ? $order['BuyerFirstName'] . " " . $order['BuyerLastName'] . " (" . $order['ExplorerOrCreatorId'] . ")" : 'manual');
        // Display buyer name (company, individual, or manual)
        $buyerName = isset($order['manual']) && $order['manual'] == 1 
        ? (strlen($order['IfManualFurtherInformation']) > 30 
            ? substr($order['IfManualFurtherInformation'], 0, 30) . "..." 
            : $order['IfManualFurtherInformation']) . " (manual)"
        : (isset($order['CompanyName']) && !empty($order['CompanyName']) 
            ? $order['CompanyName'] . " (" . $order['ExplorerOrCreatorId'] . ")" 
            : (isset($order['BuyerFirstName']) && isset($order['BuyerLastName']) 
                ? $order['BuyerFirstName'] . " " . $order['BuyerLastName'] . " (" . $order['ExplorerOrCreatorId'] . ")" 
                : 'manual'));
        
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
        if ($i < 3) {
            $opacity = 1.0; // Full opacity for the first 6 orders
        } elseif ($i < 4) {
            $opacity = 0.6; // Opacity for the 7th order
        } else {
            $opacity = 0.3; // Opacity for the 8th order
        }
    
        // Handle potential null value for TotalAmount
        $formattedAmount = isset($order['TotalAmount']) ? number_format($order['TotalAmount'], 2) : '0.00';
    
        // Build the order display
        $orderList .= "<div style=\"opacity: {$opacity};\">";
        $orderList .= htmlspecialchars($buyerName ?? 'unknown') . " bought ";
            $productName = htmlspecialchars($order['ProductName'] ?? 'unknown');
            $truncatedName = strlen($productName) > 50 ? substr($productName, 0, 47) . "..." : $productName;
        $orderList .= "<span title='{$productName} ({$order['ProductIdpk']})'>{$truncatedName} ({$order['ProductIdpk']}), quantity: " . intval($order['Quantity']) . ",</span> ";
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



<br><br>
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






<?php
// Fetch events from today, tomorrow, and the day after tomorrow
$stmt = $pdo->prepare("
    SELECT 
        EventName, 
        StartTime, 
        EndTime, 
        Location, 
        EventDescription,
        AllDay
    FROM CalendarEvents
    WHERE DATE(FROM_UNIXTIME(StartTime)) BETWEEN CURDATE() AND CURDATE() + INTERVAL 2 DAY
      AND IdpkExplorerOrCreator = :user_id
    ORDER BY StartTime ASC
");
$stmt->execute(['user_id' => $user_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize the event list output
$calendarList = "";

// Check if there are any events
if (empty($events)) {
    $calendarList = "<div style=\"opacity: 0.4;\">There are no upcoming calendar events within the next days.</div>"; // Message if no events are found
    $calendarList .= '<br><a href="index.php?content=calendar.php">🗓️ VIEW CALENDAR</a>';
} else {
    foreach ($events as $event) {
        // Determine the event date category: today, tomorrow, or the day after
        $eventDate = date('Y-m-d', $event['StartTime']);
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));

        if ($eventDate === $today) {
            $dateLabel = "today,";
            $opacity = 1.0;
        } elseif ($eventDate === $tomorrow) {
            $dateLabel = "tomorrow,";
            $opacity = 0.6;
        } else {
            $dateLabel = "the day after tomorrow,";
            $opacity = 0.3;
        }

        // Format start and end times (only if it's not an all-day event)
        $timeRange = "";
        if (empty($event['IsAllDay']) || $event['IsAllDay'] == 0) {
            $startTime = date('H:i', $event['StartTime']);
            $endTime = date('H:i', $event['EndTime']);
            $timeRange = " {$startTime}-{$endTime}";
        }

        // Format location (only show if it's not empty)
        $location = !empty($event['Location']) ? " ({$event['Location']})" : "";

        // Format description (truncate to 50 characters and add hover title for the full text)
        $description = htmlspecialchars($event['EventDescription'] ?? "no description");
        $truncatedDescription =  strlen($description) > 50 ? substr($description, 0, 47) . "..." : $description;

        // Build the event entry
        $calendarList .= "<div style=\"opacity: {$opacity};\">";
        $calendarList .= "{$dateLabel}{$timeRange}{$location}";
        $calendarList .= ", <span title=\"{$description}\">{$truncatedDescription}</span>";
        $calendarList .= "</div><br>";
    }

    // Add the "VIEW CALENDAR" link at the end
    $calendarList .= '<a href="index.php?content=calendar.php">🗓️ VIEW CALENDAR</a>';
}
?>






<br><br>
<h3>🗓️ UPCOMING CALENDAR EVENTS</h3>
<div class="steps">
    <?php echo $calendarList; ?>
</div>







<br><br>
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
<!-- <br> -->
<br>
<h3>📒 NOTES</h3>
<textarea id="PersonalNotes" rows="16" style="width: 100%;" oninput="saveData('PersonalNotes', this.value)"><?php echo htmlspecialchars(trim($user['PersonalNotes'] ?? '')); ?></textarea>
<br>
<br>
<h3>📊 STRATEGIC PLANNING NOTES</h3>
<textarea id="PersonalStrategicPlaningNotes" rows="10" style="width: 100%;" oninput="saveData('PersonalStrategicPlaningNotes', this.value)"><?php echo htmlspecialchars(trim($user['PersonalStrategicPlaningNotes'] ?? '')); ?></textarea>




<?php
echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
echo "<h3>🧭 MENU</h3>";
include ("menu.php"); // include the menu
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

























// Function to dynamically load an icon and tooltip based on the current date and time
function loadAdditionalIcon() {
    const iconElement = document.getElementById('additionalIcon');
    const currentDate = new Date();
    const hours = currentDate.getHours();
    const month = currentDate.getMonth() + 1; // JavaScript months are 0-based
    const day = currentDate.getDate();

    let iconHTML = '';
    let titleText = '';

    // Time-based icons
    if (hours >= 6 && hours < 9) {
        iconHTML = '☀️'; // Rising Sun
        titleText = 'good morning   : )';
    } else if (hours >= 21 || hours < 6) {
        iconHTML = '🌙'; // Moon
        titleText = 'good night   : )';
    } else {
        // Day-based themes
        const dateKey = `${month}-${day}`;
        const dayThemes = {
            '1-1': { icon: '🎉', title: 'happy new year   : )' },
            '1-2': { icon: '🚀', title: 'happy science fiction day   : )' },
            '1-6': { icon: '👑', title: 'happy epiphany   : )' },
            '1-14': { icon: '🪁', title: 'happy makar sankranti   : )' },
            '1-16': { icon: '🐉', title: 'happy appreciate a dragon day   : )' },
            '1-25': { icon: '🐇', title: 'happy lunar new year   : )' },
            '2-2': { icon: '🦫', title: 'happy groundhog day   : )' },
            '2-9': { icon: '🍕', title: 'happy national pizza day   : )' },
            '2-14': { icon: '❤️', title: 'happy valentine’s day   : )' },
            '2-20': { icon: '🪁', title: 'happy kite flying day   : )' },
            '3-14': { icon: '🥧', title: 'happy pi day   : )' },
            '3-17': { icon: '☘️', title: 'happy st. patrick’s day   : )' },
            '3-20': { icon: '🌞', title: 'happy first day of spring   : )' },
            '3-23': { icon: '🦉', title: 'happy world meteorological day   : )' },
            '4-1': { icon: '😂', title: 'happy april fools’ day   : )' },
            '4-20': { icon: '🍀', title: 'happy earth day eve   : )' },
            '4-22': { icon: '🔵', title: 'happy earth day   : )' },
            '4-26': { icon: '👽', title: 'happy alien day   : )' },
            '4-30': { icon: '🎷', title: 'happy international jazz day   : )' },
            '5-1': { icon: '🛠️', title: 'happy labor day   : )' },
            '5-4': { icon: '🌌', title: 'may the fourth be with you   : )' },
            '5-5': { icon: '🎉', title: 'happy cinco de mayo   : )' },
            '5-25': { icon: '🧻', title: 'happy towel day   : )' },
            '6-8': { icon: '🌊', title: 'happy world oceans day   : )' },
            '6-21': { icon: '☀️', title: 'happy summer solstice   : )' },
            '6-30': { icon: '☄️', title: 'happy asteroid day   : )' },
            '7-4': { icon: '🎆', title: 'happy independence day   : )' },
            '7-20': { icon: '🌕', title: 'happy moon landing day   : )' },
            '8-9': { icon: '📚', title: 'happy book lovers day   : )' },
            '8-12': { icon: '🌌', title: 'happy perseid meteor shower viewing day   : )' },
            '9-12': { icon: '💻', title: 'happy programmers’ day   : )' },
            '9-19': { icon: '🏴‍☠️', title: 'talk like a pirate day   : )' },
            '9-21': { icon: '🕊️', title: 'happy international day of peace   : )' },
            '10-1': { icon: '🎼', title: 'happy international music day   : )' },
            '10-4': { icon: '🐾', title: 'happy world animal day   : )' },
            '10-23': { icon: '⚗️', title: 'happy mole day   : )' },
            '10-31': { icon: '🎃', title: 'happy halloween   : )' },
            '11-1': { icon: '🕯️', title: 'happy all saints’ day   : )' },
            '11-07': { icon: '🎂', title: 'happy TRAMANN PORT first release day   : )' },
            '11-11': { icon: '🎖️', title: 'veterans day   : )' },
            '11-13': { icon: '🤝', title: 'happy world kindness day   : )' },
            '11-24': { icon: '🦃', title: 'happy thanksgiving   : )' },
            '12-1': { icon: '⛄️', title: 'happy first day of winter   : )' },
            '12-4': { icon: '🛰️', title: 'happy world space exploration day   : )' },
            '12-8': { icon: '⏳', title: 'happy pretend to be a time traveler day   : )' },
            '12-10': { icon: '📜', title: 'happy human rights day   : )' },
            '12-24': { icon: '🎄', title: 'merry christmas eve   : )' },
            '12-25': { icon: '🎁', title: 'merry christmas   : )' },
            '12-31': { icon: '🎆', title: 'happy new year’s eve   : )' },
        };

        // Default day-time icon
        const defaultTheme = { icon: '⭐', title: 'have a great day   : )' };

        const selectedTheme = dayThemes[dateKey] || defaultTheme;
        iconHTML = selectedTheme.icon;
        titleText = selectedTheme.title;
    }

    // Set the selected icon and tooltip into the element
    iconElement.innerHTML = iconHTML;
    iconElement.title = titleText;
}

// Run the function on page load
document.addEventListener('DOMContentLoaded', loadAdditionalIcon);
</script>
