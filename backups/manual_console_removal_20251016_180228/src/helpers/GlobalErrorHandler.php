<?php
/**
 * 글로벌 에러 핸들러
 * 문서: 22.에러처리_및_로깅_표준.md
 */

/**
 * 애플리케이션 예외 베이스 클래스
 */
abstract class ApplicationException extends Exception {
    protected $context = [];
    protected $userMessage = '';
    
    public function __construct($message = '', $code = 0, $previous = null, $context = []) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    
    public function getContext() {
        return $this->context;
    }
    
    public function getUserMessage() {
        return $this->userMessage ?: $this->getMessage();
    }
    
    public function setUserMessage($message) {
        $this->userMessage = $message;
        return $this;
    }
}

/**
 * 데이터 검증 예외
 */
class ValidationException extends ApplicationException {
    protected $errors = [];
    
    public function __construct($errors = [], $message = '데이터 검증에 실패했습니다.') {
        $this->errors = $errors;
        parent::__construct($message, 400);
        $this->userMessage = '입력하신 정보를 다시 확인해주세요.';
    }
    
    public function getErrors() {
        return $this->errors;
    }
}

/**
 * 데이터베이스 예외
 */
class DatabaseException extends ApplicationException {
    protected $sql = '';
    
    public function __construct($message, $sql = '', $code = 500) {
        $this->sql = $sql;
        parent::__construct($message, $code);
        $this->userMessage = '일시적인 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
    }
    
    public function getSql() {
        return $this->sql;
    }
}

/**
 * 인증 예외
 */
class AuthenticationException extends ApplicationException {
    public function __construct($message = '인증이 필요합니다.', $code = 401) {
        parent::__construct($message, $code);
        $this->userMessage = '로그인이 필요합니다.';
    }
}

/**
 * 권한 예외
 */
class AuthorizationException extends ApplicationException {
    public function __construct($message = '접근 권한이 없습니다.', $code = 403) {
        parent::__construct($message, $code);
        $this->userMessage = '접근 권한이 없습니다. 로그인을 확인해주세요.';
    }
}

/**
 * 리소스 없음 예외
 */
class NotFoundException extends ApplicationException {
    public function __construct($message = '요청한 리소스를 찾을 수 없습니다.', $code = 404) {
        parent::__construct($message, $code);
        $this->userMessage = '요청하신 내용을 찾을 수 없습니다.';
    }
}

/**
 * 요청 제한 예외
 */
class RateLimitException extends ApplicationException {
    public function __construct($message = '요청 한도를 초과했습니다.', $code = 429) {
        parent::__construct($message, $code);
        $this->userMessage = '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.';
    }
}

/**
 * 파일 처리 예외
 */
class FileException extends ApplicationException {
    public function __construct($message = '파일 처리 중 오류가 발생했습니다.', $code = 400) {
        parent::__construct($message, $code);
        $this->userMessage = '파일 처리 중 문제가 발생했습니다. 파일을 확인해주세요.';
    }
}

/**
 * 글로벌 에러 핸들러
 */
class GlobalErrorHandler {
    private static $isRegistered = false;
    
    /**
     * 에러 핸들러 등록
     */
    public static function register() {
        if (self::$isRegistered) {
            return;
        }
        
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        self::$isRegistered = true;
        
        WebLogger::info('Global Error Handler registered');
    }
    
    /**
     * PHP 에러 처리
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorLevel = self::mapErrorLevel($severity);
        
        WebLogger::log($errorLevel, 'PHP Error: ' . $message, [
            'file' => $file,
            'line' => $line,
            'severity' => $severity,
            'severity_name' => self::getSeverityName($severity)
        ]);
        
        // 심각한 오류는 예외로 변환
        if ($severity & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
        
        return true;
    }
    
    /**
     * 예외 처리
     */
    public static function handleException($exception) {
        $isAjax = self::isApiRequest();
        
        try {
            if ($exception instanceof ApplicationException) {
                // 애플리케이션 예외: 사용자 친화적 처리
                self::handleApplicationException($exception, $isAjax);
            } else {
                // 시스템 예외: 기술적 오류
                self::handleSystemException($exception, $isAjax);
            }
        } catch (Exception $e) {
            // 예외 처리 중 오류 발생 시 최소한의 응답
            self::handleCriticalError($e);
        }
    }
    
    /**
     * 셧다운 시 Fatal Error 처리
     */
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            WebLogger::critical('Fatal Error', [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]);
            
            if (!headers_sent()) {
                http_response_code(500);
                
                $isAjax = self::isApiRequest();
                
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'error',
                        'message' => '시스템에 심각한 오류가 발생했습니다.',
                        'timestamp' => date('c')
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    echo '시스템에 심각한 오류가 발생했습니다.';
                }
            }
        }
    }
    
    /**
     * 애플리케이션 예외 처리
     */
    private static function handleApplicationException(ApplicationException $exception, $isAjax) {
        WebLogger::warning('Application Exception', [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'context' => $exception->getContext(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
        
        if ($isAjax) {
            if ($exception instanceof ValidationException) {
                ResponseHelper::validationError($exception->getErrors(), $exception->getUserMessage());
            } elseif ($exception instanceof AuthenticationException) {
                ResponseHelper::unauthorized($exception->getUserMessage());
            } elseif ($exception instanceof AuthorizationException) {
                ResponseHelper::forbidden($exception->getUserMessage());
            } elseif ($exception instanceof NotFoundException) {
                ResponseHelper::notFound($exception->getUserMessage());
            } elseif ($exception instanceof RateLimitException) {
                ResponseHelper::rateLimited($exception->getUserMessage());
            } else {
                ResponseHelper::error($exception->getUserMessage(), $exception->getCode());
            }
        } else {
            if ($exception instanceof AuthenticationException) {
                header('Location: /auth/login');
                exit;
            } elseif ($exception instanceof NotFoundException) {
                ResponseHelper::render404();
            } elseif ($exception instanceof AuthorizationException) {
                ResponseHelper::render403();
            } else {
                self::renderErrorPage($exception->getCode(), $exception->getUserMessage());
            }
        }
    }
    
    /**
     * 시스템 예외 처리
     */
    private static function handleSystemException(Exception $exception, $isAjax) {
        WebLogger::critical('System Exception', [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        if ($isAjax) {
            ResponseHelper::serverError('시스템 오류가 발생했습니다.');
        } else {
            ResponseHelper::render500();
        }
    }
    
    /**
     * 심각한 오류 처리 (에러 핸들러 자체 오류)
     */
    private static function handleCriticalError(Exception $e) {
        error_log("Critical Error in Error Handler: " . $e->getMessage());
        
        if (!headers_sent()) {
            http_response_code(500);
            echo '시스템에 심각한 오류가 발생했습니다.';
        }
    }
    
    /**
     * 에러 페이지 렌더링
     */
    private static function renderErrorPage($code, $message) {
        switch ($code) {
            case 403:
                ResponseHelper::render403();
                break;
            case 404:
                ResponseHelper::render404();
                break;
            default:
                ResponseHelper::render500();
        }
    }
    
    /**
     * PHP 에러 레벨을 로그 레벨로 매핑
     */
    private static function mapErrorLevel($severity) {
        switch ($severity) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return LogLevel::ERROR;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return LogLevel::WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
                return LogLevel::NOTICE;
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return LogLevel::INFO;
            default:
                return LogLevel::WARNING;
        }
    }
    
    /**
     * 에러 심각도 이름 반환
     */
    private static function getSeverityName($severity) {
        $severities = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];
        
        return $severities[$severity] ?? 'UNKNOWN';
    }
    
    /**
     * API 요청인지 판단
     */
    private static function isApiRequest() {
        // AJAX 요청 헤더 확인
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // API 경로 확인
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $isApiPath = strpos($uri, '/api/') !== false;
        
        // Content-Type 헤더 확인 (JSON 요청)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJsonRequest = strpos($contentType, 'application/json') !== false;
        
        // Accept 헤더 확인 (JSON 응답 요청)
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $acceptsJson = strpos($accept, 'application/json') !== false;
        
        return $isAjax || $isApiPath || $isJsonRequest || $acceptsJson;
    }
}