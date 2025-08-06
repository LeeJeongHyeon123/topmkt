<?php
/**
 * QA 테스트: NoticeComment 모델 간단 버전
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/NoticeComment.php';

echo "🧪 === NoticeComment 간단 QA 테스트 ===\n";
echo str_repeat("=", 45) . "\n\n";

try {
    $commentModel = new NoticeComment();
    echo "✅ NoticeComment 모델 인스턴스 생성\n";
    
    // 최근 댓글 조회
    $recentComments = $commentModel->getRecentComments(3);
    echo "✅ 최근 댓글 조회: " . count($recentComments) . "개\n";
    
    // 공지사항별 댓글 조회 (기존 공지사항 ID 2 사용)
    $comments = $commentModel->getByNoticeId(2);
    echo "✅ 공지사항 ID 2의 댓글: " . count($comments) . "개\n";
    
    // 계층형 댓글 조회
    $hierarchical = $commentModel->getHierarchicalComments(2);
    echo "✅ 계층형 댓글 조회: " . count($hierarchical) . "개\n";
    
    echo "\n" . str_repeat("=", 45) . "\n";
    echo "🎉 NoticeComment 기본 기능 정상!\n";
    echo str_repeat("=", 45) . "\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>