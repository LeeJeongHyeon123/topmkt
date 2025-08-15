<?php
/**
 * 미디어 파일 업로드 컨트롤러
 * 리치 텍스트 에디터 이미지 업로드 처리
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/config/upload.php';

class MediaController {
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize; // 공통 설정에서 가져옴
    private $uploadBasePath;
    
    public function __construct() {
        $this->uploadBasePath = ROOT_PATH . '/public/assets/uploads';
        $this->maxFileSize = UploadConfig::getMaxFileSize(); // 공통 설정에서 가져옴 (30MB)
    }
    
    /**
     * 이미지 업로드 처리
     */
    public function uploadImage() {
        try {
            // 🚀 Ultra Think v3.13.0: 간소화된 로깅 (운영 환경용)
            error_log("[이미지업로드] 업로드 요청 - 사용자ID: " . ($_SESSION['user_id'] ?? 'N/A'));
            
            // 상세 디버깅은 개발 환경에서만 활성화
            if (defined('UPLOAD_DEBUG') && UPLOAD_DEBUG) {
                $logFile = ROOT_PATH . '/upload_debug.log';
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 업로드 요청 - 사용자ID: " . ($_SESSION['user_id'] ?? 'N/A') . "\n", FILE_APPEND);
            }
            
            // 🚀 Ultra Think v3.14.0: 향상된 인증 로직 (JWT + 세션 하이브리드)
            $isAuthenticated = false;
            $currentUserId = null;
            
            // 1차: JWT 토큰 기반 인증 시도
            if (AuthMiddleware::isLoggedIn()) {
                $isAuthenticated = true;
                $currentUserId = AuthMiddleware::getCurrentUserId();
                error_log("✅ JWT 인증 성공 - 사용자ID: $currentUserId");
            } else {
                error_log("⚠️ JWT 인증 실패, 세션 기반 인증 시도");
                
                // 2차: 세션 기반 인증 시도 (하위 호환성)
                session_start();
                if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
                    $isAuthenticated = true;
                    $currentUserId = $_SESSION['user_id'];
                    error_log("✅ 세션 인증 성공 - 사용자ID: $currentUserId");
                } else {
                    error_log("❌ 세션에 user_id 없음");
                    
                    // 3차: 디버깅 정보 수집
                    error_log("🔍 인증 디버깅 정보:");
                    error_log("- JWT 토큰 (access_token): " . (isset($_COOKIE['access_token']) ? 'EXISTS' : 'NOT_EXISTS'));
                    error_log("- JWT 토큰 (refresh_token): " . (isset($_COOKIE['refresh_token']) ? 'EXISTS' : 'NOT_EXISTS'));
                    error_log("- 세션 ID: " . (session_id() ?: 'NO_SESSION'));
                    error_log("- 세션 user_id: " . ($_SESSION['user_id'] ?? 'NOT_SET'));
                    error_log("- 세션 user: " . (isset($_SESSION['user']) ? 'EXISTS' : 'NOT_EXISTS'));
                    
                    if (isset($_SESSION['user'])) {
                        error_log("- 세션 user 내용: " . json_encode($_SESSION['user'], JSON_UNESCAPED_UNICODE));
                    }
                }
            }
            
            if (!$isAuthenticated) {
                error_log("❌ 모든 인증 방식 실패");
                return $this->jsonResponse(false, '로그인이 필요합니다. 페이지를 새로고침 후 다시 시도해주세요.', null, 401);
            }
            
            // 사용자 ID를 세션에 저장 (호환성)
            $_SESSION['user_id'] = $currentUserId;
            error_log("✅ 인증 완료 - 사용자ID: $currentUserId");
            
            // 🚀 Ultra Think v3.14.0: 향상된 CSRF 토큰 검증
            if (!$this->validateCsrfToken()) {
                error_log("❌ CSRF 토큰 검증 실패");
                error_log("- POST csrf_token: " . ($_POST['csrf_token'] ?? 'NOT_SET'));
                error_log("- SESSION csrf_token: " . ($_SESSION['csrf_token'] ?? 'NOT_SET'));
                return $this->jsonResponse(false, 'CSRF 토큰이 유효하지 않습니다. 페이지를 새로고침 후 다시 시도해주세요.', null, 403);
            }
            error_log("✅ CSRF 토큰 검증 완료");
            
            // 파일 업로드 확인
            error_log("🔍 $_FILES 상태 확인:");
            error_log("- \$_FILES 존재: " . (isset($_FILES) ? 'YES' : 'NO'));
            error_log("- \$_FILES['image'] 존재: " . (isset($_FILES['image']) ? 'YES' : 'NO'));
            
            if (isset($_FILES['image'])) {
                error_log("- 파일명: " . ($_FILES['image']['name'] ?? 'N/A'));
                error_log("- 파일 크기: " . ($_FILES['image']['size'] ?? 'N/A'));
                error_log("- 파일 타입: " . ($_FILES['image']['type'] ?? 'N/A'));
                error_log("- 에러 코드: " . ($_FILES['image']['error'] ?? 'N/A'));
                error_log("- 임시 경로: " . ($_FILES['image']['tmp_name'] ?? 'N/A'));
            }
            
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                error_log("❌ 파일 업로드 확인 실패");
                if (isset($_FILES['image']['error'])) {
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'upload_max_filesize 초과',
                        UPLOAD_ERR_FORM_SIZE => 'MAX_FILE_SIZE 초과',
                        UPLOAD_ERR_PARTIAL => '일부만 업로드됨',
                        UPLOAD_ERR_NO_FILE => '파일이 업로드되지 않음',
                        UPLOAD_ERR_NO_TMP_DIR => '임시 디렉토리 없음',
                        UPLOAD_ERR_CANT_WRITE => '디스크 쓰기 실패',
                        UPLOAD_ERR_EXTENSION => 'PHP 확장에 의해 중단됨'
                    ];
                    $errorCode = $_FILES['image']['error'];
                    error_log("❌ 업로드 오류 코드 $errorCode: " . ($errorMessages[$errorCode] ?? '알 수 없는 오류'));
                }
                return $this->jsonResponse(false, '파일 업로드에 실패했습니다.', null, 400);
            }
            error_log("✅ 파일 업로드 확인 완료");
            
            $uploadedFile = $_FILES['image'];
            
            // 파일 크기 검증
            if ($uploadedFile['size'] > $this->maxFileSize) {
                $maxSizeMB = $this->maxFileSize / (1024 * 1024);
                return $this->jsonResponse(false, "파일 크기는 {$maxSizeMB}MB를 초과할 수 없습니다.", null, 400);
            }
            
            // 파일 확장자 검증
            $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedExtensions)) {
                $allowedStr = implode(', ', $this->allowedExtensions);
                return $this->jsonResponse(false, "허용되지 않는 파일 형식입니다. 허용 형식: {$allowedStr}", null, 400);
            }
            
            // MIME 타입 검증
            if (!$this->validateMimeType($uploadedFile['tmp_name'], $extension)) {
                return $this->jsonResponse(false, '파일 형식이 올바르지 않습니다.', null, 400);
            }
            
            // 업로드 경로 생성
            $uploadDir = $this->createUploadDirectory();
            if (!$uploadDir) {
                return $this->jsonResponse(false, '업로드 디렉토리 생성에 실패했습니다.', null, 500);
            }
            
            // 안전한 파일명 생성
            $safeFileName = $this->generateSafeFileName($extension);
            $fullPath = $uploadDir . '/' . $safeFileName;
            
            // 경로 조작 공격 방지
            $realUploadPath = realpath($uploadDir);
            $realTargetPath = realpath(dirname($fullPath)) . '/' . basename($fullPath);
            if (!$realUploadPath || strpos($realTargetPath, $realUploadPath) !== 0) {
                return $this->jsonResponse(false, '잘못된 업로드 경로입니다.', null, 400);
            }
            
            // 파일 이동 (상세 로깅은 디버그 모드에서만)
            if (defined('UPLOAD_DEBUG') && UPLOAD_DEBUG) {
                $debugInfo = [
                    "파일 이동 시도",
                    "임시 파일: {$uploadedFile['tmp_name']}",
                    "목적지: $fullPath",
                    "is_uploaded_file 체크: " . (is_uploaded_file($uploadedFile['tmp_name']) ? 'YES' : 'NO')
                ];
                
                foreach ($debugInfo as $info) {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $info\n", FILE_APPEND);
                }
            }
            
            $moveSuccess = move_uploaded_file($uploadedFile['tmp_name'], $fullPath);
            
            if (!$moveSuccess) {
                // 🚀 Ultra Think v3.13.0: move_uploaded_file 실패 시 대안 방법 자동 적용
                error_log("[이미지업로드] move_uploaded_file 실패, 대안 방법 시도");
                
                if (copy($uploadedFile['tmp_name'], $fullPath)) {
                    // 복사 성공하면 임시 파일 삭제
                    unlink($uploadedFile['tmp_name']);
                    error_log("[이미지업로드] 대안 방법(copy+unlink) 성공");
                    $moveSuccess = true;
                    
                    // 상세 디버깅 로그 (개발 환경에서만)
                    if (defined('UPLOAD_DEBUG') && UPLOAD_DEBUG) {
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 대안 방법 성공: copy() + unlink()\n", FILE_APPEND);
                    }
                } else {
                    error_log("[이미지업로드] 대안 방법도 실패 - 파일: " . basename($uploadedFile['name']));
                    return $this->jsonResponse(false, '파일 저장에 실패했습니다.', null, 500);
                }
            }
            
            // 파일 이동/복사 성공한 경우에만 계속 진행
            if (!$moveSuccess) {
                return $this->jsonResponse(false, '파일 저장에 실패했습니다.', null, 500);
            }
            
            // 업로드된 파일 권한 설정
            chmod($fullPath, 0644);
            error_log("[이미지업로드] 업로드 성공 - 파일: " . basename($safeFileName) . ", 크기: " . number_format($uploadedFile['size']) . "bytes");
            
            // 이미지 최적화 (PNG는 건너뛰기 - 파일 손상 방지)
            if ($extension !== 'png') {
                $this->optimizeImage($fullPath, $extension);
            } else {
                error_log('PNG 파일 최적화 건너뜀 - 원본 파일 유지: ' . $fullPath);
            }
            
            // 웹 접근 URL 생성 
            $webUrl = str_replace(ROOT_PATH . '/public', '', $fullPath);
            
            // 디버깅을 위한 로그
            error_log("이미지 업로드 경로 정보:");
            error_log("ROOT_PATH: " . ROOT_PATH);
            error_log("Full Path: " . $fullPath);
            error_log("Web URL: " . $webUrl);
            
            // 업로드 정보 로깅
            $this->logUpload($safeFileName, $uploadedFile['size'], AuthMiddleware::getCurrentUserId());
            
            return $this->jsonResponse(true, '이미지가 성공적으로 업로드되었습니다.', [
                'url' => $webUrl,
                'filename' => $safeFileName,
                'size' => $uploadedFile['size']
            ]);
            
        } catch (Exception $e) {
            error_log('이미지 업로드 오류: ' . $e->getMessage());
            error_log('오류 스택 트레이스: ' . $e->getTraceAsString());
            return $this->jsonResponse(false, '서버 오류가 발생했습니다: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 🚀 Ultra Think v3.14.0: 향상된 CSRF 토큰 검증
     */
    private function validateCsrfToken() {
        // 세션이 시작되지 않은 경우 시작
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        // 디버깅 로그
        error_log("🔒 CSRF 토큰 검증:");
        error_log("- 요청 토큰 존재: " . (!empty($token) ? 'YES' : 'NO'));
        error_log("- 세션 토큰 존재: " . (!empty($sessionToken) ? 'YES' : 'NO'));
        error_log("- 요청 토큰 (앞 16자): " . substr($token, 0, 16));
        error_log("- 세션 토큰 (앞 16자): " . substr($sessionToken, 0, 16));
        
        // 토큰이 없는 경우 새로 생성 (개발 환경 지원)
        if (empty($sessionToken)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            error_log("⚠️ 세션에 CSRF 토큰이 없어서 새로 생성");
            $sessionToken = $_SESSION['csrf_token'];
        }
        
        // 요청 토큰이 없는 경우
        if (empty($token)) {
            error_log("❌ 요청에 CSRF 토큰이 없음");
            return false;
        }
        
        // 토큰 비교
        $isValid = hash_equals($sessionToken, $token);
        error_log("🔍 토큰 비교 결과: " . ($isValid ? 'VALID' : 'INVALID'));
        
        return $isValid;
    }
    
    /**
     * MIME 타입 검증
     */
    private function validateMimeType($filePath, $extension) {
        $allowedMimes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp']
        ];
        
        if (!isset($allowedMimes[$extension])) {
            return false;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedMimes[$extension]);
    }
    
    /**
     * 업로드 디렉토리 생성
     */
    private function createUploadDirectory() {
        $year = date('Y');
        $month = date('m');
        
        // 업로드 타입을 결정
        $uploadType = $this->determineUploadType();
        $uploadDir = $this->uploadBasePath . "/{$uploadType}/{$year}/{$month}";
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("디렉토리 생성 실패: $uploadDir");
                return false;
            }
            // 생성된 디렉토리 권한 설정 (보안 강화: 0755)
            chmod($uploadDir, 0755);
            error_log("디렉토리 생성 완료: $uploadDir");
        }
        
        return $uploadDir;
    }
    
    /**
     * 업로드 타입 결정
     */
    private function determineUploadType() {
        // POST 데이터나 Referer 헤더에서 컨텍스트 판단
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $uploadType = $_POST['upload_type'] ?? '';
        $isQuillUpload = $_POST['is_quill_upload'] ?? false; // 🚀 Ultra Think v3.13.0: Quill 업로드 구분
        
        // 디버깅을 위한 로깅
        error_log("MediaController uploadType 디버깅:");
        error_log("- POST upload_type: " . ($uploadType ?: 'NULL'));
        error_log("- POST is_quill_upload: " . ($isQuillUpload ? 'TRUE' : 'FALSE'));
        error_log("- HTTP_REFERER: " . $referer);
        
        // 🚀 Ultra Think v3.13.0: Quill 에디터 업로드는 별도 디렉토리 (중복 방지)
        if ($isQuillUpload) {
            if (strpos($referer, '/notices/') !== false) {
                error_log("- 최종 결정: notices-content (Quill 업로드)");
                return 'notices-content';
            } elseif (strpos($referer, '/community/') !== false) {
                error_log("- 최종 결정: posts-content (Quill 업로드)");
                return 'posts-content';
            }
        }
        
        // 명시적으로 upload_type이 전달된 경우
        if (!empty($uploadType)) {
            error_log("- 최종 결정: $uploadType (명시적 파라미터)");
            return $uploadType;
        }
        
        // Referer 헤더로 판단
        if (strpos($referer, '/events/') !== false) {
            error_log("- 최종 결정: events (Referer 기반)");
            return 'events';
        } elseif (strpos($referer, '/lectures/') !== false) {
            error_log("- 최종 결정: lectures (Referer 기반)");
            return 'lectures';
        } elseif (strpos($referer, '/notices/') !== false) {
            error_log("- 최종 결정: notices (Referer 기반)");
            return 'notices';
        } elseif (strpos($referer, '/community/') !== false) {
            error_log("- 최종 결정: posts (Referer 기반)");
            return 'posts';
        }
        
        // 기본값은 posts
        error_log("- 최종 결정: posts (기본값)");
        return 'posts';
    }
    
    /**
     * 안전한 파일명 생성
     */
    private function generateSafeFileName($extension) {
        $timestamp = date('YmdHis');
        $randomString = bin2hex(random_bytes(8));
        return "{$timestamp}_{$randomString}.{$extension}";
    }
    
    /**
     * 이미지 최적화
     */
    private function optimizeImage($filePath, $extension) {
        // EXIF 데이터 제거 및 기본 최적화
        try {
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($filePath);
                    if ($image !== false) {
                        imagejpeg($image, $filePath, 85); // 85% 품질
                        imagedestroy($image);
                    }
                    break;
                    
                case 'png':
                    // PNG 최적화는 파일 손상 위험이 있으므로 보수적으로 처리
                    $image = imagecreatefrompng($filePath);
                    if ($image !== false) {
                        // 투명도 보존
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                        
                        // 낮은 압축 레벨 사용 (0-9, 낮을수록 큰 파일)
                        imagepng($image, $filePath, 3); // 압축 레벨 3 (보수적)
                        imagedestroy($image);
                        error_log('PNG 파일 최적화 완료: ' . $filePath);
                    } else {
                        error_log('PNG 파일 읽기 실패, 원본 유지: ' . $filePath);
                    }
                    break;
            }
        } catch (Exception $e) {
            error_log('이미지 최적화 오류: ' . $e->getMessage());
            // 최적화 실패는 치명적이지 않으므로 계속 진행
        }
    }
    
    /**
     * 업로드 로깅
     */
    private function logUpload($filename, $fileSize, $userId) {
        try {
            $db = Database::getInstance();
            
            $description = "이미지 업로드: {$filename}";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $extraData = json_encode([
                'filename' => $filename,
                'file_size' => $fileSize,
                'upload_type' => 'rich_text_editor'
            ]);
            
            $db->execute("
                INSERT INTO user_logs (user_id, action, description, ip_address, user_agent, extra_data, created_at) 
                VALUES (?, 'IMAGE_UPLOAD', ?, ?, ?, ?, NOW())
            ", [$userId, $description, $ipAddress, $userAgent, $extraData]);
        } catch (Exception $e) {
            error_log('업로드 로깅 오류: ' . $e->getMessage());
            // 로깅 실패해도 업로드는 성공으로 처리
        }
    }
    
    /**
     * JSON 응답 헬퍼
     */
    private function jsonResponse($success, $message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}