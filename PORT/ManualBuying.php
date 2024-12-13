<?php
echo "<h1>👈 MANUAL BUYING</h1>";
?>
<script>
function setFormAction(actionType) {
    // Save the manual further information as a cookie before submitting
    saveManualFurtherInformation();

    // Submit the form#
    document.getElementById('manualBuyingForm').submit();
}






















// Function to save the further information to the cookie
function saveManualFurtherInformation() {
    const furtherInfo = document.getElementById('IfManualFurtherInformationManualBuying').value;
    setCookie('IfManualFurtherInformationManualBuying', furtherInfo, 7); // Save for 7 days
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const furtherInfoTextarea = document.getElementById('IfManualFurtherInformationManualBuying');
    const suggestionsDiv = document.getElementById('ShowSuggestionsForIfManualFurtherInformation');

    if (furtherInfoTextarea) {
        // Save information to cookie on input
        furtherInfoTextarea.addEventListener('input', () => {
            saveManualFurtherInformation();
            loadSuggestions(furtherInfoTextarea.value);
            if (suggestionsDiv) {
                suggestionsDiv.style.display = 'block'; // Show suggestions when typing
            }
        });

        // Hide suggestions when clicking outside the textarea
        document.addEventListener('click', (event) => {
            if (suggestionsDiv && !furtherInfoTextarea.contains(event.target) && !suggestionsDiv.contains(event.target)) {
                suggestionsDiv.style.display = 'none'; // Hide suggestions
            }
        });
    }
});

// Function to load suggestions based on input
function loadSuggestions(query) {
    if (query.trim().length < 1) {
        document.getElementById('ShowSuggestionsForIfManualFurtherInformation').innerHTML = '';
        return;
    }

    fetch('SaveDataManualSellingOrBuying.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ query, type: 'ManualBuying' }) // Specify the type
    })
    .then(response => response.json())
    .then(data => {
        const suggestionsDiv = document.getElementById('ShowSuggestionsForIfManualFurtherInformation');
        suggestionsDiv.innerHTML = ''; // Clear previous suggestions

        if (data.success && data.suggestions.length > 0) {
            const list = document.createElement('div');
            list.style.listStyleType = 'none';

            data.suggestions.forEach(suggestion => {
                const listItem = document.createElement('div');
                const link = document.createElement('a');
                link.textContent = suggestion.text;
                link.href = 'javascript:void(0);';
                link.title = suggestion.fullText; // Full text on hover
                link.addEventListener('click', () => {
                    document.getElementById('IfManualFurtherInformationManualBuying').value = suggestion.fullText;
                    saveManualFurtherInformation();
                    suggestionsDiv.innerHTML = ''; // Clear suggestions after selection
                });
                listItem.appendChild(link);
                listItem.appendChild(document.createElement('br'));
                listItem.appendChild(document.createElement('br'));
                list.appendChild(listItem);
            });

            suggestionsDiv.appendChild(list);
        } else {
            suggestionsDiv.innerHTML = '';
        }
    })
    .catch(error => {
        console.error('Error fetching suggestions:', error);
        document.getElementById('ShowSuggestionsForIfManualFurtherInformation').innerHTML = '';
    });
}







// Helper function to get a cookie value by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Helper function to set a cookie
function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value}; path=/; expires=${date.toUTCString()}`;
}
</script>








































<?php
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize an array to hold error messages
    $errors = [];

    // Get the user ID from the cookie
    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;



    // create an entry into the table carts (database connection already established ($stmt = $pdo->prepare("...))
    // TimestampCreation (integer) is set to the current: $timestampCreation = time(); // Current timestamp
    // manual is set to 1, IfManualFurtherInformation is set to the value from this field: IfManualFurtherInformation (if it exists), DeliveryType is set to 0
    //
    // now we create an entry into the table transactions
    // we set: TimestampCreation = as handled above, IdpkExplorer = user idpk, IdpkProductOrService = 0,
    // IdpkCart = the idpk of the cart just created before, quantity = 0, AmountInDollars = TotalPricePaid, state = 9



    $ifManualFurtherInformationManualBuying = isset($_POST['IfManualFurtherInformationManualBuying']) ? trim($_POST['IfManualFurtherInformationManualBuying']) : '';
    $totalPricePaid = isset($_POST['TotalPricePaid']) ? (float)$_POST['TotalPricePaid'] : 0;

    // Check if total price is valid
    if ($totalPricePaid <= 0) {
        $errors[] = "Total price paid must be greater than zero.<br><br><a href=\"index.php?content=ManualBuying.php\">▶️ CONTINUE</a>";
    }

    // If no errors, proceed with database operations
    if (empty($errors)) {
        try {
            // Create a new entry in the 'carts' table
            $timestampCreation = time(); // Current timestamp

            $stmt = $pdo->prepare("INSERT INTO carts (TimestampCreation, manual, IfManualFurtherInformation, DeliveryType, IdpkExplorerOrCreator) 
                                   VALUES (:timestampCreation, 1, :ifManualFurtherInformationManualBuying, 0, :userId)");
            $stmt->bindParam(':timestampCreation', $timestampCreation, PDO::PARAM_INT);
            $stmt->bindParam(':ifManualFurtherInformationManualBuying', $ifManualFurtherInformationManualBuying, PDO::PARAM_STR);
            $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT); // Bind the user_id to the IdpkExplorerOrCreator
            $stmt->execute();

            // Get the last inserted cart ID
            $cartId = $pdo->lastInsertId();

            // Create a new entry in the 'transactions' table
            $stmt = $pdo->prepare("INSERT INTO transactions (TimestampCreation, IdpkExplorer, IdpkProductOrService, IdpkCart, quantity, AmountInDollars, state) 
                                   VALUES (:timestampCreation, :userId, 0, :cartId, 0, :totalPrice, 9)");
            $stmt->bindParam(':timestampCreation', $timestampCreation, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $stmt->bindParam(':totalPrice', $totalPricePaid, PDO::PARAM_STR);
            $stmt->execute();

            echo "Saved successfully.<br><br><a href=\"index.php?content=ManualBuying.php\">▶️ CONTINUE</a>";
            ?>
            <script>
                document.cookie = 'IfManualFurtherInformationManualBuying=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                console.log('Cookies cleared');
            </script>
            <?php
        } catch (PDOException $e) {
            // Catch any database errors
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // If there are errors, display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "$error";
        }
    }

    // add rest here

} else {























    echo "<div style=\"opacity: 0.5;\">Here you can directly save a manual purchase for creators (suppliers) outside our systeme where you already paid the money.</div>";
    echo "<br><br>";



    echo "<form id=\"manualBuyingForm\" onsubmit=\"return submitFormManualBuying()\" action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
        echo "<table>";
            echo "<tr>";
                echo "<td>";
                    $IfManualFurtherInformationManualBuying = isset($_COOKIE['IfManualFurtherInformationManualBuying']) ? $_COOKIE['IfManualFurtherInformationManualBuying'] : '';
                    echo "<textarea id=\"IfManualFurtherInformationManualBuying\" name=\"IfManualFurtherInformationManualBuying\" rows=\"12\" style=\"width: 500px;\" placeholder=\"if you want to, you can insert further information about the creator (supplier) here, for example company name, first name, last name, street, house number, ZIP code, city, country, planet, VATID, email, further notes, ...\">" . htmlspecialchars($IfManualFurtherInformationManualBuying) . "</textarea>";
                    echo "<br><br><br><div id=\"ShowSuggestionsForIfManualFurtherInformation\"></div>";
                    echo "</td>";   
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td>";
                    echo "<input type=\"number\" id=\"TotalPricePaid\" name=\"TotalPricePaid\" placeholder=\"How much?\" style=\"width: 200px;\">";
                    echo "<br><label for=\"TotalPricePaid\">total price paid (in USD)</label>";
                    echo "<br><br><br>";
                    echo "<a href=\"javascript:void(0);\" class=\"mainbutton\" onclick=\"setFormAction()\">↗️ SAVE</a>";
                echo "</td>";
            echo "</tr>";
        echo "</table>";
    echo "</form>";





}
?>