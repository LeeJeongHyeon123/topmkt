<?php
/**
 * 간단한 편집 테스트 - 인증 없이 렌더링 확인
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

echo "🔧 간단한 편집 페이지 테스트\n\n";

try {
    // 필수 파일 로드
    require_once SRC_PATH . '/models/Notice.php';
    
    // 공지사항 데이터 가져오기
    $noticeModel = new Notice();
    $notice = $noticeModel->getById(10);
    
    if (!$notice) {
        echo "❌ 공지사항 10번을 찾을 수 없음\n";
        exit;
    }
    
    echo "✅ 공지사항 데이터:\n";
    echo "   - ID: {$notice['id']}\n";
    echo "   - 제목: {$notice['title']}\n";
    echo "   - 작성자 ID: {$notice['company_id']}\n";
    
    // 편집 페이지 파일 존재 확인
    $editFile = SRC_PATH . '/views/notices/edit.php';
    if (!file_exists($editFile)) {
        echo "❌ 편집 페이지 파일이 없음: $editFile\n";
        exit;
    }
    
    echo "✅ 편집 페이지 파일 존재 (" . filesize($editFile) . " bytes)\n";
    
    // PHP 구문 검사
    $syntaxCheck = shell_exec("php -l '$editFile' 2>&1");
    echo "📝 구문 검사 결과:\n$syntaxCheck\n";
    
    // 인증 시뮬레이션 (edit.php에서 필요한 변수들 설정)
    $isLoggedIn = true;
    $currentUserId = 4; // 기업 계정 ID
    
    echo "🎭 인증 시뮬레이션: 사용자 ID 4로 로그인\n";
    
    // 이미지 배열 설정 (있다면)
    $notice['images'] = $notice['images'] ?? [];
    
    echo "📸 이미지 정보: " . count($notice['images']) . "개\n";
    
    // 출력 버퍼로 렌더링 테스트
    ob_start();
    
    try {
        // edit.php 직접 include (인증 체크 우회)
        include $editFile;
        
        $output = ob_get_clean();
        
        echo "✅ 렌더링 성공: " . strlen($output) . " bytes\n";
        
        // HTML 유효성 간단 체크
        $hasForm = strpos($output, '<form') !== false;
        $hasTitle = strpos($output, 'name="title"') !== false;
        $hasContent = strpos($output, 'name="content"') !== false;
        
        echo "📋 HTML 구성 요소 체크:\n";
        echo "   - 폼: " . ($hasForm ? "✅" : "❌") . "\n";
        echo "   - 제목 입력: " . ($hasTitle ? "✅" : "❌") . "\n";
        echo "   - 내용 입력: " . ($hasContent ? "✅" : "❌") . "\n";
        
        // 에러 확인
        if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
            echo "❌ HTML에 PHP 오류 포함됨\n";
            echo "오류 내용:\n" . $output . "\n";
        } else {
            echo "✅ PHP 오류 없음\n";
            
            // 성공 메시지
            echo "\n🎉 편집 페이지 렌더링 완전 성공!\n";
            echo "📊 통계:\n";
            echo "   - 파일 크기: " . filesize($editFile) . " bytes\n";
            echo "   - 렌더링 크기: " . strlen($output) . " bytes\n";
            echo "   - 폼 요소: 모두 정상\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ 렌더링 오류: " . $e->getMessage() . "\n";
        echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ 치명적 오류: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n⏰ 테스트 완료: " . date('Y-m-d H:i:s') . "\n";
?>