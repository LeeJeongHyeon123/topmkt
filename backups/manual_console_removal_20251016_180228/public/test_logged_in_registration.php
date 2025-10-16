<?php
/**
 * 로그인된 사용자로 신청관리 대시보드 테스트
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/routes.php';

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

echo "<h1>로그인된 사용자 신청관리 대시보드 테스트</h1>";
echo "<p>사용자 ID: 4</p>";
echo "<p>사용자 역할: ROLE_USER</p>";
echo "<p>로그인 상태: " . ($_SESSION['is_logged_in'] ? '로그인됨' : '로그아웃됨') . "</p>";

// 라우터 실행
$_SERVER['REQUEST_URI'] = '/registrations';
$_SERVER['REQUEST_METHOD'] = 'GET';

try {
    $router = new Router();
    
    echo "<h2>라우터 실행 시작</h2>";
    
    // 출력 캡처
    ob_start();
    $router->dispatch();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p style='color: green;'>✅ 라우터 실행 성공</p>";
    echo "<p>출력 길이: " . strlen($output) . " 바이트</p>";
    
    // 출력 결과 분석
    if (strpos($output, 'Location:') !== false) {
        echo "<p style='color: orange;'>⚠️ 리다이렉트 발생</p>";
        // 리다이렉트 헤더 추출
        $headers = headers_list();
        foreach ($headers as $header) {
            if (strpos($header, 'Location:') !== false) {
                echo "<p>리다이렉트 대상: " . $header . "</p>";
            }
        }
    }
    
    if (strpos($output, '500') !== false) {
        echo "<p style='color: red;'>❌ 500 오류 발생</p>";
    }
    
    if (strpos($output, '<!DOCTYPE html>') !== false) {
        echo "<p style='color: green;'>✅ HTML 출력 확인</p>";
    }
    
    // 출력 내용 일부 표시
    echo "<details><summary>출력 내용 (처음 1000자)</summary>";
    echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre>";
    echo "</details>";
    
    // 전체 출력 표시
    echo "<hr>";
    echo "<h2>실제 대시보드 출력</h2>";
    echo $output;
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 라우터 실행 실패: " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='/registrations'>실제 신청관리 페이지로 이동</a></p>";
echo "<p><a href='/'>홈으로 이동</a></p>";
?>