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
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return $this->jsonResponse(false, '로그인이 필요합니다.', null, 401);
            }
            
            // CSRF 토큰 검증
            if (!$this->validateCsrfToken()) {
                return $this->jsonResponse(false, 'CSRF 토큰이 유효하지 않습니다.', null, 403);
            }
            
            // 파일 업로드 확인
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse(false, '파일 업로드에 실패했습니다.', null, 400);
            }
            
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
            
            // 파일 이동
            if (!move_uploaded_file($uploadedFile['tmp_name'], $fullPath)) {
                error_log("파일 이동 실패: {$uploadedFile['tmp_name']} -> $fullPath");
                return $this->jsonResponse(false, '파일 저장에 실패했습니다.', null, 500);
            }
            
            // 업로드된 파일 권한 설정
            chmod($fullPath, 0644);
            error_log("파일 업로드 성공: $fullPath");
            
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
     * CSRF 토큰 검증
     */
    private function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
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
        
        // 디버깅을 위한 로깅
        error_log("MediaController uploadType 디버깅:");
        error_log("- POST upload_type: " . ($uploadType ?: 'NULL'));
        error_log("- HTTP_REFERER: " . $referer);
        
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