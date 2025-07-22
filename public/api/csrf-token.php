<?php
/**
 * CSRF 토큰 반환 API
 */
session_start();

header('Content-Type: application/json');

// CSRF 토큰 생성 (없으면)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'token' => $_SESSION['csrf_token']
]);
?>