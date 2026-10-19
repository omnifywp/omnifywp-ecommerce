<?php
$html = file_get_contents(__DIR__ . '/generate_banners.html');
preg_match('/<head>([\s\S]*?)<\/head>/', $html, $headMatch);
$head = $headMatch[1] ?? '';

// Regex to extract each banner block
preg_match_all('/<!-- BANNER (\d+):[^-]+-->\s*(<div class="banner" id="banner-\1"[\s\S]*?<\/div>\s*<\/div>\s*<\/div>)/', $html, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $num = $match[1];
    $bannerHtml = $match[2];
    $full = "<!DOCTYPE html>\n<html>\n<head>" . $head . "<style>html,body{margin:0;padding:0;overflow:hidden;width:1200px;height:675px;}</style></head>\n<body>\n" . $bannerHtml . "\n</body>\n</html>";
    file_put_contents(__DIR__ . "/banners/banner{$num}.html", $full);
    echo "Saved banner{$num}.html\n";
}
