<?php
// Check for preselected search option
$preselectedOption = $preselectedOption ?? '';
// echo "test $preselectedOption"; // This should output the value
// Check for preselected viewing
$preselectedViewing = $preselectedViewing ?? '';
// echo "test $preselectedViewing"; // This should output the value
?>

<br><br>

<div style="display: flex; width: 100%; justify-content: center; align-items: center;">
    <input type="search" id="SearchBar" name="SearchBar" placeholder="explore ..." style="width: 100%; font-weight: bold; font-size: 1.2rem;">
</div>
<br>
<div style="display: flex; width: 100%; justify-content: center; align-items: center;">
    <a href="javascript:void(0)" id="StartSearchButton" class="mainbutton" onclick="StartSearch()"">🔍 SEARCH</a>
    <div style="width: 5px;"></div>
    <a href="javascript:void(0)" id="ShowSearchOptionsDiv" class="button" onclick="ShowSearchOptionsDiv()"">v</a>
</div>
<div id="SearchOptionsDiv">
    <br>
    <select id="SearchOptions" class="search-dropdown" style="width: 300px;">
        <option value="products_services" <?php echo ($preselectedOption === 'products_services') ? 'selected' : ''; ?>>
            search for products and services
        </option> <!-- // all products and services -->

        <?php if ($userRole === 1) { ?>
            <option value="your_products_services" <?php echo ($preselectedOption === 'your_products_services') ? 'selected' : ''; ?>>
                search for your products and services
            </option> <!-- // add this for creators -->
        <?php } ?>
        
        <option value="creators_explorers" <?php echo ($preselectedOption === 'creators_explorers') ? 'selected' : ''; ?>>
            search for creators and explorers
        </option> <!-- // all creators and explorers (for the latter only the ones you had transactions with) -->
        
        <?php if ($userRole === 1) { ?>
            <option value="your_explorers_customers" <?php echo ($preselectedOption === 'your_explorers_customers') ? 'selected' : ''; ?>>
                search for your explorers (customers)
            </option> <!-- // add this for creators -->
        <?php } ?>
        
        <?php if ($userRole === 1) { ?>
            <option value="your_creators_suppliers" <?php echo ($preselectedOption === 'your_creators_suppliers') ? 'selected' : ''; ?>>
                search for your creators (suppliers)
            </option> <!-- // add this for creators -->
        <?php } ?>
        
        <option value="transactions" <?php echo ($preselectedOption === 'transactions') ? 'selected' : ''; ?>>
            search for transactions
        </option> <!-- // only the ones you are involved in -->
        
        <option value="carts" <?php echo ($preselectedOption === 'carts') ? 'selected' : ''; ?>>
            search for carts
        </option> <!-- // only the ones you are involved in -->
    </select>
</div>

<br><br><br>

<?php
echo "<div id=\"ShowProduct\">";
    include ("ShowProduct.php");
echo "</div>";





echo "<div id=\"ShowCreatorOrExplorer\">";
    include ("ShowCreatorOrExplorer.php");
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

        // Add an event listener to the search input field for the Enter key
        const searchInput = document.getElementById('SearchBar');
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent form submission or default behavior
                StartSearch(); // Trigger the search function
            }
        });
    });


    function StartSearch() {
        const searchQuery = document.getElementById('SearchBar').value;
        const selectedOption = document.getElementById('SearchOptions').value;
        const selectedViewing = "<?php echo $preselectedViewing; ?>";

        document.getElementById('ShowProduct').style.display = 'none';
        document.getElementById('ShowCreatorOrExplorer').style.display = 'none';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'search.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('FeedAndResults').innerHTML = xhr.responseText;
            }
        };

        xhr.send('query=' + encodeURIComponent(searchQuery) + '&preselectedOption=' + encodeURIComponent(selectedOption) + '&preselectedViewing=' + encodeURIComponent(selectedViewing));

    }
</script>