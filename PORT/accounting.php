<?php
echo '<h1>🧮 ACCOUNTING</h1>';



// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// metrics
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && $_GET['action'] === 'ShowMetrics') {
    echo "<h3>📊 METRICS</h3>";

    // Get the date range filter
    $dateRange = isset($_GET['dateRange']) ? $_GET['dateRange'] : 'all';

    // Calculate the start date based on the selected range
    $currentDate = new DateTime();
    switch ($dateRange) {
        case '30days':
            $startDate = $currentDate->modify('-30 days')->format('Y-m-d');
            break;
        case '120days':
            $startDate = $currentDate->modify('-120 days')->format('Y-m-d');
            break;
        case '365days':
            $startDate = $currentDate->modify('-365 days')->format('Y-m-d');
            break;
        case '5years':
            $startDate = $currentDate->modify('-5 years')->format('Y-m-d');
            break;
        case '10years':
            $startDate = $currentDate->modify('-10 years')->format('Y-m-d');
            break;
        default:
            $startDate = null; // No limit for 'all time'
            break;
    }

    try {
        // Build the query with optional date range
        $query = "SELECT t.IdpkProductOrService, SUM(t.quantity) AS totalQuantity, SUM(t.AmountInDollars) AS totalAmount
                  FROM transactions t
                  INNER JOIN ProductsAndServices p ON t.IdpkProductOrService = p.idpk
                  WHERE t.state >= 3
                  AND p.IdpkCreator = :user_id";

        if ($startDate) {
            $query .= " AND t.TimestampCreation >= :start_date";
        }

        $query .= " GROUP BY t.IdpkProductOrService";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($startDate) {
            $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        }

        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare data for visualization
        $cumulativeQuantity = 0;
        $cumulativeAmount = 0.0;
        $productData = [];

        foreach ($transactions as $transaction) {
            $cumulativeQuantity += $transaction['totalQuantity'];
            $cumulativeAmount += $transaction['totalAmount'];

            $productData[] = [
                'productId' => $transaction['IdpkProductOrService'],
                'quantity' => $transaction['totalQuantity'],
                'amount' => $transaction['totalAmount']
            ];
        }

        echo '<div style="display: none;">';
        // Return results as JSON for AJAX
        echo json_encode([
            'cumulativeQuantity' => $cumulativeQuantity,
            'cumulativeAmount' => $cumulativeAmount,
            'productData' => $productData
        ]);
        echo "</div>";
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }

?>

<!-- Render Metrics Dropdown and Metrics Chart -->
<div>
    <select id="dateRangeSelect" onchange="updateMetrics()" style="width: 300px;">
        <option value="30days" <?= $dateRange === '30days' ? 'selected' : '' ?>>last 30 days</option>
        <option value="120days" <?= $dateRange === '120days' ? 'selected' : '' ?>>last 120 days</option>
        <option value="365days" <?= $dateRange === '365days' ? 'selected' : '' ?>>last 365 days</option>
        <option value="5years" <?= $dateRange === '5years' ? 'selected' : '' ?>>last 5 years</option>
        <option value="10years" <?= $dateRange === '10years' ? 'selected' : '' ?>>last 10 years</option>
        <option value="all" <?= $dateRange === 'all' ? 'selected' : '' ?>>all time</option>
    </select>
</div>

<!-- Render Cumulative Metrics Chart -->
<canvas id="cumulativeChart"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize chart data (initial values)
let cumulativeQuantity = 0;
let cumulativeAmount = 0.0;
let productData = [];

const updateMetrics = () => {
    const dateRange = document.getElementById('dateRangeSelect').value;

    // AJAX request to get new data without page reload
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `index.php?content=accounting.php&action=ShowMetrics&dateRange=${dateRange}`, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);

            if (response.error) {
                alert(response.error);
                return;
            }

            // Update the data
            cumulativeQuantity = response.cumulativeQuantity;
            cumulativeAmount = response.cumulativeAmount;
            productData = response.productData;

            // Update the charts with new data
            updateCharts();
        } else {
            alert('Error loading metrics');
        }
    };
    xhr.send();
};

const updateCharts = () => {
    // Update cumulative chart
    const ctx = document.getElementById('cumulativeChart').getContext('2d');
    const cumulativeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total Quantity', 'Total Amount'],
            datasets: [{
                label: 'Cumulative Metrics',
                data: [cumulativeQuantity, cumulativeAmount],
                backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Update per-product data chart
    const productCtx = document.getElementById('productChart').getContext('2d');
    const productChart = new Chart(productCtx, {
        type: 'bar',
        data: {
            labels: productData.map(item => item.productId),
            datasets: [
                {
                    label: 'Quantity Sold',
                    data: productData.map(item => item.quantity),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Amount ($)',
                    data: productData.map(item => item.amount),
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
};

// Initialize charts with initial values
updateCharts();
</script>

<!-- Render Per-Product Data Chart -->
<canvas id="productChart"></canvas>

<?php
// echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
}


















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// rising
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && $_GET['action'] === 'ShowRising') {
    echo "<h3>📈 RISING</h3>";

    echo "rising - currently under construction, if you need this function urgently, tell Lasse to hurry up   ; )";



    echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
}

















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// declining
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && $_GET['action'] === 'ShowDeclining') {
    echo "<h3>📉 DECLINING</h3>";

    echo "declining - currently under construction, if you need this function urgently, tell Lasse to hurry up   ; )";



    echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
}

















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// all transactions
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && $_GET['action'] === 'ShowAllTransactions') {
    echo "<h3>↔️ ALL TRANSACTIONS</h3>";

    echo "all transactions - currently under construction, if you need this function urgently, tell Lasse to hurry up   ; )";



    echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
}

















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// cash method of accounting
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && $_GET['action'] === 'ShowCashMethodOfAccounting') {
    echo "<h3>📄 CASH METHOD OF ACCOUNTING</h3>";

    echo "cash method of accounting - currently under construction, if you need this function urgently, tell Lasse to hurry up   ; )";



    echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
}


















// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// menu of the page as main part
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo '<h3>🎯 MANAGEMENT ACCOUNTING</h3>';
echo '<a href="index.php?content=accounting.php&action=ShowMetrics">📊 METRICS</a><br><br>';
echo '<a href="index.php?content=accounting.php&action=ShowRising">📈 RISING</a><br><br>';
echo '<a href="index.php?content=accounting.php&action=ShowDeclining">📉 DECLINING</a><br><br>';

echo '<h3>⚖️ FINANCIAL ACCOUNTING</h3>';
echo '<a href="index.php?content=accounting.php&action=ShowAllTransactions">↔️ ALL TRANSACTIONS</a><br><br>';
echo '<a href="index.php?content=accounting.php&action=ShowCashMethodOfAccounting">📄 CASH METHOD OF ACCOUNTING</a><br><br>';



?>