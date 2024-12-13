<?php
// Query to check if there are any pending transactions (state = 0) for the user
$query = "SELECT COUNT(*) FROM transactions WHERE IdpkExplorer = :user_id AND state = 0";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// Get the result (number of pending transactions)
$pendingTransactions = $stmt->fetchColumn();

// Conditionally apply inline CSS for the "SHOPPING CART" link and the "SOURCING CART" link
// $borderStyle = ($pendingTransactions > 0) ? 'border: 3px solid yellow; padding: 2px;' : '';
// $borderStyle = ($pendingTransactions > 0) ? ';' : '';
$borderStyle = ($pendingTransactions > 0) ? 'background-color: #505050; padding: 5px;' : '';

// Conditionally display the number of pending transactions
$pendingText = ($pendingTransactions > 0) ? "($pendingTransactions)" : '';















if ($isLoggedIn && $userRole !== null) {
    // User is logged in
    if ($userRole === 0) {
        // Show Explorer links
        echo "<br><a href=\"index.php?content=explore.php\" title=\"your main page, search for interesting things in TRAMANN PORT\">⭐ EXPLORE</a>";
        echo "<br>";
        // echo "<br><a href=\"index.php?content=cart.php\">SHOPPING CART</a>";
        echo "<br><a href=\"index.php?content=cart.php\" style=\"$borderStyle\" title=\"manage your current purchase and make the order\">🛒 SHOPPING CART $pendingText</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=PreviousCarts.php\" title=\"see things you already bought earlier (and if wanted, you can repick them too)\">🛍️ PREVIOUS CARTS</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=help.php\" title=\"learn more about TRAMANN PORT, how it works, get tips for your business and more information, if you want to contribute to our open source code\">📖 HEEELP!</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=account.php\" title=\"manage your account and your settings (of course we also got a darkmode   ; ) )\">⚙️ ACCOUNT</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=calendar.php\" title=\"plan your path to world domination, muhahaha\">🗓️ CALENDAR</a>";
    } elseif ($userRole === 1) {
        // Show Creator links
        echo "<br><a href=\"index.php?content=dashboard.php\" title=\"main page, get an overview about your business and manage your notes\">⭐ DASHBOARD</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=orders.php\" title=\"manage the orders that explorers (or other creators too) have submitted to you\">📝 ORDERS</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=ManualSelling.php\" title=\"directly save a manual sale for explorers (customers) outside our systeme where you already got the money\">👉 MANUAL SELLING</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=inventory.php\" title=\"manage your inventory, how much you have in stock\">🏰 INVENTORY</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=products.php\" title=\"create and manage the products and services you want to offer\">📦 PRODUCTS AND SERVICES</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=ExplorersCustomers.php\" title=\"list of all those who have already bought something from you\">👥 EXPLORERS (CUSTOMERS)</a>";
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=CreatorsSuppliers.php\" title=\"list of all those from whom you have already bought something\">🏭 CREATORS (SUPPLIERS)</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=explore.php\" title=\"search for interesting things in TRAMANN PORT\">🔍 EXPLORE</a>";
        echo "<br>";
        // echo "<br><a href=\"index.php?content=cart.php\">SOURCING CART</a>";
        echo "<br><a href=\"index.php?content=cart.php\" style=\"$borderStyle\" title=\"manage your current purchase and make the order\">🛒 SOURCING CART $pendingText</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=PreviousCarts.php\" title=\"see things you already bought earlier\">🛍️ PREVIOUS CARTS</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=ManualBuying.php\" title=\"directly save a manual purchase for creators (suppliers) outside our systeme where you already paid the money\">👈 MANUAL BUYING</a>";
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=accounting.php\" title=\"hack yourself through the numbers, see metrics and statistics that can help you running and improving your business\">🧮 ACCOUNTING</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=help.php\" title=\"learn more about TRAMANN PORT, how it works, get tips for your business and more information, if you want to contribute to our open source code\">📖 HEEELP!</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=account.php\" title=\"manage your account and your settings (of course we also got a darkmode   ; ) )\">⚙️ ACCOUNT</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=calendar.php\" title=\"plan your path to world domination, muhahaha\">🗓️ CALENDAR</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=YourWebsite.php\" title=\"claim that land: here you can create a subpage on our site to give explorers a place to learn more about your business\">🌐 YOUR WEBSITE</a>";
    }
} else {
    // User is not logged in
    echo "<br><a href=\"index.php?content=login.php\" title=\"come on board, we got cookies\">🗝️ COME ON BOARD</a>";
}
?>
<br>
<br>
<br>
<br>
<br>
<br>
<br><a href="https://github.com/StultusEstQuiHocLegit/TRAMANNPORT/" title="we are open source (as all TRAMANN PROJECTS are), here you can see our source code and contribute to a better way of doing business" target="_blank">🔧 GITHUB</a>
<br>
<br>
<br>
<br><a href="mailto:hi@tramann-projects.com?subject=TRAMANN PORT - Hi  : )&body=Hi,%0D%0A%0D%0A%0D%0A[ContentOfYourMessage]%0D%0A%0D%0A%0D%0A%0D%0AWith best regards,%0D%0A[YourName]" title="always at your service   : )">✉️ CONTACT US   : )</a>
<br>
<br>
<br>
<br><a href="../index.php" title="view our other projects">👑 BACK TO TRAMANN PROJECTS</a>
<br>
<br>
<br>
<div style="opacity: 0.2;">
<br><a href="../imprint.php" title="legal stuff">🖋️ IMPRINT</a>
<br>
<br><a href="../DataSecurity.php" title="more legal stuff">🔒 DATA SECURITY</a>
<br>
<br><a href="../license.php" title="very short, not legally binding overview: you can do what you wan't for your own purposes, but we are the only ones that are allowed to sell and earn money">📜 LICENSE</a>
<br>
<br>
<br>
</div>