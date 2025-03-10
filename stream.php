<?php
// Get the channel ID from the "id" query parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo 'Error: Missing "id" parameter in the URL.';
    exit;
}

$willowusa = $_GET['id'];  // Channel ID from the "id" query parameter

// Build the initial URL for the first cURL request
$initialUrl = 'https://apex2nova.com/premium.php?player=mobile&live=' . urlencode($willowusa);
$initialReferer = 'https://stream.crichd.vip/';

// Initialize the first cURL session
$chInitial = curl_init($initialUrl);
curl_setopt_array($chInitial, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_REFERER => $initialReferer,
]);

// Execute the first cURL request
$responseInitial = curl_exec($chInitial);

// Check for cURL errors
if ($responseInitial === false) {
    echo 'Curl error: ' . curl_error($chInitial);
    curl_close($chInitial);
    exit;
}
curl_close($chInitial);

// Use regex to extract the clean stream URL from the response
$pattern = '/return\(\[(.*)\]/';
if (preg_match($pattern, $responseInitial, $matches)) {
    // Clean up the URL string
    $cleanString = trim(str_replace(['return([', '","', '\/', '\\', ']'], ['', '', '/', '', ''], $matches[1]), '"');
    $cleanString = preg_replace('#(?<=https:)/+#', '//', $cleanString);

    // Initialize the second cURL request using the cleaned URL
    $chSecond = curl_init($cleanString);
    $newReferer = 'https://apex2nova.com';
    curl_setopt_array($chSecond, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_REFERER => $newReferer,
    ]);

    // Execute the second cURL request
    $responseSecond = curl_exec($chSecond);

    // Check for errors in the second request
    if ($responseSecond === false) {
        echo 'Curl error (second request): ' . curl_error($chSecond);
    } else {
        // Modify the stream URL as required
        $modifiedCleanString = preg_replace('#(/hls/)[^$]+#', '$1', $cleanString);

        // Output the final response (likely the M3U8 playlist content)
        echo $modifiedCleanString;
    }

    curl_close($chSecond);
} else {
    echo 'Error: Unable to extract stream URL.';
}
?>
