<?php
// Check if action and idpk are set
if (isset($_GET['action']) && $_GET['action'] === 'ShowCarts' && isset($_GET['idpk'])) {
    // Retrieve the idpk from the URL
    $cartId = intval($_GET['idpk']);

    $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

    // Access global $pdo if not already defined
    global $pdo;

    // Fetch only cart-level details accessible by the given user
    $sql = "
        SELECT 
            c.idpk AS CartId,
            c.TimestampCreation AS CartCreationTimestamp,
            c.IdpkExplorerOrCreator AS BuyerId,
            c.manual,
            c.IfManualFurtherInformation,
            c.DeliveryType,
            c.WishedIdealDeliveryOrPickUpTime,

            SUM(COALESCE(ts.AmountInDollars, 0) + COALESCE(ts.ForTRAMANNPORTInDollars, 0) + COALESCE(ts.TaxesInDollars, 0)) AS TotalAmount,
            SUM(ts.AmountInDollars) AS TotalAmountIfSellingSide,

            SUM(ts.AmountInDollars) AS AmountInDollars,
            SUM(ts.ForTRAMANNPORTInDollars) AS ForTRAMANNPORTInDollars,
            SUM(ts.TaxesInDollars) AS TaxesInDollars,

            -- Buyer info
            ecBuyer.ExplorerOrCreator AS BuyerRole,
            ecBuyer.FirstName AS BuyerFirstName,
            ecBuyer.LastName AS BuyerLastName,
            ecBuyer.CompanyName AS BuyerCompanyName,
            ecBuyer.country AS BuyerCountry,
            ecBuyer.city AS BuyerCity,
            ecBuyer.ZIPCode AS BuyerZIPCode,
            ecBuyer.street AS BuyerStreet,
            ecBuyer.HouseNumber AS BuyerHouseNumber,
            ecBuyer.IBAN AS BuyerIBAN,
            ecBuyer.VATID AS BuyerVATID,
            ecBuyer.EmailForExplorersAsContact AS BuyerEmailForExplorersAsContact,
            ecBuyer.PhoneNumberForExplorersAsContact AS BuyerPhoneNumberForExplorersAsContact,
            ecBuyer.AdditionalTextForInvoices AS BuyerAdditionalTextForInvoices,

            -- Now aggregate creator info if multiple
            GROUP_CONCAT(DISTINCT ps.IdpkCreator SEPARATOR ',') AS CreatorIds,
            GROUP_CONCAT(DISTINCT IF(ecCreator.ExplorerOrCreator=1, ecCreator.CompanyName, CONCAT(ecCreator.FirstName, ' ', ecCreator.LastName)) SEPARATOR ' | ') AS CreatorNames,
            GROUP_CONCAT(DISTINCT CONCAT(ecCreator.country, ', ', ecCreator.city, ', ', ecCreator.ZIPCode, ', ', ecCreator.street, ' ', ecCreator.HouseNumber) SEPARATOR ' | ') AS CreatorAddresses,
            GROUP_CONCAT(DISTINCT ecCreator.ExplorerOrCreator SEPARATOR ',') AS CreatorRoles

        FROM carts c
        LEFT JOIN transactions ts ON c.idpk = ts.IdpkCart
        LEFT JOIN ProductsAndServices ps ON ts.IdpkProductOrService = ps.idpk
        LEFT JOIN ExplorersAndCreators ecBuyer ON ecBuyer.idpk = c.IdpkExplorerOrCreator
        LEFT JOIN ExplorersAndCreators ecCreator ON ecCreator.idpk = ps.IdpkCreator
        WHERE
            c.idpk = :cartId
            AND (c.IdpkExplorerOrCreator = :user_id OR ps.IdpkCreator = :user_id)
        GROUP BY c.idpk
        LIMIT 1;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$details) {
        // No cart found or not accessible by this user
        echo "<p>No accessible cart found.</p>";
        exit;
    }

    // Extract and format cart details
    $CartId = $details['CartId'];
    $IfManualFurtherInformation = $details['IfManualFurtherInformation'];
    $cartTimestamp = date('Y-m-d H:i:s', $details['CartCreationTimestamp']);
    $deliveryType = isset($details['DeliveryType']) ? (int)$details['DeliveryType'] : 0;

    $formattedAmount = number_format($details['TotalAmount'] ?? 0, 2);
    $formattedAmountIfSellingSide = number_format($details['TotalAmountIfSellingSide'] ?? 0, 2);



    $cartAmountInDollars = $details['AmountInDollars'];
    $cartAmountInOtherCurrency = $cartAmountInDollars * $ExchangeRateOneDollarIsEqualTo;

    $cartForTRAMANNPORTInDollars = $details['ForTRAMANNPORTInDollars'];
    $cartForTRAMANNPORTInOtherCurrency = $cartForTRAMANNPORTInDollars * $ExchangeRateOneDollarIsEqualTo;

    $cartAmountPlusForTRAMANNPORTInDollars = round($cartAmountInDollars + $cartForTRAMANNPORTInDollars, 2);
    $cartAmountPlusForTRAMANNPORTInOtherCurrency = round($cartAmountPlusForTRAMANNPORTInDollars * $ExchangeRateOneDollarIsEqualTo, 2);

    $cartTaxesInDollars = isset($details['TaxesInDollars']) ? round($details['TaxesInDollars'], 2) : 0;
    $cartTaxesInOtherCurrency = round($cartTaxesInDollars * $ExchangeRateOneDollarIsEqualTo, 2);

    $formattedAmount = number_format($details['TotalAmount'] ?? 0, 2);

    // Avoid division by zero
    if ($cartAmountPlusForTRAMANNPORTInDollars != 0) {
        $cartTaxesInPercent = round($cartTaxesInDollars / $cartAmountPlusForTRAMANNPORTInDollars, 2);
    } else {
        $cartTaxesInPercent = 0; // 0%
    }



    // Define a mapping for delivery type values
    $deliveryTypeMapping = [
        0 => 'standard',
        1 => 'express',
        2 => 'as soon as possible',
        3 => 'pick up in store',
        4 => 'best matching wished ideal delivery time'
    ];

    $formattedDeliveryType = $deliveryTypeMapping[$deliveryType] ?? 'unknown'; // Fallback to 'unknown' if the value is not in the mapping
    $cartWishedIdealDeliveryOrPickUpTime = isset($details['WishedIdealDeliveryOrPickUpTime']) ? date('Y-m-d H:i:s', $details['WishedIdealDeliveryOrPickUpTime']) : 'N/A';

    $isBuyer = ($details['BuyerId'] == $user_id);

    // Determine transaction partner:
    // If manual, just use IfManualFurtherInformation
    // Otherwise, if current user is buyer, partner is the creator (seller)
    // If current user is seller (creator), partner is the buyer
    if ($details['manual'] == 1) {
        // Manual transaction partner information
        $transactionPartnerName = $details['IfManualFurtherInformation'] . " (manual)";
        $transactionPartnerId = "";
        $partnerRole = null;
        $transactionPartnerAddress = "";
    } else {
        if ($isBuyer) {
            // If needed, we could restore logic here for the creators, 
            // but currently it's not fully implemented in the snippet.
            $partnerRole = ""; // Placeholder since logic isn't fully implemented in the snippet
            $transactionPartnerId = "";
            $transactionPartnerName = "";
            $transactionPartnerAddress = "";
        } else {
            // Current user is seller, partner is the buyer
            $partnerRole = $details['BuyerRole'];
            $transactionPartnerId = $details['BuyerId'];
            if ($partnerRole == 1) {
                // Buyer is a company (Creator account type)
                $transactionPartnerName = $details['BuyerCompanyName'];
            } else {
                $transactionPartnerName = trim($details['BuyerFirstName'] . " " . $details['BuyerLastName']);
            }
            // Combine partner address details if needed
            $transactionPartnerAddress = "{$details['BuyerCountry']}, {$details['BuyerCity']}, {$details['BuyerZIPCode']}, {$details['BuyerStreet']} {$details['BuyerHouseNumber']}";
        }
    }

    // Attempt to load the company logo if partner is a company and not manual
    $profilePicturePath = null;
    if ($details['manual'] != 1 && $partnerRole == 1 && !empty($transactionPartnerId)) {
        // Partner is a company (Creator), try to load logo
        $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];
        $uploadDirCompany = './uploads/AccountPictures/';
        foreach ($imageExtensions as $ext) {
            $potentialPath = $uploadDirCompany . $transactionPartnerId . '.' . $ext;
            if (file_exists($potentialPath)) {
                $profilePicturePath = $potentialPath;
                break;
            }
        }
    }

    $opacity = "1";

    echo "<div id=\"DivContentToPDFTitleInvoice\" style=\"display: none;\">";
        echo "<h1>↔️ INVOICE {$CartId}</h1>";
    echo "</div>";
    echo "<div id=\"DivContentToPDFTitleDeliveryReceipt\" style=\"display: none;\">";
        echo "<h1>🚚 DELIVERY RECEIPT {$CartId}</h1>";
    echo "</div>";

    echo "<div id=\"DivContentToPDFCart\">";

        // Display Cart Header
        echo "<h3>"
            . ($isBuyer ? "<span title='you bought' style='color:red;'>◀</span>" : "<span title='you sold' style='color:green;'>▶</span>")
            . " CART {$CartId}</h3>";

        // Display Cart Details
        echo "<table style='width: 100%; text-align: left;'>";
        echo "<tr style='opacity: {$opacity};'>";

        echo "<td style='width: 5px;'></td>";
        echo "<td>{$cartTimestamp}</td>";
        echo "<td style='width: 5px;'></td>";

        if ($details['manual'] == 1) {
            // Manual scenario
            $transactionPartnerFullName = htmlspecialchars($transactionPartnerName);
            echo "<td title='{$transactionPartnerFullName}' style='text-align:center;'>{$transactionPartnerFullName}</td>";
        } else {
            if (!empty($partnerRole)) {
                // Non-manual scenario: show partner link and title with address
                echo "<td title='" . htmlspecialchars($transactionPartnerName) . " ({$transactionPartnerId}), {$transactionPartnerAddress}' style='text-align:center;'>";
                    
                    // If partner is a company and we have a logo, display it above the name
                    if ($partnerRole == 1 && $profilePicturePath) {
                        echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$transactionPartnerId}'>
                                <img src=\"" . htmlspecialchars($profilePicturePath) . "\" style=\"height:50px; display:block; margin: 0 auto;\">
                              </a>";
                    }
                    echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$transactionPartnerId}'>"
                        . htmlspecialchars($transactionPartnerName) . " ({$transactionPartnerId})</a>";
                    
                    echo "<span class='OnlyDisplayInPDF' style=\"display: none;\">";
                        echo " ◆ {$transactionPartnerAddress}";
                        if (!empty($details['BuyerIBAN'])) {
                            echo " ◆ IBAN: {$details['BuyerIBAN']}";
                        }
                        if ($partnerRole == 1) { // only for other creators (businesses)
                            if (!empty($details['BuyerVATID'])) {
                                echo " ◆ VATID: {$details['BuyerVATID']}";
                            }
                            if (!empty($details['BuyerEmailForExplorersAsContact'])) {
                                echo " ◆ ✉️ {$details['BuyerEmailForExplorersAsContact']}";
                            }
                            if (!empty($details['BuyerPhoneNumberForExplorersAsContact'])) {
                                echo " ◆ 📞 {$details['BuyerPhoneNumberForExplorersAsContact']}";
                            }
                            if (!empty($details['BuyerAdditionalTextForInvoices'])) {
                                echo "<span class='OnlyDisplayInPDFInInvoice' style=\"display: none;\"><br>{$details['BuyerAdditionalTextForInvoices']}</span>";
                            }
                        }
                    echo "<span>";

                echo "</td>";
            } else {
                // $partnerRole is empty - no partner information
                echo "<td></td>";
            }
        }

        echo "<td class='DontDisplayInPDF'>" . (!$isBuyer ? "you received <strong>{$formattedAmountIfSellingSide}$</strong>" : "you paid <strong>{$formattedAmount}$</strong>") . "</td>";

        echo "<td class='OnlyDisplayInPDFInInvoice' style=\"display: none; border: 3px solid;\"><strong>";
            if ($ExchangeRateCurrencyCode == "USD") {
                $totalGrossPriceInUSD = round($cartAmountPlusForTRAMANNPORTInDollars + $cartTaxesInDollars, 2);
                echo "total net price: $cartAmountPlusForTRAMANNPORTInDollars, taxes: $cartTaxesInDollars ($cartTaxesInPercent%), total gross price therefore: $totalGrossPriceInUSD (in USD)";
            } else {
                $totalGrossPriceInOtherCurrency = round($cartAmountPlusForTRAMANNPORTInOtherCurrency + $cartTaxesInOtherCurrency, 2);
                $totalGrossPriceInUSD = round($cartAmountPlusForTRAMANNPORTInDollars + $cartTaxesInDollars, 2);
                echo "total net price: $cartAmountPlusForTRAMANNPORTInOtherCurrency, taxes: $cartTaxesInOtherCurrency ($cartTaxesInPercent%), total gross price therefore: $totalGrossPriceInOtherCurrency (in $ExchangeRateCurrencyCode)";
                echo "<br><span style=\"opacity: 0.5;\">(total net price: $cartAmountPlusForTRAMANNPORTInDollars, taxes: $cartTaxesInDollars ($cartTaxesInPercent%), total gross price therefore: $totalGrossPriceInUSD (in USD))</span>";
            }
        echo "</strong></td>";

        echo "<td style='width: 5px;'></td>";
        if ($deliveryType == 3 || $deliveryType == 4) {
            echo "<td>{$formattedDeliveryType} ({$cartWishedIdealDeliveryOrPickUpTime})</td>";
        } else {
            echo "<td>{$formattedDeliveryType}</td>";
        }

        echo "</tr>";
        echo "</table>";

    echo "</div>";






















    echo "<br><br><br><br>";
    echo "<table style='width: 100%; text-align: center;'>";
        echo "<tr>";
            // echo "<td>";
            //     echo "<a href='#' onclick='generatePDF({$CartId}, \"download\", \"invoice\")' class='button'>📄 DOWNLOAD INVOICE</a>";
            //     echo "<br><br><a href='#' onclick='generatePDF({$CartId}, \"download\", \"deliveryReceipt\")' class='button'>📄 DOWNLOAD DELIVERY RECEIPT</a>";
            // echo "</td>";
            // echo "<td>";
            //     echo "<a href='#' onclick='generatePDF({$CartId}, \"email\", \"invoice\")' class='button'>✉️ EMAIL INVOICE</a>";
            //     echo "<br><br><a href='#' onclick='generatePDF({$CartId}, \"email\", \"deliveryReceipt\")' class='button'>✉️ EMAIL DELIVERY RECEIPT</a>";
            // echo "</td>";
            // echo "<td>";
            //     echo "<a href='#' onclick='generatePDF({$CartId}, \"print\", \"invoice\")' class='button'>🖨️ PRINT INVOICE</a>";
            //     echo "<br><br><a href='#' onclick='generatePDF({$CartId}, \"print\", \"deliveryReceipt\")' class='button'>🖨️ PRINT DELIVERY RECEIPT</a>";
            // echo "</td>";
            echo "<td style='text-align: center;'>";
                echo "<a href='#' onclick='generatePDF({$CartId}, \"download\", \"invoice\")' class='button'>↔️ INVOICE DOWNLOAD</a>";
            echo "</td>";
            $opacityForTheDeliveryReceiptButton = $isBuyer ? '0.5' : '1'; // reduce opacity if the user is the buyer
            echo "<td style='text-align: center;'>";
                echo "<a href='#' onclick='generatePDF({$CartId}, \"download\", \"deliveryReceipt\")' class='button' style='opacity: $opacityForTheDeliveryReceiptButton;'>🚚 DELIVERY RECEIPT DOWNLOAD</a>";
            echo "</td>";
        echo "</tr>";
    echo "</table>";

























    echo "<div id=\"DivContentToPDFAssociatedTransactions\">";
    
        echo "<br><br><br><br><br><strong>🖇️ ASSOCIATED TRANSACTIONS</strong>";
        echo "<br><br>";

        /**
         * Function to display all associated transactions of a cart
         *
         * @param int $cartId
         * @param int $user_id
         */
        function displayAssociatedTransactions($cartId, $user_id, $ExchangeRateCurrencyCode, $ExchangeRateOneDollarIsEqualTo) {
            global $pdo;

            // First, determine if the user is the buyer or seller in this cart
            $isBuyer = false;

            // Fetch cart details to determine the role
            $cartSql = "SELECT IdpkExplorerOrCreator FROM carts WHERE idpk = :cartId LIMIT 1";
            $cartStmt = $pdo->prepare($cartSql);
            $cartStmt->bindParam(':cartId', $cartId, PDO::PARAM_INT);
            $cartStmt->execute();
            $cartDetails = $cartStmt->fetch(PDO::FETCH_ASSOC);

            if ($cartDetails && $cartDetails['IdpkExplorerOrCreator'] == $user_id) {
                $isBuyer = true;
            }

            // Prepare SQL for associated transactions
            if ($isBuyer) {
                // If the user is a buyer, show all transactions in the same cart
                $associatedSql = "
                    SELECT 
                        t.idpk AS TransactionId,
                        t.IdpkExplorer AS BuyerId,
                        t.IdpkProductOrService AS ProductId,
                        t.IdpkCart AS CartId,
                        COALESCE(t.AmountInDollars, 0) + COALESCE(t.ForTRAMANNPORTInDollars, 0) + COALESCE(t.TaxesInDollars, 0) AS TotalPrice,
                        t.AmountInDollars AS TotalPriceIfSellingSide,
                        t.CommentsNotesSpecialRequests,
                        t.state,
                        t.quantity,
                        ps.name AS ProductName,
                        ps.ShortDescription AS ShortDescription,
                        ps.state AS ProductState,
                        ps.IdpkCreator AS CreatorId,

                        c.manual,
                        c.IfManualFurtherInformation,

                        -- Buyer info
                        ecBuyer.ExplorerOrCreator AS BuyerRole,
                        ecBuyer.FirstName AS BuyerFirstName,
                        ecBuyer.LastName AS BuyerLastName,
                        ecBuyer.CompanyName AS BuyerCompanyName,

                        -- Creator (Seller) info
                        ecCreator.ExplorerOrCreator AS CreatorRole,
                        ecCreator.FirstName AS CreatorFirstName,
                        ecCreator.LastName AS CreatorLastName,
                        ecCreator.CompanyName AS CreatorCompanyName,
                        ecCreator.country AS CreatorCountry,
                        ecCreator.city AS CreatorCity,
                        ecCreator.ZIPCode AS CreatorZIPCode,
                        ecCreator.street AS CreatorStreet,
                        ecCreator.HouseNumber AS CreatorHouseNumber,
                        ecCreator.IBAN AS CreatorIBAN,
                        ecCreator.VATID AS CreatorVATID,
                        ecCreator.EmailForExplorersAsContact AS CreatorEmailForExplorersAsContact,
                        ecCreator.PhoneNumberForExplorersAsContact AS CreatorPhoneNumberForExplorersAsContact,
                        ecCreator.AdditionalTextForInvoices AS CreatorAdditionalTextForInvoices

                    FROM transactions t
                    LEFT JOIN ProductsAndServices ps ON t.IdpkProductOrService = ps.idpk
                    LEFT JOIN carts c ON t.IdpkCart = c.idpk
                    LEFT JOIN ExplorersAndCreators ecBuyer ON t.IdpkExplorer = ecBuyer.idpk
                    LEFT JOIN ExplorersAndCreators ecCreator ON ps.IdpkCreator = ecCreator.idpk
                    WHERE 
                        t.IdpkCart = :cartId
                        AND (t.state >= 3)
                    ORDER BY t.idpk ASC
                ";
                $associatedParams = [
                    ':cartId' => $cartId
                ];
            } else {
                // If the user is a seller, show only transactions where the product's creator is the user
                $associatedSql = "
                    SELECT 
                        t.idpk AS TransactionId,
                        t.IdpkExplorer AS BuyerId,
                        t.IdpkProductOrService AS ProductId,
                        t.IdpkCart AS CartId,
                        COALESCE(t.AmountInDollars, 0) + COALESCE(t.ForTRAMANNPORTInDollars, 0) + COALESCE(t.TaxesInDollars, 0) AS TotalPrice,
                        t.AmountInDollars AS TotalPriceIfSellingSide,
                        t.AmountInDollars AS AmountInDollars,
                        t.ForTRAMANNPORTInDollars AS ForTRAMANNPORTInDollars,
                        t.TaxesInDollars AS TaxesInDollars,
                        t.CommentsNotesSpecialRequests,
                        t.state,
                        t.quantity,
                        ps.name AS ProductName,
                        ps.ShortDescription AS ShortDescription,
                        ps.state AS ProductState,
                        ps.IdpkCreator AS CreatorId,

                        c.manual,
                        c.IfManualFurtherInformation,

                        -- Buyer info
                        ecBuyer.ExplorerOrCreator AS BuyerRole,
                        ecBuyer.FirstName AS BuyerFirstName,
                        ecBuyer.LastName AS BuyerLastName,
                        ecBuyer.CompanyName AS BuyerCompanyName,
                        ecBuyer.country AS BuyerCountry,
                        ecBuyer.city AS BuyerCity,
                        ecBuyer.ZIPCode AS BuyerZIPCode,
                        ecBuyer.street AS BuyerStreet,
                        ecBuyer.HouseNumber AS BuyerHouseNumber,
                        ecBuyer.IBAN AS BuyerIBAN,
                        ecBuyer.VATID AS BuyerVATID,
                        ecBuyer.EmailForExplorersAsContact AS BuyerEmailForExplorersAsContact,
                        ecBuyer.PhoneNumberForExplorersAsContact AS BuyerPhoneNumberForExplorersAsContact,
                        ecBuyer.AdditionalTextForInvoices AS BuyerAdditionalTextForInvoices,

                        -- Creator (Seller) info
                        ecCreator.ExplorerOrCreator AS CreatorRole,
                        ecCreator.FirstName AS CreatorFirstName,
                        ecCreator.LastName AS CreatorLastName,
                        ecCreator.CompanyName AS CreatorCompanyName

                    FROM transactions t
                    LEFT JOIN ProductsAndServices ps ON t.IdpkProductOrService = ps.idpk
                    LEFT JOIN carts c ON t.IdpkCart = c.idpk
                    LEFT JOIN ExplorersAndCreators ecBuyer ON t.IdpkExplorer = ecBuyer.idpk
                    LEFT JOIN ExplorersAndCreators ecCreator ON ps.IdpkCreator = ecCreator.idpk
                    WHERE 
                        t.IdpkCart = :cartId
                        AND ps.IdpkCreator = :userId
                        AND (t.state >= 3)
                    ORDER BY t.idpk ASC
                ";
                $associatedParams = [
                    ':cartId' => $cartId,
                    ':userId' => $user_id
                ];
            }

            $associatedStmt = $pdo->prepare($associatedSql);
            $associatedStmt->execute($associatedParams);
            $associatedTransactions = $associatedStmt->fetchAll(PDO::FETCH_ASSOC);

            if ($associatedTransactions) {
                echo "<table style='width: 100%; text-align: left;'>";
                foreach ($associatedTransactions as $assoc) {
                    // Process each associated transaction
                    $assocProductName = htmlspecialchars($assoc['ProductName'] ?? '');
                    $assocTruncatedName = strlen($assocProductName) > 50 ? substr($assocProductName, 0, 47) . "..." : $assocProductName;
                    $assocCommentsNotes = !empty($assoc['CommentsNotesSpecialRequests']) ? " (" . htmlspecialchars($assoc['CommentsNotesSpecialRequests']) . ")" : "";
                    $assocShortDescriptione = htmlspecialchars($assoc['ShortDescription'] ?? '');
                    $assocTruncatedShortDescriptione = strlen($assocShortDescriptione) > 100 ? substr($assocShortDescriptione, 0, 97) . "..." : $assocShortDescriptione;

                    $assocIsActive = isset($assoc['ProductState']) && $assoc['ProductState'] == 1;

                    $assocTransactionStateMapping = [
                        0 => 'collecting',
                        1 => 'ordered',
                        2 => 'paid',
                        3 => 'orders transmitted to creators',
                        4 => 'creators producing or selecting',
                        5 => 'creators shipping',
                        6 => 'in customs',
                        7 => 'at distribution center',
                        8 => 'arriving',
                        9 => 'finished'
                    ];

                    $assocTransactionState = isset($assoc['state']) ? (int)$assoc['state'] : 0;
                    $assocTranslatedTransactionState = $assocTransactionStateMapping[$assocTransactionState] ?? 'unknown';

                    $assocIsBuyer = ($assoc['BuyerId'] == $user_id);

                    $assocTransactionId = $assoc['TransactionId'];
                    $assocProductId = $assoc['ProductId'];
                    $assocQuantity = isset($assoc['quantity']) ? $assoc['quantity'] : 1;
                    $assocTotalPrice = isset($assoc['TotalPrice']) ? $assoc['TotalPrice'] : 0;
                    $assocTotalPriceIfSellingSide = isset($assoc['TotalPriceIfSellingSide']) ? $assoc['TotalPriceIfSellingSide'] : 0;
                    $assocCartId = isset($assoc['CartId']) ? $assoc['CartId'] : 0;



                    $assocTransactionAmountInDollars = isset($assoc['AmountInDollars']) ? $assoc['AmountInDollars'] : 0;
                    $assocTransactionAmountInOtherCurrency = $assocTransactionAmountInDollars * $ExchangeRateOneDollarIsEqualTo;

                    $assocTransactionForTRAMANNPORTInDollars = isset($assoc['ForTRAMANNPORTInDollars']) ? $assoc['ForTRAMANNPORTInDollars'] : 0;
                    $assocTransactionForTRAMANNPORTInOtherCurrency = $assocTransactionForTRAMANNPORTInDollars * $ExchangeRateOneDollarIsEqualTo;
                                    
                    $assocTransactionAmountPlusForTRAMANNPORTInDollars = round($assocTransactionAmountInDollars + $assocTransactionForTRAMANNPORTInDollars, 2);
                    $assocTransactionAmountPlusForTRAMANNPORTInOtherCurrency = round($assocTransactionAmountPlusForTRAMANNPORTInDollars * $ExchangeRateOneDollarIsEqualTo, 2);
                                    
                    $assocTransactionTaxesInDollars = isset($assoc['TaxesInDollars']) ? round($assoc['TaxesInDollars'], 2) : 0;
                    $assocTransactionTaxesInOtherCurrency = round($assocTransactionTaxesInDollars * $ExchangeRateOneDollarIsEqualTo, 2);
                                    
                    // Avoid division by zero
                    if ($assocTransactionAmountPlusForTRAMANNPORTInDollars != 0) {
                        $assocTransactionTaxesInPercent = round($assocTransactionTaxesInDollars / $assocTransactionAmountPlusForTRAMANNPORTInDollars, 2);
                    } else {
                        $assocTransactionTaxesInPercent = 0; // 0%
                    }



                    // Determine transaction partner:
                    if ($assoc['manual'] == 1) {
                        $assocTransactionPartnerName = htmlspecialchars($assoc['IfManualFurtherInformation']) . " (manual)";
                        $assocTransactionPartnerId = "";
                        $assocTruncatedTransactionPartnerName = $assocTransactionPartnerName;

                        // we don't need the following in this case
                        $assocTransactionPartnerCountry = "";
                        $assocTransactionPartnerCity = "";
                        $assocTransactionPartnerCityZIPCode = "";
                        $assocTransactionPartnerStreet = "";
                        $assocTransactionPartnerHouseNumber = "";
                        $assocTransactionPartnerIBAN = "";
                        $assocTransactionPartnerVATID = "";
                        $assocTransactionPartnerEmailForExplorersAsContact = "";
                        $assocTransactionPartnerPhoneNumberForExplorersAsContact = "";
                        $assocTransactionPartnerAdditionalTextForInvoices = "";
                    } else {
                        if ($assocIsBuyer) {
                            // Current user is buyer, partner is the creator
                            $assocPartnerRole = $assoc['CreatorRole'];
                            $assocTransactionPartnerId = $assoc['CreatorId'];
                            if ($assocPartnerRole == 1) { 
                                // ExplorerOrCreator=1 means a Creator representing a Company
                                $assocTransactionPartnerName = htmlspecialchars($assoc['CreatorCompanyName']);
                            } else {
                                $assocTransactionPartnerName = htmlspecialchars(trim($assoc['CreatorFirstName'] . " " . $assoc['CreatorLastName']));
                            }

                            $assocTransactionPartnerCountry = htmlspecialchars($assoc['CreatorCountry']);
                            $assocTransactionPartnerCity = htmlspecialchars($assoc['CreatorCity']);
                            $assocTransactionPartnerCityZIPCode = htmlspecialchars($assoc['CreatorZIPCode']);
                            $assocTransactionPartnerStreet = htmlspecialchars($assoc['CreatorStreet']);
                            $assocTransactionPartnerHouseNumber = htmlspecialchars($assoc['CreatorHouseNumber']);
                            $assocTransactionPartnerIBAN = htmlspecialchars($assoc['CreatorIBAN']);
                            $assocTransactionPartnerVATID = htmlspecialchars($assoc['CreatorVATID']);
                            $assocTransactionPartnerEmailForExplorersAsContact = htmlspecialchars($assoc['CreatorEmailForExplorersAsContact']);
                            $assocTransactionPartnerPhoneNumberForExplorersAsContact = htmlspecialchars($assoc['CreatorPhoneNumberForExplorersAsContact']);
                            $assocTransactionPartnerAdditionalTextForInvoices = htmlspecialchars($assoc['CreatorAdditionalTextForInvoices']);
                        } else {
                            // Current user is seller, partner is the buyer
                            $assocPartnerRole = $assoc['BuyerRole'];
                            $assocTransactionPartnerId = $assoc['BuyerId'];
                            if ($assocPartnerRole == 1) {
                                // Buyer is a company (Creator account type)
                                $assocTransactionPartnerName = htmlspecialchars($assoc['BuyerCompanyName']);
                            } else {
                                $assocTransactionPartnerName = htmlspecialchars(trim($assoc['BuyerFirstName'] . " " . $assoc['BuyerLastName']));
                            }

                            // we don't need the following in this case
                            $assocTransactionPartnerCountry = "";
                            $assocTransactionPartnerCity = "";
                            $assocTransactionPartnerCityZIPCode = "";
                            $assocTransactionPartnerStreet = "";
                            $assocTransactionPartnerHouseNumber = "";
                            $assocTransactionPartnerIBAN = "";
                            $assocTransactionPartnerVATID = "";
                            $assocTransactionPartnerEmailForExplorersAsContact = "";
                            $assocTransactionPartnerPhoneNumberForExplorersAsContact = "";
                            $assocTransactionPartnerAdditionalTextForInvoices = "";
                        }

                        $assocTruncatedTransactionPartnerName = strlen($assocTransactionPartnerName) > 50 ? substr($assocTransactionPartnerName, 0, 47) . "..." : $assocTransactionPartnerName;
                    }

                    // Set opacity based on conditions
                    $assocOpacity = "1";
                    if ($assocTransactionState != 3 && $assocTransactionState != 4) {
                        $assocOpacity = "0.5";
                    }

                    // echo "<tr style='opacity: {$assocOpacity};'>";
                    echo "</tr></tr><tr id='cart-products-{$assocTransactionId}' style='opacity: {$assocOpacity};'>";
                    echo "<td style='font-weight: bold; font-size: 1.2rem;'>" 
                        . ($assocIsBuyer ? "<span title='you bought' style='color:red;'>◀</span>" : "<span title='you sold' style='color:green;'>▶</span>") 
                        . "</td>";
                    echo "<td title='TRANSACTION {$assocTransactionId}'><a href='index.php?content=explore.php&action=ShowTransaction&idpk={$assocTransactionId}'>TRANSACTION {$assocTransactionId}</a></td>";
                    // echo "<td title='CART {$assocCartId}' class='DontDisplayInPDF'><a href='index.php?content=explore.php&action=ShowCarts&idpk={$assocCartId}'>(CART {$assocCartId})</a></td>";

                    if ($assoc['manual'] == 1) {
                        // Manual scenario
                        $assocTransactionPartnerFullName = htmlspecialchars($assocTransactionPartnerName);
                        // Truncate the manual text if it's longer than 30 characters
                        $assocTruncatedManualText = (strlen($assocTransactionPartnerFullName) > 30) 
                            ? substr($assocTransactionPartnerFullName, 0, 27) . '...' 
                            : $assocTransactionPartnerFullName;

                        echo "<td title='{$assocTransactionPartnerFullName}'>{$assocTruncatedManualText}</td>";
                    } else {
                        // Non-manual scenario: show partner link
                        echo "<span class='DontDisplayInPDF'>";
                            echo "<td title='" . htmlspecialchars($assocTransactionPartnerName) . " ({$assocTransactionPartnerId})' class='DontDisplayInPDF'>";
                            echo "<a href='index.php?content=explore.php&action=ShowCreatorOrExplorer&idpk={$assocTransactionPartnerId}'>";
                            echo "{$assocTruncatedTransactionPartnerName} ({$assocTransactionPartnerId})";
                            echo "</a>";
                            echo "</td>";
                        echo "</span>";

                        if ($isBuyer) { // current user is the buyer
                            echo "<td class='OnlyDisplayInPDF' style=\"display: none;\">";
                                echo "{$assocTransactionPartnerName} ({$assocTransactionPartnerId})";
                                echo " ◆ {$assocTransactionPartnerCountry}, {$assocTransactionPartnerCity}, {$assocTransactionPartnerCityZIPCode}, {$assocTransactionPartnerStreet} {$assocTransactionPartnerHouseNumber}";

                                if (!empty($assocTransactionPartnerIBAN)) {
                                    echo " ◆ IBAN: {$assocTransactionPartnerIBAN}";
                                }
                                if ($assocPartnerRole == 1) { // only for other creators (businesses)
                                    if (!empty($assocTransactionPartnerVATID)) {
                                        echo " ◆ VATID: {$assocTransactionPartnerVATID}";
                                    }
                                    if (!empty($assocTransactionPartnerEmailForExplorersAsContact)) {
                                        echo " ◆ ✉️ {$assocTransactionPartnerEmailForExplorersAsContact}";
                                    }
                                    if (!empty($assocTransactionPartnerPhoneNumberForExplorersAsContact)) {
                                        echo " ◆ 📞 {$assocTransactionPartnerPhoneNumberForExplorersAsContact}";
                                    }
                                    if (!empty($assocTransactionPartnerAdditionalTextForInvoices)) {
                                        echo "<span class='OnlyDisplayInPDFInInvoice' style=\"display: none;\"><br>{$assocTransactionPartnerAdditionalTextForInvoices}</span>";
                                    }
                                }
                            echo "<td>";
                        }
                    }

                    // echo "<td title='" . htmlspecialchars($assocProductName) . " ({$assocProductId})'><a href='index.php?content=explore.php&action=ShowProduct&idpk={$assocProductId}'>$assocTruncatedName ({$assocProductId})</a><br>{$assocCommentsNotes}</td>";
                    if ($isBuyer and $assoc['manual'] == 1) {
                        echo "<td></td>";
                        echo "<td></td>";
                    } else {
                        echo "<td><a href='index.php?content=explore.php&action=ShowProduct&idpk={$assocProductId}' title='" . htmlspecialchars($assocProductName) . " ({$assocProductId})'><span class='DontDisplayInPDF'>$assocTruncatedName</span> <span class='OnlyDisplayInPDF' style=\"display: none;\">$assocProductName</span> ({$assocProductId})</a><br><span title=\"" . htmlspecialchars($assocShortDescriptione) . "\" class='OnlyDisplayInPDF' style=\"opacity: 0.5; display: none;\">$assocTruncatedShortDescriptione<br></span><span style='font-weight: bold;'>{$assocCommentsNotes}</span></td>";
                        echo "<td style='font-weight: bold; font-size: 1.2rem;'>{$assocQuantity}x</td>";
                    }
                    echo "<td class='DontDisplayInPDF'>" . (!$assocIsBuyer ? "{$assocTotalPriceIfSellingSide}$" : "{$assocTotalPrice}$") . "</td>";

                    echo "<td class='OnlyDisplayInPDFInInvoice' style=\"display: none;\">";
                        if ($ExchangeRateCurrencyCode == "USD") {
                            $totalGrossPriceInUSD = round($assocTransactionAmountPlusForTRAMANNPORTInDollars + $assocTransactionTaxesInDollars, 2);
                            echo "total net price: $assocTransactionAmountPlusForTRAMANNPORTInDollars, taxes: $assocTransactionTaxesInDollars ($assocTransactionTaxesInPercent%), total gross price therefore: $totalGrossPriceInUSD (in USD)";
                        } else {
                            $totalGrossPriceInOtherCurrency = round($assocTransactionAmountPlusForTRAMANNPORTInOtherCurrency + $assocTransactionTaxesInOtherCurrency, 2);
                            $totalGrossPriceInUSD = round($assocTransactionAmountPlusForTRAMANNPORTInDollars + $assocTransactionTaxesInDollars, 2);
                            echo "total net price: $assocTransactionAmountPlusForTRAMANNPORTInOtherCurrency, taxes: $assocTransactionTaxesInOtherCurrency ($assocTransactionTaxesInPercent%), total gross price therefore: $totalGrossPriceInOtherCurrency (in $ExchangeRateCurrencyCode)";
                            echo "<br><span style=\"opacity: 0.5;\">(total net price: $assocTransactionAmountPlusForTRAMANNPORTInDollars, taxes: $assocTransactionTaxesInDollars ($assocTransactionTaxesInPercent%), total gross price therefore: $totalGrossPriceInUSD (in USD))</span>";
                        }
                    echo "</td>";

                    echo "<td title='(0 = collecting, 1 = ordered, 2 = paid, 3 = orders transmitted to creators, 4 = creators producing or selecting, 5 = creators shipping, 6 = in customs, 7 = at distribution center, 8 = arriving, 9 = finished)' class='DontDisplayInPDF'>{$assocTranslatedTransactionState}</td>";

                    // Actions based on user role and transaction state
                    if ($assocIsBuyer && $assoc['manual'] != 1) { // the user is the buyer
                        if ($assocIsActive) {
                            echo "<td class='DontDisplayInPDF'><a href='index.php?content=explore.php' onclick='addToCartGlow(event, {$assocProductId})' class='mainbutton'>🛒 REPICK</a></td>";
                        } else {
                            echo "<td class='DontDisplayInPDF'>inactive</td>";
                        }
                    } elseif ($assocIsBuyer && $assoc['manual'] == 1) {
                        echo "<td></td>";
                    } elseif ($assocTransactionState == 3 || $assocTransactionState == 4) { // the user is the seller
                        echo "<td class='DontDisplayInPDF'>";
                            if ($assocTransactionState == 3) {
                                echo "<a href='javascript:void(0);' id='4-link-{$assocTransactionId}' class='mainbutton' onclick='updateTransactionState(event, {$assocTransactionId}, \"4\")'>🛠️ WORKING</a>";
                                echo "<a href='javascript:void(0);' id='5-link-{$assocTransactionId}' class='mainbutton' style='display:none;' onclick='updateTransactionState(event, {$assocTransactionId}, \"5\")'>🚚 SHIPPING</a>";
                                echo "<br><a href='javascript:void(0);' id='3-link-{$assocTransactionId}' style='opacity: 0.4; display:none;' onclick='updateTransactionState(event, {$assocTransactionId}, \"3\")'>⬅️ BACK</a>";
                            } elseif ($assocTransactionState == 4) {
                                echo "<a href='javascript:void(0);' id='4-link-{$assocTransactionId}' class='mainbutton' style='display:none;' onclick='updateTransactionState(event, {$assocTransactionId}, \"4\")'>🛠️ WORKING</a>";
                                echo "<a href='javascript:void(0);' id='5-link-{$assocTransactionId}' class='mainbutton' onclick='updateTransactionState(event, {$assocTransactionId}, \"5\")' >🚚 SHIPPING</a>";
                                echo "<br><a href='javascript:void(0);' id='3-link-{$assocTransactionId}' style='opacity: 0.4;' onclick='updateTransactionState(event, {$assocTransactionId}, \"3\")'>⬅️ BACK</a>";
                            }
                        echo "</td>";
                    } else {
                        echo "<td></td>";
                    }

                    echo "</tr>";
                    echo "<tr></tr>";
                }
                echo "</table>";
            } else {
                echo "We are very sorry, but there were no associated transactions found. The payment may still be in processing, please come back later.";
            }
        }

        // Call the refactored function to display all associated transactions
        displayAssociatedTransactions($CartId, $user_id, $ExchangeRateCurrencyCode, $ExchangeRateOneDollarIsEqualTo);

        echo "<br><br><br><br><br><br><br><br><br><br>";
    
    echo "</div>";













    echo "<div id=\"DivContentToPDFAdditionalInformationForInvoices\" style=\"display: none;\">";
        echo "<br><br>";
        echo "<div style='opacity: 0.3;'>Unless otherwise stated, the invoice date and delivery date are the same. Payable immediately without deductions. The goods remain the property of the seller until full payment has been made.</div>";
        if (!empty($user['AdditionalTextForInvoices'])) {
            echo "<br>";
            echo "<div style='opacity: 0.5;'>";
                echo " {$user['AdditionalTextForInvoices']}";
            echo "</div>";
        }
    echo "</div>";


    echo "<div id=\"DivContentToPDFAdditionalInformationAboutTheOwnCompany\" style=\"display: none;\">";
        $currentDateTime = date('Y-m-d H:i:s'); // Format: YYYY-MM-DD HH:MM:SS
        echo "<br><br>";
        echo "<div style='opacity: 0.5;'>";
            echo "<table>";
                echo "<tr>";
                    echo "<td>";
                        // Get the user's `idpk` (already sanitized with `htmlspecialchars`)
                        $idpk = htmlspecialchars($user['idpk']);

                        // Define the possible image file extensions
                        $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif'];

                        // Base directory for profile pictures
                        $uploadDir = './uploads/AccountPictures/';

                        // Initialize a variable to hold the profile picture path (if found)
                        $profilePicturePath = null;

                        // Iterate through the possible extensions and check if the file exists
                        foreach ($imageExtensions as $ext) {
                            $potentialPath = $uploadDir . $idpk . '.' . $ext;
                            if (file_exists($potentialPath)) {
                                $profilePicturePath = $potentialPath;
                                break; // Exit the loop once we find the file
                            }
                        }
                    
                        // Display the profile picture if it exists
                        if ($profilePicturePath) {
                            // Output the image tag for the found profile picture
                            echo "<img src=\"$profilePicturePath\" style=\"height:50px;\">";
                        } else {
                            // If no profile picture is found, display nothing
                        }
                    echo "</td>";
                    echo "<td>";
                        echo "{$user['CompanyName']} ({$user['idpk']})";
                        echo " ◆ {$user['country']}, {$user['city']}, {$user['ZIPCode']}, {$user['street']} {$user['HouseNumber']}";
                        if (!empty($user['IBAN'])) {
                            echo " ◆ IBAN: {$user['IBAN']}";
                        }
                        if (!empty($user['VATID'])) {
                            echo " ◆ VATID: {$user['VATID']}";
                        }
                        if (!empty($user['EmailForExplorersAsContact'])) {
                            echo " ◆ ✉️ {$user['EmailForExplorersAsContact']}";
                        }
                        if (!empty($user['PhoneNumberForExplorersAsContact'])) {
                            echo " ◆ 📞 {$user['PhoneNumberForExplorersAsContact']}";
                        }
                        // echo " ◆ managing director: {$user['FirstName']} {$user['LastName']}";
                    echo "</td>";
                echo "</tr>";
            echo "</table>";
        echo "</div>";
        echo "<br><br>";
        echo "<div style='opacity: 0.2;'>";
            echo "This document was created by ";
            if ($user['ExplorerOrCreator'] == 0) { // explorer
                echo "{$user['FirstName']} {$user['LastName']} ({$user['idpk']}) ";
            } else { // creator
                echo "{$user['CompanyName']} ({$user['idpk']}) ";
            }            
            echo "on $currentDateTime ";
            if ($ExchangeRateCurrencyCode != "USD") {
                echo "<span class='DontDisplayInPDFInDeliveryReceipt'>(exchange rate: 1 USD is equal to $ExchangeRateOneDollarIsEqualTo $ExchangeRateCurrencyCode) </span>";
            }
            echo "using TRAMANN PORT.";

            echo "<br><br>";
            // Get the protocol (http or https)
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            // Get the host (domain name or IP)
            $host = $_SERVER['HTTP_HOST'];
            // Get the requested URI (path and query string)
            $requestUri = $_SERVER['REQUEST_URI'];
            // Construct the full URL
            $currentUrl = $protocol . $host . $requestUri;

            // echo "current URL: " . $currentUrl;

            // Display the QR code
            echo "<div id=\"qrcodeForCurrentSite\" style=\"display: flex; justify-content: center; align-items: center;\"></div>";

            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>';
            echo '<script>
                // PHP passes the current URL to JavaScript
                var currentUrl = "' . $currentUrl . '";

                // Generate the QR code
                var qrcodeForCurrentSite = new QRCode(document.getElementById("qrcodeForCurrentSite"), {
                    text: currentUrl,
                    width: 300,
                    height: 300
                });
            </script>';
        echo "</div>";
    echo "</div>";




    
    
    
    
    include("CreateDocumentsShowCarts.php");
}
?>






