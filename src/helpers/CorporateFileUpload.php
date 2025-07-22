<?php
/**
 * CorporateFileUpload 클래스
 * 기업 인증 관련 파일 업로드 보안 처리
 */

require_once SRC_PATH . '/config/upload.php';

class CorporateFileUpload {
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    // MAX_SIZE는 UploadConfig::getMaxFileSize()를 사용 (30MB)
    private const UPLOAD_PATH = '/assets/uploads/corp_docs/';
    
    /**
     * 사업자등록증 파일 업로드
     */
    public static function uploadBusinessRegistration($file, $userId) {
        try {
            // 파일 검증
            $validation = self::validateFile($file);
            if (!$validation['success']) {
                throw new Exception($validation['message']);
            }
            
            // 업로드 디렉토리 확인 및 생성
            $uploadDir = PUBLIC_PATH . self::UPLOAD_PATH;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // 안전한 파일명 생성
            $filename = self::generateSecureFileName($file, $userId);
            $targetPath = $uploadDir . $filename;
            
            // 파일 이동
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('파일 저장에 실패했습니다.');
            }
            
            // 파일 권한 설정
            chmod($targetPath, 0644);
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $targetPath
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 파일 검증
     */
    private static function validateFile($file) {
        // 업로드 오류 확인
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => '파일이 너무 큽니다.',
                UPLOAD_ERR_FORM_SIZE => '파일이 너무 큽니다.',
                UPLOAD_ERR_PARTIAL => '파일이 부분적으로만 업로드되었습니다.',
                UPLOAD_ERR_NO_FILE => '파일이 업로드되지 않았습니다.',
                UPLOAD_ERR_NO_TMP_DIR => '임시 디렉토리가 없습니다.',
                UPLOAD_ERR_CANT_WRITE => '파일 쓰기에 실패했습니다.',
                UPLOAD_ERR_EXTENSION => '파일 업로드가 확장에 의해 중단되었습니다.'
            ];
            
            $message = $errorMessages[$file['error']] ?? '파일 업로드 중 알 수 없는 오류가 발생했습니다.';
            return ['success' => false, 'message' => $message];
        }
        
        // 파일 크기 검증
        if (!UploadConfig::validateFileSize($file['size'])) {
            return ['success' => false, 'message' => UploadConfig::getErrorMessage('file_too_large')];
        }
        
        if ($file['size'] <= 0) {
            return ['success' => false, 'message' => '유효하지 않은 파일입니다.'];
        }
        
        // MIME 타입 검증
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            return ['success' => false, 'message' => 'JPG, PNG, WebP, PDF 파일만 업로드 가능합니다.'];
        }
        
        // 파일 확장자 검증
        $extension = self::getFileExtension($file['name']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'message' => 'JPG, PNG, WebP, PDF 파일만 업로드 가능합니다.'];
        }
        
        // 파일 내용 검증 (기본적인 헤더 확인)
        if (!self::validateFileContent($file['tmp_name'], $mimeType)) {
            return ['success' => false, 'message' => '유효하지 않은 파일 형식입니다.'];
        }
        
        return ['success' => true];
    }
    
    /**
     * 파일 내용 검증
     */
    private static function validateFileContent($filePath, $mimeType) {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 16);
        fclose($handle);
        
        // 파일 시그니처 검증
        $signatures = [
            'image/jpeg' => [
                "\xFF\xD8\xFF", // JPEG
            ],
            'image/png' => [
                "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A", // PNG
            ],
            'image/webp' => [
                "RIFF", // WebP
            ],
            'application/pdf' => [
                "%PDF-", // PDF
            ]
        ];
        
        if (!isset($signatures[$mimeType])) {
            return false;
        }
        
        foreach ($signatures[$mimeType] as $signature) {
            if (strpos($header, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 안전한 파일명 생성
     */
    private static function generateSecureFileName($file, $userId) {
        $extension = self::getFileExtension($file['name']);
        $timestamp = date('Ymd_His');
        $randomString = bin2hex(random_bytes(8));
        
        return sprintf(
            'business_reg_%d_%s_%s.%s',
            $userId,
            $timestamp,
            $randomString,
            $extension
        );
    }
    
    /**
     * 파일 확장자 추출
     */
    private static function getFileExtension($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // 확장자 정규화
        if ($extension === 'jpeg') {
            return 'jpg';
        }
        
        return $extension;
    }
    
    /**
     * 업로드된 파일 삭제
     */
    public static function deleteFile($filename) {
        if (empty($filename)) {
            return false;
        }
        
        $filePath = PUBLIC_PATH . self::UPLOAD_PATH . $filename;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
    
    /**
     * 파일 존재 확인
     */
    public static function fileExists($filename) {
        if (empty($filename)) {
            return false;
        }
        
        $filePath = PUBLIC_PATH . self::UPLOAD_PATH . $filename;
        return file_exists($filePath);
    }
    
    /**
     * 파일 URL 생성
     */
    public static function getFileUrl($filename) {
        if (empty($filename) || !self::fileExists($filename)) {
            return null;
        }
        
        return self::UPLOAD_PATH . $filename;
    }
    
    /**
     * 파일 크기를 사람이 읽기 쉬운 형태로 변환
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}