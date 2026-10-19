<?php
$html = file_get_contents('http://wp.local/blog/');
if (preg_match('/<(?:ul|div)[^>]*class=["\'][^"\']*om-blog-cards-grid[^"\']*["\'][\s\S]*?<\/(?:ul|div)>/', $html, $m)) {
    echo substr($m[0], 0, 1200) . "\n";
} else {
    echo "NO MATCH\n";
}
