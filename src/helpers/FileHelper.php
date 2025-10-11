<?php
/**
 * FileHelper 클래스
 * 파일 업로드 및 처리 관련 공통 기능을 제공합니다.
 */

require_once SRC_PATH . '/config/upload.php';

class FileHelper {

    /**
     * 파일 업로드 처리
     */
    public static function handleFileUpload($file, $uploadDir, $allowedTypes = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // 파일 타입 검증
        if ($allowedTypes && !in_array($fileExt, $allowedTypes)) {
            return false;
        }

        // 파일 크기 검증 (UploadConfig 사용)
        if (!UploadConfig::validateFileSize($fileSize)) {
            return false;
        }

        // 고유한 파일명 생성
        $newFileName = uniqid() . '.' . $fileExt;
        $uploadPath = $uploadDir . '/' . $newFileName;

        // 업로드 디렉토리 생성
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 파일 이동
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            return $newFileName;
        }

        return false;
    }

    /**
     * 이미지 파일 검증 및 업로드
     */
    public static function handleImageUpload($file, $uploadDir) {
        return self::handleFileUpload($file, $uploadDir, UploadConfig::ALLOWED_IMAGE_EXTENSIONS);
    }

    /**
     * 파일 삭제
     */
    public static function deleteFile($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * 파일 존재 확인
     */
    public static function fileExists($filePath) {
        return file_exists($filePath);
    }

    /**
     * 파일 크기 반환
     */
    public static function getFileSize($filePath) {
        if (self::fileExists($filePath)) {
            return filesize($filePath);
        }
        return 0;
    }

    /**
     * 파일 MIME 타입 확인
     */
    public static function getMimeType($filePath) {
        if (self::fileExists($filePath)) {
            return mime_content_type($filePath);
        }
        return false;
    }

    /**
     * 디렉토리 생성
     */
    public static function createDirectory($path, $permissions = 0755) {
        if (!is_dir($path)) {
            return mkdir($path, $permissions, true);
        }
        return true;
    }

    /**
     * 디렉토리 권한 설정
     */
    public static function setDirectoryPermissions($path, $permissions = 0755) {
        if (is_dir($path)) {
            return chmod($path, $permissions);
        }
        return false;
    }
}
?>
