<?php
/**
 * NoticeComment 모델 테스트 스크립트
 */

// 설정
define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/models/NoticeComment.php';

echo "🧪 NoticeComment 모델 테스트 시작\n";
echo str_repeat("=", 50) . "\n";

try {
    // 모델 인스턴스 생성
    $noticeModel = new Notice();
    $commentModel = new NoticeComment();
    echo "✅ NoticeComment 모델 인스턴스 생성 성공\n";
    
    // 테스트용 공지사항 먼저 생성
    echo "\n--- 0. 테스트용 공지사항 생성 ---\n";
    $testNoticeData = [
        'user_id' => 4,
        'company_id' => 1, // 윈카드
        'title' => '댓글 테스트용 공지사항 - ' . date('Y-m-d H:i:s'),
        'content' => '<h2>댓글 테스트를 위한 공지사항</h2><p>이 공지사항에 댓글을 작성해봅니다.</p>',
        'is_featured' => false
    ];
    
    $noticeId = $noticeModel->create($testNoticeData);
    if ($noticeId) {
        echo "✅ 테스트 공지사항 생성 성공! ID: $noticeId\n";
    } else {
        throw new Exception('테스트용 공지사항 생성 실패');
    }
    
    // 1. 빈 댓글 목록 조회 테스트
    echo "\n--- 1. 빈 댓글 목록 조회 테스트 ---\n";
    $comments = $commentModel->getByNoticeId($noticeId);
    echo "현재 댓글 수: " . count($comments) . "개\n";
    
    $commentCount = $commentModel->getCountByNoticeId($noticeId);
    echo "댓글 카운트: $commentCount 개\n";
    
    // 2. 댓글 생성 테스트
    echo "\n--- 2. 댓글 생성 테스트 ---\n";
    $testCommentData = [
        'notice_id' => $noticeId,
        'user_id' => 4,
        'content' => '이것은 첫 번째 테스트 댓글입니다!\n\n여러 줄의 내용도 지원합니다.'
    ];
    
    $commentId1 = $commentModel->create($testCommentData);
    if ($commentId1) {
        echo "✅ 첫 번째 댓글 생성 성공! ID: $commentId1\n";
    } else {
        echo "❌ 첫 번째 댓글 생성 실패\n";
    }
    
    // 3. 두 번째 댓글 생성
    echo "\n--- 3. 두 번째 댓글 생성 테스트 ---\n";
    $testCommentData2 = [
        'notice_id' => $noticeId,
        'user_id' => 4,
        'content' => '두 번째 댓글입니다. 🎉\n다양한 내용을 테스트해봅니다!'
    ];
    
    $commentId2 = $commentModel->create($testCommentData2);
    if ($commentId2) {
        echo "✅ 두 번째 댓글 생성 성공! ID: $commentId2\n";
    } else {
        echo "❌ 두 번째 댓글 생성 실패\n";
    }
    
    // 4. 대댓글 생성 테스트
    echo "\n--- 4. 대댓글 생성 테스트 ---\n";
    $testReplyData = [
        'notice_id' => $noticeId,
        'user_id' => 4,
        'parent_id' => $commentId1, // 첫 번째 댓글에 대한 대댓글
        'content' => '이것은 첫 번째 댓글에 대한 답글입니다! 👍'
    ];
    
    $replyId1 = $commentModel->create($testReplyData);
    if ($replyId1) {
        echo "✅ 대댓글 생성 성공! ID: $replyId1\n";
    } else {
        echo "❌ 대댓글 생성 실패\n";
    }
    
    // 5. 댓글 목록 재조회 테스트
    echo "\n--- 5. 댓글 목록 재조회 테스트 ---\n";
    $comments = $commentModel->getAllByNoticeId($noticeId);
    echo "총 댓글 수: " . count($comments) . "개\n";
    
    foreach ($comments as $comment) {
        $indent = $comment['parent_id'] ? "  └─ " : "- ";
        $parentInfo = $comment['parent_id'] ? " (답글)" : " (원댓글)";
        echo "{$indent}[{$comment['id']}] {$comment['author_name']}: " . 
             substr(str_replace("\n", " ", $comment['content']), 0, 50) . "...{$parentInfo}\n";
    }
    
    // 6. 댓글 카운트 재확인
    echo "\n--- 6. 댓글 카운트 재확인 테스트 ---\n";
    $commentCount = $commentModel->getCountByNoticeId($noticeId);
    echo "현재 댓글 카운트: $commentCount 개\n";
    
    // 공지사항의 comment_count도 업데이트되었는지 확인
    $notice = $noticeModel->getById($noticeId);
    echo "공지사항의 comment_count: {$notice['comment_count']} 개\n";
    
    // 7. 특정 댓글 조회 테스트
    echo "\n--- 7. 특정 댓글 조회 테스트 ---\n";
    $comment = $commentModel->getById($commentId1);
    if ($comment) {
        echo "✅ 댓글 조회 성공\n";
        echo "- 댓글 ID: {$comment['id']}\n";
        echo "- 작성자: {$comment['author_name']}\n";
        echo "- 내용: " . substr($comment['content'], 0, 50) . "...\n";
        echo "- 생성일: {$comment['created_at']}\n";
    } else {
        echo "❌ 댓글 조회 실패\n";
    }
    
    // 8. 대댓글 조회 테스트
    echo "\n--- 8. 대댓글 조회 테스트 ---\n";
    $replies = $commentModel->getReplies($commentId1);
    echo "댓글 ID {$commentId1}의 대댓글 수: " . count($replies) . "개\n";
    if (count($replies) > 0) {
        foreach ($replies as $reply) {
            echo "- [{$reply['id']}] {$reply['author_name']}: " . 
                 substr($reply['content'], 0, 50) . "...\n";
        }
    }
    
    // 9. 소유자 확인 테스트
    echo "\n--- 9. 소유자 확인 테스트 ---\n";
    $isOwner = $commentModel->isOwner($commentId1, 4);
    echo "사용자 ID 4가 댓글 {$commentId1}의 소유자인가? " . ($isOwner ? "예" : "아니요") . "\n";
    
    $isNotOwner = $commentModel->isOwner($commentId1, 999);
    echo "사용자 ID 999가 댓글 {$commentId1}의 소유자인가? " . ($isNotOwner ? "예" : "아니요") . "\n";
    
    // 10. 댓글 수정 테스트
    echo "\n--- 10. 댓글 수정 테스트 ---\n";
    $updateData = [
        'content' => '수정된 댓글 내용입니다! ✨\n\n이 댓글이 성공적으로 수정되었습니다.'
    ];
    
    $updateSuccess = $commentModel->update($commentId1, $updateData);
    echo "댓글 수정: " . ($updateSuccess ? "성공" : "실패") . "\n";
    
    if ($updateSuccess) {
        $updatedComment = $commentModel->getById($commentId1);
        echo "- 수정된 내용: " . substr($updatedComment['content'], 0, 100) . "...\n";
    }
    
    // 11. 댓글 통계 테스트
    echo "\n--- 11. 댓글 통계 테스트 ---\n";
    $stats = $commentModel->getCommentStats($noticeId);
    if ($stats) {
        echo "✅ 댓글 통계 조회 성공\n";
        echo "- 총 댓글 수: {$stats['total_comments']}\n";
        echo "- 원댓글 수: {$stats['parent_comments']}\n";
        echo "- 대댓글 수: {$stats['reply_comments']}\n";
        echo "- 댓글 작성자 수: {$stats['unique_commenters']}\n";
    } else {
        echo "❌ 댓글 통계 조회 실패\n";
    }
    
    // 12. 사용자별 댓글 조회 테스트
    echo "\n--- 12. 사용자별 댓글 조회 테스트 ---\n";
    $userComments = $commentModel->getByUserId(4, 10);
    echo "사용자 ID 4의 댓글 수: " . count($userComments) . "개\n";
    if (count($userComments) > 0) {
        foreach ($userComments as $comment) {
            echo "- [{$comment['id']}] {$comment['notice_title']} ({$comment['company_name']}): " . 
                 substr($comment['content'], 0, 30) . "...\n";
        }
    }
    
    // 13. 최근 댓글 조회 테스트
    echo "\n--- 13. 최근 댓글 조회 테스트 ---\n";
    $recentComments = $commentModel->getRecentComments(5);
    echo "최근 댓글 수: " . count($recentComments) . "개\n";
    if (count($recentComments) > 0) {
        foreach ($recentComments as $comment) {
            echo "- [{$comment['id']}] {$comment['author_name']} → {$comment['notice_title']}: " . 
                 $comment['content_preview'] . "\n";
        }
    }
    
    // 14. 댓글 검색 테스트
    echo "\n--- 14. 댓글 검색 테스트 ---\n";
    $searchResults = $commentModel->searchComments('테스트', 10);
    echo "'테스트' 키워드 검색 결과: " . count($searchResults) . "개\n";
    if (count($searchResults) > 0) {
        foreach ($searchResults as $comment) {
            echo "- [{$comment['id']}] {$comment['author_name']} → {$comment['notice_title']}: " . 
                 substr($comment['content'], 0, 50) . "...\n";
        }
    }
    
    // 15. 댓글 삭제 테스트 (테스트 데이터 정리)
    echo "\n--- 15. 댓글 삭제 테스트 (테스트 데이터 정리) ---\n";
    
    // 대댓글부터 삭제
    $deleteSuccess1 = $commentModel->delete($replyId1);
    echo "대댓글 삭제: " . ($deleteSuccess1 ? "성공" : "실패") . "\n";
    
    // 원댓글들 삭제
    $deleteSuccess2 = $commentModel->delete($commentId1);
    echo "첫 번째 댓글 삭제: " . ($deleteSuccess2 ? "성공" : "실패") . "\n";
    
    $deleteSuccess3 = $commentModel->delete($commentId2);
    echo "두 번째 댓글 삭제: " . ($deleteSuccess3 ? "성공" : "실패") . "\n";
    
    // 삭제 후 댓글 카운트 확인
    $finalCommentCount = $commentModel->getCountByNoticeId($noticeId);
    echo "삭제 후 댓글 카운트: $finalCommentCount 개\n";
    
    // 공지사항의 comment_count도 업데이트되었는지 확인
    $notice = $noticeModel->getById($noticeId);
    echo "공지사항의 comment_count 업데이트: {$notice['comment_count']} 개\n";
    
    // 16. 테스트용 공지사항 삭제 (정리)
    echo "\n--- 16. 테스트 데이터 정리 ---\n";
    $noticeDeleteSuccess = $noticeModel->delete($noticeId);
    echo "테스트 공지사항 삭제: " . ($noticeDeleteSuccess ? "성공" : "실패") . "\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 NoticeComment 모델 테스트 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 중 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
    
    // 오류 발생 시 테스트 데이터 정리 시도
    if (isset($noticeId) && $noticeId) {
        try {
            $noticeModel->delete($noticeId);
            echo "테스트 데이터 정리 완료\n";
        } catch (Exception $cleanupE) {
            echo "테스트 데이터 정리 중 오류: " . $cleanupE->getMessage() . "\n";
        }
    }
}
?>