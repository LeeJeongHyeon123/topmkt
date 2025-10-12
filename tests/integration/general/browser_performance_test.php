<?php
/**
 * 🔥 울트라씽크 모드: 브라우저 리소스 로딩 성능 테스트
 */

echo "🔥 울트라씽크 모드: 브라우저 리소스 로딩 성능 분석\n";
echo "=" . str_repeat("=", 80) . "\n\n";

// 테스트할 리소스들 (실제 프로필 페이지에서 로딩되는 파일들)
$resources = [
    // CSS 파일들
    'CSS' => [
        '/assets/css/main.css',
        '/assets/css/loading.css',
        '/assets/css/components/profile-modal.css'
    ],
    // JavaScript 파일들
    'JavaScript' => [
        '/assets/js/loading.js',
        '/assets/js/jwt-auth.js',
        '/assets/js/main.js',
        '/assets/js/chat-notifications.js',
        '/assets/js/registration-notifications-realtime.js',
        '/assets/js/profile-modal.js'
    ],
    // 이미지 파일들
    'Images' => [
        '/assets/images/favicon.svg',
        '/assets/images/default-avatar.png',
        '/assets/images/topmkt-og-image.png'
    ]
];

$baseUrl = 'https://www.topmktx.com';
$totalLoadTime = 0;
$totalSize = 0;

foreach ($resources as $category => $files) {
    echo "📁 {$category} 파일들:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $categoryTime = 0;
    $categorySize = 0;
    
    foreach ($files as $file) {
        $url = $baseUrl . $file;
        
        // 여러 번 측정하여 평균 계산
        $times = [];
        $sizes = [];
        
        for ($i = 0; $i < 3; $i++) {
            $startTime = microtime(true);
            
            // curl로 실제 파일 다운로드 시뮬레이션
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $downloadTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            $downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
            
            curl_close($ch);
            
            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000; // ms
            
            if ($httpCode == 200 && $content !== false) {
                $times[] = $totalTime;
                $sizes[] = $downloadSize;
            } else {
                $times[] = -1; // 실패 표시
                $sizes[] = 0;
            }
            
            usleep(100000); // 0.1초 대기
        }
        
        // 성공한 요청들만 계산
        $validTimes = array_filter($times, function($t) { return $t > 0; });
        $validSizes = array_slice($sizes, 0, count($validTimes));
        
        if (count($validTimes) > 0) {
            $avgTime = array_sum($validTimes) / count($validTimes);
            $avgSize = array_sum($validSizes) / count($validSizes);
            
            $categoryTime += $avgTime;
            $categorySize += $avgSize;
            
            echo "   📄 " . str_pad(basename($file), 35) . ": ";
            echo str_pad(number_format($avgTime, 1) . "ms", 8) . " | ";
            echo str_pad(number_format($avgSize / 1024, 1) . "KB", 8) . "\n";
        } else {
            echo "   ❌ " . str_pad(basename($file), 35) . ": 로딩 실패\n";
        }
    }
    
    echo "   " . str_repeat("-", 50) . "\n";
    echo "   🏆 {$category} 총합: " . number_format($categoryTime, 1) . "ms, " . number_format($categorySize / 1024, 1) . "KB\n\n";
    
    $totalLoadTime += $categoryTime;
    $totalSize += $categorySize;
}

// 전체 결과 요약
echo "📊 전체 리소스 로딩 성능 요약:\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "⏱️  총 로딩 시간: " . number_format($totalLoadTime, 1) . "ms\n";
echo "📏 총 다운로드 크기: " . number_format($totalSize / 1024, 1) . "KB\n";
echo "📈 평균 파일당 시간: " . number_format($totalLoadTime / count(array_merge(...$resources)), 1) . "ms\n";

// 성능 등급 평가
echo "\n🎯 브라우저 로딩 성능 평가:\n";
if ($totalLoadTime < 1000) { // 1초 미만
    echo "   ✅ 훌륭함: 1초 미만 (매우 빠름)\n";
    $grade = "A+";
} elseif ($totalLoadTime < 2000) { // 2초 미만
    echo "   ✅ 좋음: 2초 미만 (빠름)\n";
    $grade = "A";
} elseif ($totalLoadTime < 3000) { // 3초 미만
    echo "   ⚠️  보통: 3초 미만 (개선 가능)\n";
    $grade = "B";
} elseif ($totalLoadTime < 5000) { // 5초 미만
    echo "   ⚠️  느림: 5초 미만 (개선 필요)\n";
    $grade = "C";
} else {
    echo "   ❌ 매우 느림: 5초 이상 (즉시 개선 필요)\n";
    $grade = "D";
}

echo "🏆 성능 등급: {$grade}\n";

// 개선 제안
echo "\n💡 성능 개선 제안:\n";

if ($totalLoadTime > 2000) {
    echo "🔧 리소스 최적화 필요:\n";
    echo "   - CSS/JS 파일 압축 (minification)\n";
    echo "   - 리소스 번들링 (여러 파일을 하나로 합침)\n";
    echo "   - CDN 사용 (전 세계 캐시 서버 활용)\n";
}

if ($totalSize > 500000) { // 500KB 이상
    echo "🔧 파일 크기 최적화:\n";
    echo "   - 이미지 압축 및 WebP 포맷 사용\n";
    echo "   - 불필요한 CSS/JS 제거\n";
}

if ($totalLoadTime < 1000) {
    echo "✅ 현재 리소스 로딩은 양호한 상태입니다.\n";
}

echo "\n💾 캐시 효과 시뮬레이션:\n";
echo "   첫 방문: " . number_format($totalLoadTime, 1) . "ms\n";
echo "   재방문 (캐시): " . number_format($totalLoadTime * 0.1, 1) . "ms (90% 절약)\n";

echo "\n🔚 테스트 완료 - " . date('Y-m-d H:i:s') . "\n";