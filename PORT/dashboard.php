<h1>DASHBOARD</h1>
<div class="stats-container">
    <div class="stat-item">
        <span class="stat-number">20$ (+5%)</span>
        revenue today
    </div>
    <div class="vertical-line"></div>
    <div class="stat-item">
        <span class="stat-number">352$ (+11%)</span>
        revenue last 30 days
    </div>
    <div class="vertical-line"></div>
    <div class="stat-item">
        <span class="stat-number">15</span>
        creators this month
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
<textarea id="PersonalToDoList" rows="16" style="width: 100%;" oninput="saveData('PersonalToDoList', this.value)">
    <?php echo htmlspecialchars(trim($user['PersonalToDoList'] ?? '')); ?>
</textarea>
<br>
<br>
<h3>COLLECTION OF LINKS</h3>
<textarea id="PersonalCollectionOfLinks" rows="10" style="width: 100%;" oninput="saveData('PersonalCollectionOfLinks', this.value)">
    <?php echo htmlspecialchars(trim($user['PersonalCollectionOfLinks'] ?? '')); ?>
</textarea>
<br>
<br>
<h3>NOTES</h3>
<textarea id="PersonalNotes" rows="16" style="width: 100%;" oninput="saveData('PersonalNotes', this.value)">
    <?php echo htmlspecialchars(trim($user['PersonalNotes'] ?? '')); ?>
</textarea>
<br>
<br>
<h3>STRATEGIC PLANNING NOTES</h3>
<textarea id="PersonalStrategicPlaningNotes" rows="10" style="width: 100%;" oninput="saveData('PersonalStrategicPlaningNotes', this.value)">
    <?php echo htmlspecialchars(trim($user['PersonalStrategicPlaningNotes'] ?? '')); ?>
</textarea>


<script>
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

