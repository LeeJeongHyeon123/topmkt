<?php
/**
 * 경로 상수 정의
 * 모든 파일 경로는 이 파일에서 중앙 관리됩니다.
 */

// 기본 경로
define('UPLOADS_PATH', ROOT_PATH . '/public/assets/uploads');
define('UPLOADS_WEB_PATH', '/assets/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('CACHE_PATH', ROOT_PATH . '/cache');
define('TEMP_PATH', ROOT_PATH . '/temp');

// 업로드 세부 경로 (서버 파일시스템 경로)
define('PROFILES_UPLOAD_PATH', UPLOADS_PATH . '/profiles');
define('LECTURES_UPLOAD_PATH', UPLOADS_PATH . '/lectures');
define('INSTRUCTORS_UPLOAD_PATH', UPLOADS_PATH . '/instructors');
define('EVENTS_UPLOAD_PATH', UPLOADS_PATH . '/events');
define('GALLERY_UPLOAD_PATH', UPLOADS_PATH . '/gallery');

// 웹 접근 경로 (URL 경로)
define('PROFILES_WEB_PATH', UPLOADS_WEB_PATH . '/profiles');
define('LECTURES_WEB_PATH', UPLOADS_WEB_PATH . '/lectures');
define('INSTRUCTORS_WEB_PATH', UPLOADS_WEB_PATH . '/instructors');
define('EVENTS_WEB_PATH', UPLOADS_WEB_PATH . '/events');
define('GALLERY_WEB_PATH', UPLOADS_WEB_PATH . '/gallery');

// 로그 파일 경로
define('DEBUG_POST_DATA_LOG', LOGS_PATH . '/debug_post_data.log');
define('DEBUG_LECTURE_IMAGES_LOG', LOGS_PATH . '/debug_lecture_images.log');
define('DEBUG_INSTRUCTOR_IMAGES_LOG', LOGS_PATH . '/debug_instructor_images.log');
define('DEBUG_STORE_FLOW_LOG', LOGS_PATH . '/debug_store_flow.log');
define('DEBUG_INSTRUCTOR_VALIDATION_LOG', LOGS_PATH . '/debug_instructor_validation.log');

// 기본 이미지 경로
define('DEFAULT_AVATAR_PATH', '/assets/images/default-avatar.png');
define('DEFAULT_LECTURE_IMAGE_PATH', '/assets/images/default-lecture.png');

/**
 * 디렉토리 생성 함수
 */
function ensureDirectoryExists($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

/**
 * 초기화 시 필요한 디렉토리들 생성
 */
function initializeDirectories() {
    $directories = [
        LOGS_PATH,
        CACHE_PATH,
        TEMP_PATH,
        PROFILES_UPLOAD_PATH,
        LECTURES_UPLOAD_PATH,
        INSTRUCTORS_UPLOAD_PATH,
        EVENTS_UPLOAD_PATH,
        GALLERY_UPLOAD_PATH
    ];
    
    foreach ($directories as $dir) {
        ensureDirectoryExists($dir);
    }
}

// 디렉토리 초기화 실행
initializeDirectories();