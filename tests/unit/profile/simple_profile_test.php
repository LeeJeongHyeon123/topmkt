<?php
/**
 * 간단한 프로필 성능 테스트
 */

// 경로 설정
define('ROOT_PATH', '/var/www/html/topmkt');
define('BASE_PATH', '/var/www/html/topmkt');
define('SRC_PATH', BASE_PATH . '/src');

echo "🔥 울트라씽크 모드: 간단 프로필 성능 테스트\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// 1. 기본 환경 체크
echo "1. 환경 확인:\n";
echo "   ROOT_PATH: " . ROOT_PATH . "\n";
echo "   SRC_PATH: " . SRC_PATH . "\n";

// 2. 필수 파일 존재 확인
$requiredFiles = [
    SRC_PATH . '/config/database.php',
    SRC_PATH . '/models/UserOptimized.php',
    ROOT_PATH . '/cache/profile_4.json'
];

echo "\n2. 파일 존재 확인:\n";
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ " . basename($file) . "\n";
    } else {
        echo "   ❌ " . basename($file) . " (NOT FOUND)\n";
    }
}

// 3. 캐시 파일 직접 읽기 테스트
echo "\n3. 캐시 데이터 직접 확인:\n";
$cacheFile = ROOT_PATH . '/cache/profile_4.json';
if (file_exists($cacheFile)) {
    $startTime = microtime(true);
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    $loadTime = (microtime(true) - $startTime) * 1000;
    
    echo "   ⏱️  캐시 로딩 시간: " . number_format($loadTime, 3) . "ms\n";
    echo "   📏 캐시 파일 크기: " . number_format(filesize($cacheFile) / 1024, 2) . "KB\n";
    
    if ($cacheData) {
        echo "   ✅ 캐시 데이터 파싱 성공\n";
        echo "   👤 사용자: " . ($cacheData['nickname'] ?? 'Unknown') . "\n";
        echo "   📊 게시글: " . ($cacheData['stats']['post_count'] ?? 0) . "개\n";
        echo "   💬 댓글: " . ($cacheData['stats']['comment_count'] ?? 0) . "개\n";
        echo "   👍 좋아요: " . ($cacheData['stats']['like_count'] ?? 0) . "개\n";
    } else {
        echo "   ❌ 캐시 데이터 파싱 실패\n";
    }
} else {
    echo "   ❌ 캐시 파일 없음\n";
}

// 4. MySQL 연결 테스트
echo "\n4. 데이터베이스 연결 테스트:\n";
try {
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    echo "   ✅ Database 클래스 로딩 성공\n";
    
    // 간단한 쿼리 테스트
    $startTime = microtime(true);
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE id = 4");
    $queryTime = (microtime(true) - $startTime) * 1000;
    
    echo "   ⏱️  쿼리 시간: " . number_format($queryTime, 3) . "ms\n";
    
    if ($result && $result['count'] > 0) {
        echo "   ✅ 사용자 4 존재 확인\n";
    } else {
        echo "   ❌ 사용자 4 없음\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ 데이터베이스 오류: " . $e->getMessage() . "\n";
}

// 5. UserOptimized 클래스 테스트
echo "\n5. UserOptimized 클래스 테스트:\n";
try {
    require_once SRC_PATH . '/models/UserOptimized.php';
    
    $userOptimized = new UserOptimized();
    echo "   ✅ UserOptimized 클래스 생성 성공\n";
    
    // 실제 메서드 호출
    $startTime = microtime(true);
    $profileData = $userOptimized->getOptimizedProfileDataWithCache(4);
    $methodTime = (microtime(true) - $startTime) * 1000;
    
    echo "   ⏱️  메서드 실행 시간: " . number_format($methodTime, 3) . "ms\n";
    
    if ($profileData) {
        echo "   ✅ 프로필 데이터 로딩 성공\n";
        echo "   📊 데이터 필드 수: " . count($profileData) . "개\n";
    } else {
        echo "   ❌ 프로필 데이터 로딩 실패\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ UserOptimized 오류: " . $e->getMessage() . "\n";
}

echo "\n🔚 테스트 완료 - " . date('Y-m-d H:i:s') . "\n";