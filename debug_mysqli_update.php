<?php
/**
 * MySQLi Database 클래스를 사용한 업데이트 디버깅
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

echo "🔍 MySQLi Database 클래스 업데이트 디버깅\n\n";

try {
    $db = Database::getInstance();
    
    // 1. 기본 연결 테스트
    echo "1. 데이터베이스 연결 테스트...\n";
    $result = $db->fetch("SELECT COUNT(*) as count FROM users");
    echo "✅ 연결 성공: 총 사용자 수 {$result['count']}\n\n";
    
    // 2. 사용자 ID 5 정보 확인
    echo "2. 사용자 ID 5 정보 확인...\n";
    $user = $db->fetch("SELECT id, nickname, email, bio FROM users WHERE id = ?", [5]);
    
    if (!$user) {
        echo "❌ 사용자 ID 5를 찾을 수 없습니다.\n";
        exit(1);
    }
    
    echo "📋 원본 사용자 정보:\n";
    echo "  - ID: {$user['id']}\n";
    echo "  - 닉네임: {$user['nickname']}\n";
    echo "  - 이메일: " . ($user['email'] ?: '없음') . "\n";
    echo "  - 소개: " . ($user['bio'] ?: '없음') . "\n\n";
    
    // 3. 직접 업데이트 테스트 (Database::execute 사용)
    echo "3. Database::execute 메서드를 사용한 업데이트 테스트...\n";
    
    $testNickname = $user['nickname'] . '_디버그' . date('His');
    $testBio = '디버그 테스트 - ' . date('Y-m-d H:i:s');
    
    // 명명된 파라미터 방식
    $sql = "UPDATE users SET nickname = :nickname, bio = :bio, updated_at = NOW() WHERE id = :user_id";
    $params = [
        ':user_id' => 5,
        ':nickname' => $testNickname,
        ':bio' => $testBio
    ];
    
    echo "📝 실행할 쿼리: {$sql}\n";
    echo "📊 파라미터: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 글로벌 디버깅 변수 초기화
    $GLOBALS['debug_update_binding'] = null;
    $GLOBALS['debug_last_binding'] = null;
    
    echo "🔄 Database::execute 메서드 호출...\n";
    $affectedRows = $db->execute($sql, $params);
    
    echo "📊 영향받은 행 수: {$affectedRows}\n\n";
    
    // 디버깅 정보 출력
    if (isset($GLOBALS['debug_last_binding'])) {
        echo "🔍 디버깅 정보 (마지막 바인딩):\n";
        echo "  - 변환된 SQL: " . $GLOBALS['debug_last_binding']['sql'] . "\n";
        echo "  - 바인딩 타입: " . $GLOBALS['debug_last_binding']['types'] . "\n";
        echo "  - 바인딩 값: " . json_encode($GLOBALS['debug_last_binding']['params'], JSON_UNESCAPED_UNICODE) . "\n";
        echo "  - UPDATE 쿼리 여부: " . ($GLOBALS['debug_last_binding']['is_update'] ? '예' : '아니오') . "\n\n";
    }
    
    if (isset($GLOBALS['debug_update_binding'])) {
        echo "🔍 UPDATE 전용 디버깅 정보:\n";
        echo "  - SQL: " . $GLOBALS['debug_update_binding']['sql'] . "\n";
        echo "  - 타입: " . $GLOBALS['debug_update_binding']['types'] . "\n";
        echo "  - 파라미터: " . json_encode($GLOBALS['debug_update_binding']['params'], JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    
    if ($affectedRows > 0) {
        echo "✅ 업데이트 성공!\n";
        
        // 업데이트된 데이터 확인
        $updatedUser = $db->fetch("SELECT nickname, bio, updated_at FROM users WHERE id = ?", [5]);
        echo "📋 업데이트된 데이터:\n";
        echo "  - 닉네임: {$updatedUser['nickname']}\n";
        echo "  - 소개: {$updatedUser['bio']}\n";
        echo "  - 수정일: {$updatedUser['updated_at']}\n\n";
        
        // 4. User 모델 방식 테스트
        echo "4. User 모델 updateProfile 메서드 테스트...\n";
        require_once SRC_PATH . '/models/User.php';
        
        $userModel = new User();
        
        $modelUpdateData = [
            'nickname' => $updatedUser['nickname'] . '_모델테스트',
            'bio' => '모델 테스트 - ' . date('Y-m-d H:i:s')
        ];
        
        echo "📤 모델 업데이트 데이터: " . json_encode($modelUpdateData, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 글로벌 디버깅 변수 초기화
        $GLOBALS['debug_update_binding'] = null;
        $GLOBALS['debug_last_binding'] = null;
        
        $modelResult = $userModel->updateProfile(5, $modelUpdateData);
        
        echo "📊 모델 업데이트 결과: " . ($modelResult ? '성공' : '실패') . "\n";
        
        // 모델 실행 후 디버깅 정보
        if (isset($GLOBALS['debug_last_binding'])) {
            echo "🔍 모델 실행 디버깅 정보:\n";
            echo "  - 변환된 SQL: " . $GLOBALS['debug_last_binding']['sql'] . "\n";
            echo "  - 바인딩 타입: " . $GLOBALS['debug_last_binding']['types'] . "\n";
            echo "  - 바인딩 값: " . json_encode($GLOBALS['debug_last_binding']['params'], JSON_UNESCAPED_UNICODE) . "\n\n";
        }
        
        if ($modelResult) {
            $finalUser = $db->fetch("SELECT nickname, bio, updated_at FROM users WHERE id = ?", [5]);
            echo "📋 모델 업데이트 후 최종 데이터:\n";
            echo "  - 닉네임: {$finalUser['nickname']}\n";
            echo "  - 소개: {$finalUser['bio']}\n";
            echo "  - 수정일: {$finalUser['updated_at']}\n\n";
            
            echo "🎉 모든 테스트 성공! User 모델이 정상 작동합니다.\n";
        } else {
            echo "❌ User 모델 업데이트 실패. 추가 조사 필요.\n";
        }
        
    } else {
        echo "❌ 업데이트 실패 (영향받은 행 수: {$affectedRows})\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>