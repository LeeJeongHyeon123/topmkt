<?php
/**
 * 프로필 페이지 성능 테스트 스크립트
 * v3.66.0: 캐시 TTL 1시간 연장 효과 측정
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/models/UserOptimized.php';

echo "🚀 프로필 페이지 성능 테스트 (v3.66.0)\n";
echo str_repeat("=", 60) . "\n\n";

$userOptimized = new UserOptimized();
$testUserId = 4; // 우리집탄이 사용자

// 1. 캐시 무효화
echo "1️⃣ 캐시 무효화 테스트\n";
$userOptimized->invalidateProfileCache($testUserId);
echo "   ✅ 캐시 삭제 완료\n\n";

// 2. 첫 번째 로드 (캐시 미스 - 느림)
echo "2️⃣ 첫 번째 로드 (캐시 MISS 예상)\n";
$start = microtime(true);
$profile1 = $userOptimized->getOptimizedProfileDataWithCache($testUserId);
$time1 = (microtime(true) - $start) * 1000;
echo "   ⏱️ 소요 시간: " . round($time1, 2) . "ms\n";
echo "   📊 데이터: " . ($profile1 ? "✅ 로드 성공" : "❌ 실패") . "\n\n";

// 3. 두 번째 로드 (캐시 히트 - 빠름)
echo "3️⃣ 두 번째 로드 (캐시 HIT 예상)\n";
$start = microtime(true);
$profile2 = $userOptimized->getOptimizedProfileDataWithCache($testUserId);
$time2 = (microtime(true) - $start) * 1000;
echo "   ⏱️ 소요 시간: " . round($time2, 2) . "ms\n";
echo "   📊 데이터: " . ($profile2 ? "✅ 로드 성공" : "❌ 실패") . "\n\n";

// 4. 성능 개선율 계산
echo "4️⃣ 성능 분석\n";
$improvement = (($time1 - $time2) / $time1) * 100;
echo "   📈 캐시 없을 때: " . round($time1, 2) . "ms\n";
echo "   ⚡ 캐시 있을 때: " . round($time2, 2) . "ms\n";
echo "   🎯 성능 개선율: " . round($improvement, 1) . "%\n";
echo "   🔄 속도 비율: " . round($time1 / $time2, 1) . "배 빠름\n\n";

// 5. 캐시 정보 확인
echo "5️⃣ 캐시 상태 확인\n";
$cacheFile = ROOT_PATH . '/cache/profile_' . $testUserId . '.json';
if (file_exists($cacheFile)) {
    $cacheAge = time() - filemtime($cacheFile);
    $cacheTTL = 3600; // 1시간
    $remainingTime = $cacheTTL - $cacheAge;
    
    echo "   📁 캐시 파일: 존재 ✅\n";
    echo "   ⏰ 캐시 나이: " . $cacheAge . "초\n";
    echo "   ⏳ 남은 시간: " . $remainingTime . "초 (" . round($remainingTime / 60, 1) . "분)\n";
    echo "   📏 파일 크기: " . round(filesize($cacheFile) / 1024, 2) . " KB\n";
} else {
    echo "   📁 캐시 파일: 없음 ❌\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ 테스트 완료!\n";

if ($time1 > 1000 && $time2 < 100) {
    echo "🎉 성능 최적화 성공: 캐시가 정상 작동합니다!\n";
} elseif ($time1 > 500) {
    echo "⚠️ 캐시 미스 시 여전히 느림: 추가 최적화 필요\n";
} else {
    echo "✅ 전반적으로 빠른 성능\n";
}
