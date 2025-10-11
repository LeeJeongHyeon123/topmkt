<?php
/**
 * 로그인 리다이렉트 기능 테스트 스크립트
 */

// 필요한 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');

require_once SRC_PATH . '/controllers/AuthController.php';

echo "🔍 로그인 리다이렉트 기능 테스트\n\n";

// AuthController 인스턴스 생성
$authController = new AuthController();

// 테스트할 URL들
$testUrls = [
    '/lectures/217' => '강의 상세 페이지 (문제 있었던 URL)',
    '/events/123' => '행사 상세 페이지',
    '/community' => '커뮤니티 메인',
    '/user/profile' => '사용자 프로필',
    '/post/123' => '게시글 상세',
    '/home' => '홈 페이지',
    '/legal/terms' => '약관 페이지',
    '/' => '루트 페이지',
    'https://evil.com' => '외부 URL (차단되어야 함)',
    '/admin/dashboard' => '관리자 페이지 (허용되지 않아야 함)',
    '' => '빈 URL (차단되어야 함)'
];

echo "📋 테스트 결과:\n";
echo str_repeat("=", 60) . "\n";

foreach ($testUrls as $url => $description) {
    // Reflection을 사용해서 private 메소드 호출
    $reflection = new ReflectionClass($authController);
    $method = $reflection->getMethod('isValidRedirectUrl');
    $method->setAccessible(true);

    $result = $method->invoke($authController, $url);

    $status = $result ? '✅ 허용' : '❌ 차단';
    echo sprintf("%-20s | %s | %s\n", $url, $status, $description);
}

echo str_repeat("=", 60) . "\n";
echo "✨ 테스트 완료!\n";

// 실제 사용 시나리오 테스트
echo "\n🎯 실제 시나리오 테스트:\n";
echo "- 강의 상세 페이지(/lectures/217)에서 '로그인 후 신청' 클릭\n";
echo "- 로그인 페이지로 이동하면서 redirect 파라미터 전달\n";
echo "- 로그인 성공 후 원래 페이지로 리다이렉트\n\n";

$testUrl = '/lectures/217';
$reflection = new ReflectionClass($authController);
$method = $reflection->getMethod('isValidRedirectUrl');
$method->setAccessible(true);
$result = $method->invoke($authController, $testUrl);

if ($result) {
    echo "✅ 성공: 강의 상세 페이지 리다이렉트가 정상적으로 허용됩니다!\n";
} else {
    echo "❌ 실패: 여전히 강의 상세 페이지 리다이렉트가 차단됩니다.\n";
}
?>
