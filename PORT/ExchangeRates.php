<?php

// User's country (ensure it is sanitized)
$countryOfTheUser = strtolower(trim(htmlspecialchars($user['country'])));

$currencyMapping = [
    // Euro countries and their variations
    'eur' => [
        'germany', 'de', 'deutschland',
        'austria', 'at', 'österreich',
        'france', 'fr', 'france',
        'italy', 'it', 'italia',
        'spain', 'es', 'espana', 'españa',
        'portugal', 'pt', 'portugal',
        'ireland', 'ie', 'éire',
        'finland', 'fi', 'suomi',
        'greece', 'gr', 'ελλάδα', 'ellada',
        'belgium', 'be', 'belgique', 'belgië', 'belgien',
        'netherlands', 'nl', 'nederland',
        'luxembourg', 'lu', 'luxemburg', 'luxembourg',
        'cyprus', 'cy', 'κύπρος', 'kypros',
        'slovakia', 'sk', 'slovensko',
        'slovenia', 'si', 'slovenija',
        'estonia', 'ee', 'eesti',
        'latvia', 'lv', 'latvija',
        'lithuania', 'lt', 'lietuvos',
        'malta', 'mt', 'malta'
    ],
    // US Dollar countries
    'usd' => [
        'united states of america', 'usa', 'united states', 'us', 'america',
        'puerto rico', 'pr', 'guam', 'gu', 'american samoa', 'as',
        'u.s. virgin islands', 'vi', 'northern mariana islands', 'mp'
    ],
    // British Pound countries
    'gbp' => [
        'united kingdom', 'uk', 'britain', 'great britain', 'gb', 'england', 'scotland', 'wales', 'northern ireland',
        'gibraltar', 'gi', 'gibraltar',
        'jersey', 'je', 'jersey',
        'guernsey', 'gg', 'guernsey',
        'falkland islands', 'fk', 'falklands'
    ],
    // Swiss Franc
    'chf' => [
        'switzerland', 'ch', 'schweiz', 'suisse', 'svizzera', 'svizra',
        'liechtenstein', 'li', 'liechtenstein'
    ],
    // Japanese Yen
    'jpy' => [
        'japan', 'jp', '日本', 'nippon', 'nihon'
    ],
    // Chinese Yuan
    'cny' => [
        'china', 'cn', '中国', 'zhongguo'
    ],
    // Indian Rupee
    'inr' => [
        'india', 'in', 'bharat', 'भारत',
        'bhutan', 'bt', 'bhutanese ngultrum (at par)'
    ],
    // Russian Ruble
    'rub' => [
        'russia', 'ru', 'россия', 'rossiya'
    ],
    // Brazilian Real
    'brl' => [
        'brazil', 'br', 'brasil'
    ],
    // Canadian Dollar
    'cad' => [
        'canada', 'ca', 'canada'
    ],
    // Australian Dollar
    'aud' => [
        'australia', 'au', 'australia',
        'kiribati', 'ki', 'nauru', 'nr', 'tuvalu', 'tv'
    ],
    // New Zealand Dollar
    'nzd' => [
        'new zealand', 'nz', 'aotearoa',
        'pitcairn islands', 'pn'
    ],
    // South African Rand
    'zar' => [
        'south africa', 'za', 'zuid-afrika',
        'lesotho', 'ls', 'eswatini', 'sz', 'namibia', 'na'
    ],
    // Mexican Peso
    'mxn' => [
        'mexico', 'mx', 'méxico'
    ],
    // Singapore Dollar
    'sgd' => [
        'singapore', 'sg', '新加坡', 'singa pura'
    ],
    // Saudi Riyal
    'sar' => [
        'saudi arabia', 'sa', 'السعودية', 'saudi'
    ],
    // Turkish Lira
    'try' => [
        'turkey', 'tr', 'türkiye'
    ],
    // Swedish Krona
    'sek' => [
        'sweden', 'se', 'sverige'
    ],
    // Norwegian Krone
    'nok' => [
        'norway', 'no', 'norge'
    ],
    // Danish Krone
    'dkk' => [
        'denmark', 'dk', 'danmark',
        'faroe islands', 'fo', 'greenland', 'gl'
    ],
    // Thai Baht
    'thb' => [
        'thailand', 'th', 'ประเทศไทย', 'thai'
    ],
    // Argentinian Peso
    'ars' => [
        'argentina', 'ar', 'argentino', 'argentine'
    ],
    // Bangladeshi Taka
    'bdt' => [
        'bangladesh', 'bd', 'বাংলাদেশ', 'bangladeshi'
    ],
    // Bahraini Dinar
    'bhd' => [
        'bahrain', 'bh', 'البحرين', 'bahraini'
    ],
    // Botswana Pula
    'bwp' => [
        'botswana', 'bw', 'batswana', 'pula'
    ],
    // Chilean Peso
    'clp' => [
        'chile', 'cl', 'chileno', 'peso chileno'
    ],
    // Colombian Peso
    'cop' => [
        'colombia', 'co', 'colombiano', 'peso colombiano'
    ],
    // Egyptian Pound
    'egp' => [
        'egypt', 'eg', 'مصر', 'egyptian'
    ],
    // Ghanaian Cedi
    'ghs' => [
        'ghana', 'gh', 'ghanaian', 'cedi'
    ],
    // Indonesian Rupiah
    'idr' => [
        'indonesia', 'id', 'indonesian', 'rupiah'
    ],
    // Kenyan Shilling
    'kes' => [
        'kenya', 'ke', 'kenyan', 'shilling'
    ],
    // Kuwaiti Dinar
    'kwd' => [
        'kuwait', 'kw', 'الكويت', 'kuwaiti'
    ],
    // Malaysian Ringgit
    'myr' => [
        'malaysia', 'my', 'ماليزيا', 'malaysian'
    ],
    // Nigerian Naira
    'ngn' => [
        'nigeria', 'ng', 'naija', 'nigerian', 'naira'
    ],
    // Philippine Peso
    'php' => [
        'philippines', 'ph', 'pilipinas', 'peso'
    ],
    // Qatari Riyal
    'qar' => [
        'qatar', 'qa', 'قطر', 'qatar riyal'
    ],
    // South Korean Won
    'krw' => [
        'south korea', 'kr', 'korea', '대한민국', 'won'
    ],
    // Tanzanian Shilling
    'tzs' => [
        'tanzania', 'tz', 'tanzanian', 'shilling'
    ],
    // Vietnamese Dong
    'vnd' => [
        'vietnam', 'vn', 'việt nam', 'dong'
    ],
    // Venezuelan Bolívar
    'ves' => [
        'venezuela', 've', 'venezolano', 'bolívar'
    ],
    // Ugandan Shilling
    'ugx' => [
        'uganda', 'ug', 'ugandan', 'shilling'
    ],
    // Zambian Kwacha
    'zmw' => [
        'zambia', 'zm', 'zambian', 'kwacha'
    ],
    // Zimbabwean Dollar
    'zwl' => [
        'zimbabwe', 'zw', 'zimbabwean', 'dollar'
    ]
];

// Default currency code
$currencyCode = 'usd'; // fallback to USD if no match





















// Find matching currency
foreach ($currencyMapping as $currency => $countries) {
    if (in_array($countryOfTheUser, $countries)) {
        $currencyCode = strtoupper($currency); // Ensure ISO format (e.g., EUR, USD)
        break;
    }
}

// Check and update ExchangeRates
$currentTimestamp = time();
$query = "SELECT TimestampLastUpdate FROM ExchangeRates LIMIT 1";
$stmt = $pdo->query($query);
$lastUpdate = $stmt->fetch(PDO::FETCH_ASSOC)['TimestampLastUpdate'] ?? 0;

if ($currentTimestamp - (int)$lastUpdate > 86400) { // 24 hours = 86400 seconds

    // we are using this API for the exchange rates: https://www.exchangerate-api.com/docs/free

    // Fetch new rates from the API
    $apiUrl = "https://open.er-api.com/v6/latest/USD";
    $apiResponse = file_get_contents($apiUrl);
    $exchangeData = json_decode($apiResponse, true);

    if (isset($exchangeData['rates'])) {
        $pdo->beginTransaction(); // Start a transaction

        try {
            $updateQuery = "UPDATE ExchangeRates SET OneDollarIsEqualTo = ?, TimestampLastUpdate = ? WHERE CurrencyCode = ?";
            $updateStmt = $pdo->prepare($updateQuery);

            foreach ($exchangeData['rates'] as $currency => $rate) {
                $updateStmt->execute([$rate, $currentTimestamp, $currency]);
            }

            $pdo->commit(); // Commit the transaction
        } catch (Exception $e) {
            $pdo->rollBack(); // Rollback in case of an error
            error_log("Error updating exchange rates: " . $e->getMessage());
        }
    }
}

// Query the database to get ExchangeRate details
$query = "SELECT * FROM ExchangeRates WHERE CurrencyCode = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$currencyCode]);
$ExchangeRate = $stmt->fetch(PDO::FETCH_ASSOC);


















if ($ExchangeRate) {
    $ExchangeRateCurrencyCode = htmlspecialchars($ExchangeRate['CurrencyCode']);
    $ExchangeRateOneDollarIsEqualTo = htmlspecialchars($ExchangeRate['OneDollarIsEqualTo']);

    // echo "currency code: $ExchangeRateCurrencyCode<br>";
    // echo "1 USD equals $ExchangeRateOneDollarIsEqualTo $ExchangeRateCurrencyCode<br>";
} else { // fallback to USD
    $ExchangeRateCurrencyCode = "USD";
    $ExchangeRateOneDollarIsEqualTo = 1;
    // echo "We are very sorry, but we couldn't find the exchange rate for currency code: $currencyCode.";
}

?>
