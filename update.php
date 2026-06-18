<?php
$source_url = "https://play.ksrtech.fun/playlist.php?token=fb5198ff3896583e4c7d92aee27400fa";
$output_file = "playlist.m3u";

echo "--- Debugging Information ---\n";
echo "Fetching from: $source_url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Set realistic headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: VLC/3.0.12 LibVLC/3.0.12',
    'Accept: */*',
    'Referer: https://play.ksrtech.fun/',
    'Origin: https://play.ksrtech.fun'
]);

$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Response Code: $http_code\n";

if ($data === false) {
    die("CURL Error: $curl_error\n");
}

// Check if the server returned an error page instead of a playlist
if (strpos($data, "<!DOCTYPE html>") !== false || strpos($data, "<html>") !== false) {
    echo "Warning: Server returned an HTML page instead of an M3U file. Content might be a login or error page.\n";
}

if ($http_code == 200 && !empty($data)) {
    if (file_put_contents($output_file, $data)) {
        echo "SUCCESS: Saved " . strlen($data) . " bytes to $output_file\n";
    } else {
        echo "ERROR: Could not write to file. Check folder permissions.\n";
    }
} else {
    echo "FAILED: No data received or invalid HTTP code.\n";
}
?>
