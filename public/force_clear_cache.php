<?php
// OPcache 클리어
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
}

// APCu 클리어
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cleared\n";
}

// 파일 시스템 캐시 클리어
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "✅ File stat cache cleared\n";
}

// 브라우저 캐시 방지 헤더
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

echo "\n🔄 Redirecting to admin/users with cache-busting...\n";
echo '<meta http-equiv="refresh" content="1;url=/admin/users?nocache=' . time() . '">';
?>
