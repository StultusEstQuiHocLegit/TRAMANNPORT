<h1>📖 HEEELP!</h1>

<br>If you shouldn't be able to find anything helpful below, please feel free to
<?php
// Determine sender name
$senderName = $user['ExplorerOrCreator'] == 0 
? "{$user['FirstName']} {$user['LastName']} ({$user['idpk']})" 
: "{$user['CompanyName']} ({$user['idpk']})";

// Prepare the dynamic link
$contactEmail = "hi@tramann-projects.com";
$emailSubject = "TRAMANN PORT - $senderName needs heeelp!";
$emailBody = "Hi,\n\n\n[ContentOfYourMessage]\n\n\n\nWith best regards,\n$senderName";

// Encode the subject and body for URL-safe usage
$mailtoSubject = rawurlencode($emailSubject);
$mailtoBody = rawurlencode($emailBody);

// Output the link
echo ' <a href="mailto:' . $contactEmail . '?subject=' . $mailtoSubject . '&body=' . $mailtoBody . '" title="always at your service   : )">✉️ CONTACT US   : )</a>';
?>

<br><br><br><br><br>


<div id="topics"></div>


<script>
















        // Data for the topics: an array of objects with title and content
        const topics = [
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// app donwload
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '📱 APP DOWNLOAD',
    content: `
        Download our app (apk file for android phones) by visiting this page with your mobile phone and then click on <a href=\"./DownloadApp/TRAMANN.apk\">📱 DOWNLOAD APP</a>
    `
},
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// database structure CalendarEvents
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '🧬 DATABASE STRUCTURE CALENDAR EVENTS',
    content: `
        <br>
        <strong>table name: CalendarEvents</strong>
        <br><br>
        <div style="opacity: 0.5;">(r = rights (e = editable, s = system only, * means that this field is required)</div>
        <br><br><br>
        <table>
            <thead>
                <tr><th>field name</th><th>type</th><th>r</th><th>description</th></tr>
            </thead>
            <tbody>
                <tr><td>idpk</td><td>int, auto increment, primary key</td><td>s*</td><td></td></tr>
                <tr><td>IdpkExplorerOrCreator</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>EventName</td><td>varchar(255)</td><td>e*</td><td></td></tr>
                <tr><td>EventDescription</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>AllDay</td><td>tinyint</td><td>e*</td><td>(0 = no, 1 = yes (standard))</td></tr>
                <tr><td>StartTime</td><td>int</td><td>e*</td><td></td></tr>
                <tr><td>EndTime</td><td>int</td><td>e*</td><td></td></tr>
                <tr><td>location</td><td>varchar(255)</td><td>e</td><td></td></tr>
                <tr><td>CreatedOn</td><td>timestamp</td><td>s*</td><td></td></tr>
                <tr><td>UpdatedOn</td><td>timestamp</td><td>s*</td><td></td></tr>
            </tbody>
        </table>
    `
},
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////// database structure ExplorersAndCreators
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '🧬 DATABASE STRUCTURE EXPLORERSANDCREATORS',
    content: `
        <br>
        <strong>table name: ExplorersAndCreators</strong>
        <br><br>
        <div style="opacity: 0.5;">(r = rights (e = editable, s = system only, * means that this field is required)</div>
        <br><br><br>
        <table>
            <thead>
                <tr><th>field name</th><th>type</th><th>r</th><th>description</th></tr>
            </thead>
            <tbody>
                <tr><td>idpk</td><td>int, auto increment, primary key</td><td>s*</td><td></td></tr>
                <tr><td>TimestampCreation</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>email</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>password</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>PhoneNumber</td><td>int</td><td>e</td><td></td></tr>
                <tr><td>FirstName</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>LastName</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>street</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>HouseNumber</td><td>int</td><td>e*</td><td></td></tr>
                <tr><td>ZIPCode</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>city</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>country</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>planet</td><td>varchar(250)</td><td>e</td><td></td></tr>
                <tr><td>IBAN</td><td>varchar(250)</td><td>e*</td><td></td></tr>
                <tr><td>CapitalInAccountInDollars</td><td>decimal(10,2)</td><td>s</td><td></td></tr>
                <tr><td>darkmode</td><td>tinyint</td><td>e</td><td>(0 = no (standard), 1 = yes)</td></tr>
                <tr><td>ExplorerOrCreator</td><td>tinyint</td><td>e*</td><td>(0 = explorer, 1 = creator)</td></tr>
                <tr><td>level</td><td>tinyint</td><td>s</td><td>(0 = new, 1 = experienced, 2 = experts, 3 = checked experts, 4 = official partners)</td></tr>

                <tr><td><br><br><br></td><td></td><td><td></td></tr>
                <tr style='opacity: 0.5;'><td>the above was for both, the following is mainly for creators</td><td></td><td></td><td></td></tr>
                <tr><td><br><br><br></td><td></td><td><td></td></tr>

                <tr><td>CompanyName</td><td>varchar(250)</td><td>e</td><td></td></tr>
                <tr><td>VATID</td><td>varchar(250)</td><td>e</td><td>VAT identification number</td></tr>
                <tr><td>PhoneNumberForExplorersAsContact</td><td>int</td><td>e</td><td></td></tr>
                <tr><td>EmailForExplorersAsContact</td><td>varchar(250)</td><td>e</td><td></td></tr>
                <tr><td>ShowAddressToExplorers</td><td>tinyint</td><td>e</td><td>(0 = no, 1 = yes (standard))</td></tr>
                <tr><td>CanExplorersVisitYou</td><td>tinyint</td><td>e</td><td>(0 = no (standard), 1 = yes)</td></tr>
                <tr><td>OpeningHoursMondayOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursMondayClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursTuesdayOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursTuesdayClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursWednesdayOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursWednesdayClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursThursdayOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursThursdayClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursFridayOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursFridayClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursSaturdayOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursSaturdayClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursSundayOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursSundayClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursNationalHolidaysOpening</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>OpeningHoursNationalHolidaysClosing</td><td>time</td><td>e</td><td></td></tr>
                <tr><td>CloseOnlineShopIfPhysicalShopIsClosed</td><td>tinyint</td><td>e</td><td>(0 = no (standard), 1 = yes)</td></tr>
                <tr><td>PhysicalShopClosedBecauseOfHolidaysClosing</td><td>int</td><td>e</td><td>special feature to plan holiday times (closing time)</td></tr>
                <tr><td>PhysicalShopClosedBecauseOfHolidaysOpening</td><td>int</td><td>e</td><td>special feature to plan holiday times (opening time)</td></tr>
                <tr><td>ShortDescription</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>LongDescription</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>LinksToSocialMediaAndOtherSites</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading1</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text1</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading2</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text2</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading3</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text3</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading4</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text4</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading5</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text5</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading6</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text6</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading7</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text7</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading8</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text8</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading9</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text9</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Heading10</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>Text10</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>PersonalNotes</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>PersonalStrategicPlaningNotes</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>PersonalToDoList</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>PersonalCollectionOfLinks</td><td>text</td><td>e</td><td></td></tr>
            </tbody>
        </table>
    `
},
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////// database structure ProductsAndServices
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '🧬 DATABASE STRUCTURE PRODUCTSANDSERVICES',
    content: `
        <br>
        <strong>table name: ProductsAndServices</strong>
        <br><br>
        <div style="opacity: 0.5;">(r = rights (e = editable, s = system only, * means that this field is required)</div>
        <br><br><br>
        <table>
            <thead>
                <tr><th>field name</th><th>type</th><th>r</th><th>description</th></tr>
            </thead>
            <tbody>
                <tr><td>idpk</td><td>int, auto increment, primary key</td><td>s*</td><td></td></tr>
                <tr><td>TimestampCreation</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>IdpkCreator</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>KeywordsForSearch</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>name</td><td>text</td><td>e*</td><td></td></tr>
                <tr><td>ShortDescription</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>LongDescription</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>AllowCommentsNotesSpecialRequests</td><td>tinyint</td><td>e</td><td>(0 = no, 1 = yes (standard))</td></tr>
                <tr><td>type</td><td>tinyint</td><td>e*</td><td>(0 = product (standard), 1 = restaurant food, 2 = other food products, 3 = physical service, 4 = digital service)</td></tr>
                <tr><td>WeightInKg</td><td>decimal(10,5)</td><td>e</td><td style="opacity: 0.5;">(0 for services)</td></tr>
                <tr><td>DimensionsLengthInMm</td><td>decimal(10,2)</td><td>e</td><td style="opacity: 0.5;">(0 for services)</td></tr>
                <tr><td>DimensionsWidthInMm</td><td>decimal(10,2)</td><td>e</td><td style="opacity: 0.5;">(0 for services)</td></tr>
                <tr><td>DimensionsHeightInMm</td><td>decimal(10,2)</td><td>e</td><td style="opacity: 0.5;">(0 for services)</td></tr>
                <tr><td>SellingPriceProductOrServiceInDollars</td><td>decimal(10,2)</td><td>e*</td><td></td></tr>
                <tr><td>SellingPricePackagingAndShippingInDollars</td><td>decimal(10,2)</td><td>e</td><td style="opacity: 0.5;">(0 for services)</td></tr>
                <tr><td>ManageInventory</td><td>tinyint</td><td>e</td><td>(0 = no, 1 = yes (standard))</td></tr>
                <tr><td>InventoryAvailable</td><td>int</td><td>e</td><td style="opacity: 0.5;">(0 for services or if inventory is not managed)</td></tr>
                <tr><td>InventoryInProduction</td><td>int</td><td>e</td><td style="opacity: 0.5;">(0 for services or if inventory is not managed)</td></tr>
                <tr><td>InventoryMinimumLevel</td><td>int</td><td>e</td><td style="opacity: 0.5;">(0 for services or if inventory is not managed)</td></tr>
                <tr><td>PersonalNotes</td><td>text</td><td>e</td><td></td></tr>
                <tr><td>state</td><td>tinyint</td><td>e*</td><td>(0 = inactive, 1 = active (standard))</td></tr>
            </tbody>
        </table>
    `
},
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// database structure transactions
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '🧬 DATABASE STRUCTURE TRANSACTIONS',
    content: `
        <br>
        <strong>table name: transactions</strong>
        <br><br>
        <div style="opacity: 0.5;">(r = rights (e = editable, s = system only, * means that this field is required)</div>
        <br><br><br>
        <table>
            <thead>
                <tr><th>field name</th><th>type</th><th>r</th><th>description</th></tr>
            </thead>
            <tbody>
                <tr><td>idpk</td><td>int, auto increment, primary key</td><td>s*</td><td></td></tr>
                <tr><td>TimestampCreation</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>IdpkExplorer</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>IdpkProductOrService</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>IdpkCart</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>quantity</td><td>int</td><td>e*</td><td></td></tr>
                <tr><td>AmountInDollars</td><td>decimal(10,2)</td><td>s*</td><td>(total amount (already multiplied with the quantity), because prices can change)</td></tr>
                <tr><td>state</td><td>tinyint</td><td>e*</td><td>
                    (0 = collecting, 1 = ordered, 2 = paid, 3 = orders transmitted to creators, 
                    4 = creators producing or selecting, 5 = creators shipping, 
                    6 = in customs, 7 = at distribution center, 
                    8 = arriving, 9 = finished)
                </td></tr>
                <tr><td>CommentsNotesSpecialRequests</td><td>text</td><td>e</td><td></td></tr>
            </tbody>
        </table>
    `
},
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// database structure carts
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '🧬 DATABASE STRUCTURE CARTS',
    content: `
        <br>
        <strong>table name: carts</strong>
        <br><br>
        <div style="opacity: 0.5;">(r = rights (e = editable, s = system only, * means that this field is required)</div>
        <br><br><br>
        <table>
            <thead>
                <tr><th>field name</th><th>type</th><th>r</th><th>description</th></tr>
            </thead>
            <tbody>
                <tr><td>idpk</td><td>int, auto increment, primary key</td><td>s*</td><td></td></tr>
                <tr><td>TimestampCreation</td><td>int</td><td>s*</td><td></td></tr>
                <tr><td>IdpkExplorerOrCreator</td><td>int</td><td>s*</td><td></td></tr>

                <tr><td><br><br><br></td><td></td><td></td><td></td></tr>
                <tr style='opacity: 0.5;'><td>if the ProductsAndServices aren't restaurant food (type)</td><td></td><td></td><td></td></tr>
                <tr><td><br><br><br></td><td></td><td></td><td></td></tr>

                <tr><td>DeliveryType</td><td>tinyint</td><td>e*</td><td>
                    (0 = standard (default), 1 = express, 2 = as soon as possible, 
                    3 = pick up in store, 4 = best matching wished ideal delivery time)
                </td></tr>
                <tr><td>WishedIdealDeliveryOrPickUpTime</td><td>int</td><td>e</td><td style="opacity: 0.5;">
                    (show only, if DeliveryType is 3 or 4, for 3 show OpeningHours (from database ExplorersAndCreators) also)
                </td></tr>

                <tr><td><br><br><br></td><td></td><td></td><td></td></tr>
                <tr style='opacity: 0.5;'><td>if the ProductsAndServices are restaurant food (type)</td><td></td><td></td><td></td></tr>
                <tr><td><br><br><br></td><td></td><td></td><td></td></tr>

                <tr><td>DeliveryType</td><td>tinyint</td><td>e*</td><td>
                    <div style="opacity: 0.5;">(0 = standard (don't show), 1 = express (don't show), )</div>(2 = as soon as possible, 3 = pick up in store, 
                    4 = best matching wished ideal delivery time (default))
                </td></tr>
                <tr><td>WishedIdealDeliveryOrPickUpTime</td><td>int</td><td>e</td><td style="opacity: 0.5;">
                    (show only, if DeliveryType is 4, for 3 show OpeningHours (from database ExplorersAndCreators) also)
                </td></tr>
            </tbody>
        </table>
    `
},
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// page structure
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '📚 PAGE STRUCTURE',
    content: `
        <div class="steps">
            all pages in alphabetic order:
            <br>
            <br>account.php
            <br>accounting.php
            <br>calendar.php
            <br>cart.php
            <br>CreateAccount.php
            <br>CreatorsSuppliers.php
            <br>dashboard.php
            <br>explore.php
            <br>ExplorersCustomers.php
            <br>ForgotPassword.php
            <br>header.php
            <br>help.php
            <br>index.php
            <br>inventory.php
            <br>LandingPage.php
            <br>login.php
            <br>logout.php
            <br>menu.php
            <br>orders.php
            <br>PreviousCarts.php
            <br>products.php
            <br>SaveDataCalendarGetEvents.php
            <br>SaveDataCalendarSaveEvents.php
            <br>SaveDataCart.php
            <br>SaveDataDashboard.php
            <br>SaveDataInventory.php
            <br>SaveDataOrders.php
            <br>SaveDataShowProduct.php
            <br>search.php
            <br>ShowCreatorOrExplorer.php
            <br>ShowProduct.php
            <br>YourWebsite.php
        </div>
    `
},
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// process of ordering
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
{
    title: '⛓️ PROCESS OF ORDERING',
    content: `
        <table>
            <thead>
                <tr><th>state (from table - transactions -)</th><th>agent</th><th>description</th></tr>
            </thead>
            <tbody>
                <tr><td>0 = collecting</td><td>explorer (or other creator)</td><td>collectiong products and services, for example in explore.php, clicking on - ADD TO CART - and adding them to cart.php, transactions are created in the table - transactions -</td></tr>
                <tr><td>1 = ordered</td><td>explorer (or other creator)</td><td>clicked on - BUY NOW - in cart.php, carts are created in the table - carts -, TRAMANN PORT payment system gets triggered, additional email is send to system</td></tr>
                <tr><td>2 = paid</td><td>system</td><td>TRAMANN PORT payment system is processing the payment and updating the table - transactions - accordingly, orders are from now on displayed in PreviousCarts.php (for explorer (or other creator))</td></tr>
                <tr><td>3 = orders transmitted to creators</td><td>creators</td><td>orders are now displayed in dashboard.php (preview) and orders.php (for creators, - state - can be updated there)</td></tr>
                <tr><td>4 = creators producing or selecting</td><td>creators</td><td>orders are now displayed in dashboard.php (preview) and orders.php (for creators, - state - can be updated there), quantities are updated automatically too</td></tr>
                <tr><td>5 = creators shipping</td><td>creators</td><td>orders are from now on displayed in order.php (with lower opacity and no longer editable (old orders))</td></tr>
                <tr><td> 6 = in customs</td><td>freight forwarders</td><td>shipping</td></tr>
                <tr><td>7 = at distribution center</td><td>freight forwarders</td><td>shipping</td></tr>
                <tr><td>8 = arriving</td><td>freight forwarders</td><td>shipping</td></tr>
                <tr><td>9 = finished</td><td>explorer (or other creator)</td><td>shipping</td></tr>
            </tbody>
        </table>
    `
}
        ];
























        // Function to toggle visibility of the div content
        function toggleVisibility(event, topicId) {
            event.preventDefault(); // Prevent the link from being executed (page reload)

            var contentDiv = document.getElementById(topicId);
            if (contentDiv.style.display === "none" || contentDiv.style.display === "") {
                contentDiv.style.display = "block"; // Show content
            } else {
                contentDiv.style.display = "none"; // Hide content
            }
        }

        // Dynamically generate topic content
        window.onload = function() {
            const topicsContainer = document.getElementById('topics');
            topics.forEach((topic, index) => {
                // Create a div to hold each topic
                const topicDiv = document.createElement('div');
                const topicId = `topic${index + 1}`;
            
                // Create the link with the topic title
                const topicLink = document.createElement('a');
                topicLink.href = "#"; // Set the href to "#" but we will prevent its default behavior
                topicLink.onclick = (event) => toggleVisibility(event, topicId); // Prevent page reload and toggle visibility

                const topicTitle = document.createElement('h3');
                topicTitle.textContent = topic.title;
                topicLink.appendChild(topicTitle);
            
                // Create the div for the content
                const contentDiv = document.createElement('div');
                contentDiv.id = topicId;
                contentDiv.classList.add('entry');
                contentDiv.innerHTML = topic.content;
            
                // Append the link and content to the topic div
                topicDiv.appendChild(topicLink);
                topicDiv.appendChild(contentDiv);
            
                // Append the topic div to the main container
                topicsContainer.appendChild(topicDiv);
            });
        
            // Hide all entries by default using JavaScript (in case of fallback)
            const entries = document.querySelectorAll('.entry');
            entries.forEach(entry => {
                entry.style.display = 'none';  // Ensure all entries are hidden by default
            });
        };
    </script>
