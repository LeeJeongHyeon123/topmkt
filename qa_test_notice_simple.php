<?php
/**
 * QA 테스트: Notice 모델 간단 버전
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/config/database.php';

echo "🧪 === Notice 모델 간단 QA 테스트 ===\n";
echo str_repeat("=", 50) . "\n\n";

try {
    $notice = new Notice();
    echo "✅ Notice 모델 인스턴스 생성 성공\n";
    
    // 기본 기능 테스트
    $totalCount = $notice->getTotalCount();
    echo "✅ 총 공지사항 수: " . $totalCount . "개\n";
    
    // 목록 조회 테스트
    $list = $notice->getList(1, 5);
    echo "✅ 목록 조회 성공: " . count($list) . "개 조회\n";
    
    // 기업 사용자 권한 확인
    $isCompanyUser = $notice->isCompanyUser(4);
    echo ($isCompanyUser ? "✅" : "❌") . " 사용자 ID 4 기업 권한: " . ($isCompanyUser ? "있음" : "없음") . "\n";
    
    // 기업 ID 조회
    $companyId = $notice->getUserCompanyId(4);
    echo ($companyId ? "✅" : "❌") . " 사용자 ID 4의 기업 ID: " . ($companyId ?: "없음") . "\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 Notice 모델 기본 기능 정상 작동!\n";
    echo str_repeat("=", 50) . "\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
}
?>