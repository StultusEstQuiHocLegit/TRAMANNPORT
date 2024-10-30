<h1>DASHBOARD</h1>
<div class="stats-container">
    <div class="stat-item">
        <span class="stat-number">20$ (+5%)</span>
        <div style="opacity: 0.4;">revenue value created last 24h</div>
    </div>
    <div class="vertical-line"></div>
    <div class="stat-item">
        <span class="stat-number">352$ (+11%)</span>
        <div style="opacity: 0.4;">revenue value created last 30 days</div>
    </div>
    <div class="vertical-line"></div>
    <div class="stat-item">
        <span class="stat-number">15 (+3%)</span>
        <div style="opacity: 0.4;">explorers made happy last 30 days</div>
    </div>
</div>






<br><br><br><br><br>
<h3>NEWEST ORDERS</h3>
<div class="steps">
    MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere
    <br><br>MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere
    <br><br>MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere
    <br><br><div style="opacity: 0.6;">MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere</div>
    <br><div style="opacity: 0.3;">MaxMustermann bought ReallyGreatProduct (2x) in the cart CartNumber on the TimestampHere for XX$ with the following comments: CommentsHere</div>
    <br><a href="index.php?content=orders.php">VIEW ALL ORDERS</a>
</div>







<br><br><br><br><br>
<h3>TO DO LIST</h3>
<textarea id="PersonalToDoList" rows="16" style="width: 100%;" oninput="saveData('PersonalToDoList', this.value)"><?php echo htmlspecialchars(trim($user['PersonalToDoList'] ?? '')); ?></textarea>
<br>
<br>
<h3>COLLECTION OF LINKS</h3>
<!-- <textarea id="PersonalCollectionOfLinks" rows="10" style="width: 100%;" oninput="saveData('PersonalCollectionOfLinks', this.value)"><?php // echo htmlspecialchars(trim($user['PersonalCollectionOfLinks'] ?? '')); ?></textarea> -->
<!-- Hidden Textarea for Input -->
<!-- <div style="width: 50%; max-width: 50%;"> -->
<textarea id="PersonalCollectionOfLinks" rows="10" style="width: 100%; display: none; text-align: left;"
    oninput="updateDisplay(); saveData('PersonalCollectionOfLinks', this.value)">
<?php echo htmlspecialchars(trim($user['PersonalCollectionOfLinks'] ?? '')); ?>
</textarea>

<!-- Display Area for Clickable Links -->
<div id="displayLinks" style="width: 100%; white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; text-align: left;"
    onclick="handleDisplayClick(event)">
    <!-- Display will be dynamically updated here -->
</div>
<!-- </div> -->
<br>
<br>
<h3>NOTES</h3>
<textarea id="PersonalNotes" rows="16" style="width: 100%;" oninput="saveData('PersonalNotes', this.value)"><?php echo htmlspecialchars(trim($user['PersonalNotes'] ?? '')); ?></textarea>
<br>
<br>
<h3>STRATEGIC PLANNING NOTES</h3>
<textarea id="PersonalStrategicPlaningNotes" rows="10" style="width: 100%;" oninput="saveData('PersonalStrategicPlaningNotes', this.value)"><?php echo htmlspecialchars(trim($user['PersonalStrategicPlaningNotes'] ?? '')); ?></textarea>


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
        const displayDomain = hostname.replace('www.', '');

        // Remove TLDs from hostname
        const domainParts = displayDomain.split('.');
        const cleanDomain = domainParts.length > 1 ? domainParts.slice(0, -1).join('.') : displayDomain; // Join parts except last

        // Get the last part of the pathname for the page name
        const pathParts = pathname.split('/').filter(part => part); 
        const pageName = pathParts.length > 0 ? pathParts[pathParts.length - 1].split(/[?#]/)[0] : ''; // Get the last part without query or fragment

        // Handle the full path for display
        const limitedDomain = cleanDomain.length > 20 ? cleanDomain.substring(0, 20) + '...' : cleanDomain;
        const limitedPageName = pageName.length > 20 ? pageName.substring(0, 20) + '...' : pageName;

        // Create the display text
        const displayText = pageName ? `${limitedDomain} (${limitedPageName})` : limitedDomain;

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

