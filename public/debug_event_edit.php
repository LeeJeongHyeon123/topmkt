<?php
/**
 * EventController edit 디버깅 페이지
 */

// 현재 경로 및 환경 정보
echo "<h1>🔍 EventController Edit 디버깅</h1>";
echo "<h2>환경 정보</h2>";
echo "<p><strong>현재 작업 디렉토리:</strong> " . getcwd() . "</p>";
echo "<p><strong>현재 스크립트:</strong> " . __FILE__ . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</p>";

// 프로젝트 루트 경로 설정
define('PROJECT_ROOT', dirname(__DIR__));
define('SRC_PATH', PROJECT_ROOT . '/src');

echo "<h2>경로 확인</h2>";
echo "<p><strong>PROJECT_ROOT:</strong> " . PROJECT_ROOT . "</p>";
echo "<p><strong>SRC_PATH:</strong> " . SRC_PATH . "</p>";

// EventController 파일 존재 확인
$eventControllerPath = SRC_PATH . '/controllers/EventController.php';
echo "<p><strong>EventController 파일:</strong> " . $eventControllerPath . "</p>";
echo "<p><strong>파일 존재:</strong> " . (file_exists($eventControllerPath) ? '✅ YES' : '❌ NO') . "</p>";

if (file_exists($eventControllerPath)) {
    echo "<p><strong>파일 권한:</strong> " . substr(sprintf('%o', fileperms($eventControllerPath)), -4) . "</p>";
    echo "<p><strong>파일 크기:</strong> " . filesize($eventControllerPath) . " bytes</p>";
}

// config 파일들 확인
$configFiles = [
    'routes.php' => SRC_PATH . '/config/routes.php',
    'database.php' => SRC_PATH . '/config/database.php',
    'config.php' => SRC_PATH . '/config/config.php'
];

echo "<h2>Config 파일들</h2>";
foreach ($configFiles as $name => $path) {
    echo "<p><strong>$name:</strong> " . ($path) . " - " . (file_exists($path) ? '✅ 존재' : '❌ 없음') . "</p>";
}

// 세션 및 사용자 정보
session_start();
echo "<h2>세션 정보</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'N/A') . "</p>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['role'] ?? 'N/A') . "</p>";

// JWT 토큰 확인
$jwt_token = $_COOKIE['jwt_token'] ?? null;
echo "<p><strong>JWT Token:</strong> " . ($jwt_token ? '✅ 존재' : '❌ 없음') . "</p>";

// 데이터베이스 연결 테스트
echo "<h2>데이터베이스 연결 테스트</h2>";
try {
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance()->getConnection();

    echo "<p>✅ <strong>데이터베이스 연결 성공</strong></p>";

    // 이벤트 202 정보 조회
    $stmt = $db->prepare("SELECT id, title, start_date, user_id, content_type FROM lectures WHERE id = ?");
    $stmt->bind_param("i", $eventId);
    $eventId = 202;
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if ($event) {
        echo "<p>✅ <strong>이벤트 202 정보:</strong></p>";
        echo "<ul>";
        echo "<li><strong>제목:</strong> " . htmlspecialchars($event['title']) . "</li>";
        echo "<li><strong>시작일:</strong> " . $event['start_date'] . "</li>";
        echo "<li><strong>소유자 ID:</strong> " . $event['user_id'] . "</li>";
        echo "<li><strong>타입:</strong> " . $event['content_type'] . "</li>";
        echo "</ul>";

        // 날짜 비교
        $today = date('Y-m-d');
        $isPast = $event['start_date'] < $today;
        echo "<p><strong>오늘 날짜:</strong> $today</p>";
        echo "<p><strong>지난 일정 여부:</strong> " . ($isPast ? '✅ YES (수정 차단 대상)' : '❌ NO (수정 가능)') . "</p>";

        // 권한 체크
        $currentUserId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['role'] ?? 'ROLE_USER';
        $canEdit = ($userRole === 'ROLE_ADMIN') || ($event['user_id'] == $currentUserId);

        echo "<p><strong>현재 사용자 ID:</strong> $currentUserId</p>";
        echo "<p><strong>현재 사용자 역할:</strong> $userRole</p>";
        echo "<p><strong>수정 권한:</strong> " . ($canEdit ? '✅ YES' : '❌ NO') . "</p>";

        if (!$canEdit) {
            echo "<p style='color: red;'><strong>❌ 권한 체크 실패!</strong></p>";
            echo "<p>이벤트 소유자 ID: {$event['user_id']}, 현재 사용자 ID: $currentUserId</p>";
            echo "<p><strong>문제:</strong> 403 Forbidden 오류는 이 권한 체크에서 발생했을 것입니다.</p>";
        } else if ($isPast) {
            echo "<p style='color: orange;'><strong>⚠️ 지난 일정 수정 차단!</strong></p>";
            echo "<p><strong>예상 메시지:</strong> '지난 일정은 수정할 수 없습니다. 삭제만 가능합니다.'</p>";
            echo "<p style='color: green;'><strong>✅ 우리가 구현한 기능이 정상 작동해야 함!</strong></p>";
        } else {
            echo "<p style='color: green;'><strong>✅ 수정 가능!</strong></p>";
        }

        // AuthMiddleware 권한 체크 시뮬레이션
        echo "<h3>AuthMiddleware 권한 체크 시뮬레이션</h3>";
        require_once SRC_PATH . '/middleware/AuthMiddleware.php';

        // JWT 기반 사용자 정보 확인
        if ($jwt_token) {
            echo "<p><strong>JWT 기반 인증 시도...</strong></p>";
            try {
                // JWT 디코드 시뮬레이션 (실제 구현에 따라 다를 수 있음)
                $jwtRole = AuthMiddleware::getUserRole();
                echo "<p><strong>JWT Role:</strong> $jwtRole</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'><strong>JWT 오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }

    } else {
        echo "<p style='color: red;'>❌ <strong>이벤트 202를 찾을 수 없습니다.</strong></p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>데이터베이스 오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 라우트 테스트
echo "<h2>라우트 테스트</h2>";
echo "<p><strong>요청 URL 패턴:</strong> /events/202/edit</p>";
echo "<p><strong>라우트 정의:</strong> GET:/events/{id}/edit</p>";

// 실제 URL 테스트 링크들
echo "<h2>테스트 링크들</h2>";
echo "<p><a href='/events/202/edit' target='_blank' style='color: red;'>❌ 현재 문제가 있는 URL (403 오류)</a></p>";
echo "<p><a href='/events/205/edit' target='_blank' style='color: blue;'>💡 미래 일정 테스트 (ID 205)</a></p>";
echo "<p><a href='/lectures/3/edit' target='_blank' style='color: orange;'>🔄 강의 수정 테스트 (ID 3)</a></p>";

echo "<hr>";
echo "<p><strong>디버깅 완료 시간:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 추가 정보
echo "<h2>추가 디버깅 정보</h2>";
echo "<p><strong>IP 주소:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</p>";
echo "<p><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</p>";
echo "<p><strong>Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// 결론
echo "<h2>🎯 결론 및 다음 단계</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc;'>";
echo "<h3>예상 문제 원인:</h3>";
echo "<ol>";
echo "<li><strong>권한 체크 실패:</strong> EventController에서 권한 확인 시 403 반환</li>";
echo "<li><strong>JWT 인증 문제:</strong> 세션과 JWT 간 불일치</li>";
echo "<li><strong>라우트 미들웨어:</strong> 라우트 레벨에서 권한 차단</li>";
echo "</ol>";
echo "<h3>확인 사항:</h3>";
echo "<ul>";
echo "<li>현재 사용자 ID와 이벤트 소유자 ID 일치 여부</li>";
echo "<li>지난 일정 여부 및 차단 메시지 표시 여부</li>";
echo "<li>AuthMiddleware의 권한 체크 로직</li>";
echo "</ul>";
echo "</div>";
?>