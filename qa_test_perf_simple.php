<?php
/**
 * QA 테스트: 성능 간단 버전
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';

echo "🧪 === 성능 간단 QA 테스트 ===\n";
echo str_repeat("=", 40) . "\n\n";

try {
    $notice = new Notice();
    
    // 단일 조회 성능
    $start = microtime(true);
    $single = $notice->findById(2);
    $singleTime = round((microtime(true) - $start) * 1000, 2);
    echo "✅ 단일 조회: {$singleTime}ms\n";
    
    // 목록 조회 성능
    $start = microtime(true);
    $list = $notice->getList(1, 10);
    $listTime = round((microtime(true) - $start) * 1000, 2);
    echo "✅ 목록 조회: {$listTime}ms (" . count($list) . "건)\n";
    
    // 검색 성능
    $start = microtime(true);
    $search = $notice->search('테스트', 'all', null, 1, 5);
    $searchTime = round((microtime(true) - $start) * 1000, 2);
    echo "✅ 검색 기능: {$searchTime}ms (" . count($search) . "건)\n";
    
    // 총 개수 조회
    $start = microtime(true);
    $total = $notice->getTotalCount();
    $totalTime = round((microtime(true) - $start) * 1000, 2);
    echo "✅ 총 개수 조회: {$totalTime}ms ({$total}건)\n";
    
    // 메모리 사용량
    $memory = round(memory_get_peak_usage() / 1024 / 1024, 2);
    echo "✅ 메모리 사용량: {$memory}MB\n";
    
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "🎉 성능 테스트 완료!\n";
    
    // 성능 평가
    if ($singleTime < 50 && $listTime < 100 && $searchTime < 200) {
        echo "⚡ 우수한 성능!\n";
    } elseif ($singleTime < 100 && $listTime < 200 && $searchTime < 500) {
        echo "✅ 양호한 성능!\n";
    } else {
        echo "⚠️ 성능 최적화 필요\n";
    }
    
    echo str_repeat("=", 40) . "\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>