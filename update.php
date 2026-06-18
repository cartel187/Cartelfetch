<?php
// Configuration
$source_url = "https://play.ksrtech.fun/playlist.php?token=fb5198ff3896583e4c7d92aee27400fa";
$output_file = "playlist.m3u";

// Common IPTV User-Agents to bypass blocks
$user_agents = [
    'VLC/3.0.12 LibVLC/3.0.12',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
    'OTT Navigator/1.6.7.7'
];

echo "Starting fetcher...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, $user_agents[array_rand($user_agents)]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds timeout
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL errors if any
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: */*',
    'Connection: keep-alive',
    'Referer: https://play.ksrtech.fun/'
]);

$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || empty($data)) {
    die("Error: Failed to fetch playlist. HTTP Code: $http_code\n");
}

// POWERFUL STEP: Content Validation
// Check if it's actually an M3U file
if (strpos($data, "#EXTM3U") === false) {
    die("Error: The fetched content is not a valid M3U playlist.\n");
}

// POWERFUL STEP: Data Cleaning
// Remove empty lines and normalize line endings
$lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $data));
$cleaned_lines = [];

foreach ($lines as $line) {
    $trimmed = trim($line);
    if (!empty($trimmed)) {
        // Optional: Filter out specific keywords if needed
        // if (strpos($trimmed, 'ADULT') !== false) continue; 
        $cleaned_lines[] = $trimmed;
    }
}

$final_playlist = implode("\n", $cleaned_lines);

// Save the file
if (file_put_contents($output_file, $final_playlist)) {
    echo "Successfully updated $output_file (" . count($cleaned_lines) . " lines found).\n";
} else {
    echo "Error: Failed to save the file.\n";
}
?>
