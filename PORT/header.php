<html>
<head>
	<meta charset="utf-8"/>
    <title>TRAMANN PROJECTS - PORT</title>
  	<header>
  	<link rel="stylesheet" type="text/css" href="../style.css">
  	<link rel="icon" type="image/png" href="../logos/favicon.png">
</header>

<style type="text/css">
@font-face {
    font-family: 'OCR A Std';
    font-style: normal;
    font-weight: normal;
    src: local('OCR A Std'), url('../OCRAStd.woff') format('woff');
}

body {
  font-family: 'OCR A Std', monospace;
}
</style>

<body>
<div class="header">
    <a href="javascript:void(0);" title="TRAMANN" id="dropdownMenuLogo">
        <img src="../logos/TramannLogoWhite.png" height="40" alt="TRAMANN">
    </a>
    <div id="dropdownMenu">
        <?php
            if (1 == 2) {
                echo "<br><a href=\"explore.php\">EXPLORE</a>";
                echo "<br>";
                echo "<br><a href=\"cart.php\">SHOPPING CART</a>";
                echo "<br>";
                echo "<br><a href=\"PreviousCarts.php\">PREVIOUS CARTS</a>";
                echo "<br>";
                echo "<br><a href=\"account.php\">ACCOUNT</a>";
            } elseif (1 == 2) {
                echo "<br><a href=\"dashboard.php\">DASHBOARD</a>";
                echo "<br>";
                echo "<br><a href=\"orders.php\">ORDERS</a>";
                echo "<br>";
                echo "<br><a href=\"inventory.php\">INVENTORY</a>";
                echo "<br>";
                echo "<br><a href=\"products.php\">PRODUCTS</a>";
                echo "<br>";
                echo "<br><a href=\"ExplorersCustomers.php\">EXPLORERS (CUSTOMERS)</a>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br><a href=\"CreatorsSuppliers.php\">CREATORS (SUPPLIERS)</a>";
                echo "<br>";
                echo "<br><a href=\"explore.php\">EXPLORE</a>";
                echo "<br>";
                echo "<br><a href=\"cart.php\">SOURCING CART</a>";
                echo "<br>";
                echo "<br><a href=\"PreviousCarts.php\">PREVIOUS CARTS</a>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br><a href=\"accounting.php\">ACCOUNTING</a>";
                echo "<br>";
                echo "<br><a href=\"help.php\">HELP</a>";
                echo "<br>";
                echo "<br><a href=\"account.php\">ACCOUNT</a>";
                echo "<br>";
                echo "<br><a href=\"YourWebsite.php\">YOUR WEBSITE</a>";
            } else {
                echo "<br><a href=\"login.php\">COME ON BOARD</a>";
            }
        ?>
        <br>
        <br>
        <br>
        <br>
        <br><a href="https://github.com/StultusEstQuiHocLegit/TRAMANNPORT/" target="_blank">GITHUB</a>
        <br>
        <br>
        <br>
        <br><a href="mailto:hi@tramann-projects.com?subject=Hi  : )&body=Hi,%0D%0A%0D%0A%0D%0A[ContentOfYourMessage]%0D%0A%0D%0A%0D%0A%0D%0AWith best regards,%0D%0A[YourName]" title="Always at your service   : )">CONTACT US   : )</a>
        <br>
        <br>
        <br>
        <br><a href="../index.php">BACK TO TRAMANN PROJECTS</a>
        <br>
        <br>
        <br>
        <div style="opacity: 0.2;">
            <br><a href="../imprint.php">IMPRINT</a>
            <br>
            <br><a href="../DataSecurity.php">DATA SECURITY</a>
            <br>
            <br><a href="../license.php">LICENSE</a>
            <br>
            <br>
            <br>
        </div>
    </div>
</div>
<!-- // ///////////////////////////////////////////// old version:
<div class="header">
    <a href="./index.php" title="TRAMANN PROJECTS"><img src="../logos/TramannLogoWhite.png" height="40" alt="TRAMANN PROJECTS"></a>
    <br><a href="./index.php" class="sublogo" style="font-size: 14px;" title="TRAMANN PORT">PORT</a>
</div>
<div class="topright">
    <div><a href="./index.php">CART</a></div>
</div>
<footer class="footerleft">
    <div><a href="./index.php">ACCOUNT</a></div>
</footer>
<footer class="footerright">
    <div style="opacity: 0.08;"><a href="../imprint.php">IMPRINT</a> - <a href="../DataSecurity.php">DATA SECURITY</a> - <a href="../license.php">LICENSE</a></div>
</footer>
-->
<div id="cookie-container">
    <div id="cookie-content">
        <div id="cookie-sentences">To give you the best user experience on our websites, we use cookies.<br>By continuing to use our websites, you consent to the use of cookies.</div>
        <button id="close-cookie">&times;</button>
    </div>
</div>
<script type="text/javascript">
// for dropdown menu
document.getElementById("dropdownMenuLogo").addEventListener("click", function(event) {
    event.stopPropagation(); // prevents the instant closing if there is a click on the dropdownMenuLogo
    var dropdown = document.getElementById("dropdownMenu");
    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
    } else {
        dropdown.style.display = "block";
    }
});

// closes the dropdown menu if there is a click somewhere else on the page
document.addEventListener("click", function(event) {
    var dropdown = document.getElementById("dropdownMenu");
    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
    }
});

// prevents the closing if there is a click inside the dropdown menu
document.getElementById("dropdownMenu").addEventListener("click", function(event) {
    event.stopPropagation();
});






document.addEventListener("DOMContentLoaded", function() {
    var cookieContainer = document.getElementById("cookie-container");
    var closeButton = document.getElementById("close-cookie");

    // check if cookie alert has already been closed
    if (!getCookie("cookieAccepted")) {
        cookieContainer.style.display = "block";
    }

    // event listener to close the cookie alert
    closeButton.addEventListener("click", function() {
        cookieContainer.style.display = "none";
        // set cookie to save that cookie alert was accepted
        setCookie("cookieAccepted", "true", 3650); // 3650 days = 10 years
    });

    // function to read cookies
    function getCookie(name) {
        var cookieArr = document.cookie.split("; ");
        for (var i = 0; i < cookieArr.length; i++) {
            var cookiePair = cookieArr[i].split("=");
            if (name === cookiePair[0]) {
                return cookiePair[1];
            }
        }
        return null;
    }

    // function to set cookies
    function setCookie(name, value, days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }
});
</script>



