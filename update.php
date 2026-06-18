<?php
// Settings
$source_url = "https://play.ksrtech.fun/playlist.php?token=fb5198ff3896583e4c7d92aee27400fa";
$full_file = "playlist.m3u";
$custom_file = "custom.m3u";

// Categories to INCLUDE
$include_prefixes = ['JTV+', 'XTRA']; // Will match "JTV+ Sports", "XTRA Movies", etc.
$include_exact = ['CricHD PACKAGE', 'SUNNXT PACKAGE', 'Z5 PACKAGE'];

// Categories to EXCLUDE
$exclude_keyword = 'SS CRICKET';

echo "Fetching playlist from source...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
// IMPORTANT: Many IPTV servers block if these headers aren't perfect
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: VLC/3.0.12 LibVLC/3.0.12',
    'Accept: */*',
    'Connection: keep-alive'
]);

$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || empty($data) || strpos($data, '#EXTM3U') === false) {
    die("Error: Source is unreachable or invalid. HTTP Code: $http_code\n");
}

// Save Full Playlist
file_put_contents($full_file, $data);
echo "Full playlist saved successfully.\n";

// --- CUSTOM FILTERING LOGIC ---
$lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $data));
$custom_content = "#EXTM3U\n";

$total_channels = 0;
$kept_channels = 0;

for ($i = 0; $i < count($lines); $i++) {
    $line = trim($lines[$i]);

    // Find the channel info line
    if (strpos($line, '#EXTINF') === 0) {
        $total_channels++;
        
        // Extract group-title
        preg_match('/group-title="([^"]+)"/', $line, $matches);
        $group = isset($matches[1]) ? $matches[1] : '';

        // Filtering Logic
        $should_keep = false;

        // 1. Check EXCLUDE keyword first
        if (stripos($group, $exclude_keyword) !== false) {
            continue; // Skip this channel entirely
        }

        // 2. Check Include (Prefixes: JTV+, XTRA)
        foreach ($include_prefixes as $prefix) {
            if (stripos($group, $prefix) === 0) {
                $should_keep = true;
                break;
            }
        }

        // 3. Check Include (Exact/Contains: Packages)
        if (!$should_keep) {
            foreach ($include_exact as $exact) {
                if (stripos($group, $exact) !== false) {
                    $should_keep = true;
                    break;
                }
            }
        }

        // If it passed the filter, get the URL (usually the next line)
        if ($should_keep) {
            $url_line = "";
            // Look for the next non-empty line which should be the URL
            for ($j = $i + 1; $j < count($lines); $j++) {
                $potential_url = trim($lines[$j]);
                if (!empty($potential_url) && strpos($potential_url, '#') !== 0) {
                    $url_line = $potential_url;
                    break;
                }
            }

            if (!empty($url_line)) {
                $custom_content .= $line . "\n" . $url_line . "\n";
                $kept_channels++;
            }
        }
    }
}

file_put_contents($custom_file, $custom_content);
echo "Filtering complete. Total: $total_channels | Filtered: $kept_channels channels saved to $custom_file\n";
?>
