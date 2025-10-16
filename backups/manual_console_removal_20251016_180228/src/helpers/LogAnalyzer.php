<?php
/**
 * 로그 분석 도구
 * 문서: 22.에러처리_및_로깅_표준.md
 */

class LogAnalyzer {
    private static $logDir = '/workspace/logs/';
    
    /**
     * 에러 통계 조회
     */
    public static function getErrorStats($date = null) {
        $date = $date ?: date('Y-m-d');
        $logFile = self::$logDir . "error-{$date}.log";
        
        if (!file_exists($logFile)) {
            return [
                'total' => 0,
                'by_level' => [],
                'by_hour' => [],
                'top_errors' => [],
                'by_user' => [],
                'by_ip' => []
            ];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $stats = [
            'total' => 0,
            'by_level' => [],
            'by_hour' => [],
            'top_errors' => [],
            'by_user' => [],
            'by_ip' => [],
            'by_url' => [],
            'performance' => [
                'slow_requests' => 0,
                'memory_warnings' => 0
            ]
        ];
        
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (!$data) continue;
            
            $stats['total']++;
            
            // 레벨별 통계
            $level = $data['level'] ?? 'unknown';
            $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
            
            // 시간별 통계
            $hour = substr($data['timestamp'], 11, 2);
            $stats['by_hour'][$hour] = ($stats['by_hour'][$hour] ?? 0) + 1;
            
            // 사용자별 통계
            $userId = $data['user_id'] ?? 'anonymous';
            $stats['by_user'][$userId] = ($stats['by_user'][$userId] ?? 0) + 1;
            
            // IP별 통계
            $ip = $data['ip'] ?? 'unknown';
            $stats['by_ip'][$ip] = ($stats['by_ip'][$ip] ?? 0) + 1;
            
            // URL별 통계
            $url = $data['url'] ?? '';
            if (!empty($url)) {
                $stats['by_url'][$url] = ($stats['by_url'][$url] ?? 0) + 1;
            }
            
            // 자주 발생하는 에러
            $message = $data['message'] ?? '';
            $stats['top_errors'][$message] = ($stats['top_errors'][$message] ?? 0) + 1;
            
            // 성능 관련 통계
            if (isset($data['memory_usage']) && $data['memory_usage'] > 100 * 1024 * 1024) { // 100MB 이상
                $stats['performance']['memory_warnings']++;
            }
        }
        
        // 상위 항목들 정렬
        arsort($stats['top_errors']);
        arsort($stats['by_user']);
        arsort($stats['by_ip']);
        arsort($stats['by_url']);
        
        // 상위 10개만 유지
        $stats['top_errors'] = array_slice($stats['top_errors'], 0, 10, true);
        $stats['by_user'] = array_slice($stats['by_user'], 0, 10, true);
        $stats['by_ip'] = array_slice($stats['by_ip'], 0, 10, true);
        $stats['by_url'] = array_slice($stats['by_url'], 0, 10, true);
        
        return $stats;
    }
    
    /**
     * 성능 통계 조회
     */
    public static function getPerformanceStats($date = null) {
        $date = $date ?: date('Y-m-d');
        $logFile = self::$logDir . "info-{$date}.log";
        
        if (!file_exists($logFile)) {
            return [
                'total_requests' => 0,
                'avg_memory' => 0,
                'peak_memory' => 0,
                'slow_operations' => [],
                'api_calls' => []
            ];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $stats = [
            'total_requests' => 0,
            'memory_usage' => [],
            'slow_operations' => [],
            'api_calls' => [],
            'user_activities' => []
        ];
        
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (!$data) continue;
            
            $stats['total_requests']++;
            
            // 메모리 사용량 수집
            if (isset($data['memory_usage'])) {
                $stats['memory_usage'][] = $data['memory_usage'];
            }
            
            // 성능 관련 로그 분석
            if (isset($data['context']['operation'])) {
                $operation = $data['context']['operation'];
                $duration = $data['context']['duration_ms'] ?? 0;
                
                if ($duration > 1000) { // 1초 이상
                    $stats['slow_operations'][] = [
                        'operation' => $operation,
                        'duration' => $duration,
                        'timestamp' => $data['timestamp']
                    ];
                }
            }
            
            // API 호출 분석
            if (isset($data['context']['api_call'])) {
                $endpoint = $data['context']['endpoint'];
                $method = $data['context']['method'];
                $statusCode = $data['context']['status_code'];
                
                $key = "{$method} {$endpoint}";
                if (!isset($stats['api_calls'][$key])) {
                    $stats['api_calls'][$key] = [
                        'count' => 0,
                        'success' => 0,
                        'error' => 0,
                        'avg_response_time' => 0,
                        'response_times' => []
                    ];
                }
                
                $stats['api_calls'][$key]['count']++;
                
                if ($statusCode >= 200 && $statusCode < 400) {
                    $stats['api_calls'][$key]['success']++;
                } else {
                    $stats['api_calls'][$key]['error']++;
                }
                
                if (isset($data['context']['response_time_ms'])) {
                    $stats['api_calls'][$key]['response_times'][] = $data['context']['response_time_ms'];
                }
            }
            
            // 사용자 활동 분석
            if (isset($data['context']['activity'])) {
                $action = $data['context']['action'];
                $stats['user_activities'][$action] = ($stats['user_activities'][$action] ?? 0) + 1;
            }
        }
        
        // 메모리 통계 계산
        if (!empty($stats['memory_usage'])) {
            $stats['avg_memory'] = array_sum($stats['memory_usage']) / count($stats['memory_usage']);
            $stats['peak_memory'] = max($stats['memory_usage']);
            $stats['min_memory'] = min($stats['memory_usage']);
        }
        
        // API 응답 시간 평균 계산
        foreach ($stats['api_calls'] as $endpoint => &$data) {
            if (!empty($data['response_times'])) {
                $data['avg_response_time'] = array_sum($data['response_times']) / count($data['response_times']);
            }
            unset($data['response_times']); // 메모리 절약
        }
        
        // 정렬
        uasort($stats['slow_operations'], function($a, $b) {
            return $b['duration'] <=> $a['duration'];
        });
        
        arsort($stats['user_activities']);
        
        return $stats;
    }
    
    /**
     * 보안 이벤트 분석
     */
    public static function getSecurityStats($date = null) {
        $date = $date ?: date('Y-m-d');
        $logFiles = [
            self::$logDir . "warning-{$date}.log",
            self::$logDir . "error-{$date}.log"
        ];
        
        $stats = [
            'total_events' => 0,
            'unauthorized_attempts' => 0,
            'forbidden_attempts' => 0,
            'suspicious_ips' => [],
            'event_types' => [],
            'hourly_distribution' => []
        ];
        
        foreach ($logFiles as $logFile) {
            if (!file_exists($logFile)) continue;
            
            $lines = file($logFile, FILE_IGNORE_NEW_LINES);
            
            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if (!$data) continue;
                
                // 보안 이벤트만 필터링
                if (!isset($data['context']['security_event'])) continue;
                
                $stats['total_events']++;
                
                // 이벤트 타입별 분류
                $eventType = $data['context']['event_type'] ?? 'unknown';
                $stats['event_types'][$eventType] = ($stats['event_types'][$eventType] ?? 0) + 1;
                
                // 시간별 분포
                $hour = substr($data['timestamp'], 11, 2);
                $stats['hourly_distribution'][$hour] = ($stats['hourly_distribution'][$hour] ?? 0) + 1;
                
                // 의심스러운 IP 추적
                $ip = $data['ip'] ?? 'unknown';
                if (!isset($stats['suspicious_ips'][$ip])) {
                    $stats['suspicious_ips'][$ip] = [
                        'count' => 0,
                        'events' => []
                    ];
                }
                $stats['suspicious_ips'][$ip]['count']++;
                $stats['suspicious_ips'][$ip]['events'][] = $eventType;
                
                // 특정 이벤트 카운팅
                if (strpos($data['message'], 'Unauthorized') !== false) {
                    $stats['unauthorized_attempts']++;
                }
                if (strpos($data['message'], 'Forbidden') !== false) {
                    $stats['forbidden_attempts']++;
                }
            }
        }
        
        // 의심스러운 IP 정렬 (이벤트 수 기준)
        uasort($stats['suspicious_ips'], function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        arsort($stats['event_types']);
        
        return $stats;
    }
    
    /**
     * 이상 징후 탐지
     */
    public static function detectAnomalies($date = null) {
        $date = $date ?: date('Y-m-d');
        $currentHour = (int)date('H');
        
        $errorStats = self::getErrorStats($date);
        $performanceStats = self::getPerformanceStats($date);
        
        $anomalies = [];
        
        // 에러 급증 감지
        if (!empty($errorStats['by_hour'])) {
            $currentHourErrors = $errorStats['by_hour'][$currentHour] ?? 0;
            $avgErrors = array_sum($errorStats['by_hour']) / count($errorStats['by_hour']);
            
            if ($currentHourErrors > $avgErrors * 3) {
                $anomalies[] = [
                    'type' => 'error_spike',
                    'severity' => 'high',
                    'message' => '현재 시간대 에러 급증 감지',
                    'details' => [
                        'current_errors' => $currentHourErrors,
                        'average_errors' => round($avgErrors, 2),
                        'threshold' => round($avgErrors * 3, 2)
                    ]
                ];
            }
        }
        
        // 메모리 사용량 이상 감지
        if (isset($performanceStats['peak_memory']) && $performanceStats['peak_memory'] > 500 * 1024 * 1024) { // 500MB 이상
            $anomalies[] = [
                'type' => 'high_memory_usage',
                'severity' => 'medium',
                'message' => '높은 메모리 사용량 감지',
                'details' => [
                    'peak_memory_mb' => round($performanceStats['peak_memory'] / 1024 / 1024, 2),
                    'avg_memory_mb' => round(($performanceStats['avg_memory'] ?? 0) / 1024 / 1024, 2)
                ]
            ];
        }
        
        // 느린 연산 감지
        if (!empty($performanceStats['slow_operations'])) {
            $verySlowOps = array_filter($performanceStats['slow_operations'], function($op) {
                return $op['duration'] > 5000; // 5초 이상
            });
            
            if (!empty($verySlowOps)) {
                $anomalies[] = [
                    'type' => 'very_slow_operations',
                    'severity' => 'medium',
                    'message' => '매우 느린 연산 감지',
                    'details' => [
                        'count' => count($verySlowOps),
                        'operations' => array_slice($verySlowOps, 0, 5) // 상위 5개만
                    ]
                ];
            }
        }
        
        // 보안 이벤트 급증 감지
        $securityStats = self::getSecurityStats($date);
        if ($securityStats['total_events'] > 50) { // 하루 50건 이상
            $anomalies[] = [
                'type' => 'security_events_spike',
                'severity' => 'high',
                'message' => '보안 이벤트 급증 감지',
                'details' => [
                    'total_events' => $securityStats['total_events'],
                    'unauthorized_attempts' => $securityStats['unauthorized_attempts'],
                    'forbidden_attempts' => $securityStats['forbidden_attempts']
                ]
            ];
        }
        
        return [
            'anomaly_detected' => !empty($anomalies),
            'anomalies' => $anomalies,
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 로그 요약 보고서 생성
     */
    public static function generateDailyReport($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $errorStats = self::getErrorStats($date);
        $performanceStats = self::getPerformanceStats($date);
        $securityStats = self::getSecurityStats($date);
        $anomalies = self::detectAnomalies($date);
        
        return [
            'date' => $date,
            'summary' => [
                'total_requests' => $performanceStats['total_requests'],
                'total_errors' => $errorStats['total'],
                'error_rate' => $performanceStats['total_requests'] > 0 
                    ? round(($errorStats['total'] / $performanceStats['total_requests']) * 100, 2)
                    : 0,
                'security_events' => $securityStats['total_events'],
                'anomalies_detected' => count($anomalies['anomalies'])
            ],
            'errors' => $errorStats,
            'performance' => $performanceStats,
            'security' => $securityStats,
            'anomalies' => $anomalies,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 로그 파일 정리 (오래된 로그 압축/삭제)
     */
    public static function cleanupLogs($keepDays = 30) {
        $cleaned = [
            'compressed' => 0,
            'deleted' => 0,
            'errors' => []
        ];
        
        try {
            $files = glob(self::$logDir . '*.log');
            
            foreach ($files as $file) {
                $fileDate = filemtime($file);
                $daysOld = (time() - $fileDate) / (24 * 3600);
                
                if ($daysOld > $keepDays) {
                    // 30일 이상 된 파일 삭제
                    if (unlink($file)) {
                        $cleaned['deleted']++;
                    } else {
                        $cleaned['errors'][] = "Failed to delete: " . basename($file);
                    }
                } elseif ($daysOld > 7) {
                    // 7일 이상 된 파일 압축
                    if (function_exists('gzencode')) {
                        $content = file_get_contents($file);
                        $compressedFile = $file . '.gz';
                        
                        if (file_put_contents($compressedFile, gzencode($content))) {
                            unlink($file);
                            $cleaned['compressed']++;
                        } else {
                            $cleaned['errors'][] = "Failed to compress: " . basename($file);
                        }
                    }
                }
            }
            
            WebLogger::info('Log cleanup completed', $cleaned);
            
        } catch (Exception $e) {
            $cleaned['errors'][] = $e->getMessage();
            WebLogger::error('Log cleanup failed', ['error' => $e->getMessage()]);
        }
        
        return $cleaned;
    }
}