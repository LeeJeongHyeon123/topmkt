<?php
/**
 * 클라이언트 사이드 업로드 설정
 * PHP 공통 설정을 JavaScript로 전달
 */

require_once dirname(dirname(__DIR__)) . '/config/upload.php';
?>
<script>
// 탑마케팅 업로드 설정 (공통)
window.TOPMKT_UPLOAD_CONFIG = <?= UploadConfig::getJavaScriptConfig() ?>;

// 편의 함수들
window.validateFileSize = function(fileSize) {
    return fileSize > 0 && fileSize <= window.TOPMKT_UPLOAD_CONFIG.maxFileSize;
};

window.formatFileSize = function(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

window.getFileSizeErrorMessage = function() {
    return window.TOPMKT_UPLOAD_CONFIG.errorMessages.file_too_large;
};

window.validateImageExtension = function(fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    return window.TOPMKT_UPLOAD_CONFIG.allowedImageExtensions.includes(extension);
};

// 디버깅용 정보 출력
console.log('🚀 TOPMKT 업로드 설정 로드됨:', {
    maxFileSize: window.formatFileSize(window.TOPMKT_UPLOAD_CONFIG.maxFileSize),
    maxFileSizeMB: window.TOPMKT_UPLOAD_CONFIG.maxFileSizeMB + 'MB',
    allowedExtensions: window.TOPMKT_UPLOAD_CONFIG.allowedImageExtensions
});
</script>