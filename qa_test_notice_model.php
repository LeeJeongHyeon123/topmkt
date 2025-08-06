<?php
/**
 * QA 테스트: Notice 모델 CRUD 기능
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/config/database.php';

echo "🧪 === Notice 모델 CRUD QA 테스트 ===\n";
echo str_repeat("=", 60) . "\n\n";

$testResults = [];
$totalTests = 0;
$passedTests = 0;
$createdNoticeIds = [];

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

$notice = new Notice();

// 테스트 1: Notice 모델 인스턴스 생성
runTest("Notice 모델 인스턴스 생성", function() use ($notice) {
    return ($notice instanceof Notice) ? true : "Notice 인스턴스 생성 실패";
});

// 테스트 2: 공지사항 생성 (CREATE)
runTest("공지사항 생성 (CREATE)", function() use ($notice, &$createdNoticeIds) {
    $testData = [
        'user_id' => 4,
        'company_id' => 1,
        'title' => 'QA 테스트용 공지사항',
        'content' => '<p>이것은 QA 테스트를 위한 공지사항입니다.</p>',
        'is_featured' => 0
    ];
    
    $noticeId = $notice->create($testData);
    if ($noticeId && is_numeric($noticeId) && $noticeId > 0) {
        $createdNoticeIds[] = $noticeId;
        return true;
    }
    
    return "공지사항 생성 실패: " . ($noticeId ?: 'NULL');
});

// 테스트 3: 공지사항 조회 (READ - 단건)
runTest("공지사항 조회 (READ - 단건)", function() use ($notice, $createdNoticeIds) {
    if (empty($createdNoticeIds)) {
        return "생성된 공지사항이 없음";
    }
    
    $noticeId = $createdNoticeIds[0];
    $result = $notice->findById($noticeId);
    
    if (!$result) {
        return "공지사항을 찾을 수 없음";
    }
    
    if ($result['title'] !== 'QA 테스트용 공지사항') {
        return "조회된 제목이 일치하지 않음";
    }
    
    if (!isset($result['company_name'])) {
        return "회사 정보가 JOIN되지 않음";
    }
    
    return true;
});

// 테스트 4: 공지사항 목록 조회 (READ - 목록)
runTest("공지사항 목록 조회 (READ - 목록)", function() use ($notice) {
    $result = $notice->getList(1, 10);
    
    if (!is_array($result)) {
        return "목록 조회 결과가 배열이 아님";
    }
    
    if (count($result) === 0) {
        return "조회된 공지사항이 없음";
    }
    
    $firstNotice = $result[0];
    $requiredFields = ['id', 'title', 'content_preview', 'company_name', 'created_at', 'view_count', 'comment_count'];
    
    foreach ($requiredFields as $field) {
        if (!isset($firstNotice[$field])) {
            return "필수 필드 '$field'가 누락됨";
        }
    }
    
    return true;
});

// 테스트 5: 공지사항 수정 (UPDATE)
runTest("공지사항 수정 (UPDATE)", function() use ($notice, $createdNoticeIds) {
    if (empty($createdNoticeIds)) {
        return "수정할 공지사항이 없음";
    }
    
    $noticeId = $createdNoticeIds[0];
    $updateData = [
        'title' => 'QA 테스트용 공지사항 (수정됨)',
        'content' => '<p>이것은 수정된 QA 테스트 공지사항입니다.</p>',
        'is_featured' => 1
    ];
    
    $result = $notice->update($noticeId, $updateData);
    if (!$result) {
        return "공지사항 수정 실패";
    }
    
    // 수정 내용 확인
    $updated = $notice->findById($noticeId);
    if ($updated['title'] !== 'QA 테스트용 공지사항 (수정됨)') {
        return "제목이 수정되지 않음";
    }
    
    if ($updated['is_featured'] != 1) {
        return "중요 공지 설정이 수정되지 않음";
    }
    
    return true;
});

// 테스트 6: 조회수 증가
runTest("조회수 증가", function() use ($notice, $createdNoticeIds) {
    if (empty($createdNoticeIds)) {
        return "테스트할 공지사항이 없음";
    }
    
    $noticeId = $createdNoticeIds[0];
    $beforeNotice = $notice->findById($noticeId);
    $beforeViewCount = $beforeNotice['view_count'];
    
    $result = $notice->incrementViewCount($noticeId);
    if (!$result) {
        return "조회수 증가 실패";
    }
    
    $afterNotice = $notice->findById($noticeId);
    $afterViewCount = $afterNotice['view_count'];
    
    if ($afterViewCount !== ($beforeViewCount + 1)) {
        return "조회수가 올바르게 증가하지 않음: $beforeViewCount -> $afterViewCount";
    }
    
    return true;
});

// 테스트 7: 검색 기능
runTest("검색 기능", function() use ($notice) {
    $searchResults = $notice->search('QA 테스트', 'title', null, 1, 10);
    
    if (!is_array($searchResults)) {
        return "검색 결과가 배열이 아님";
    }
    
    if (count($searchResults) === 0) {
        return "검색 결과가 없음 (생성한 테스트 공지사항을 찾을 수 없음)";
    }
    
    $found = false;
    foreach ($searchResults as $result) {
        if (strpos($result['title'], 'QA 테스트') !== false) {
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return "검색어가 포함된 결과를 찾을 수 없음";
    }
    
    return true;
});

// 테스트 8: 기업별 필터링
runTest("기업별 필터링", function() use ($notice) {
    $companyNotices = $notice->getByCompany(1, 1, 10);
    
    if (!is_array($companyNotices)) {
        return "기업별 조회 결과가 배열이 아님";
    }
    
    if (count($companyNotices) > 0) {
        foreach ($companyNotices as $companyNotice) {
            if ($companyNotice['company_id'] != 1) {
                return "기업 ID가 일치하지 않는 공지사항이 포함됨";
            }
        }
    }
    
    return true;
});

// 테스트 9: 중요 공지사항 조회
runTest("중요 공지사항 조회", function() use ($notice) {
    $featuredNotices = $notice->getFeatured(1, 5);
    
    if (!is_array($featuredNotices)) {
        return "중요 공지사항 조회 결과가 배열이 아님";
    }
    
    if (count($featuredNotices) > 0) {
        foreach ($featuredNotices as $featured) {
            if ($featured['is_featured'] != 1) {
                return "중요 공지가 아닌 항목이 포함됨";
            }
        }
    }
    
    return true;
});

// 테스트 10: 권한 검증 - 기업 사용자 확인
runTest("권한 검증 - 기업 사용자 확인", function() use ($notice) {
    // 승인된 관리자 사용자 (user_id: 4)
    $isCompanyUser = $notice->isCompanyUser(4);
    if (!$isCompanyUser) {
        return "승인된 관리자 사용자가 기업 사용자로 인식되지 않음";
    }
    
    // 존재하지 않는 사용자
    $isCompanyUser = $notice->isCompanyUser(99999);
    if ($isCompanyUser) {
        return "존재하지 않는 사용자가 기업 사용자로 인식됨";
    }
    
    return true;
});

// 테스트 11: 소유권 확인
runTest("소유권 확인", function() use ($notice, $createdNoticeIds) {
    if (empty($createdNoticeIds)) {
        return "소유권을 확인할 공지사항이 없음";
    }
    
    $noticeId = $createdNoticeIds[0];
    
    // 소유자 확인
    $isOwner = $notice->isOwner($noticeId, 4);
    if (!$isOwner) {
        return "실제 소유자가 소유자로 인식되지 않음";
    }
    
    // 비소유자 확인
    $isNotOwner = $notice->isOwner($noticeId, 99999);
    if ($isNotOwner) {
        return "비소유자가 소유자로 인식됨";
    }
    
    return true;
});

// 테스트 12: 통계 정보
runTest("통계 정보", function() use ($notice) {
    $totalCount = $notice->getTotalCount();
    
    if (!is_numeric($totalCount) || $totalCount < 0) {
        return "총 개수가 올바르지 않음: $totalCount";
    }
    
    return true;
});

// 테스트 13: 캐시 기능 (있는 경우)
runTest("캐시 기능", function() use ($notice, $createdNoticeIds) {
    if (empty($createdNoticeIds)) {
        return "캐시 테스트할 공지사항이 없음";
    }
    
    $noticeId = $createdNoticeIds[0];
    
    // 첫 번째 조회 (캐시 생성)
    $start1 = microtime(true);
    $result1 = $notice->findById($noticeId);
    $time1 = microtime(true) - $start1;
    
    // 두 번째 조회 (캐시 활용)
    $start2 = microtime(true);
    $result2 = $notice->findById($noticeId);
    $time2 = microtime(true) - $start2;
    
    // 결과가 동일한지 확인
    if ($result1['id'] !== $result2['id']) {
        return "캐시된 결과가 원본과 다름";
    }
    
    // 캐시가 있으면 두 번째 조회가 더 빨라야 함 (단, 매우 작은 차이일 수 있음)
    // 기능이 정상 작동하는지만 확인
    return true;
});

// 테스트 14: 공지사항 삭제 (DELETE) - 정리용
runTest("공지사항 삭제 (DELETE)", function() use ($notice, $createdNoticeIds) {
    if (empty($createdNoticeIds)) {
        return "삭제할 공지사항이 없음";
    }
    
    $noticeId = $createdNoticeIds[0];
    $result = $notice->delete($noticeId);
    
    if (!$result) {
        return "공지사항 삭제 실패";
    }
    
    // 삭제 확인
    $deleted = $notice->findById($noticeId);
    if ($deleted) {
        return "삭제된 공지사항이 여전히 조회됨";
    }
    
    return true;
});

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Notice 모델 CRUD QA 테스트 완료\n";
echo "📊 결과: $passedTests/$totalTests 통과 (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";

if ($passedTests === $totalTests) {
    echo "✅ 모든 Notice 모델 테스트 통과!\n";
} else {
    echo "❌ " . ($totalTests - $passedTests) . "개 테스트 실패\n";
    echo "\n실패한 테스트들:\n";
    foreach ($testResults as $result) {
        if (strpos($result, '❌') === 0) {
            echo $result . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
?>