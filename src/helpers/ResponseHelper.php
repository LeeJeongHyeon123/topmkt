<?php
/**
 * 통합 응답 처리 헬퍼
 * 문서: 22.에러처리_및_로깅_표준.md
 */

class ResponseHelper {
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_WARNING = 'warning';
    
    /**
     * 기본 JSON 응답
     */
    public static function json($data = null, $status = 200, $message = '') {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'status' => $status < 400 ? self::STATUS_SUCCESS : self::STATUS_ERROR,
            'data' => $data,
            'message' => $message,
            'timestamp' => date('c'),
            'request_id' => self::getRequestId()
        ];
        
        // API 호출 로그 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::api($_SERVER['REQUEST_URI'] ?? '', $_SERVER['REQUEST_METHOD'] ?? '', null, $status);
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 에러 응답
     */
    public static function error($message, $code = 400, $details = null) {
        $response = [
            'status' => self::STATUS_ERROR,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
                'timestamp' => date('c'),
                'request_id' => self::getRequestId()
            ]
        ];
        
        // 개발 환경에서만 상세 에러 정보 포함
        if (self::isDevelopment()) {
            $backtrace = debug_backtrace();
            $response['error']['debug'] = [
                'file' => $backtrace[0]['file'] ?? null,
                'line' => $backtrace[0]['line'] ?? null,
                'trace' => array_slice($backtrace, 0, 5) // 스택 트레이스 제한
            ];
        }
        
        // 에러 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::error("Response Error: {$message}", [
                'code' => $code,
                'details' => $details,
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? ''
            ]);
        }
        
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 성공 응답
     */
    public static function success($data = null, $message = '성공') {
        // 성공 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::info("Response Success: {$message}", [
                'data_type' => gettype($data),
                'data_size' => is_array($data) ? count($data) : (is_string($data) ? strlen($data) : null)
            ]);
        }
        
        return self::json($data, 200, $message);
    }
    
    /**
     * 데이터 검증 실패 응답
     */
    public static function validationError($errors = [], $message = '입력 데이터를 확인해주세요.') {
        // 검증 오류 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::warning("Validation Error", [
                'errors' => $errors,
                'field_count' => is_array($errors) ? count($errors) : 0
            ]);
        }
        
        return self::error($message, 400, ['validation_errors' => $errors]);
    }
    
    /**
     * 인증 실패 응답
     */
    public static function unauthorized($message = '인증이 필요합니다.') {
        // 인증 실패 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::security('Unauthorized Access Attempt', [
                'message' => $message,
                'ip' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
        
        return self::error($message, 401);
    }
    
    /**
     * 권한 부족 응답
     */
    public static function forbidden($message = '접근 권한이 없습니다.') {
        // 권한 부족 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::security('Forbidden Access Attempt', [
                'message' => $message,
                'user_id' => $_SESSION['user_id'] ?? 'anonymous',
                'ip' => self::getClientIp()
            ]);
        }
        
        return self::error($message, 403);
    }
    
    /**
     * 리소스 없음 응답
     */
    public static function notFound($message = '요청한 리소스를 찾을 수 없습니다.') {
        // 리소스 없음 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::warning("Resource Not Found", [
                'message' => $message,
                'url' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
        }
        
        return self::error($message, 404);
    }
    
    /**
     * 서버 에러 응답
     */
    public static function serverError($message = '서버 오류가 발생했습니다.', $details = null) {
        // 서버 오류 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::error("Server Error", [
                'message' => $message,
                'details' => $details,
                'trace' => debug_backtrace()
            ]);
        }
        
        return self::error($message, 500, $details);
    }
    
    /**
     * 페이지 리다이렉트
     */
    public static function redirect($url, $flash = []) {
        foreach ($flash as $key => $value) {
            $_SESSION[$key] = $value;
        }
        
        // 리다이렉트 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::info("Page Redirect", [
                'redirect_to' => $url,
                'flash_messages' => array_keys($flash)
            ]);
        }
        
        header("Location: $url");
        exit;
    }
    
    /**
     * 404 페이지 렌더링
     */
    public static function render404() {
        http_response_code(404);
        
        // 404 페이지 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::warning("404 Page Rendered", [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'referer' => $_SERVER['HTTP_REFERER'] ?? ''
            ]);
        }
        
        // AJAX 요청인지 확인
        if (self::isAjaxRequest()) {
            self::notFound();
        }
        
        include SRC_PATH . '/views/templates/404.php';
        exit;
    }
    
    /**
     * 403 페이지 렌더링
     */
    public static function render403() {
        http_response_code(403);
        
        // 403 페이지 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::security("403 Page Rendered", [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'user_id' => $_SESSION['user_id'] ?? 'anonymous'
            ]);
        }
        
        // AJAX 요청인지 확인
        if (self::isAjaxRequest()) {
            self::forbidden();
        }
        
        include SRC_PATH . '/views/templates/403.php';
        exit;
    }
    
    /**
     * 500 페이지 렌더링
     */
    public static function render500($error = '') {
        http_response_code(500);
        
        // 500 페이지 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::critical("500 Page Rendered", [
                'error' => $error,
                'url' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
        }
        
        // AJAX 요청인지 확인
        if (self::isAjaxRequest()) {
            self::serverError();
        }
        
        // 디버그 모드에서는 오류 메시지 로깅
        if (self::isDevelopment() && !empty($error)) {
            error_log($error);
        }
        
        include SRC_PATH . '/views/templates/500.php';
        exit;
    }
    
    /**
     * 요청 제한 초과 응답
     */
    public static function rateLimited($message = '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.') {
        // 요청 제한 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::warning("Rate Limit Exceeded", [
                'ip' => self::getClientIp(),
                'user_id' => $_SESSION['user_id'] ?? 'anonymous',
                'url' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
        }
        
        return self::error($message, 429);
    }
    
    /**
     * 메인터넌스 모드 응답
     */
    public static function maintenance($message = '시스템 점검 중입니다. 잠시 후 다시 접속해주세요.') {
        // 메인터넌스 모드 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::info("Maintenance Mode Response", [
                'ip' => self::getClientIp(),
                'url' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
        }
        
        return self::error($message, 503);
    }
    
    /**
     * 페이지네이션 응답
     */
    public static function paginated($data, $pagination, $message = '조회 완료') {
        return self::success([
            'items' => $data,
            'pagination' => $pagination
        ], $message);
    }
    
    /**
     * 파일 다운로드 응답
     */
    public static function download($filePath, $fileName = null, $mimeType = null) {
        if (!file_exists($filePath)) {
            if (class_exists('WebLogger')) {
                WebLogger::error("File Download Failed: File not found", ['file_path' => $filePath]);
            }
            self::notFound('파일을 찾을 수 없습니다.');
        }
        
        $fileName = $fileName ?: basename($filePath);
        $mimeType = $mimeType ?: self::getMimeType($filePath);
        
        // 파일 다운로드 로깅 (WebLogger가 사용 가능한 경우에만)
        if (class_exists('WebLogger')) {
            WebLogger::activity("File Download", [
                'file_name' => $fileName,
                'file_size' => filesize($filePath),
                'mime_type' => $mimeType
            ]);
        }
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($filePath);
        exit;
    }
    
    /**
     * 요청 ID 획득
     */
    private static function getRequestId() {
        return $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid('req_', true);
    }
    
    /**
     * 클라이언트 IP 주소 획득
     */
    private static function getClientIp() {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        
        return 'unknown';
    }
    
    /**
     * AJAX 요청 여부 확인
     */
    private static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * 개발 환경 여부 확인
     */
    private static function isDevelopment() {
        return (getenv('APP_ENV') === 'development') || 
               (defined('APP_DEBUG') && APP_DEBUG) ||
               (!empty($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false);
    }
    
    /**
     * MIME 타입 추론
     */
    private static function getMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    
    // 하위 호환성을 위한 메서드들
    public static function jsonSuccess($message = '성공적으로 처리되었습니다.', $data = [], $status = 200) {
        return self::success($data, $message);
    }
    
    public static function jsonError($message = '요청을 처리할 수 없습니다.', $status = 400, $errors = []) {
        return self::error($message, $status, $errors);
    }
    
}