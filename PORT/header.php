<?php
include ("../config.php");

session_start(); // Start the session
?>

<html>
<head>
	<meta charset="utf-8"/>
    <title>TRAMANN PROJECTS - PORT</title>
  	<header>
  	<!-- <link rel="stylesheet" type="text/css" href="../style.css"> -->
  	<link rel="icon" type="image/png" href="../logos/favicon.png">

    <!-- PWA (progressive web app) -->
    <link rel="../manifest" href="manifest.json">
    <meta name="theme-color" content="#000000">
</header>

<style type="text/css">
/*
@font-face {
    font-family: 'OCR A Std';
    font-style: normal;
    font-weight: normal;
    src: local('OCR A Std'), url('../OCRAStd.woff') format('woff');
}

body {
  font-family: 'OCR A Std', monospace;
}
*/
</style>

<body>
<div class="header">
    <a href="javascript:void(0);" title="TRAMANN" id="dropdownMenuLogo">
    <div id="glowEffect"></div>
    
    <?php
            // Check if the user is logged in using cookies
            $isLoggedIn = isset($_COOKIE['user_id']);
            $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;
            
            $userRole = null; // Initialize user role
            $userFont = ''; // Initialize user font
            
            if ($user_id !== null) {
                try {
                    // Prepare the SQL query to get the user role
                    $stmt = $pdo->prepare('SELECT ExplorerOrCreator, darkmode, font FROM ExplorersAndCreators WHERE idpk = :id');
                    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                    
                    // Execute the query
                    $stmt->execute();
                    
                    // Fetch the result
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Check if a role and dark mode preference were found
                    if ($result) {
                        $userRole = (int)$result['ExplorerOrCreator']; // Cast to integer if needed
                        $darkMode = (int)$result['darkmode']; // 0 = no, 1 = yes
                        $userFont = isset($result['font']) ? $result['font'] : '';

                        // Determine the appropriate stylesheet based on the dark mode preference
                        if ($darkMode === 1) {
                            echo '<img id="menuIcon" src="../logos/TramannLogoWhite.png" height="40" alt="TRAMANN">'; // Dark mode logo
                            echo '<link rel="stylesheet" type="text/css" href="../style.css">'; // Dark mode stylesheet
                        } else {
                            echo '<img id="menuIcon" src="../logos/TramannLogo.png" height="40" alt="TRAMANN">'; // Light mode logo
                            echo '<link rel="stylesheet" type="text/css" href="../style.css">'; // Dark mode stylesheet
                            echo '<link rel="stylesheet" type="text/css" href="../StyleLightmode.css">'; // Light mode stylesheet adding to the existing one
                        }
                    } else {
                        // Handle case where user role or dark mode is not found
                        $userRole = null; // Set to a default value or handle as needed
                        $darkMode = null;
                        $userFont = '';
                    }
                } catch (PDOException $e) {
                    // Handle database errors
                    echo "database error: " . $e->getMessage();
                }
                // You can now use $userFont here
            } else {
                echo '<img id="menuIcon" src="../logos/TramannLogo.png" height="40" alt="TRAMANN">'; // Light mode logo
                echo '<link rel="stylesheet" type="text/css" href="../style.css">'; // Dark mode stylesheet
                echo '<link rel="stylesheet" type="text/css" href="../StyleLightmode.css">'; // Light mode stylesheet adding to the existing one
                $userFont = '';
            }




            ?>
                <style type="text/css">
                    <?php
                    // Fonts URL mapping for user-selected fonts
                    $differentFonts = [
                        "Agu Display" => "Agu+Display",
                        "Aldrich" => "Aldrich",
                        "Aladin" => "Aladin",
                        "Amarante" => "Amarante",
                        "Arial" => null, // Arial is a system font
                        "Astloch" => "Astloch",
                        "Atomic Age" => "Atomic+Age",
                        "Audiowide" => "Audiowide",
                        "Barriecito" => "Barriecito",
                        "Chewy" => "Chewy",
                        "Condiment" => "Condiment",
                        "Delius" => "Delius",
                        "Delius Swash Caps" => "Delius+Swash+Caps",
                        "Delicious Handrawn" => "Delicious+Handrawn",
                        "DM Serif Text" => "DM+Serif+Text",
                        "Dynalight" => "Dynalight",
                        "DynaPuff" => "DynaPuff",
                        "Fontdiner Swanky" => "Fontdiner+Swanky",
                        "Funnel Display" => "Funnel+Display",
                        "Funnel Sans" => "Funnel+Sans",
                        "Geostar" => "Geostar",
                        "Henny Penny" => "Henny+Penny",
                        "Homemade Apple" => "Homemade+Apple",
                        "Iceberg" => "Iceberg",
                        "Jacquard 12" => "Jacquard+12",
                        "Kablammo" => "Kablammo",
                        "League Script" => "League+Script",
                        "Limelight" => "Limelight",
                        "Macondo" => "Macondo",
                        "Megrim" => "Megrim",
                        "Merriweather" => "Merriweather",
                        "Montserrat" => "Montserrat",
                        "Mystery Quest" => "Mystery+Quest",
                        "Newsreader" => "Newsreader",
                        "Noto Serif" => "Noto+Serif",
                        "Nunito" => "Nunito",
                        "Open Sans" => "Open+Sans",
                        "Orbitron" => "Orbitron",
                        "Pinyon Script" => "Pinyon+Script",
                        "Playfair Display" => "Playfair+Display",
                        "Poiret One" => "Poiret+One",
                        "Princess Sofia" => "Princess+Sofia",
                        "Roboto" => "Roboto",
                        "Rubik Iso" => "Rubik+Iso",
                        "Rye" => "Rye",
                        "Sancreek" => "Sancreek",
                        "Send Flowers" => "Send+Flowers",
                        "Shadows Into Light" => "Shadows+Into+Light",
                        "Shadows Into Light Two" => "Shadows+Into+Light+Two",
                        "Silkscreen" => "Silkscreen",
                        "Sixtyfour" => "Sixtyfour",
                        "Sour Gummy" => "Sour+Gummy",
                        "Tomorrow" => "Tomorrow",
                        "Twinkle Star" => "Twinkle+Star",
                        "UnifrakturMaguntia" => "UnifrakturMaguntia"
                    ];
                
                    if ($userFont === "Arial") {
                        // Arial is a system font
                        echo "
                        body, input[type='text'],
                        input[type='email'],
                        input[type='password'],
                        input[type='number'],
                        input[type='time'],
                        input[type='date'],
                        input[type='datetime-local'],
                        input[type='search'],
                        textarea,
                        select {
                            font-family: 'Arial', 'OCR A Std', monospace;
                        }
                        ";
                    } elseif (isset($differentFonts[$userFont]) && $differentFonts[$userFont] !== null) {
                        // Import the Font if it's mapped
                        $fontUrl = $differentFonts[$userFont];
                        echo "@import url('https://fonts.googleapis.com/css2?family=$fontUrl&display=swap');";
                        echo "
                        body, input[type='text'],
                        input[type='email'],
                        input[type='password'],
                        input[type='number'],
                        input[type='time'],
                        input[type='date'],
                        input[type='datetime-local'],
                        input[type='search'],
                        textarea,
                        select {
                            font-family: '$userFont', 'OCR A Std', monospace;
                        }
                        ";
                    } else {
                        // Fallback to OCR A Std if no font or invalid font is specified
                        echo "
                        @font-face {
                            font-family: 'OCR A Std';
                            font-style: normal;
                            font-weight: normal;
                            src: local('OCR A Std'), url('../OCRAStd.woff') format('woff');
                        }
                        body, input[type='text'],
                        input[type='email'],
                        input[type='password'],
                        input[type='number'],
                        input[type='time'],
                        input[type='date'],
                        input[type='datetime-local'],
                        input[type='search'],
                        textarea,
                        select {
                            font-family: 'OCR A Std', monospace;
                        }
                        ";
                    }
                    ?>
                </style>
            <?php







    echo "</a><div style=\"height: 5px;\"></div> <div id=\"dropdownMenu\">";

                                    include ("menu.php"); // Include the menu
            ?>
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
<?php if ($isLoggedIn): ?>
    <!-- don't show the cookie alert if the user is already logged in -->
    <div id="cookie-container" style="display: none;">
        <div id="cookie-content" style="display: none;">
            <div id="cookie-sentences" style="display: none;"></div>
            <button id="close-cookie" style="display: none;"></button>
        </div>
    </div>
<?php else: ?>
    <div id="cookie-container">
        <div id="cookie-content">
            <div id="cookie-sentences">To give you the best user experience on our websites, we use cookies.<br>By continuing to use our websites, you consent to the use of cookies.</div>
            <button id="close-cookie">&times;</button>
        </div>
    </div>
<?php endif; ?>
<script type="text/javascript">
// for dropdown menu
document.getElementById("dropdownMenuLogo").addEventListener("click", function(event) {
    event.stopPropagation(); // prevents the instant closing if there is a click on the dropdownMenuLogo

    // Check the screen width for redirection
    if (window.innerWidth <= 1025) { // for devices with a small screen
        // Redirect to the menu page
        window.location.href = "index.php?content=menu.php";
        return; // Exit the function to avoid further execution
    }

    // Toggle the dropdown menu visibility
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



