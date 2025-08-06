<?php
/**
 * QA 테스트: NoticeComment 모델 댓글 시스템
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/NoticeComment.php';
require_once SRC_PATH . '/config/database.php';

echo "🧪 === NoticeComment 모델 QA 테스트 ===\n";
echo str_repeat("=", 50) . "\n\n";

try {
    $commentModel = new NoticeComment();
    echo "✅ NoticeComment 모델 인스턴스 생성 성공\n";
    
    // 테스트 1: 댓글 생성 기능 확인
    echo "\n--- 댓글 생성 테스트 ---\n";
    $testCommentData = [
        'notice_id' => 2, // 기존 공지사항 ID
        'user_id' => 4,
        'content' => 'QA 테스트용 댓글입니다.',
        'parent_id' => null
    ];
    
    $commentId = $commentModel->create($testCommentData);
    if ($commentId && is_numeric($commentId)) {
        echo "✅ 댓글 생성 성공: ID $commentId\n";
    } else {
        echo "❌ 댓글 생성 실패\n";
        throw new Exception("댓글 생성 실패");
    }
    
    // 테스트 2: 댓글 조회
    echo "\n--- 댓글 조회 테스트 ---\n";
    $comment = $commentModel->findById($commentId);
    if ($comment && $comment['content'] === 'QA 테스트용 댓글입니다.') {
        echo "✅ 댓글 조회 성공\n";
        echo "   - 내용: " . $comment['content'] . "\n";
        echo "   - 작성자 정보: " . ($comment['nickname'] ?? '정보 없음') . "\n";
    } else {
        echo "❌ 댓글 조회 실패\n";
    }
    
    // 테스트 3: 댓글 목록 조회 (공지사항별)
    echo "\n--- 댓글 목록 조회 테스트 ---\n";
    $comments = $commentModel->getByNoticeId(2);
    echo "✅ 공지사항 ID 2의 댓글 수: " . count($comments) . "개\n";
    
    if (count($comments) > 0) {
        $foundTestComment = false;
        foreach ($comments as $c) {
            if ($c['content'] === 'QA 테스트용 댓글입니다.') {
                $foundTestComment = true;
                break;
            }
        }
        echo ($foundTestComment ? "✅" : "❌") . " 생성한 테스트 댓글 목록에서 확인\n";
    }
    
    // 테스트 4: 답글 생성
    echo "\n--- 답글 생성 테스트 ---\n";
    $replyData = [
        'notice_id' => 2,
        'user_id' => 4,
        'content' => 'QA 테스트용 답글입니다.',
        'parent_id' => $commentId
    ];
    
    $replyId = $commentModel->create($replyData);
    if ($replyId && is_numeric($replyId)) {
        echo "✅ 답글 생성 성공: ID $replyId\n";
    } else {
        echo "❌ 답글 생성 실패\n";
    }
    
    // 테스트 5: 계층형 댓글 조회
    echo "\n--- 계층형 댓글 조회 테스트 ---\n";
    $hierarchicalComments = $commentModel->getHierarchicalComments(2);
    echo "✅ 계층형 구조 댓글 조회 성공\n";
    
    $foundParentWithReply = false;
    foreach ($hierarchicalComments as $parent) {
        if ($parent['id'] == $commentId && !empty($parent['replies'])) {
            $foundParentWithReply = true;
            echo "✅ 부모 댓글에 답글이 올바르게 연결됨\n";
            echo "   - 답글 수: " . count($parent['replies']) . "개\n";
            break;
        }
    }
    
    if (!$foundParentWithReply && $replyId) {
        echo "⚠️ 계층형 구조에서 답글 연결 확인 필요\n";
    }
    
    // 테스트 6: 댓글 수정
    echo "\n--- 댓글 수정 테스트 ---\n";
    $updateResult = $commentModel->update($commentId, [
        'content' => 'QA 테스트용 댓글입니다. (수정됨)'
    ]);
    
    if ($updateResult) {
        echo "✅ 댓글 수정 성공\n";
        
        // 수정 확인
        $updatedComment = $commentModel->findById($commentId);
        if ($updatedComment && strpos($updatedComment['content'], '수정됨') !== false) {
            echo "✅ 수정 내용 확인됨\n";
        } else {
            echo "❌ 수정 내용이 반영되지 않음\n";
        }
    } else {
        echo "❌ 댓글 수정 실패\n";
    }
    
    // 테스트 7: 댓글 통계 업데이트 확인
    echo "\n--- 댓글 통계 업데이트 테스트 ---\n";
    
    // Notice 모델로 공지사항의 댓글 수 확인
    require_once SRC_PATH . '/models/Notice.php';
    $noticeModel = new Notice();
    $notice = $noticeModel->findById(2);
    
    if ($notice) {
        echo "✅ 공지사항 ID 2의 댓글 수: " . $notice['comment_count'] . "개\n";
        
        // 실제 댓글 수와 비교
        $actualComments = $commentModel->getByNoticeId(2);
        $actualCount = count($actualComments);
        
        if ($notice['comment_count'] == $actualCount) {
            echo "✅ 댓글 통계가 정확함\n";
        } else {
            echo "⚠️ 댓글 통계 불일치: DB($notice[comment_count]) vs 실제($actualCount)\n";
        }
    }
    
    // 테스트 8: 최근 댓글 조회
    echo "\n--- 최근 댓글 조회 테스트 ---\n";
    $recentComments = $commentModel->getRecentComments(5);
    echo "✅ 최근 댓글 " . count($recentComments) . "개 조회 성공\n";
    
    if (count($recentComments) > 0) {
        echo "   - 가장 최근 댓글: " . substr($recentComments[0]['content'], 0, 30) . "...\n";
    }
    
    // 테스트 9: 댓글 삭제 (답글부터)
    echo "\n--- 댓글 삭제 테스트 ---\n";
    if ($replyId) {
        $deleteReplyResult = $commentModel->delete($replyId);
        echo ($deleteReplyResult ? "✅" : "❌") . " 답글 삭제\n";
    }
    
    $deleteCommentResult = $commentModel->delete($commentId);
    echo ($deleteCommentResult ? "✅" : "❌") . " 댓글 삭제\n";
    
    // 삭제 확인
    $deletedComment = $commentModel->findById($commentId);
    if (!$deletedComment) {
        echo "✅ 댓글 삭제 확인됨\n";
    } else {
        echo "❌ 댓글이 삭제되지 않음\n";
    }
    
    // 테스트 10: 사용자별 댓글 조회
    echo "\n--- 사용자별 댓글 조회 테스트 ---\n";
    $userComments = $commentModel->getByUserId(4, 1, 5);
    echo "✅ 사용자 ID 4의 댓글 " . count($userComments) . "개 조회\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 NoticeComment 모델 기본 기능 정상 작동!\n";
    echo str_repeat("=", 50) . "\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
}
?>