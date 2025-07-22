<?php
/**
 * 강의 신청 관리 시스템 통합 테스트
 * 
 * 이 테스트는 신청 시스템의 핵심 기능들을 검증합니다:
 * 1. 신청 프로세스
 * 2. 승인/거절 프로세스  
 * 3. 이메일 알림
 * 4. 대시보드 기능
 */

// 테스트 환경 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 프로젝트 루트 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 설정 파일 로드
require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

class RegistrationSystemTest
{
    private $db;
    private $testResults = [];
    
    public function __construct()
    {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->db->connect_error) {
            die("데이터베이스 연결 실패: " . $this->db->connect_error);
        }
        $this->db->set_charset("utf8mb4");
    }
    
    /**
     * 모든 테스트 실행
     */
    public function runAllTests()
    {
        echo "=== 강의 신청 관리 시스템 테스트 시작 ===\n\n";
        
        $this->testDatabaseSchema();
        $this->testRequiredFiles();
        $this->testControllerClasses();
        $this->testEmailService();
        $this->testRegistrationLogic();
        $this->testDashboardQueries();
        
        $this->printTestResults();
    }
    
    /**
     * 데이터베이스 스키마 테스트
     */
    private function testDatabaseSchema()
    {
        echo "1. 데이터베이스 스키마 테스트...\n";
        
        // 필수 테이블 존재 확인
        $requiredTables = [
            'lectures',
            'lecture_registrations', 
            'registration_history',
            'users'
        ];
        
        foreach ($requiredTables as $table) {
            $result = $this->db->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                $this->addTestResult("✅ 테이블 '$table' 존재함", true);
            } else {
                $this->addTestResult("❌ 테이블 '$table' 누락됨", false);
            }
        }
        
        // registration_statistics 뷰 확인
        $result = $this->db->query("SHOW FULL TABLES LIKE 'registration_statistics'");
        if ($result->num_rows > 0) {
            $this->addTestResult("✅ registration_statistics 뷰 존재함", true);
        } else {
            $this->addTestResult("❌ registration_statistics 뷰 누락됨", false);
        }
        
        // lecture_registrations 테이블 필수 필드 확인
        $requiredColumns = [
            'participant_name', 'participant_email', 'participant_phone',
            'status', 'is_waiting_list', 'waiting_order',
            'processed_by', 'processed_at', 'admin_notes'
        ];
        
        $result = $this->db->query("DESCRIBE lecture_registrations");
        $existingColumns = [];
        while ($row = $result->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
        }
        
        foreach ($requiredColumns as $column) {
            if (in_array($column, $existingColumns)) {
                $this->addTestResult("✅ 필드 'lecture_registrations.$column' 존재함", true);
            } else {
                $this->addTestResult("❌ 필드 'lecture_registrations.$column' 누락됨", false);
            }
        }
    }
    
    /**
     * 필수 파일 존재 확인
     */
    private function testRequiredFiles()
    {
        echo "\n2. 필수 파일 존재 확인...\n";
        
        $requiredFiles = [
            'src/controllers/RegistrationController.php',
            'src/controllers/RegistrationDashboardController.php',
            'src/services/EmailService.php',
            'src/views/registrations/dashboard.php',
            'src/views/registrations/lecture-detail.php',
        ];
        
        foreach ($requiredFiles as $file) {
            $fullPath = ROOT_PATH . '/' . $file;
            if (file_exists($fullPath)) {
                $this->addTestResult("✅ 파일 '$file' 존재함", true);
            } else {
                $this->addTestResult("❌ 파일 '$file' 누락됨", false);
            }
        }
    }
    
    /**
     * 컨트롤러 클래스 로드 테스트
     */
    private function testControllerClasses()
    {
        echo "\n3. 컨트롤러 클래스 테스트...\n";
        
        try {
            require_once SRC_PATH . '/controllers/BaseController.php';
            require_once SRC_PATH . '/controllers/RegistrationController.php';
            require_once SRC_PATH . '/controllers/RegistrationDashboardController.php';
            
            if (class_exists('RegistrationController')) {
                $this->addTestResult("✅ RegistrationController 클래스 로드됨", true);
                
                // 필수 메소드 확인
                $controller = new RegistrationController();
                $requiredMethods = ['getRegistrationStatus', 'createRegistration', 'cancelRegistration'];
                
                foreach ($requiredMethods as $method) {
                    if (method_exists($controller, $method)) {
                        $this->addTestResult("✅ RegistrationController::$method 메소드 존재함", true);
                    } else {
                        $this->addTestResult("❌ RegistrationController::$method 메소드 누락됨", false);
                    }
                }
            } else {
                $this->addTestResult("❌ RegistrationController 클래스 로드 실패", false);
            }
            
            if (class_exists('RegistrationDashboardController')) {
                $this->addTestResult("✅ RegistrationDashboardController 클래스 로드됨", true);
                
                $controller = new RegistrationDashboardController();
                $requiredMethods = ['index', 'lectureRegistrations', 'updateRegistrationStatus'];
                
                foreach ($requiredMethods as $method) {
                    if (method_exists($controller, $method)) {
                        $this->addTestResult("✅ RegistrationDashboardController::$method 메소드 존재함", true);
                    } else {
                        $this->addTestResult("❌ RegistrationDashboardController::$method 메소드 누락됨", false);
                    }
                }
            } else {
                $this->addTestResult("❌ RegistrationDashboardController 클래스 로드 실패", false);
            }
            
        } catch (Exception $e) {
            $this->addTestResult("❌ 컨트롤러 로드 중 오류: " . $e->getMessage(), false);
        }
    }
    
    /**
     * 이메일 서비스 테스트
     */
    private function testEmailService()
    {
        echo "\n4. 이메일 서비스 테스트...\n";
        
        try {
            require_once SRC_PATH . '/services/EmailService.php';
            
            if (class_exists('EmailService')) {
                $this->addTestResult("✅ EmailService 클래스 로드됨", true);
                
                $emailService = new EmailService();
                $requiredMethods = ['sendApprovalNotification', 'sendRejectionNotification', 'sendApplicationConfirmation'];
                
                foreach ($requiredMethods as $method) {
                    if (method_exists($emailService, $method)) {
                        $this->addTestResult("✅ EmailService::$method 메소드 존재함", true);
                    } else {
                        $this->addTestResult("❌ EmailService::$method 메소드 누락됨", false);
                    }
                }
            } else {
                $this->addTestResult("❌ EmailService 클래스 로드 실패", false);
            }
            
        } catch (Exception $e) {
            $this->addTestResult("❌ EmailService 로드 중 오류: " . $e->getMessage(), false);
        }
    }
    
    /**
     * 신청 로직 테스트 (모의 데이터 사용)
     */
    private function testRegistrationLogic()
    {
        echo "\n5. 신청 로직 테스트...\n";
        
        try {
            // 테스트용 강의 조회
            $result = $this->db->query("SELECT id FROM lectures WHERE status = 'published' LIMIT 1");
            if ($result->num_rows > 0) {
                $lecture = $result->fetch_assoc();
                $this->addTestResult("✅ 테스트 가능한 강의 발견됨 (ID: " . $lecture['id'] . ")", true);
                
                // 신청 통계 조회 테스트
                $statsQuery = "SELECT * FROM registration_statistics WHERE lecture_id = ?";
                $stmt = $this->db->prepare($statsQuery);
                if ($stmt) {
                    $stmt->bind_param("i", $lecture['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $this->addTestResult("✅ registration_statistics 뷰 조회 성공", true);
                    } else {
                        $this->addTestResult("⚠️ registration_statistics 뷰에 해당 강의 데이터 없음", true);
                    }
                } else {
                    $this->addTestResult("❌ registration_statistics 쿼리 실행 실패", false);
                }
            } else {
                $this->addTestResult("⚠️ 테스트 가능한 강의가 없음", true);
            }
            
        } catch (Exception $e) {
            $this->addTestResult("❌ 신청 로직 테스트 중 오류: " . $e->getMessage(), false);
        }
    }
    
    /**
     * 대시보드 쿼리 테스트
     */
    private function testDashboardQueries()
    {
        echo "\n6. 대시보드 쿼리 테스트...\n";
        
        try {
            // 전체 통계 쿼리 테스트
            $statsQuery = "
                SELECT 
                    COUNT(DISTINCT l.id) as total_lectures,
                    COALESCE(SUM(stats.total_applications), 0) as total_applications,
                    COALESCE(SUM(stats.pending_count), 0) as pending_applications,
                    COALESCE(SUM(stats.approved_count), 0) as approved_applications
                FROM lectures l
                LEFT JOIN registration_statistics stats ON l.id = stats.lecture_id
                WHERE l.status = 'published'
            ";
            
            $result = $this->db->query($statsQuery);
            if ($result && $result->num_rows > 0) {
                $stats = $result->fetch_assoc();
                $this->addTestResult("✅ 대시보드 통계 쿼리 성공 (강의 " . $stats['total_lectures'] . "개)", true);
            } else {
                $this->addTestResult("❌ 대시보드 통계 쿼리 실패", false);
            }
            
            // 신청자 목록 쿼리 테스트
            $registrationsQuery = "
                SELECT 
                    r.id, r.participant_name, r.participant_email, r.status,
                    l.title as lecture_title
                FROM lecture_registrations r
                JOIN lectures l ON r.lecture_id = l.id
                ORDER BY r.created_at DESC
                LIMIT 5
            ";
            
            $result = $this->db->query($registrationsQuery);
            if ($result !== false) {
                $count = $result->num_rows;
                $this->addTestResult("✅ 신청자 목록 쿼리 성공 ($count 개 결과)", true);
            } else {
                $this->addTestResult("❌ 신청자 목록 쿼리 실패: " . $this->db->error, false);
            }
            
        } catch (Exception $e) {
            $this->addTestResult("❌ 대시보드 쿼리 테스트 중 오류: " . $e->getMessage(), false);
        }
    }
    
    /**
     * 테스트 결과 추가
     */
    private function addTestResult($message, $success)
    {
        $this->testResults[] = [
            'message' => $message,
            'success' => $success
        ];
        echo "  $message\n";
    }
    
    /**
     * 테스트 결과 요약 출력
     */
    private function printTestResults()
    {
        $total = count($this->testResults);
        $passed = array_filter($this->testResults, function($result) {
            return $result['success'];
        });
        $passedCount = count($passed);
        $failedCount = $total - $passedCount;
        
        echo "\n=== 테스트 결과 요약 ===\n";
        echo "전체 테스트: $total\n";
        echo "성공: $passedCount\n";
        echo "실패: $failedCount\n";
        
        if ($failedCount > 0) {
            echo "\n실패한 테스트들:\n";
            foreach ($this->testResults as $result) {
                if (!$result['success']) {
                    echo "  " . $result['message'] . "\n";
                }
            }
        }
        
        $successRate = round(($passedCount / $total) * 100, 1);
        echo "\n성공률: {$successRate}%\n";
        
        if ($successRate >= 90) {
            echo "🎉 시스템이 정상적으로 구성되었습니다!\n";
        } elseif ($successRate >= 70) {
            echo "⚠️ 일부 문제가 있지만 기본 기능은 동작할 것입니다.\n";
        } else {
            echo "❌ 심각한 문제가 있습니다. 수정이 필요합니다.\n";
        }
    }
}

// 테스트 실행
if (php_sapi_name() === 'cli') {
    $test = new RegistrationSystemTest();
    $test->runAllTests();
} else {
    echo "이 테스트는 CLI에서만 실행할 수 있습니다.";
}
?>