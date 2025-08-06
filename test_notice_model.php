<?php
/**
 * Notice 모델 테스트 스크립트
 */

// 설정
define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';

echo "🧪 Notice 모델 테스트 시작\n";
echo str_repeat("=", 50) . "\n";

try {
    // Notice 모델 인스턴스 생성
    $noticeModel = new Notice();
    echo "✅ Notice 모델 인스턴스 생성 성공\n";
    
    // 1. 기업 사용자 확인 테스트
    echo "\n--- 1. 기업 사용자 확인 테스트 ---\n";
    $isCompanyUser = $noticeModel->isCompanyUser(4); // 윈카드 사용자
    echo "사용자 ID 4 기업 사용자 여부: " . ($isCompanyUser ? "예" : "아니요") . "\n";
    
    // 2. 기업 ID 조회 테스트
    echo "\n--- 2. 기업 ID 조회 테스트 ---\n";
    $companyId = $noticeModel->getUserCompanyId(4);
    echo "사용자 ID 4의 기업 ID: " . ($companyId ?? "없음") . "\n";
    
    // 3. 기업 목록 조회 테스트  
    echo "\n--- 3. 기업 목록 조회 테스트 ---\n";
    $companies = $noticeModel->getCompaniesWithNotices();
    echo "공지사항이 있는 기업 수: " . count($companies) . "개\n";
    if (count($companies) > 0) {
        foreach ($companies as $company) {
            echo "- {$company['company_name']}: {$company['notice_count']}개 공지사항\n";
        }
    }
    
    // 4. 빈 목록 조회 테스트
    echo "\n--- 4. 공지사항 목록 조회 테스트 (빈 상태) ---\n";
    $notices = $noticeModel->getList(1, 10);
    echo "현재 공지사항 수: " . count($notices) . "개\n";
    
    $totalCount = $noticeModel->getTotalCount();
    echo "총 공지사항 수: " . $totalCount . "개\n";
    
    // 5. 공지사항 생성 테스트 (승인된 기업 사용자로)
    if ($isCompanyUser && $companyId) {
        echo "\n--- 5. 공지사항 생성 테스트 ---\n";
        
        $testNoticeData = [
            'user_id' => 4,
            'company_id' => $companyId,
            'title' => '테스트 공지사항 - ' . date('Y-m-d H:i:s'),
            'content' => '<h2>테스트 공지사항입니다</h2><p>이것은 <strong>Notice 모델</strong> 테스트를 위한 공지사항입니다.</p><ul><li>기능 테스트 중</li><li>HTML 콘텐츠 지원</li><li>정상 작동 확인</li></ul>',
            'is_featured' => false
        ];
        
        $noticeId = $noticeModel->create($testNoticeData);
        if ($noticeId) {
            echo "✅ 테스트 공지사항 생성 성공! ID: $noticeId\n";
            
            // 6. 생성된 공지사항 조회 테스트
            echo "\n--- 6. 생성된 공지사항 조회 테스트 ---\n";
            $notice = $noticeModel->getById($noticeId);
            if ($notice) {
                echo "✅ 공지사항 조회 성공\n";
                echo "- 제목: {$notice['title']}\n";
                echo "- 기업명: {$notice['company_name']}\n";
                echo "- 작성자: {$notice['author_name']}\n";
                echo "- 생성일: {$notice['created_at']}\n";
                echo "- 조회수: {$notice['view_count']}\n";
            } else {
                echo "❌ 생성된 공지사항 조회 실패\n";
            }
            
            // 7. 공지사항 목록 재조회 (생성 후)
            echo "\n--- 7. 공지사항 목록 재조회 테스트 ---\n";
            $notices = $noticeModel->getList(1, 10);
            echo "공지사항 목록 수: " . count($notices) . "개\n";
            if (count($notices) > 0) {
                foreach ($notices as $notice) {
                    echo "- [{$notice['id']}] {$notice['title']} ({$notice['company_name']})\n";
                }
            }
            
            // 8. 조회수 증가 테스트
            echo "\n--- 8. 조회수 증가 테스트 ---\n";
            $success = $noticeModel->incrementViewCount($noticeId);
            echo "조회수 증가 처리: " . ($success ? "성공" : "실패") . "\n";
            
            // 조회수 확인
            $notice = $noticeModel->getById($noticeId);
            echo "현재 조회수: {$notice['view_count']}\n";
            
            // 9. 소유자 확인 테스트
            echo "\n--- 9. 소유자 확인 테스트 ---\n";
            $isOwner = $noticeModel->isOwner($noticeId, 4);
            echo "사용자 ID 4가 공지사항 $noticeId 소유자인가? " . ($isOwner ? "예" : "아니요") . "\n";
            
            $isNotOwner = $noticeModel->isOwner($noticeId, 999);
            echo "사용자 ID 999가 공지사항 $noticeId 소유자인가? " . ($isNotOwner ? "예" : "아니요") . "\n";
            
            // 10. 공지사항 수정 테스트
            echo "\n--- 10. 공지사항 수정 테스트 ---\n";
            $updateData = [
                'title' => '수정된 테스트 공지사항 - ' . date('Y-m-d H:i:s'),
                'content' => '<h2>수정된 내용입니다</h2><p>이 공지사항이 성공적으로 <em>수정</em>되었습니다!</p>',
                'is_featured' => true
            ];
            
            $updateSuccess = $noticeModel->update($noticeId, $updateData);
            echo "공지사항 수정: " . ($updateSuccess ? "성공" : "실패") . "\n";
            
            if ($updateSuccess) {
                $updatedNotice = $noticeModel->getById($noticeId);
                echo "- 수정된 제목: {$updatedNotice['title']}\n";
                echo "- 추천 공지사항: " . ($updatedNotice['is_featured'] ? "예" : "아니요") . "\n";
            }
            
            // 11. 추천 공지사항 조회 테스트
            echo "\n--- 11. 추천 공지사항 조회 테스트 ---\n";
            $featuredNotices = $noticeModel->getFeaturedNotices(5);
            echo "추천 공지사항 수: " . count($featuredNotices) . "개\n";
            if (count($featuredNotices) > 0) {
                foreach ($featuredNotices as $notice) {
                    echo "- ⭐ {$notice['title']} ({$notice['company_name']})\n";
                }
            }
            
            // 12. 기업별 공지사항 조회 테스트
            echo "\n--- 12. 기업별 공지사항 조회 테스트 ---\n";
            $companyNotices = $noticeModel->getByCompanyId($companyId, 5);
            echo "기업 ID $companyId 공지사항 수: " . count($companyNotices) . "개\n";
            if (count($companyNotices) > 0) {
                foreach ($companyNotices as $notice) {
                    echo "- {$notice['title']} (작성자: {$notice['author_name']})\n";
                }
            }
            
            // 13. 공지사항 삭제 테스트 (테스트 데이터 정리)
            echo "\n--- 13. 공지사항 삭제 테스트 (테스트 데이터 정리) ---\n";
            $deleteSuccess = $noticeModel->delete($noticeId);
            echo "테스트 공지사항 삭제: " . ($deleteSuccess ? "성공" : "실패") . "\n";
            
            // 삭제 후 조회 확인
            $deletedNotice = $noticeModel->getById($noticeId);
            echo "삭제 후 조회 결과: " . ($deletedNotice ? "여전히 존재 (soft delete)" : "완전 삭제됨") . "\n";
            
        } else {
            echo "❌ 테스트 공지사항 생성 실패\n";
        }
    } else {
        echo "\n--- 공지사항 생성 테스트 건너뜀 ---\n";
        echo "사용자 ID 4가 승인된 기업 사용자가 아니거나 기업 ID를 찾을 수 없습니다.\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 Notice 모델 테스트 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 중 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
}
?>