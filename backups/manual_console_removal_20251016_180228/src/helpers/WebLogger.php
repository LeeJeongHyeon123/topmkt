<?php
/**
 * 중앙집중식 로깅 시스템
 * 문서: 22.에러처리_및_로깅_표준.md
 */

class LogLevel {
    const DEBUG = 100;      // 개발용 상세 정보
    const INFO = 200;       // 일반적인 애플리케이션 이벤트
    const NOTICE = 250;     // 주목할 만한 이벤트
    const WARNING = 300;    // 경고 상황 (처리 가능한 문제)
    const ERROR = 400;      // 오류 상황 (처리 가능한 오류)
    const CRITICAL = 500;   // 심각한 오류 (즉시 대응 필요)
    const ALERT = 550;      // 즉시 조치 필요
    const EMERGENCY = 600;  // 시스템 사용 불가
}

class WebLogger {
    private static $logDir = null;
    private static $maxFileSize = 10 * 1024 * 1024; // 10MB
    private static $isInitialized = false;
    private static $useUnifiedLog = true;  // 통합 로그 사용 여부
    private static $unifiedLogFile = 'topmkt_errors.log';  // 통합 로그 파일명
    
    /**
     * 로거 초기화
     */
    public static function init($config = []) {
        if (self::$isInitialized) {
            return;
        }
        
        // 로그 디렉토리 설정 (LOGS_PATH 상수 사용)
        self::$logDir = defined('LOGS_PATH') ? LOGS_PATH : '/var/www/html/topmkt/logs';
        
        // 설정 옵션 적용
        if (isset($config['useUnifiedLog'])) {
            self::$useUnifiedLog = $config['useUnifiedLog'];
        }
        if (isset($config['unifiedLogFile'])) {
            self::$unifiedLogFile = $config['unifiedLogFile'];
        }
        if (isset($config['maxFileSize'])) {
            self::$maxFileSize = $config['maxFileSize'];
        }
        
        // 로그 디렉토리 생성
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        
        self::$isInitialized = true;
    }
    
    /**
     * 중앙집중식 로그 기록
     */
    public static function log($level, $message, $context = []) {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? self::generateRequestId();
        $userId = $_SESSION['user_id'] ?? 'anonymous';
        $ip = self::getClientIp();
        
        // 민감한 정보 마스킹
        $context = self::maskSensitiveData($context);
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => self::getLevelName($level),
            'message' => $message,
            'context' => $context,
            'request_id' => $requestId,
            'user_id' => $userId,
            'ip' => $ip,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 로그 파일 결정
        $logFile = self::getLogFile($level);
        
        // 로그 로테이션 확인
        self::rotateLogIfNeeded($logFile);
        
        // 로그 기록
        if (file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX) === false) {
            // 로그 기록 실패 시 PHP 에러 로그로 폴백
            error_log("WebLogger: Failed to write to {$logFile} - " . $message);
        }
        
        // 심각한 레벨은 즉시 알림
        if ($level >= LogLevel::CRITICAL) {
            self::sendAlert($logEntry);
        }
        
        // 개발 환경에서는 콘솔에도 출력
        if (self::isDevelopment()) {
            error_log("[{$logEntry['level']}] {$message}");
        }
    }
    
    /**
     * 디버그 로그
     */
    public static function debug($message, $context = []) {
        if (self::isDevelopment()) {
            self::log(LogLevel::DEBUG, $message, $context);
        }
    }
    
    /**
     * 정보 로그
     */
    public static function info($message, $context = []) {
        self::log(LogLevel::INFO, $message, $context);
    }
    
    /**
     * 알림 로그
     */
    public static function notice($message, $context = []) {
        self::log(LogLevel::NOTICE, $message, $context);
    }
    
    /**
     * 경고 로그
     */
    public static function warning($message, $context = []) {
        self::log(LogLevel::WARNING, $message, $context);
    }
    
    /**
     * 에러 로그
     */
    public static function error($message, $context = []) {
        self::log(LogLevel::ERROR, $message, $context);
    }
    
    /**
     * 심각한 에러 로그
     */
    public static function critical($message, $context = []) {
        self::log(LogLevel::CRITICAL, $message, $context);
    }
    
    /**
     * 즉시 조치 필요 로그
     */
    public static function alert($message, $context = []) {
        self::log(LogLevel::ALERT, $message, $context);
    }
    
    /**
     * 응급 상황 로그
     */
    public static function emergency($message, $context = []) {
        self::log(LogLevel::EMERGENCY, $message, $context);
    }
    
    /**
     * 민감한 데이터 마스킹
     */
    private static function maskSensitiveData($data) {
        $sensitiveKeys = [
            'password', 'passwd', 'pwd',
            'token', 'access_token', 'refresh_token',
            'api_key', 'apikey', 'key',
            'secret', 'private_key', 'private',
            'phone', 'mobile', 'tel',
            'ssn', 'social_security',
            'credit_card', 'card_number',
            'auth', 'authorization'
        ];
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $lowerKey = strtolower($key);
                $shouldMask = false;
                
                foreach ($sensitiveKeys as $sensitiveKey) {
                    if (strpos($lowerKey, $sensitiveKey) !== false) {
                        $shouldMask = true;
                        break;
                    }
                }
                
                if ($shouldMask) {
                    $data[$key] = '***MASKED***';
                } elseif (is_array($value)) {
                    $data[$key] = self::maskSensitiveData($value);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 로그 레벨명 반환
     */
    private static function getLevelName($level) {
        $levels = [
            LogLevel::DEBUG => 'DEBUG',
            LogLevel::INFO => 'INFO',
            LogLevel::NOTICE => 'NOTICE',
            LogLevel::WARNING => 'WARNING',
            LogLevel::ERROR => 'ERROR',
            LogLevel::CRITICAL => 'CRITICAL',
            LogLevel::ALERT => 'ALERT',
            LogLevel::EMERGENCY => 'EMERGENCY'
        ];
        
        return $levels[$level] ?? 'UNKNOWN';
    }
    
    /**
     * 로그 파일 경로 결정
     */
    private static function getLogFile($level) {
        // 통합 로그 사용 시
        if (self::$useUnifiedLog) {
            return self::$logDir . '/' . self::$unifiedLogFile;
        }
        
        // 기존 날짜별 분리 로그
        $date = date('Y-m-d');
        
        switch ($level) {
            case LogLevel::DEBUG:
                return self::$logDir . "/debug-{$date}.log";
            case LogLevel::INFO:
            case LogLevel::NOTICE:
                return self::$logDir . "/info-{$date}.log";
            case LogLevel::WARNING:
                return self::$logDir . "/warning-{$date}.log";
            case LogLevel::ERROR:
                return self::$logDir . "/error-{$date}.log";
            case LogLevel::CRITICAL:
            case LogLevel::ALERT:
            case LogLevel::EMERGENCY:
                return self::$logDir . "/critical-{$date}.log";
            default:
                return self::$logDir . "/app-{$date}.log";
        }
    }
    
    /**
     * 로그 로테이션 확인 및 실행
     */
    private static function rotateLogIfNeeded($logFile) {
        if (!file_exists($logFile)) {
            return;
        }
        
        if (filesize($logFile) > self::$maxFileSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $rotatedFile = $logFile . '.' . $timestamp;
            
            if (rename($logFile, $rotatedFile)) {
                // 압축 (선택사항)
                if (function_exists('gzencode')) {
                    $content = file_get_contents($rotatedFile);
                    file_put_contents($rotatedFile . '.gz', gzencode($content));
                    unlink($rotatedFile);
                }
            }
        }
    }
    
    /**
     * 클라이언트 IP 주소 획득
     */
    private static function getClientIp() {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // 여러 IP가 있는 경우 첫 번째 사용
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        
        return 'unknown';
    }
    
    /**
     * 요청 ID 생성
     */
    private static function generateRequestId() {
        return uniqid('req_', true);
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
     * 심각한 에러 알림 발송
     */
    private static function sendAlert($logEntry) {
        try {
            // 에러 로그에 알림 시도 기록
            $alertLog = self::$logDir . 'alerts-' . date('Y-m-d') . '.log';
            $alertEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => $logEntry['level'],
                'message' => $logEntry['message'],
                'request_id' => $logEntry['request_id'],
                'alert_sent' => true
            ];
            
            file_put_contents($alertLog, json_encode($alertEntry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
            
            // 실제 알림 발송 (이메일, 슬랙 등)은 별도 구현
            // self::sendEmailAlert($logEntry);
            // self::sendSlackAlert($logEntry);
            
        } catch (Exception $e) {
            // 알림 발송 실패해도 원본 로그는 유지
            error_log("Alert sending failed: " . $e->getMessage());
        }
    }
    
    /**
     * 성능 추적용 특별 로그
     */
    public static function performance($operation, $duration, $context = []) {
        self::info("Performance: {$operation}", array_merge($context, [
            'operation' => $operation,
            'duration_ms' => round($duration * 1000, 2),
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]));
    }
    
    /**
     * 보안 이벤트 로그
     */
    public static function security($event, $context = []) {
        // 보안 이벤트는 항상 기록
        self::warning("Security Event: {$event}", array_merge($context, [
            'security_event' => true,
            'event_type' => $event
        ]));
    }
    
    /**
     * 사용자 활동 로그
     */
    public static function activity($action, $context = []) {
        self::info("User Activity: {$action}", array_merge($context, [
            'activity' => true,
            'action' => $action
        ]));
    }
    
    /**
     * API 호출 로그
     */
    public static function api($endpoint, $method, $responseTime = null, $statusCode = null, $context = []) {
        self::info("API Call: {$method} {$endpoint}", array_merge($context, [
            'api_call' => true,
            'endpoint' => $endpoint,
            'method' => $method,
            'response_time_ms' => $responseTime ? round($responseTime * 1000, 2) : null,
            'status_code' => $statusCode
        ]));
    }
    
    /**
     * 데이터베이스 쿼리 로그 (느린 쿼리만)
     */
    public static function slowQuery($sql, $duration, $context = []) {
        if ($duration > 1.0) { // 1초 이상인 쿼리만 로그
            self::warning("Slow Query", array_merge($context, [
                'slow_query' => true,
                'sql' => substr($sql, 0, 500), // SQL 길이 제한
                'duration_ms' => round($duration * 1000, 2)
            ]));
        }
    }
    
    /**
     * 빠른 로깅을 위한 헬퍼 메소드들
     */
    
    /**
     * 컨트롤러 시작 로그
     */
    public static function controllerStart($controller, $action, $context = []) {
        self::info("Controller started: {$controller}::{$action}", array_merge($context, [
            'controller' => $controller,
            'action' => $action
        ]));
    }
    
    /**
     * 컨트롤러 종료 로그
     */
    public static function controllerEnd($controller, $action, $duration = null, $context = []) {
        self::info("Controller finished: {$controller}::{$action}", array_merge($context, [
            'controller' => $controller,
            'action' => $action,
            'duration_ms' => $duration ? round($duration * 1000, 2) : null
        ]));
    }
    
    /**
     * 예외 로그 (자동으로 스택 트레이스 포함)
     */
    public static function exception($exception, $context = []) {
        $level = $exception instanceof Error ? LogLevel::CRITICAL : LogLevel::ERROR;
        
        self::log($level, "Exception: " . $exception->getMessage(), array_merge($context, [
            'exception_type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]));
    }
    
    /**
     * 설정 변경
     */
    public static function setConfig($key, $value) {
        switch ($key) {
            case 'useUnifiedLog':
                self::$useUnifiedLog = $value;
                break;
            case 'unifiedLogFile':
                self::$unifiedLogFile = $value;
                break;
            case 'maxFileSize':
                self::$maxFileSize = $value;
                break;
        }
    }
    
    /**
     * 현재 설정 반환
     */
    public static function getConfig() {
        return [
            'logDir' => self::$logDir,
            'useUnifiedLog' => self::$useUnifiedLog,
            'unifiedLogFile' => self::$unifiedLogFile,
            'maxFileSize' => self::$maxFileSize,
            'isInitialized' => self::$isInitialized
        ];
    }
}