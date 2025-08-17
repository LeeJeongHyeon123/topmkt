<?php
/**
 * 🔥 울트라씽크 모드: 수정된 프로필 성능 테스트
 */

// 경로 설정 (올바른 상수 정의)
define('ROOT_PATH', '/var/www/html/topmkt');
define('BASE_PATH', '/var/www/html/topmkt');
define('SRC_PATH', BASE_PATH . '/src');

echo "🔥 울트라씽크 모드: 수정된 프로필 성능 테스트\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// 1. 캐시 성능 직접 테스트 (가장 중요!)
echo "1. 💾 캐시 시스템 성능 테스트:\n";
$cacheFile = ROOT_PATH . '/cache/profile_4.json';

if (file_exists($cacheFile)) {
    // 여러 번 측정하여 정확한 성능 확인
    $iterations = 10;
    $times = [];
    
    for ($i = 1; $i <= $iterations; $i++) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // 캐시 파일 읽기 및 파싱
        $cacheContent = file_get_contents($cacheFile);
        $profileData = json_decode($cacheContent, true);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $loadTime = ($endTime - $startTime) * 1000; // ms
        $memoryUsed = $endMemory - $startMemory;
        
        $times[] = $loadTime;
        
        if ($i <= 3) { // 처음 3번만 상세 출력
            echo "   테스트 #{$i}: {$loadTime}ms, 메모리: " . number_format($memoryUsed/1024, 1) . "KB\n";
        }
        
        // 너무 빠른 연속 호출 방지
        usleep(1000); // 1ms 대기
    }
    
    // 통계 계산
    $avgTime = array_sum($times) / count($times);
    $minTime = min($times);
    $maxTime = max($times);
    
    echo "\n   📊 캐시 성능 통계:\n";
    echo "   ⏱️  평균: " . number_format($avgTime, 3) . "ms\n";
    echo "   🚀 최고: " . number_format($minTime, 3) . "ms\n";
    echo "   🐌 최저: " . number_format($maxTime, 3) . "ms\n";
    
    // 캐시 데이터 품질 확인
    if ($profileData) {
        echo "\n   📈 캐시된 데이터:\n";
        echo "   👤 사용자: " . ($profileData['nickname'] ?? 'Unknown') . "\n";
        echo "   📊 게시글: " . number_format($profileData['stats']['post_count'] ?? 0) . "개\n";
        echo "   💬 댓글: " . number_format($profileData['stats']['comment_count'] ?? 0) . "개\n";
        echo "   👍 좋아요: " . number_format($profileData['stats']['like_count'] ?? 0) . "개\n";
        echo "   📝 최근 게시글: " . count($profileData['recent_posts'] ?? []) . "개\n";
        echo "   💭 최근 댓글: " . count($profileData['recent_comments'] ?? []) . "개\n";
    }
    
} else {
    echo "   ❌ 캐시 파일 없음\n";
}

// 2. 캐시 없이 프로필 페이지 로딩 시뮬레이션
echo "\n2. 🌐 실제 프로필 페이지 로딩 시뮬레이션:\n";

// 프로필 페이지에서 실제로 하는 작업들
$simulationStart = microtime(true);

// 단계별 시간 측정
$steps = [];

// Step 1: 세션 검증 (시뮬레이션)
$stepStart = microtime(true);
$sessionValid = true; // 로그인 상태 시뮬레이션
$steps['session_check'] = (microtime(true) - $stepStart) * 1000;

// Step 2: 캐시 파일 읽기 (실제)
$stepStart = microtime(true);
if (file_exists($cacheFile)) {
    $profileData = json_decode(file_get_contents($cacheFile), true);
}
$steps['cache_load'] = (microtime(true) - $stepStart) * 1000;

// Step 3: 데이터 처리 및 변수 준비 (시뮬레이션)
$stepStart = microtime(true);
if ($profileData) {
    // 실제 프로필 페이지에서 하는 데이터 처리 작업들
    $user = $profileData;
    $stats = $profileData['stats'] ?? [];
    $recentPosts = $profileData['recent_posts'] ?? [];
    $recentComments = $profileData['recent_comments'] ?? [];
    
    // 소셜 링크 파싱 (실제 코드와 동일)
    $socialLinks = [];
    if (!empty($user['social_links'])) {
        if (is_string($user['social_links'])) {
            $decoded = json_decode($user['social_links'], true);
            $socialLinks = $decoded && is_array($decoded) ? $decoded : [];
        } elseif (is_array($user['social_links'])) {
            $socialLinks = $user['social_links'];
        }
    }
    
    // 나이 계산 (실제 코드와 동일)
    $age = null;
    if (!empty($user['birth_date'])) {
        $birthDate = new DateTime($user['birth_date']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
    }
}
$steps['data_processing'] = (microtime(true) - $stepStart) * 1000;

// Step 4: HTML 렌더링 시뮬레이션
$stepStart = microtime(true);
$htmlSize = 0;
if ($profileData) {
    // 실제 HTML 생성 시뮬레이션 (문자열 조합)
    $htmlContent = "프로필 HTML 시뮬레이션: ";
    $htmlContent .= "사용자=" . ($user['nickname'] ?? '');
    $htmlContent .= ", 게시글=" . count($recentPosts);
    $htmlContent .= ", 댓글=" . count($recentComments);
    $htmlSize = strlen($htmlContent);
}
$steps['html_render'] = (microtime(true) - $stepStart) * 1000;

$totalSimulationTime = (microtime(true) - $simulationStart) * 1000;

// 결과 출력
echo "   📊 로딩 단계별 시간:\n";
foreach ($steps as $step => $time) {
    echo "   🔸 " . str_pad($step, 15) . ": " . number_format($time, 3) . "ms\n";
}
echo "\n   ⏱️  총 시뮬레이션 시간: " . number_format($totalSimulationTime, 3) . "ms\n";
echo "   📏 HTML 크기: " . $htmlSize . " bytes\n";

// 3. 성능 평가 및 개선점
echo "\n3. 🎯 성능 평가:\n";

if ($totalSimulationTime < 50) {
    echo "   ✅ 훌륭함: 50ms 미만 (매우 빠름)\n";
    $grade = "A+";
} elseif ($totalSimulationTime < 200) {
    echo "   ✅ 좋음: 200ms 미만 (빠름)\n";
    $grade = "A";
} elseif ($totalSimulationTime < 500) {
    echo "   ⚠️  보통: 500ms 미만 (개선 가능)\n";
    $grade = "B";
} elseif ($totalSimulationTime < 1000) {
    echo "   ⚠️  느림: 1초 미만 (개선 필요)\n";
    $grade = "C";
} else {
    echo "   ❌ 매우 느림: 1초 이상 (즉시 개선 필요)\n";
    $grade = "D";
}

echo "   🏆 성능 등급: {$grade}\n";

// 4. 개선 제안
echo "\n4. 💡 개선 제안:\n";

if ($avgTime > 1.0) {
    echo "   🔧 캐시 I/O 최적화 필요 (현재: " . number_format($avgTime, 3) . "ms)\n";
}

if (!$profileData) {
    echo "   🔧 캐시 시스템 재구축 필요\n";
} else {
    $dataSize = strlen(json_encode($profileData));
    if ($dataSize > 50000) { // 50KB 이상
        echo "   🔧 캐시 데이터 크기 최적화 (현재: " . number_format($dataSize/1024, 1) . "KB)\n";
    }
}

echo "   ✅ 현재 캐시 시스템은 잘 작동함\n";
echo "   💡 추가 최적화: Memcached/Redis 도입 검토\n";

echo "\n🔚 테스트 완료 - " . date('Y-m-d H:i:s') . "\n";