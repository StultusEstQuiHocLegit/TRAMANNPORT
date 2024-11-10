<h1>HELP</h1>

<br>If you shouldn't be able to find anything helpful below, please feel free to <a href="mailto:hi@tramann-projects.com?subject=Hi  : )&body=Hi,%0D%0A%0D%0A%0D%0A[ContentOfYourMessage]%0D%0A%0D%0A%0D%0A%0D%0AWith best regards,%0D%0A[YourName]" title="Always at your service   : )">CONTACT US   : )</a>

<br><br><br><br><br>


<div id="topics"></div>


<script>
















        // Data for the topics: an array of objects with title and content
        const topics = [
            {
    title: 'DATABASE STRUCTURE EXPLORERSANDCREATORS',
    content: `
        <br>
        <strong>database name: ExplorersAndCreators</strong>
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
                <tr style='opacity: 0.5;'><td>the above was for both</td><td></td><td></td><td>the following is mainly for creators</td></tr>
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
{
    title: 'WHAT IS AN ERP SYSTEM',
    content: 'ERP stands for enterprise resource planning and describes the management of business processes.'
},
{
    title: 'PAGE STRUCTURE',
    content: `
        <div class="steps">
            all pages in alphabetic order:
            <br>
            <br>account.php
            <br>accounting.php
            <br>cart.php
            <br>CreateAccount.php
            <br>CreatorsSuppliers.php
            <br>CreatorWebsite.php
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
            <br>orders.php
            <br>PreviousCarts.php
            <br>products.php
            <br>SaveDataDashboard.php
            <br>SaveDataInventory.php
            <br>search.php
            <br>ShowCreator.php
            <br>ShowProduct.php
            <br>YourWebsite.php
        </div>
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
