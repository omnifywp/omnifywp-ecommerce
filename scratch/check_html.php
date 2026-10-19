<?php
$html = file_get_contents('http://wp.local/blog/');
preg_match_all('/<article[\s\S]*?<\/article>/', $html, $articles);
foreach ($articles[0] as $i => $art) {
    preg_match('/<h2[^>]*><a[^>]*>(.*?)<\/a><\/h2>/', $art, $titleMatch);
    preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $art, $imgMatch);
    preg_match('/<figure[^>]*>([\s\S]*?)<\/figure>/', $art, $figMatch);
    echo ($i+1) . ". Title: " . ($titleMatch[1] ?? 'NO TITLE') . "\n";
    echo "   Img: " . ($imgMatch[1] ?? 'NO IMG FOUND') . "\n";
    echo "   Fig: " . (isset($figMatch[1]) ? substr(strip_tags($figMatch[1]), 0, 50) : 'NO FIG') . "\n";
}
