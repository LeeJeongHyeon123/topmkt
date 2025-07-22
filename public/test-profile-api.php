<?php
require_once '../src/config/config.php';
require_once '../src/models/User.php';
require_once '../src/helpers/ResponseHelper.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $userModel = new User();
    $nickname = '우리집탄이';
    
    echo "Testing profile API for nickname: " . $nickname . "\n";
    
    // 공개 프로필 정보 조회
    $user = $userModel->getPublicProfile($nickname);
    
    if (!$user) {
        echo "User not found\n";
        ResponseHelper::json(['error' => '존재하지 않는 사용자입니다.'], 404);
        exit;
    }
    
    echo "User found: " . json_encode($user) . "\n";
    
    // 활동 통계 조회
    $stats = $userModel->getProfileStats($user['id']);
    echo "Stats: " . json_encode($stats) . "\n";
    
    ResponseHelper::json([
        'user' => $user,
        'stats' => $stats,
        'status' => 'success'
    ]);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    ResponseHelper::json(['error' => $e->getMessage()], 500);
}
?>