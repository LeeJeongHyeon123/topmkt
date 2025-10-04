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
define('PROJECT_ROOT', __DIR__);
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

// 데이터베이스 연결 테스트
echo "<h2>데이터베이스 연결 테스트</h2>";
try {
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance()->getConnection();

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
        } else if ($isPast) {
            echo "<p style='color: orange;'><strong>⚠️ 지난 일정 수정 차단!</strong></p>";
            echo "<p>메시지: 지난 일정은 수정할 수 없습니다. 삭제만 가능합니다.</p>";
        } else {
            echo "<p style='color: green;'><strong>✅ 수정 가능!</strong></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>이벤트 202를 찾을 수 없습니다.</strong></p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>데이터베이스 오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

// EventController 직접 호출 테스트
echo "<h2>EventController 직접 호출 테스트</h2>";
try {
    if (file_exists($eventControllerPath)) {
        require_once $eventControllerPath;

        if (class_exists('EventController')) {
            echo "<p>✅ <strong>EventController 클래스 로드 성공</strong></p>";

            $controller = new EventController();
            if (method_exists($controller, 'edit')) {
                echo "<p>✅ <strong>edit 메서드 존재 확인</strong></p>";
                echo "<p><strong>시뮬레이션:</strong> EventController->edit(202) 호출 시뮬레이션...</p>";

                // 실제 호출은 하지 않고 시뮬레이션만
                echo "<p style='color: blue;'>💡 <strong>실제 호출 결과는 다음 URL에서 확인:</strong></p>";
                echo "<p><a href='/events/202/edit' target='_blank'>https://www.topmktx.com/events/202/edit</a></p>";
            } else {
                echo "<p style='color: red;'>❌ <strong>edit 메서드가 존재하지 않습니다.</strong></p>";
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>EventController 클래스를 찾을 수 없습니다.</strong></p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>EventController 테스트 오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>디버깅 완료 시간:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>