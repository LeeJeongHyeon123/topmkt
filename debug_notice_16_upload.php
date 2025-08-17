<?php
/**
 * Ultra Think v3.13.0: 공지사항 #16 이미지 업로드 실패 진단 스크립트
 * 실제 업로드 과정을 단계별로 추적하고 문제점을 정확히 파악
 */

// 경로 상수 정의
define('ROOT_PATH', dirname(__FILE__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once 'src/config/database.php';
require_once 'src/middlewares/AuthMiddleware.php';
require_once 'src/controllers/MediaController.php';
require_once 'src/config/upload.php';

// 🚀 Ultra Think: 체계적 진단을 위한 7단계 분석
function ultraThinkUploadDiagnosis() {
    echo "🚀 Ultra Think: 공지사항 #16 이미지 업로드 실패 진단\n";
    echo "=".str_repeat("=", 70)."\n\n";

    // ✅ 1단계: 공지사항 #16 상태 확인
    echo "📋 1단계: 공지사항 #16 상태 확인\n";
    echo "-".str_repeat("-", 50)."\n";
    
    try {
        $db = Database::getInstance();
        $notice = $db->fetch("SELECT id, title, content, image_path, created_at, updated_at FROM notices WHERE id = 16");
        
        if ($notice) {
            echo "✅ 공지사항 #16 발견\n";
            echo "   제목: " . $notice['title'] . "\n";
            echo "   생성일시: " . $notice['created_at'] . "\n";
            echo "   수정일시: " . $notice['updated_at'] . "\n";
            echo "   이미지 경로: " . ($notice['image_path'] ?: 'NULL') . "\n";
            echo "   내용 길이: " . strlen($notice['content']) . " 문자\n";
            
            // 본문에서 img 태그 검색
            preg_match_all('/<img[^>]*>/i', $notice['content'], $imgTags);
            echo "   본문 내 img 태그 개수: " . count($imgTags[0]) . "\n";
            
            if (!empty($imgTags[0])) {
                echo "   발견된 img 태그들:\n";
                foreach ($imgTags[0] as $i => $tag) {
                    echo "     " . ($i+1) . ". " . htmlspecialchars($tag) . "\n";
                }
            }
        } else {
            echo "❌ 공지사항 #16을 찾을 수 없습니다.\n";
            return false;
        }
    } catch (Exception $e) {
        echo "❌ 데이터베이스 오류: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "\n";

    // ✅ 2단계: 업로드 디렉토리 구조 확인
    echo "📁 2단계: 업로드 디렉토리 구조 확인\n";
    echo "-".str_repeat("-", 50)."\n";
    
    $uploadBasePath = '/var/www/html/topmkt/public/assets/uploads';
    $noticesUploadPath = $uploadBasePath . '/notices';
    
    // 현재 날짜 기준 디렉토리
    $currentYear = date('Y');
    $currentMonth = date('m');
    $expectedPath = $noticesUploadPath . "/{$currentYear}/{$currentMonth}";
    
    echo "   업로드 기본 경로: $uploadBasePath\n";
    echo "   공지사항 업로드 경로: $noticesUploadPath\n";
    echo "   현재 예상 업로드 경로: $expectedPath\n";
    
    $paths = [$uploadBasePath, $noticesUploadPath, $expectedPath];
    foreach ($paths as $path) {
        if (is_dir($path)) {
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            echo "   ✅ $path (권한: $perms)\n";
        } else {
            echo "   ❌ $path (존재하지 않음)\n";
        }
    }
    
    // 8월 업로드 파일들 확인
    $augustPath = $noticesUploadPath . "/2025/08";
    if (is_dir($augustPath)) {
        $files = scandir($augustPath);
        $imageFiles = array_filter($files, function($file) {
            return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
        });
        echo "   📁 2025년 8월 업로드 파일 개수: " . count($imageFiles) . "\n";
        
        if (count($imageFiles) > 0) {
            echo "   최근 업로드 파일 (최대 5개):\n";
            $recentFiles = array_slice(array_reverse($imageFiles), 0, 5);
            foreach ($recentFiles as $file) {
                $filePath = $augustPath . '/' . $file;
                $fileTime = date('Y-m-d H:i:s', filemtime($filePath));
                $fileSize = number_format(filesize($filePath));
                echo "     - $file ($fileSize bytes, $fileTime)\n";
            }
        }
    }
    
    echo "\n";

    // ✅ 3단계: MediaController 설정 확인
    echo "⚙️  3단계: MediaController 설정 확인\n";
    echo "-".str_repeat("-", 50)."\n";
    
    try {
        $maxFileSize = UploadConfig::getMaxFileSize();
        $maxFileSizeMB = $maxFileSize / (1024 * 1024);
        echo "   최대 파일 크기: " . $maxFileSizeMB . "MB (" . number_format($maxFileSize) . " bytes)\n";
        
        $mediaController = new MediaController();
        echo "   ✅ MediaController 초기화 성공\n";
        
        // PHP 업로드 설정 확인
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $maxExecutionTime = ini_get('max_execution_time');
        $memoryLimit = ini_get('memory_limit');
        
        echo "   PHP 설정:\n";
        echo "     upload_max_filesize: $uploadMaxFilesize\n";
        echo "     post_max_size: $postMaxSize\n";
        echo "     max_execution_time: {$maxExecutionTime}초\n";
        echo "     memory_limit: $memoryLimit\n";
        
    } catch (Exception $e) {
        echo "   ❌ MediaController 초기화 실패: " . $e->getMessage() . "\n";
    }
    
    echo "\n";

    // ✅ 4단계: 세션 및 인증 상태 확인
    echo "🔐 4단계: 세션 및 인증 상태 확인\n";
    echo "-".str_repeat("-", 50)."\n";
    
    session_start();
    
    echo "   세션 상태: " . (session_status() === PHP_SESSION_ACTIVE ? '활성' : '비활성') . "\n";
    echo "   세션 ID: " . session_id() . "\n";
    echo "   사용자 로그인 상태: " . (AuthMiddleware::isLoggedIn() ? 'YES' : 'NO') . "\n";
    
    if (AuthMiddleware::isLoggedIn()) {
        $userId = AuthMiddleware::getCurrentUserId();
        $userRole = AuthMiddleware::getCurrentUserRole();
        echo "   현재 사용자 ID: $userId\n";
        echo "   현재 사용자 권한: $userRole\n";
    }
    
    // CSRF 토큰 확인
    if (isset($_SESSION['csrf_token'])) {
        echo "   CSRF 토큰: " . substr($_SESSION['csrf_token'], 0, 10) . "...\n";
    } else {
        echo "   ❌ CSRF 토큰이 설정되지 않음\n";
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        echo "   ✅ 새 CSRF 토큰 생성됨\n";
    }
    
    echo "\n";

    // ✅ 5단계: 업로드 라우팅 확인
    echo "🛣️  5단계: 업로드 라우팅 확인\n";
    echo "-".str_repeat("-", 50)."\n";
    
    $uploadEndpoint = '/api/media/upload-image';
    echo "   업로드 엔드포인트: $uploadEndpoint\n";
    
    // 라우팅 파일 확인
    $routesPath = '/var/www/html/topmkt/src/config/routes.php';
    if (file_exists($routesPath)) {
        $routesContent = file_get_contents($routesPath);
        if (strpos($routesContent, 'upload-image') !== false) {
            echo "   ✅ 업로드 라우팅 설정 발견\n";
        } else {
            echo "   ❌ 업로드 라우팅 설정 없음\n";
        }
    }
    
    echo "\n";

    // ✅ 6단계: 실제 업로드 시뮬레이션
    echo "🧪 6단계: 업로드 시뮬레이션\n";
    echo "-".str_repeat("-", 50)."\n";
    
    // 테스트 이미지 생성
    $testImagePath = '/tmp/test_upload_image.png';
    createTestImage($testImagePath);
    
    if (file_exists($testImagePath)) {
        $fileSize = filesize($testImagePath);
        echo "   ✅ 테스트 이미지 생성됨: " . number_format($fileSize) . " bytes\n";
        
        // $_FILES 구조 시뮬레이션
        $_FILES = [
            'image' => [
                'name' => 'test_notice_16.png',
                'type' => 'image/png',
                'tmp_name' => $testImagePath,
                'error' => UPLOAD_ERR_OK,
                'size' => $fileSize
            ]
        ];
        
        $_POST = [
            'csrf_token' => $_SESSION['csrf_token'],
            'upload_type' => 'notices'
        ];
        
        $_SERVER['HTTP_REFERER'] = 'https://www.topmktx.com/notices/write';
        
        echo "   시뮬레이션 설정 완료\n";
        echo "   파일명: " . $_FILES['image']['name'] . "\n";
        echo "   파일 타입: " . $_FILES['image']['type'] . "\n";
        echo "   파일 크기: " . number_format($_FILES['image']['size']) . " bytes\n";
        
        // MediaController 업로드 테스트
        echo "\n   📤 MediaController 업로드 테스트 실행...\n";
        
        ob_start();
        try {
            $mediaController = new MediaController();
            $mediaController->uploadImage();
            $output = ob_get_clean();
            
            echo "   ✅ 업로드 테스트 완료\n";
            echo "   응답 내용:\n";
            echo "   " . str_replace("\n", "\n   ", $output) . "\n";
            
        } catch (Exception $e) {
            ob_end_clean();
            echo "   ❌ 업로드 테스트 실패: " . $e->getMessage() . "\n";
        }
        
        // 테스트 파일 정리
        unlink($testImagePath);
        
    } else {
        echo "   ❌ 테스트 이미지 생성 실패\n";
    }
    
    echo "\n";

    // ✅ 7단계: 진단 결과 요약
    echo "📊 7단계: 진단 결과 요약\n";
    echo "-".str_repeat("-", 50)."\n";
    
    echo "   🎯 Ultra Think 진단 완료\n";
    echo "   📅 진단 일시: " . date('Y-m-d H:i:s') . "\n";
    echo "   🔍 분석 대상: 공지사항 #16 이미지 업로드 실패\n";
    echo "\n";
    echo "   💡 권장 조치:\n";
    echo "   1. JavaScript 클라이언트 코드 확인\n";
    echo "   2. 실제 업로드 요청 네트워크 추적\n";
    echo "   3. Quill.js 에디터 이미지 업로드 이벤트 검증\n";
    echo "\n";
    
    return true;
}

/**
 * 테스트용 이미지 생성
 */
function createTestImage($filePath) {
    // 1x1 PNG 이미지 생성 (최소 크기)
    $image = imagecreate(100, 100);
    $backgroundColor = imagecolorallocate($image, 255, 255, 255);
    $textColor = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 20, 40, 'TEST', $textColor);
    imagepng($image, $filePath);
    imagedestroy($image);
}

// 메인 실행
if (php_sapi_name() === 'cli') {
    ultraThinkUploadDiagnosis();
} else {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Ultra Think 진단</title>";
    echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;}</style></head><body>";
    echo "<pre>";
    ultraThinkUploadDiagnosis();
    echo "</pre></body></html>";
}