<?php
define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';

try {
    $userModel = new User();
    
    // "안계현" 검색 테스트
    $query = "안계현";
    echo "검색어: {$query}\n";
    
    $users = $userModel->searchUsers($query, 4); // 현재 사용자 ID 4 제외
    
    echo "검색 결과 개수: " . count($users) . "\n";
    
    if (!empty($users)) {
        foreach ($users as $user) {
            echo "사용자: ID={$user['id']}, 닉네임={$user['nickname']}\n";
        }
    } else {
        echo "검색 결과가 없습니다.\n";
        
        // 모든 사용자 확인
        echo "\n모든 활성 사용자 확인:\n";
        $allUsers = $userModel->searchUsers("", null); // 모든 사용자
        foreach ($allUsers as $user) {
            echo "사용자: ID={$user['id']}, 닉네임={$user['nickname']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage() . "\n";
}
?>