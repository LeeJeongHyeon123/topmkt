<?php
/**
 * 탑마케팅 프로젝트 업로드 설정 통합 관리
 * 
 * 모든 이미지 업로드 기능에서 사용하는 공통 설정
 * 용량 제한, 허용 확장자, 업로드 경로 등을 중앙에서 관리
 */

class UploadConfig {
    
    /**
     * 파일 업로드 용량 제한 (30MB)
     * 2025-07-14: 5MB에서 30MB로 확대
     */
    public const MAX_FILE_SIZE = 30 * 1024 * 1024; // 30MB
    
    /**
     * 허용되는 이미지 확장자
     */
    public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    /**
     * 허용되는 문서 확장자 (기업 인증용)
     */
    public const ALLOWED_DOCUMENT_EXTENSIONS = ['pdf'];
    
    /**
     * 모든 허용 확장자 (이미지 + 문서)
     */
    public const ALLOWED_ALL_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    
    /**
     * 허용되는 MIME 타입 (이미지)
     */
    public const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    /**
     * 허용되는 MIME 타입 (문서)
     */
    public const ALLOWED_DOCUMENT_MIME_TYPES = [
        'application/pdf'
    ];
    
    /**
     * 모든 허용 MIME 타입 (이미지 + 문서)
     */
    public const ALLOWED_ALL_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png', 
        'image/gif',
        'image/webp',
        'application/pdf'
    ];
    
    /**
     * 업로드 기본 경로
     */
    public const BASE_UPLOAD_PATH = '/assets/uploads';
    
    /**
     * 업로드 하위 경로들
     */
    public const UPLOAD_PATHS = [
        'events' => '/assets/uploads/events',
        'lectures' => '/assets/uploads/lectures', 
        'users' => '/assets/uploads/users',
        'corp_docs' => '/assets/uploads/corp_docs',
        'rich_text' => '/assets/uploads/rich_text',
        'community' => '/assets/uploads/community'
    ];
    
    /**
     * 업로드 에러 메시지
     */
    public const ERROR_MESSAGES = [
        'file_too_large' => '파일 크기는 30MB를 초과할 수 없습니다.',
        'invalid_extension' => '허용되지 않는 파일 형식입니다.',
        'invalid_mime_type' => '파일 형식이 올바르지 않습니다.',
        'upload_failed' => '파일 업로드에 실패했습니다.',
        'no_file' => '업로드할 파일이 선택되지 않았습니다.',
        'directory_creation_failed' => '업로드 디렉토리 생성에 실패했습니다.'
    ];
    
    /**
     * 파일 크기를 바이트 단위로 반환
     * 
     * @return int 최대 파일 크기 (바이트)
     */
    public static function getMaxFileSize(): int {
        return self::MAX_FILE_SIZE;
    }
    
    /**
     * 파일 크기를 MB 단위로 반환
     * 
     * @return int 최대 파일 크기 (MB)
     */
    public static function getMaxFileSizeMB(): int {
        return self::MAX_FILE_SIZE / (1024 * 1024);
    }
    
    /**
     * 파일 크기 검증
     * 
     * @param int $fileSize 파일 크기 (바이트)
     * @return bool 유효성 여부
     */
    public static function validateFileSize(int $fileSize): bool {
        return $fileSize > 0 && $fileSize <= self::MAX_FILE_SIZE;
    }
    
    /**
     * 파일 확장자 검증 (이미지만)
     * 
     * @param string $extension 파일 확장자
     * @return bool 유효성 여부
     */
    public static function validateImageExtension(string $extension): bool {
        return in_array(strtolower($extension), self::ALLOWED_IMAGE_EXTENSIONS);
    }
    
    /**
     * 파일 확장자 검증 (모든 타입)
     * 
     * @param string $extension 파일 확장자
     * @return bool 유효성 여부
     */
    public static function validateExtension(string $extension): bool {
        return in_array(strtolower($extension), self::ALLOWED_ALL_EXTENSIONS);
    }
    
    /**
     * MIME 타입 검증 (이미지만)
     * 
     * @param string $mimeType MIME 타입
     * @return bool 유효성 여부
     */
    public static function validateImageMimeType(string $mimeType): bool {
        return in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES);
    }
    
    /**
     * MIME 타입 검증 (모든 타입)
     * 
     * @param string $mimeType MIME 타입
     * @return bool 유효성 여부
     */
    public static function validateMimeType(string $mimeType): bool {
        return in_array($mimeType, self::ALLOWED_ALL_MIME_TYPES);
    }
    
    /**
     * 업로드 경로 가져오기
     * 
     * @param string $type 업로드 타입
     * @return string 업로드 경로
     */
    public static function getUploadPath(string $type): string {
        return self::UPLOAD_PATHS[$type] ?? self::BASE_UPLOAD_PATH;
    }
    
    /**
     * 에러 메시지 가져오기
     * 
     * @param string $errorType 에러 타입
     * @return string 에러 메시지
     */
    public static function getErrorMessage(string $errorType): string {
        return self::ERROR_MESSAGES[$errorType] ?? '알 수 없는 오류가 발생했습니다.';
    }
    
    /**
     * 파일 전체 검증 (이미지)
     * 
     * @param array $file $_FILES 배열의 파일 정보
     * @return array ['success' => bool, 'message' => string]
     */
    public static function validateImageFile(array $file): array {
        // 파일 존재 여부 확인
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => self::getErrorMessage('no_file')];
        }
        
        // 파일 크기 검증
        if (!self::validateFileSize($file['size'])) {
            return ['success' => false, 'message' => self::getErrorMessage('file_too_large')];
        }
        
        // 확장자 검증
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!self::validateImageExtension($extension)) {
            return ['success' => false, 'message' => self::getErrorMessage('invalid_extension')];
        }
        
        // MIME 타입 검증
        if (!self::validateImageMimeType($file['type'])) {
            return ['success' => false, 'message' => self::getErrorMessage('invalid_mime_type')];
        }
        
        return ['success' => true, 'message' => 'OK'];
    }
    
    /**
     * 파일 전체 검증 (모든 타입)
     * 
     * @param array $file $_FILES 배열의 파일 정보
     * @return array ['success' => bool, 'message' => string]
     */
    public static function validateFile(array $file): array {
        // 파일 존재 여부 확인
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => self::getErrorMessage('no_file')];
        }
        
        // 파일 크기 검증
        if (!self::validateFileSize($file['size'])) {
            return ['success' => false, 'message' => self::getErrorMessage('file_too_large')];
        }
        
        // 확장자 검증
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!self::validateExtension($extension)) {
            return ['success' => false, 'message' => self::getErrorMessage('invalid_extension')];
        }
        
        // MIME 타입 검증
        if (!self::validateMimeType($file['type'])) {
            return ['success' => false, 'message' => self::getErrorMessage('invalid_mime_type')];
        }
        
        return ['success' => true, 'message' => 'OK'];
    }
    
    /**
     * JavaScript에서 사용할 설정 정보 JSON 반환
     * 
     * @return string JSON 형태의 설정 정보
     */
    public static function getJavaScriptConfig(): string {
        return json_encode([
            'maxFileSize' => self::MAX_FILE_SIZE,
            'maxFileSizeMB' => self::getMaxFileSizeMB(),
            'allowedImageExtensions' => self::ALLOWED_IMAGE_EXTENSIONS,
            'allowedAllExtensions' => self::ALLOWED_ALL_EXTENSIONS,
            'errorMessages' => self::ERROR_MESSAGES
        ]);
    }
}

// 전역 상수로도 사용할 수 있도록 정의
define('MAX_UPLOAD_SIZE', UploadConfig::MAX_FILE_SIZE);
define('MAX_UPLOAD_SIZE_MB', UploadConfig::getMaxFileSizeMB());
?>