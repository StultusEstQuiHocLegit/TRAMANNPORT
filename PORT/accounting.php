<?php
echo '<h1>🧮 ACCOUNTING</h1>';



// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// metrics
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && $_GET['action'] === 'ShowMetrics') {
    echo "<h3>📊 METRICS</h3>";

    echo "metrics - currently under construction, if you need this function urgently, tell Lasse to hurry up   ; )";



    echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
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
echo '<a href="index.php?content=accounting.php&action=ShowMetrics" class="button">📊 METRICS</a><br><br>';
echo '<a href="index.php?content=accounting.php&action=ShowRising" class="button">📈 RISING</a><br><br>';
echo '<a href="index.php?content=accounting.php&action=ShowDeclining" class="button">📉 DECLINING</a><br><br>';

echo '<h3>⚖️ FINANCIAL ACCOUNTING</h3>';
echo '<a href="index.php?content=accounting.php&action=ShowAllTransactions" class="button">↔️ ALL TRANSACTIONS</a><br><br>';
echo '<a href="index.php?content=accounting.php&action=ShowCashMethodOfAccounting" class="button">📄 CASH METHOD OF ACCOUNTING</a><br><br>';



?>