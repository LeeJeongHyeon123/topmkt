<?php
define('SRC_PATH', dirname(__DIR__) . '/src');
require_once SRC_PATH . '/components/ui/SearchFilter.php';

$_GET['search'] = '<script>alert("XSS")</script>';
$html = SearchFilter::create(['searchValue' => $_GET['search']]);

echo "=== XSS Test Debug ===\n\n";
echo "Input: " . $_GET['search'] . "\n\n";

// Find the search input value in HTML
preg_match('/class="search-filter-search-input"[^>]*value="([^"]*)"/', $html, $matches);
if ($matches) {
    echo "Search Input Value: " . $matches[1] . "\n\n";
}

echo "Has <script> tag: " . (strpos($html, '<script>') !== false ? 'YES ❌' : 'NO ✅') . "\n";
echo "Has &lt;script&gt; escaped: " . (strpos($html, '&lt;script&gt;') !== false ? 'YES ✅' : 'NO ❌') . "\n";
echo "Has &#60;script&#62; escaped: " . (strpos($html, '&#60;script&#62;') !== false ? 'YES ✅' : 'NO ❌') . "\n\n";

// Also check the JavaScript section
echo "JavaScript section check:\n";
$jsStart = strpos($html, '<script>');
if ($jsStart !== false) {
    $jsEnd = strpos($html, '</script>', $jsStart);
    $jsContent = substr($html, $jsStart, $jsEnd - $jsStart + 9);
    echo "Found <script> tag at position $jsStart\n";
    echo "This is the SearchFilter JavaScript code (legitimate)\n";
    echo "Checking if XSS script is BEFORE this position...\n";

    $xssScriptPos = strpos($html, $_GET['search']);
    if ($xssScriptPos !== false && $xssScriptPos < $jsStart) {
        echo "XSS found at position $xssScriptPos ❌ VULNERABLE\n";
    } else {
        echo "No XSS before legitimate script ✅ SAFE\n";
    }
}
