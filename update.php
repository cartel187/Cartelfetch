<?php
// Configuration
$source_url = "https://play.ksrtech.fun/playlist.php?token=fb5198ff3896583e4c7d92aee27400fa";
$output_file = "playlist.m3u";

echo "Manual fetch started...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Longer timeout for manual fetch
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: VLC/3.0.12 LibVLC/3.0.12',
    'Accept: */*',
    'Referer: https://play.ksrtech.fun/'
]);

$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || empty($data)) {
    die("Error: Fetch failed. HTTP Code: $http_code. Ensure the token is still valid.\n");
}

// Basic M3U Validation
if (strpos($data, "#EXTM3U") === false) {
    die("Error: The data received is not a valid M3U playlist.\n");
}

if (file_put_contents($output_file, $data)) {
    echo "SUCCESS: $output_file has been generated and saved.\n";
} else {
    echo "ERROR: Failed to write to $output_file.\n";
}
?>
