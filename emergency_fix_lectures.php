<?php
/**
 * 강의 시스템 긴급 복구 스크립트
 * topmktx.com/lectures 시스템 오류 해결
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 프로젝트 루트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h1>🚨 강의 시스템 긴급 복구 스크립트</h1>\n";
echo "<pre>\n";

// 1. 데이터베이스 연결 테스트
echo "=== 1. 데이터베이스 연결 테스트 ===\n";

try {
    // 직접 MySQLi 연결 테스트
    $mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT');
    
    if ($mysqli->connect_error) {
        echo "❌ 데이터베이스 연결 실패: " . $mysqli->connect_error . "\n";
        
        // 대안 연결 시도
        echo "🔄 대안 연결 시도...\n";
        $mysqli = new mysqli('127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT', 3306);
        
        if ($mysqli->connect_error) {
            echo "❌ 대안 연결도 실패: " . $mysqli->connect_error . "\n";
            echo "💡 해결방법:\n";
            echo "1. MySQL 서비스 상태 확인: systemctl status mysqld\n";
            echo "2. MySQL 재시작: systemctl restart mysqld\n";
            echo "3. 비밀번호 확인: mysql -u root -p\n";
            exit(1);
        }
    }
    
    echo "✅ 데이터베이스 연결 성공!\n";
    $mysqli->set_charset('utf8mb4');
    
} catch (Exception $e) {
    echo "❌ 연결 중 예외 발생: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. 강의 테이블 구조 확인
echo "\n=== 2. 강의 테이블 구조 확인 ===\n";

$tables_to_check = ['lectures', 'lecture_registrations', 'users'];

foreach ($tables_to_check as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ 테이블 '$table' 존재함\n";
        
        // 테이블 구조 확인
        if ($table === 'lectures') {
            $result = $mysqli->query("DESCRIBE $table");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            $required_columns = ['id', 'title', 'content', 'status', 'user_id', 'start_date', 'start_time'];
            $missing = array_diff($required_columns, $columns);
            
            if (empty($missing)) {
                echo "  ✅ 필수 컬럼 모두 존재\n";
            } else {
                echo "  ⚠️ 누락된 컬럼: " . implode(', ', $missing) . "\n";
            }
        }
    } else {
        echo "❌ 테이블 '$table' 누락됨\n";
    }
}

// 3. 강의 데이터 확인
echo "\n=== 3. 강의 데이터 확인 ===\n";

$result = $mysqli->query("SELECT COUNT(*) as count FROM lectures WHERE status = 'published'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "📊 게시된 강의 수: " . $row['count'] . "개\n";
} else {
    echo "❌ 강의 데이터 조회 실패: " . $mysqli->error . "\n";
}

// 최근 강의 5개 확인
$result = $mysqli->query("SELECT id, title, status, start_date FROM lectures ORDER BY created_at DESC LIMIT 5");
if ($result) {
    echo "📋 최근 강의 5개:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - ID {$row['id']}: {$row['title']} ({$row['status']})\n";
    }
} else {
    echo "❌ 최근 강의 조회 실패: " . $mysqli->error . "\n";
}

// 4. 강의 신청 시스템 테이블 확인
echo "\n=== 4. 강의 신청 시스템 확인 ===\n";

$registration_result = $mysqli->query("SHOW TABLES LIKE 'lecture_registrations'");
if ($registration_result->num_rows > 0) {
    echo "✅ lecture_registrations 테이블 존재\n";
    
    // 신청 필드 확인
    $result = $mysqli->query("DESCRIBE lecture_registrations");
    $reg_columns = [];
    while ($row = $result->fetch_assoc()) {
        $reg_columns[] = $row['Field'];
    }
    
    $required_reg_fields = ['participant_name', 'participant_email', 'status', 'is_waiting_list'];
    $missing_reg = array_diff($required_reg_fields, $reg_columns);
    
    if (empty($missing_reg)) {
        echo "  ✅ 신청 테이블 필수 필드 모두 존재\n";
    } else {
        echo "  ⚠️ 누락된 신청 필드: " . implode(', ', $missing_reg) . "\n";
    }
} else {
    echo "⚠️ lecture_registrations 테이블이 없습니다 (신청 시스템 미설치)\n";
}

// 5. 라우팅 및 컨트롤러 파일 확인
echo "\n=== 5. 시스템 파일 확인 ===\n";

$critical_files = [
    'src/controllers/LectureController.php' => '강의 컨트롤러',
    'src/views/lectures/index.php' => '강의 목록 뷰',
    'src/config/routes.php' => '라우팅 설정',
    'src/config/database.php' => '데이터베이스 설정'
];

foreach ($critical_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description 파일 존재\n";
        
        // PHP 문법 검사
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $output = [];
            $return_code = 0;
            exec("php -l \"$file\" 2>&1", $output, $return_code);
            
            if ($return_code === 0) {
                echo "  ✅ 문법 검사 통과\n";
            } else {
                echo "  ❌ 문법 오류: " . implode(', ', $output) . "\n";
            }
        }
    } else {
        echo "❌ $description 파일 누락: $file\n";
    }
}

// 6. 임시 라우팅 테스트
echo "\n=== 6. 라우팅 시스템 테스트 ===\n";

if (file_exists('src/config/routes.php')) {
    include_once 'src/config/routes.php';
    
    if (class_exists('Router')) {
        echo "✅ Router 클래스 로드 성공\n";
    } else {
        echo "❌ Router 클래스 로드 실패\n";
    }
} else {
    echo "❌ 라우팅 설정 파일 없음\n";
}

// 7. 해결 방안 제시
echo "\n=== 🛠️ 해결 방안 ===\n";

echo "📋 즉시 조치 사항:\n";
echo "1. 데이터베이스 연결이 정상이므로 애플리케이션 레벨 문제로 추정\n";
echo "2. 웹서버 에러 로그 확인 필요: tail -f /var/log/httpd/error_log\n";
echo "3. PHP 에러 로그 확인: tail -f /var/log/php-fpm/www-error.log\n";

echo "\n🔧 권장 수정 사항:\n";
echo "1. LectureController에서 Database::getInstance() 호출 시 예외 처리 강화\n";
echo "2. 강의 목록 조회 쿼리 최적화\n";
echo "3. 세션 및 캐시 정리\n";

echo "\n💡 임시 우회 방법:\n";
echo "1. 브라우저 캐시 및 쿠키 삭제\n";
echo "2. 다른 브라우저나 시크릿 모드로 접속 테스트\n";
echo "3. 직접 URL 접근: https://www.topmktx.com/lectures?view=list\n";

$mysqli->close();

echo "\n=== 진단 완료 ===\n";
echo "📊 전체적으로 시스템은 정상이나, 런타임 에러 또는 권한 문제로 추정됩니다.\n";
echo "🔍 실시간 로그 모니터링을 통한 추가 진단이 필요합니다.\n";

echo "</pre>\n";
?>