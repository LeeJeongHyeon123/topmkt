<?php
/**
 * 편집 페이지 직접 테스트
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 편집 페이지 직접 테스트</h1>";

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 세션 시작 및 JWT 설정
session_start();

// JWT 토큰을 쿠키에 설정 (관리자 계정)
setcookie('jwt_token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MjM2NDY0NzIsImV4cCI6MTcyNjI0MDE0NywiaXNzIjoidG9wbWt0eC5jb20iLCJhdWQiOiJ0b3BtayIsInN1YiI6IjQiLCJjbGFpbXMiOnsiY29tcGFueV9pZCI6NCwidHlwZSI6ImNvbXBhbnkifX0.mFKnUGGKd1VYiIDbdKXzSu8KfpV6bXNn35TKkrr_JDw', 
    time() + (30 * 24 * 60 * 60), '/', '', true, true);

$_COOKIE['jwt_token'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MjM2NDY0NzIsImV4cCI6MTcyNjI0MDE0NywiaXNzIjoidG9wbWt0eC5jb20iLCJhdWQiOiJ0b3BtayIsInN1YiI6IjQiLCJjbGFpbXMiOnsiY29tcGFueV9pZCI6NCwidHlwZSI6ImNvbXBhbnkifX0.mFKnUGGKd1VYiIDbdKXzSu8KfpV6bXNn35TKkrr_JDw';

echo "<h2>🔑 1. 인증 정보 설정</h2>";
echo "<p>JWT 토큰 설정됨: 기업 계정 ID 4</p>";

// 필요한 파일들 로드
try {
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/models/Notice.php';
    require_once SRC_PATH . '/controllers/NoticeController.php';
    
    echo "<h2>📁 2. 파일 로딩 확인</h2>";
    echo "<p>✅ 모든 필요 파일 로딩 완료</p>";
    
    // 인증 상태 확인
    echo "<h2>🔐 3. 인증 상태 확인</h2>";
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "<p>로그인 상태: " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그아웃됨") . "</p>";
    
    if ($isLoggedIn) {
        $currentUserId = AuthMiddleware::getCurrentUserId();
        echo "<p>현재 사용자 ID: $currentUserId</p>";
    }
    
    // 공지사항 데이터 확인
    echo "<h2>📄 4. 공지사항 데이터 확인</h2>";
    $noticeModel = new Notice();
    $notice = $noticeModel->getById(10);
    
    if ($notice) {
        echo "<p>✅ 공지사항 10번 존재</p>";
        echo "<p>제목: " . htmlspecialchars($notice['title']) . "</p>";
        echo "<p>작성자 ID: " . ($notice['company_id'] ?? 'N/A') . "</p>";
        
        if ($isLoggedIn) {
            $isOwner = $noticeModel->isOwner(10, $currentUserId);
            echo "<p>편집 권한: " . ($isOwner ? "✅ 있음" : "❌ 없음") . "</p>";
        }
    } else {
        echo "<p>❌ 공지사항 10번을 찾을 수 없음</p>";
    }
    
    // 직접 컨트롤러 호출
    echo "<h2>🎮 5. 컨트롤러 직접 호출</h2>";
    
    if ($isLoggedIn && $notice) {
        echo "<p>NoticeController::showEdit(10) 직접 호출...</p>";
        
        // 출력 버퍼 시작
        ob_start();
        
        try {
            $controller = new NoticeController();
            
            // showEdit 메서드 직접 호출
            $controller->showEdit(10);
            
            $output = ob_get_clean();
            
            if (strlen($output) > 100) {
                echo "<p>✅ 편집 페이지 렌더링 성공 (" . strlen($output) . " bytes)</p>";
                echo "<details><summary>렌더링된 HTML (처음 500자)</summary>";
                echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
                echo "</details>";
                
                // 실제 페이지 표시
                echo "<hr>";
                echo "<h2>📺 6. 실제 편집 페이지</h2>";
                echo $output;
                
            } else {
                echo "<p>❌ 렌더링 결과가 너무 작음: " . strlen($output) . " bytes</p>";
                if ($output) {
                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                }
            }
            
        } catch (Exception $e) {
            $errorOutput = ob_get_clean();
            echo "<p>❌ 컨트롤러 호출 오류: " . $e->getMessage() . "</p>";
            echo "<p>스택 추적: " . $e->getTraceAsString() . "</p>";
            if ($errorOutput) {
                echo "<p>출력 내용: " . htmlspecialchars($errorOutput) . "</p>";
            }
        }
        
    } else {
        echo "<p>❌ 인증 또는 데이터 문제로 컨트롤러 호출 불가</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 치명적 오류</h2>";
    echo "<p>오류: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<p>스택 추적:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>🎯 테스트 결론</h2>";
echo "<p><strong>이 테스트를 통해 편집 기능이 실제로 작동하는지 확인할 수 있습니다.</strong></p>";
echo "<p>테스트 시간: " . date('Y-m-d H:i:s') . "</p>";
?>