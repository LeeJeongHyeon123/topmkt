<?php
/**
 * 프로필 데이터 검증 테스트
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/UserOptimized.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 프로필 데이터 검증 테스트\n";
echo "=================================\n\n";

// 세션 시작
session_start();

// 우리집탄이 세션 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_CORPORATE';
$_SESSION['nickname'] = '우리집탄이';

echo "1. 세션 상태:\n";
echo "   user_id: " . $_SESSION['user_id'] . "\n";
echo "   user_role: " . $_SESSION['user_role'] . "\n\n";

try {
    // UserOptimized 인스턴스 생성
    $userOptimized = new UserOptimized();
    echo "2. UserOptimized 인스턴스 생성 성공\n";

    // 프로필 데이터 조회
    echo "\n3. 프로필 데이터 조회 테스트:\n";
    $user = $userOptimized->getOptimizedProfileDataWithCache(4);

    if ($user === null) {
        echo "   ❌ 사용자 데이터가 null 반환됨\n";

        // 기본 사용자 조회로 확인
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
        $basicUser = $db->fetch($sql, [4]);

        if ($basicUser) {
            echo "   ℹ️ 기본 사용자 조회는 성공:\n";
            echo "     - ID: " . $basicUser['id'] . "\n";
            echo "     - 닉네임: " . $basicUser['nickname'] . "\n";
            echo "     - 상태: " . $basicUser['status'] . "\n";
        } else {
            echo "   ❌ 기본 사용자 조회도 실패\n";
        }

    } elseif (is_array($user)) {
        echo "   ✅ 사용자 데이터 조회 성공\n";
        echo "   📊 데이터 구조:\n";
        echo "     - ID: " . ($user['id'] ?? 'N/A') . "\n";
        echo "     - 닉네임: " . ($user['nickname'] ?? 'N/A') . "\n";
        echo "     - 상태: " . ($user['status'] ?? 'N/A') . "\n";
        echo "     - 통계 데이터: " . (isset($user['stats']) ? "✅" : "❌") . "\n";
        echo "     - 최근 게시글: " . (isset($user['recent_posts']) ? "✅ (" . count($user['recent_posts']) . "개)" : "❌") . "\n";
        echo "     - 최근 댓글: " . (isset($user['recent_comments']) ? "✅ (" . count($user['recent_comments']) . "개)" : "❌") . "\n";

        if (isset($user['stats'])) {
            $stats = $user['stats'];
            echo "\n   📈 통계 세부사항:\n";
            echo "     - 게시글 수: " . ($stats['post_count'] ?? 'N/A') . "\n";
            echo "     - 댓글 수: " . ($stats['comment_count'] ?? 'N/A') . "\n";
            echo "     - 좋아요 수: " . ($stats['like_count'] ?? 'N/A') . "\n";
            echo "     - 가입일수: " . ($stats['join_days'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ⚠️ 예상치 못한 데이터 타입: " . gettype($user) . "\n";
        echo "   데이터: " . print_r($user, true) . "\n";
    }

    // 4. UserController 시뮬레이션
    echo "\n4. UserController 데이터 처리 시뮬레이션:\n";

    if ($user) {
        $stats = $user['stats'];
        $recentPosts = $user['recent_posts'] ?? [];
        $recentComments = $user['recent_comments'] ?? [];

        echo "   ✅ 데이터 분리 성공\n";
        echo "   - stats: " . (is_array($stats) ? "array(" . count($stats) . ")" : gettype($stats)) . "\n";
        echo "   - recentPosts: " . count($recentPosts) . "개\n";
        echo "   - recentComments: " . count($recentComments) . "개\n";

        // 프로필 뷰 필수 변수들 확인
        $page_title = $user['nickname'] . '님의 프로필';
        $isOwnProfile = true;

        echo "\n   🎯 프로필 뷰 전달 변수들:\n";
        echo "   - page_title: '$page_title'\n";
        echo "   - isOwnProfile: " . ($isOwnProfile ? 'true' : 'false') . "\n";
        echo "   - user 배열 크기: " . count($user) . "개 키\n";

    } else {
        echo "   ❌ 데이터 처리 불가 (user가 null)\n";
    }

} catch (Exception $e) {
    echo "❌ 오류 발생:\n";
    echo "   메시지: " . $e->getMessage() . "\n";
    echo "   파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   스택 추적:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🏁 프로필 데이터 검증 완료\n";
?>