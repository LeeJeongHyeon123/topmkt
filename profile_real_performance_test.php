<?php
/**
 * 🔥 울트라씽크 모드: 프로필 페이지 실제 성능 테스트
 * 실제 UserOptimized 클래스를 사용하여 정확한 성능 측정
 */

// 환경 설정
define('BASE_PATH', '/var/www/html/topmkt');
define('SRC_PATH', BASE_PATH . '/src');

// 필요한 파일들 include
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/UserOptimized.php';

echo "🔥 울트라씽크 모드: 프로필 페이지 실제 성능 테스트\n";
echo "=" . str_repeat("=", 80) . "\n\n";

// 테스트할 사용자 ID
$testUserId = 4;

// UserOptimized 인스턴스 생성
$userOptimized = new UserOptimized();

echo "📊 사용자 ID {$testUserId}에 대한 프로필 데이터 로딩 성능 테스트 시작...\n\n";

// 여러 번 테스트하여 평균 성능 측정
$iterations = 5;
$times = [];
$memoryUsage = [];

for ($i = 1; $i <= $iterations; $i++) {
    echo "🔄 테스트 #{$i}:\n";
    
    // 메모리 및 시간 측정 시작
    $startTime = microtime(true);
    $startMemory = memory_get_usage(true);
    
    try {
        // 실제 프로필 데이터 로딩 (캐시 포함)
        $profileData = $userOptimized->getOptimizedProfileDataWithCache($testUserId);
        
        // 측정 완료
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // ms로 변환
        $memoryDiff = $endMemory - $startMemory;
        $peakMemory = memory_get_peak_usage(true);
        
        $times[] = $executionTime;
        $memoryUsage[] = $memoryDiff;
        
        echo "   ⏱️  실행 시간: " . number_format($executionTime, 3) . "ms\n";
        echo "   💾 메모리 사용: " . number_format($memoryDiff / 1024, 2) . "KB\n";
        echo "   📈 최대 메모리: " . number_format($peakMemory / 1024 / 1024, 2) . "MB\n";
        
        // 데이터 품질 확인
        if ($profileData) {
            echo "   ✅ 데이터 로딩 성공\n";
            echo "   📄 게시글 수: " . ($profileData['stats']['post_count'] ?? 0) . "개\n";
            echo "   💬 댓글 수: " . ($profileData['stats']['comment_count'] ?? 0) . "개\n";
            echo "   👍 좋아요 수: " . ($profileData['stats']['like_count'] ?? 0) . "개\n";
            echo "   📝 최근 게시글: " . count($profileData['recent_posts'] ?? []) . "개\n";
            echo "   💭 최근 댓글: " . count($profileData['recent_comments'] ?? []) . "개\n";
        } else {
            echo "   ❌ 데이터 로딩 실패\n";
        }
        
        echo "\n";
        
        // 너무 빠른 연속 요청 방지
        usleep(100000); // 0.1초 대기
        
    } catch (Exception $e) {
        echo "   ❌ 오류 발생: " . $e->getMessage() . "\n\n";
        $times[] = -1; // 오류 표시
        $memoryUsage[] = 0;
    }
}

echo "\n📈 성능 분석 결과:\n";
echo "=" . str_repeat("=", 50) . "\n";

// 성공한 테스트만 필터링
$validTimes = array_filter($times, function($time) { return $time > 0; });
$validMemory = array_slice($memoryUsage, 0, count($validTimes));

if (count($validTimes) > 0) {
    $avgTime = array_sum($validTimes) / count($validTimes);
    $minTime = min($validTimes);
    $maxTime = max($validTimes);
    $avgMemory = array_sum($validMemory) / count($validMemory);
    
    echo "⏱️  평균 실행 시간: " . number_format($avgTime, 3) . "ms\n";
    echo "🚀 최고 성능: " . number_format($minTime, 3) . "ms\n";
    echo "🐌 최저 성능: " . number_format($maxTime, 3) . "ms\n";
    echo "💾 평균 메모리: " . number_format($avgMemory / 1024, 2) . "KB\n";
    
    echo "\n🎯 성능 평가:\n";
    if ($avgTime < 50) {
        echo "✅ 훌륭함: 50ms 미만 (매우 빠름)\n";
    } elseif ($avgTime < 200) {
        echo "✅ 좋음: 200ms 미만 (빠름)\n";
    } elseif ($avgTime < 500) {
        echo "⚠️  보통: 500ms 미만 (개선 가능)\n";
    } elseif ($avgTime < 1000) {
        echo "⚠️  느림: 1초 미만 (개선 필요)\n";
    } else {
        echo "❌ 매우 느림: 1초 이상 (즉시 개선 필요)\n";
    }
    
} else {
    echo "❌ 모든 테스트가 실패했습니다.\n";
}

// 캐시 상태 확인
echo "\n💾 캐시 시스템 상태:\n";
echo "=" . str_repeat("=", 30) . "\n";

$cacheFile = BASE_PATH . "/cache/profile_{$testUserId}.json";
if (file_exists($cacheFile)) {
    $cacheInfo = stat($cacheFile);
    $cacheAge = time() - $cacheInfo['mtime'];
    $cacheSize = $cacheInfo['size'];
    
    echo "✅ 캐시 파일 존재: " . basename($cacheFile) . "\n";
    echo "📅 캐시 생성일: " . date('Y-m-d H:i:s', $cacheInfo['mtime']) . "\n";
    echo "⏰ 캐시 나이: " . $cacheAge . "초 전\n";
    echo "📏 캐시 크기: " . number_format($cacheSize / 1024, 2) . "KB\n";
    
    if ($cacheAge > 600) { // 10분 이상
        echo "⚠️  캐시가 오래됨 (10분 초과)\n";
    } else {
        echo "✅ 캐시 상태 양호\n";
    }
} else {
    echo "❌ 캐시 파일 없음\n";
}

echo "\n🔚 테스트 완료\n";
echo "현재 시간: " . date('Y-m-d H:i:s') . "\n";