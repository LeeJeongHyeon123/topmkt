<?php
/**
 * 로그인 문제 디버깅 스크립트
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';

echo "=== 로그인 디버깅 ===\n\n";

// 직접 비밀번호 검증 테스트
$password = 'Dnlszkem1!';
$stored_hash = '$2y$10$qG40m0gWzSrruLZjLxLSJuqHsFEbFj6hkkctRSNcySoc5a1EVW/X.';

echo "1. 직접 비밀번호 검증\n";
echo "입력 비밀번호: {$password}\n";
echo "저장된 해시: {$stored_hash}\n";

$verify_result = password_verify($password, $stored_hash);
echo "검증 결과: " . ($verify_result ? "성공" : "실패") . "\n\n";

// 데이터베이스에서 실제 해시 가져오기
require_once SRC_PATH . '/config/database.php';
$db = Database::getInstance();

$sql = "SELECT password_hash FROM users WHERE nickname = '우리집탄이'";
$result = $db->fetch($sql);

if ($result) {
    echo "2. 데이터베이스 해시 검증\n";
    echo "DB 해시: {$result['password_hash']}\n";
    $db_verify = password_verify($password, $result['password_hash']);
    echo "DB 검증 결과: " . ($db_verify ? "성공" : "실패") . "\n\n";
} else {
    echo "2. 사용자를 찾을 수 없음\n\n";
}

// User 모델의 findByPhone 메서드 테스트
require_once SRC_PATH . '/models/User.php';
$userModel = new User();

echo "3. findByPhone 메서드 테스트\n";
$phone = '010-2659-1346';
$foundUser = $userModel->findByPhone($phone);

if ($foundUser) {
    echo "사용자 발견: {$foundUser['nickname']}\n";
    echo "DB password_hash: {$foundUser['password_hash']}\n";

    $model_verify = password_verify($password, $foundUser['password_hash']);
    echo "모델을 통한 검증: " . ($model_verify ? "성공" : "실패") . "\n\n";
} else {
    echo "findByPhone으로 사용자를 찾을 수 없음\n\n";
}

echo "4. 새로운 해시 생성 테스트\n";
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "새 해시: {$new_hash}\n";
$new_verify = password_verify($password, $new_hash);
echo "새 해시 검증: " . ($new_verify ? "성공" : "실패") . "\n\n";

echo "=== 디버깅 완료 ===\n";
?>