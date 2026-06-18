<?php
$source_url = "https://play.ksrtech.fun/playlist.php?token=fb5198ff3896583e4c7d92aee27400fa";
$full_file = "playlist.m3u";
$custom_file = "custom.m3u";

// The categories you want to keep
$targets = ['JTV+', 'CricHD PACKAGE', 'SUNNXT PACKAGE', 'Z5 PACKAGE', 'XTRA'];

echo "Starting Powerful Fetcher...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_USERAGENT, 'VLC/3.0.12 LibVLC/3.0.12');

$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || empty($data)) {
    die("Error: Could not fetch source. HTTP $http_code\n");
}

// Save the full playlist first
file_put_contents($full_file, $data);
echo "Full playlist saved.\n";

// --- START FILTERING LOGIC ---
$lines = explode("\n", $data);
$custom_content = "#EXTM3U\n";
$keep_next = false;

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line === "#EXTM3U") continue;

    // Check if line is an INFO line
    if (strpos($line, '#EXTINF') !== false) {
        $match_found = false;
        foreach ($targets as $target) {
            // Check if the target category exists in the group-title
            if (stripos($line, 'group-title="' . $target) !== false || stripos($line, $target) !== false) {
                $match_found = true;
                break;
            }
        }

        if ($match_found) {
            $custom_content .= $line . "\n";
            $keep_next = true; // The very next line will be the URL
        } else {
            $keep_next = false;
        }
    } 
    // If this is a URL line and the previous INFO line matched our targets
    elseif ($keep_next && (strpos($line, 'http') === 0)) {
        $custom_content .= $line . "\n";
        $keep_next = false; // Reset for next entry
    }
}

// Save the custom playlist
if (file_put_contents($custom_file, $custom_content)) {
    echo "Custom playlist generated with selected categories.\n";
}
?>
