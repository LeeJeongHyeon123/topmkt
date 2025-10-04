<?php
/**
 * ROOT_PATH 정의 상태 확인
 */

echo "🔍 ROOT_PATH 정의 상태 디버깅\n";
echo "=================================\n\n";

// 1. 현재 ROOT_PATH 정의 상태
echo "1. ROOT_PATH 정의 상태:\n";
if (defined('ROOT_PATH')) {
    echo "   ✅ ROOT_PATH 정의됨: " . ROOT_PATH . "\n";
    echo "   📁 디렉토리 존재: " . (is_dir(ROOT_PATH) ? "✅" : "❌") . "\n";
    echo "   🔐 디렉토리 권한: " . decoct(fileperms(ROOT_PATH) & 0777) . "\n";
} else {
    echo "   ❌ ROOT_PATH 정의되지 않음\n";
}

// 2. SRC_PATH 정의 상태
echo "\n2. SRC_PATH 정의 상태:\n";
if (defined('SRC_PATH')) {
    echo "   ✅ SRC_PATH 정의됨: " . SRC_PATH . "\n";
} else {
    echo "   ❌ SRC_PATH 정의되지 않음\n";
}

// 3. 수동으로 ROOT_PATH 정의
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', '/var/www/html/topmkt');
    echo "\n3. 수동 ROOT_PATH 정의: " . ROOT_PATH . "\n";
}

if (!defined('SRC_PATH')) {
    define('SRC_PATH', ROOT_PATH . '/src');
    echo "   수동 SRC_PATH 정의: " . SRC_PATH . "\n";
}

// 4. 캐시 디렉토리 생성 테스트
echo "\n4. 캐시 디렉토리 테스트:\n";
$cacheDir = ROOT_PATH . '/cache';
echo "   캐시 디렉토리 경로: $cacheDir\n";

if (!is_dir($cacheDir)) {
    echo "   📁 캐시 디렉토리 없음, 생성 시도...\n";
    $result = mkdir($cacheDir, 0755, true);
    if ($result) {
        echo "   ✅ 캐시 디렉토리 생성 성공\n";
    } else {
        echo "   ❌ 캐시 디렉토리 생성 실패\n";
        echo "   마지막 오류: " . error_get_last()['message'] ?? 'Unknown error' . "\n";
    }
} else {
    echo "   ✅ 캐시 디렉토리 존재\n";
}

// 5. UserOptimized 클래스 로드 테스트
echo "\n5. UserOptimized 클래스 로드 테스트:\n";
try {
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/models/UserOptimized.php';

    echo "   ✅ UserOptimized 클래스 로드 성공\n";

    $userOptimized = new UserOptimized();
    echo "   ✅ UserOptimized 인스턴스 생성 성공\n";

    // 6. 실제 프로필 데이터 조회 테스트 (타임아웃 10초)
    echo "\n6. 프로필 데이터 조회 테스트 (user_id=4):\n";

    set_time_limit(10); // 10초 타임아웃

    $start = microtime(true);
    echo "   🚀 getOptimizedProfileDataWithCache(4) 실행 시작...\n";

    $user = $userOptimized->getOptimizedProfileDataWithCache(4);

    $duration = (microtime(true) - $start) * 1000;
    echo "   ✅ 실행 완료! 소요 시간: " . round($duration, 2) . "ms\n";

    if ($user) {
        echo "   ✅ 사용자 데이터 조회 성공\n";
        echo "   👤 닉네임: " . ($user['nickname'] ?? 'N/A') . "\n";
        echo "   📧 이메일: " . ($user['email'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ 사용자 데이터 조회 실패\n";
    }

} catch (Exception $e) {
    echo "   ❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "   📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🏁 ROOT_PATH 디버깅 완료\n";
?>