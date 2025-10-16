<?php
/**
 * 강의 신청 API 직접 테스트
 * 500 오류의 정확한 원인을 파악하기 위한 디버깅 도구
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 에러 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>강의 신청 API 테스트</h1>";
echo "<h2>1. 기본 설정 확인</h2>";

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    
    echo "✅ 기본 설정 파일 로드 완료<br>";
    
    // 헬퍼 클래스 로드
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    
    echo "✅ 컨트롤러 및 헬퍼 클래스 로드 완료<br>";
    
} catch (Exception $e) {
    echo "<span style='color:red'>❌ 설정 로드 실패: " . $e->getMessage() . "</span><br>";
    echo "<pre>스택 추적: " . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<h2>2. 데이터베이스 연결 테스트</h2>";

try {
    $db = Database::getInstance();
    echo "✅ 데이터베이스 연결 성공<br>";
    
    // 강의 테이블 확인
    $lectures = $db->query("SELECT id, title FROM lectures WHERE status = 'published' LIMIT 5");
    echo "✅ 강의 테이블 접근 성공<br>";
    
    // 사용자 테이블 확인  
    $users = $db->query("SELECT id, nickname FROM users LIMIT 3");
    echo "✅ 사용자 테이블 접근 성공<br>";
    
} catch (Exception $e) {
    echo "<span style='color:red'>❌ 데이터베이스 연결 실패: " . $e->getMessage() . "</span><br>";
    exit;
}

echo "<h2>3. 강의 신청 컨트롤러 테스트</h2>";

try {
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // CSRF 토큰 생성
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // 테스트용 사용자 설정 (실제 환경에서는 인증된 사용자 사용)
    $_SESSION['user_id'] = 4; // 테스트용 사용자 ID
    $_SESSION['user_role'] = 'user';
    
    echo "✅ 세션 및 인증 설정 완료<br>";
    
    // RegistrationController 인스턴스 생성
    $controller = new RegistrationController();
    echo "✅ RegistrationController 인스턴스 생성 완료<br>";
    
} catch (Exception $e) {
    echo "<span style='color:red'>❌ 컨트롤러 생성 실패: " . $e->getMessage() . "</span><br>";
    echo "<pre>스택 추적: " . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<h2>4. 강의 목록 조회</h2>";

try {
    $db = Database::getInstance();
    $lectures = $db->fetchAll("SELECT id, title, start_date, start_time FROM lectures WHERE status = 'published' ORDER BY created_at DESC LIMIT 10");
    
    if (!empty($lectures)) {
        echo "<h3>사용 가능한 강의 목록:</h3>";
        echo "<ul>";
        foreach ($lectures as $lecture) {
            echo "<li>ID: {$lecture['id']} - {$lecture['title']} ({$lecture['start_date']} {$lecture['start_time']})</li>";
        }
        echo "</ul>";
        
        // 첫 번째 강의로 테스트
        $testLectureId = $lectures[0]['id'];
        echo "<p><strong>테스트할 강의 ID: {$testLectureId}</strong></p>";
        
    } else {
        echo "<span style='color:orange'>⚠️ 사용 가능한 강의가 없습니다.</span><br>";
        $testLectureId = 1; // 기본값
    }
    
} catch (Exception $e) {
    echo "<span style='color:red'>❌ 강의 목록 조회 실패: " . $e->getMessage() . "</span><br>";
    $testLectureId = 1; // 기본값
}

echo "<h2>5. 강의 신청 상태 조회 테스트</h2>";

try {
    ob_start(); // 출력 버퍼링 시작
    $controller->getRegistrationStatus($testLectureId);
    $output = ob_get_clean(); // 출력 버퍼 내용 가져오기
    
    echo "✅ 신청 상태 조회 성공<br>";
    echo "<h4>응답 내용:</h4>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
} catch (Exception $e) {
    ob_clean(); // 버퍼 정리
    echo "<span style='color:red'>❌ 신청 상태 조회 실패: " . $e->getMessage() . "</span><br>";
    echo "<pre>스택 추적: " . $e->getTraceAsString() . "</pre>";
}

echo "<h2>6. 강의 신청 등록 테스트</h2>";

try {
    // POST 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/api/lectures/' . $testLectureId . '/registration';
    
    // 테스트 데이터 준비
    $testData = [
        'csrf_token' => $_SESSION['csrf_token'],
        'participant_name' => '테스트 사용자',
        'participant_email' => 'test@topmktx.com',
        'participant_phone' => '010-1234-5678',
        'company_name' => '테스트 회사',
        'position' => '테스트 직책',
        'motivation' => '테스트 참가 동기',
        'special_requests' => '테스트 요청사항',
        'how_did_you_know' => 'website'
    ];
    
    // php://input 시뮬레이션을 위해 임시 파일 생성
    $tempFile = tmpfile();
    fwrite($tempFile, json_encode($testData));
    rewind($tempFile);
    
    echo "✅ 테스트 데이터 준비 완료<br>";
    echo "<h4>테스트 데이터:</h4>";
    echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    // 실제 신청 등록 호출
    ob_start();
    
    // php://input을 시뮬레이션하기 위한 해킹
    stream_wrapper_unregister("php");
    stream_wrapper_register("php", "PhpInputMock");
    PhpInputMock::$data = json_encode($testData);
    
    $controller->createRegistration($testLectureId);
    $output = ob_get_clean();
    
    echo "✅ 신청 등록 호출 완료<br>";
    echo "<h4>응답 내용:</h4>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    fclose($tempFile);
    
} catch (Exception $e) {
    ob_clean();
    echo "<span style='color:red'>❌ 신청 등록 실패: " . $e->getMessage() . "</span><br>";
    echo "<pre>스택 추적: " . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    ob_clean();
    echo "<span style='color:red'>❌ 치명적 오류: " . $e->getMessage() . "</span><br>";
    echo "<pre>파일: " . $e->getFile() . " 라인: " . $e->getLine() . "</pre>";
    echo "<pre>스택 추적: " . $e->getTraceAsString() . "</pre>";
}

echo "<h2>7. 서버 환경 정보</h2>";
echo "<ul>";
echo "<li>PHP 버전: " . PHP_VERSION . "</li>";
echo "<li>서버 시간: " . date('Y-m-d H:i:s') . "</li>";
echo "<li>메모리 사용량: " . memory_get_usage(true) / 1024 / 1024 . " MB</li>";
echo "<li>세션 ID: " . session_id() . "</li>";
echo "</ul>";

// php://input 시뮬레이션을 위한 클래스
class PhpInputMock {
    public static $data = '';
    private $position = 0;

    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->position = 0;
        return true;
    }

    public function stream_read($count) {
        $ret = substr(self::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof() {
        return $this->position >= strlen(self::$data);
    }

    public function stream_stat() {
        return array();
    }

    public function stream_tell() {
        return $this->position;
    }
}

?>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>