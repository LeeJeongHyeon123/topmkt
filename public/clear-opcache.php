<?php
// OPcache 클리어 스크립트
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared successfully\n";
} else {
    echo "❌ OPcache not available\n";
}

echo "PHP Version: " . phpversion() . "\n";
echo "OPcache enabled: " . (ini_get('opcache.enable') ? 'Yes' : 'No') . "\n";
