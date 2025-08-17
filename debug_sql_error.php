<?php
/**
 * SQL 오류 상세 디버깅
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

echo "🔍 SQL 오류 상세 디버깅\n\n";

try {
    $db = Database::getInstance();
    
    // 1. 기본 연결 테스트
    echo "1. 데이터베이스 연결 테스트...\n";
    $result = $db->fetch("SELECT COUNT(*) as count FROM users");
    echo "✅ 연결 성공: 총 사용자 수 {$result['count']}\n\n";
    
    // 2. 사용자 ID 5 정보 확인
    echo "2. 사용자 ID 5 정보 확인...\n";
    $user = $db->fetch("SELECT id, nickname, email, bio FROM users WHERE id = 5");
    
    if (!$user) {
        echo "❌ 사용자 ID 5를 찾을 수 없습니다.\n";
        exit(1);
    }
    
    echo "📋 사용자 정보:\n";
    echo "  - ID: {$user['id']}\n";
    echo "  - 닉네임: {$user['nickname']}\n";
    echo "  - 이메일: " . ($user['email'] ?: '없음') . "\n";
    echo "  - 소개: " . ($user['bio'] ?: '없음') . "\n\n";
    
    // 3. PDO 오류 모드 설정 및 직접 쿼리 테스트
    echo "3. PDO 오류 모드 설정 및 직접 업데이트 테스트...\n";
    
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 테스트 업데이트 쿼리
    $testNickname = $user['nickname'] . '_디버그' . date('His');
    $testBio = '디버그 테스트 - ' . date('Y-m-d H:i:s');
    
    $sql = "UPDATE users SET nickname = :nickname, bio = :bio, updated_at = NOW() WHERE id = :user_id";
    $params = [
        ':user_id' => 5,
        ':nickname' => $testNickname,
        ':bio' => $testBio
    ];
    
    echo "📝 실행할 쿼리: {$sql}\n";
    echo "📊 파라미터: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        echo "❌ 쿼리 준비 실패\n";
        print_r($pdo->errorInfo());
        exit(1);
    }
    
    echo "✅ 쿼리 준비 성공\n";
    
    // 파라미터 바인딩
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
        echo "🔗 바인딩: {$key} => {$value}\n";
    }
    
    echo "\n🔄 쿼리 실행...\n";
    $result = $stmt->execute();
    
    if ($result) {
        echo "✅ 쿼리 실행 성공\n";
        echo "📊 영향받은 행 수: " . $stmt->rowCount() . "\n\n";
        
        // 업데이트된 데이터 확인
        $updatedUser = $db->fetch("SELECT nickname, bio, updated_at FROM users WHERE id = 5");
        echo "📋 업데이트된 데이터:\n";
        echo "  - 닉네임: {$updatedUser['nickname']}\n";
        echo "  - 소개: {$updatedUser['bio']}\n";
        echo "  - 수정일: {$updatedUser['updated_at']}\n\n";
        
        echo "🎉 직접 SQL 업데이트 성공!\n";
        
    } else {
        echo "❌ 쿼리 실행 실패\n";
        print_r($stmt->errorInfo());
    }
    
} catch (PDOException $e) {
    echo "❌ PDO 오류: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📊 오류 코드: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "❌ 일반 오류: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>