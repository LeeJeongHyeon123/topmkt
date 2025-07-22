<?php
// 데이터베이스 연결 테스트
require_once __DIR__ . '/src/config/database.php';

try {
    $db = Database::getInstance();
    echo "✅ 데이터베이스 연결 성공!\n\n";
    
    // lectures 테이블 총 개수 조회
    $totalResult = $db->fetch("SELECT COUNT(*) as total FROM lectures");
    echo "📊 전체 강의 수: " . $totalResult['total'] . "\n";
    
    // status별 개수 조회
    $statusResult = $db->fetchAll("SELECT status, COUNT(*) as count FROM lectures GROUP BY status");
    echo "\n📈 상태별 강의 수:\n";
    foreach ($statusResult as $row) {
        echo "  - " . $row['status'] . ": " . $row['count'] . "개\n";
    }
    
    // 최근 강의 5개 조회
    $recentResult = $db->fetchAll("SELECT id, title, status, created_at FROM lectures ORDER BY created_at DESC LIMIT 5");
    echo "\n🕒 최근 강의 5개:\n";
    foreach ($recentResult as $row) {
        echo "  - [" . $row['id'] . "] " . $row['title'] . " (" . $row['status'] . ") - " . $row['created_at'] . "\n";
    }
    
    // 임시저장된 강의 조회
    $draftResult = $db->fetchAll("SELECT id, title, user_id, created_at FROM lectures WHERE status = 'draft' ORDER BY created_at DESC");
    echo "\n💾 임시저장된 강의:\n";
    if (empty($draftResult)) {
        echo "  - 임시저장된 강의가 없습니다.\n";
    } else {
        foreach ($draftResult as $row) {
            echo "  - [" . $row['id'] . "] " . $row['title'] . " (사용자 ID: " . $row['user_id'] . ") - " . $row['created_at'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ 데이터베이스 오류: " . $e->getMessage() . "\n";
}
?>