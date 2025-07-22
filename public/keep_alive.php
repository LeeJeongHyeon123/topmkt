<?php
/**
 * 세션 Keep-Alive 엔드포인트
 * AJAX 요청을 받아 세션을 갱신합니다
 */

// 세션 시작
session_start();

// AJAX 요청인지 확인
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Forbidden');
}

// 로그인 상태 확인
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// 세션 활동 시간 업데이트
$_SESSION['last_activity'] = time();

// 세션 ID 재생성 (10분마다)
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 600) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// 응답
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'last_activity' => date('Y-m-d H:i:s', $_SESSION['last_activity']),
    'session_remaining' => ini_get('session.gc_maxlifetime') - (time() - $_SESSION['last_activity'])
]);
?>