<?php
if ($isLoggedIn && $userRole !== null) {
    // User is logged in
    if ($userRole === 0) {
        // Show Explorer links
        echo "<br><a href=\"index.php?content=explore.php\">⭐ EXPLORE</a>";
        echo "<br>";
        // echo "<br><a href=\"index.php?content=cart.php\">SHOPPING CART</a>";
        echo "<br><a href=\"index.php?content=cart.php\" style=\"$borderStyle\">🛒 SHOPPING CART $pendingText</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=PreviousCarts.php\">🛍️ PREVIOUS CARTS</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=account.php\">⚙️ ACCOUNT</a>";
    } elseif ($userRole === 1) {
        // Show Creator links
        echo "<br><a href=\"index.php?content=dashboard.php\">⭐ DASHBOARD</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=orders.php\">📝 ORDERS</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=inventory.php\">🏰 INVENTORY</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=products.php\">📦 PRODUCTS AND SERVICES</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=ExplorersCustomers.php\">👥 EXPLORERS (CUSTOMERS)</a>";
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=CreatorsSuppliers.php\">🏭 CREATORS (SUPPLIERS)</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=explore.php\">🔍 EXPLORE</a>";
        echo "<br>";
        // echo "<br><a href=\"index.php?content=cart.php\">SOURCING CART</a>";
        echo "<br><a href=\"index.php?content=cart.php\" style=\"$borderStyle\">🛒 SOURCING CART $pendingText</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=PreviousCarts.php\">🛍️ PREVIOUS CARTS</a>";
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=accounting.php\">🧮 ACCOUNTING</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=help.php\">📖 HELP</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=account.php\">⚙️ ACCOUNT</a>";
        echo "<br>";
        echo "<br><a href=\"index.php?content=YourWebsite.php\">🌐 YOUR WEBSITE</a>";
    }
} else {
    // User is not logged in
    echo "<br><a href=\"index.php?content=login.php\">🗝️ COME ON BOARD</a>";
}
?>
<br>
<br>
<br>
<br>
<br>
<br>
<br><a href="https://github.com/StultusEstQuiHocLegit/TRAMANNPORT/" target="_blank">🔧 GITHUB</a>
<br>
<br>
<br>
<br><a href="mailto:hi@tramann-projects.com?subject=Hi  : )&body=Hi,%0D%0A%0D%0A%0D%0A[ContentOfYourMessage]%0D%0A%0D%0A%0D%0A%0D%0AWith best regards,%0D%0A[YourName]" title="Always at your service   : )">✉️ CONTACT US   : )</a>
<br>
<br>
<br>
<br><a href="../index.php">👑 BACK TO TRAMANN PROJECTS</a>
<br>
<br>
<br>
<div style="opacity: 0.2;">
<br><a href="../imprint.php">🖋️ IMPRINT</a>
<br>
<br><a href="../DataSecurity.php">🔒 DATA SECURITY</a>
<br>
<br><a href="../license.php">📜 LICENSE</a>
<br>
<br>
<br>
</div>




