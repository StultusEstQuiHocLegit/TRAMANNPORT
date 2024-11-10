<br><br><br>

<div style="display: flex; width: 100%;">
    <input type="search" id="SearchBar" name="SearchBar" placeholder="explore ..." style="width: 70%;">
    <a href="javascript:void(0)" id="StartSearchButton" class="mainbutton" onclick="StartSearch()" style="width: 20%;">SEARCH</a>
    <div style="width: 3%;"></div>
    <a href="javascript:void(0)" id="ShowSearchOptionsDiv" class="button" onclick="ShowSearchOptionsDiv()" style="width: 7%;">v</a>
</div>
<div id="SearchOptionsDiv">
    <br>
    <select id="SearchOptions" class="search-dropdown">
        <option value="products_services">search for products and services</option>
        <option value="creators_explorers">search for creators and explorers</option>
        <option value="transactions">search for transactions</option>
        <option value="carts">search for carts</option>
    </select>
</div>

<br><br><br>

<?php
echo "<div id=\"ShowProduct\">";
    include ("ShowProduct.php");
echo "</div>";




echo "<div id=\"FeedAndResults\"></div>";
?>
























<script>
    function ShowSearchOptionsDiv() {
        const searchOptions = document.getElementById('SearchOptionsDiv');
        if (SearchOptionsDiv.style.display === 'none' || SearchOptionsDiv.style.display === '') {
            SearchOptionsDiv.style.display = 'block'; // Show the dropdown
        } else {
            searchOptions.style.display = 'none'; // Hide the dropdown
        }
    }

    // Hide the dropdown when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('SearchOptionsDiv').style.display = 'none';
    });

    function StartSearch() {
        const searchQuery = document.getElementById('SearchBar').value;
        const selectedOption = document.getElementById('SearchOptions').value;

        // Hide the ShowProduct div when the search is started
        document.getElementById('ShowProduct').style.display = 'none';

        // Perform your search logic here using searchQuery and selectedOption
        console.log('Search Query:', searchQuery);
        console.log('Selected Option:', selectedOption);

        // Make an AJAX call to the server-side script to search the database
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'search.php', true); // Adjust the URL to your actual PHP script
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('FeedAndResults').innerHTML = xhr.responseText;
            }
        };

        xhr.send('query=' + encodeURIComponent(searchQuery));
    }
</script>