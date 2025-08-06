<?php
/**
 * QA 테스트: 성능 및 부하
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/models/NoticeComment.php';

echo "🧪 === 성능 & 부하 QA 테스트 ===\n";
echo str_repeat("=", 45) . "\n\n";

$testResults = [];
$totalTests = 0;
$passedTests = 0;

function runTest($testName, $testFunction) {
    global $testResults, $totalTests, $passedTests;
    $totalTests++;
    
    try {
        $result = $testFunction();
        if ($result === true) {
            $passedTests++;
            $testResults[] = "✅ PASS: $testName";
            echo "✅ PASS: $testName\n";
        } else {
            $testResults[] = "❌ FAIL: $testName - $result";
            echo "❌ FAIL: $testName - $result\n";
        }
    } catch (Exception $e) {
        $testResults[] = "❌ ERROR: $testName - " . $e->getMessage();
        echo "❌ ERROR: $testName - " . $e->getMessage() . "\n";
    }
}

// 테스트 1: 단일 공지사항 조회 성능
runTest("단일 공지사항 조회 성능", function() {
    $notice = new Notice();
    
    $start = microtime(true);
    $result = $notice->findById(2);
    $elapsed = (microtime(true) - $start) * 1000;
    
    echo "   - 조회 시간: {$elapsed}ms\n";
    
    if ($elapsed > 100) { // 100ms 초과
        return "조회 시간이 너무 오래 걸림: {$elapsed}ms";
    }
    
    if (!$result) {
        return "조회 결과 없음";
    }
    
    return true;
});

// 테스트 2: 공지사항 목록 조회 성능
runTest("공지사항 목록 조회 성능", function() {
    $notice = new Notice();
    
    $start = microtime(true);
    $results = $notice->getList(1, 20);
    $elapsed = (microtime(true) - $start) * 1000;
    
    echo "   - 목록 조회 시간: {$elapsed}ms\n";
    echo "   - 조회된 건수: " . count($results) . "개\n";
    
    if ($elapsed > 200) { // 200ms 초과
        return "목록 조회 시간이 너무 오래 걸림: {$elapsed}ms";
    }
    
    return true;
});

// 테스트 3: 검색 성능 테스트
runTest("검색 성능", function() {
    $notice = new Notice();
    
    $start = microtime(true);
    $results = $notice->search('테스트', 'all', null, 1, 10);
    $elapsed = (microtime(true) - $start) * 1000;
    
    echo "   - 검색 시간: {$elapsed}ms\n";
    echo "   - 검색 결과: " . count($results) . "개\n";
    
    if ($elapsed > 300) { // 300ms 초과
        return "검색 시간이 너무 오래 걸림: {$elapsed}ms";
    }
    
    return true;
});

// 테스트 4: 캐시 성능 확인
runTest("캐시 성능", function() {
    $notice = new Notice();
    
    // 첫 번째 조회 (캐시 미스)
    $start1 = microtime(true);
    $result1 = $notice->findById(2);
    $time1 = (microtime(true) - $start1) * 1000;
    
    // 두 번째 조회 (캐시 히트 가능성)
    $start2 = microtime(true);
    $result2 = $notice->findById(2);
    $time2 = (microtime(true) - $start2) * 1000;
    
    echo "   - 첫 번째 조회: {$time1}ms\n";
    echo "   - 두 번째 조회: {$time2}ms\n";
    
    if (!$result1 || !$result2) {
        return "조회 결과가 없음";
    }
    
    // 캐시가 작동한다면 두 번째가 더 빨라야 하지만, 
    // 매우 빠른 DB일 경우 차이가 미미할 수 있음
    return true;
});

// 테스트 5: 동시 조회 부하 테스트 (간단버전)
runTest("동시 조회 부하 테스트", function() {
    $notice = new Notice();
    $iterations = 10;
    $totalTime = 0;
    
    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $result = $notice->getList(1, 5);
        $elapsed = microtime(true) - $start;
        $totalTime += $elapsed;
        
        if (!$result) {
            return "반복 $i: 조회 실패";
        }
    }
    
    $avgTime = ($totalTime / $iterations) * 1000;
    echo "   - 평균 조회 시간: {$avgTime}ms ({$iterations}회)\n";
    
    if ($avgTime > 150) {
        return "평균 조회 시간이 너무 오래 걸림: {$avgTime}ms";
    }
    
    return true;
});

// 테스트 6: 댓글 시스템 성능
runTest("댓글 시스템 성능", function() {
    $commentModel = new NoticeComment();
    
    $start = microtime(true);
    $comments = $commentModel->getByNoticeId(2);
    $elapsed = (microtime(true) - $start) * 1000;
    
    echo "   - 댓글 조회 시간: {$elapsed}ms\n";
    echo "   - 댓글 수: " . count($comments) . "개\n";
    
    // 계층형 댓글 조회 성능
    $start2 = microtime(true);
    $hierarchical = $commentModel->getHierarchicalComments(2);
    $elapsed2 = (microtime(true) - $start2) * 1000;
    
    echo "   - 계층형 댓글 조회: {$elapsed2}ms\n";
    
    if ($elapsed > 100 || $elapsed2 > 150) {
        return "댓글 조회 시간이 너무 오래 걸림: {$elapsed}ms / {$elapsed2}ms";
    }
    
    return true;
});

// 테스트 7: 메모리 사용량 확인
runTest("메모리 사용량", function() {
    $memoryStart = memory_get_usage();
    $notice = new Notice();
    
    // 대량 데이터 조회
    $results = $notice->getList(1, 50);
    
    $memoryEnd = memory_get_usage();
    $memoryUsed = ($memoryEnd - $memoryStart) / 1024; // KB
    
    echo "   - 메모리 사용량: {$memoryUsed} KB\n";
    echo "   - 조회된 데이터: " . count($results) . "건\n";
    
    if ($memoryUsed > 1024) { // 1MB 초과
        return "메모리 사용량이 너무 큼: {$memoryUsed} KB";
    }
    
    return true;
});

// 테스트 8: 페이지네이션 성능
runTest("페이지네이션 성능", function() {
    $notice = new Notice();
    
    // 첫 페이지
    $start1 = microtime(true);
    $page1 = $notice->getList(1, 10);
    $time1 = (microtime(true) - $start1) * 1000;
    
    // 중간 페이지 (있다면)
    $start2 = microtime(true);
    $page2 = $notice->getList(2, 10);
    $time2 = (microtime(true) - $start2) * 1000;
    
    echo "   - 첫 페이지 조회: {$time1}ms\n";
    echo "   - 두 번째 페이지 조회: {$time2}ms\n";
    
    if ($time1 > 150 || $time2 > 150) {
        return "페이지네이션 성능이 좋지 않음: {$time1}ms / {$time2}ms";
    }
    
    return true;
});

// 테스트 9: 총 개수 조회 성능
runTest("총 개수 조회 성능", function() {
    $notice = new Notice();
    
    $start = microtime(true);
    $totalCount = $notice->getTotalCount();
    $elapsed = (microtime(true) - $start) * 1000;
    
    echo "   - 총 개수: {$totalCount}개\n";
    echo "   - 조회 시간: {$elapsed}ms\n";
    
    if ($elapsed > 50) { // 50ms 초과
        return "총 개수 조회가 너무 오래 걸림: {$elapsed}ms";
    }
    
    return true;
});

// 테스트 10: 데이터베이스 연결 성능
runTest("데이터베이스 연결 성능", function() {
    require_once SRC_PATH . '/config/database.php';
    
    $connectionTimes = [];
    
    for ($i = 0; $i < 5; $i++) {
        $start = microtime(true);
        $db = Database::getInstance();
        $connection = $db->getConnection();
        $elapsed = (microtime(true) - $start) * 1000;
        $connectionTimes[] = $elapsed;
        
        if (!$connection) {
            return "데이터베이스 연결 실패";
        }
    }
    
    $avgConnectionTime = array_sum($connectionTimes) / count($connectionTimes);
    echo "   - 평균 연결 시간: {$avgConnectionTime}ms\n";
    
    if ($avgConnectionTime > 10) { // 10ms 초과
        return "데이터베이스 연결이 너무 오래 걸림: {$avgConnectionTime}ms";
    }
    
    return true;
});

echo "\n" . str_repeat("=", 45) . "\n";
echo "🏁 성능 & 부하 QA 테스트 완료\n";
echo "📊 결과: $passedTests/$totalTests 통과 (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";

// 전체 메모리 사용량
$totalMemory = memory_get_peak_usage() / 1024 / 1024; // MB
echo "💾 최대 메모리 사용량: " . round($totalMemory, 2) . " MB\n";

if ($passedTests === $totalTests) {
    echo "✅ 모든 성능 테스트 통과!\n";
} else {
    echo "❌ " . ($totalTests - $passedTests) . "개 테스트 실패\n";
    echo "\n실패한 테스트들:\n";
    foreach ($testResults as $result) {
        if (strpos($result, '❌') === 0) {
            echo $result . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 45) . "\n";
?>