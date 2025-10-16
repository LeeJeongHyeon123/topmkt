<?php
/**
 * 성능 디버깅 헬퍼 클래스
 */

class PerformanceDebugger {
    private static $timers = [];
    private static $queries = [];
    private static $memorySnapshots = [];
    
    /**
     * 타이머 시작
     */
    public static function startTimer($name) {
        self::$timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];
    }
    
    /**
     * 타이머 종료 및 결과 반환
     */
    public static function endTimer($name) {
        if (!isset(self::$timers[$name])) {
            return null;
        }
        
        $timer = self::$timers[$name];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $result = [
            'name' => $name,
            'execution_time_ms' => round(($endTime - $timer['start']) * 1000, 2),
            'memory_used_mb' => round(($endMemory - $timer['memory_start']) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ];
        
        unset(self::$timers[$name]);
        return $result;
    }
    
    /**
     * 쿼리 실행 시간 기록
     */
    public static function logQuery($sql, $params = [], $executionTime = 0) {
        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'timestamp' => date('Y-m-d H:i:s.u')
        ];
    }
    
    /**
     * 쿼리 실행 및 성능 측정 (Database 클래스 사용)
     */
    public static function executeQuery($db, $sql, $params = []) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Database 클래스의 fetchAll 메서드 사용
        $result = $db->fetchAll($sql, $params);
        
        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;
        
        // 쿼리 성능 로깅
        self::logQuery($sql, $params, $executionTime);
        
        // 성능 정보 로깅
        error_log("🔍 쿼리 성능: " . json_encode([
            'execution_time_ms' => round($executionTime * 1000, 2),
            'memory_used_kb' => round($memoryUsed / 1024, 2),
            'result_count' => count($result),
            'sql_preview' => substr(preg_replace('/\s+/', ' ', $sql), 0, 100) . '...'
        ], JSON_UNESCAPED_UNICODE));
        
        return $result;
    }
    
    /**
     * 전체 성능 리포트 생성
     */
    public static function generateReport() {
        $totalQueries = count(self::$queries);
        $totalTime = array_sum(array_column(self::$queries, 'execution_time_ms'));
        
        $report = [
            'summary' => [
                'total_queries' => $totalQueries,
                'total_execution_time_ms' => round($totalTime, 2),
                'average_query_time_ms' => $totalQueries > 0 ? round($totalTime / $totalQueries, 2) : 0,
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'current_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ],
            'slow_queries' => array_filter(self::$queries, function($query) {
                return $query['execution_time_ms'] > 100; // 100ms 이상
            }),
            'all_queries' => self::$queries
        ];
        
        return $report;
    }
    
    /**
     * 성능 리포트 로깅
     */
    public static function logReport($context = '') {
        $report = self::generateReport();
        
        error_log("📊 성능 리포트 ($context): " . json_encode($report['summary'], JSON_UNESCAPED_UNICODE));
        
        if (!empty($report['slow_queries'])) {
            error_log("⚠️ 느린 쿼리들: " . json_encode($report['slow_queries'], JSON_UNESCAPED_UNICODE));
        }
        
        return $report;
    }
    
    /**
     * DB 인덱스 상태 확인 (Database 클래스 사용)
     */
    public static function checkIndexUsage($db, $sql, $params = []) {
        // EXPLAIN 쿼리 실행
        $explainSql = "EXPLAIN " . $sql;
        $explainResult = $db->fetchAll($explainSql, $params);
        
        // 인덱스 사용 분석
        $indexAnalysis = [];
        foreach ($explainResult as $row) {
            $indexAnalysis[] = [
                'table' => $row['table'] ?? '',
                'type' => $row['type'] ?? '',
                'key' => $row['key'] ?? 'NULL',
                'rows' => $row['rows'] ?? 0,
                'extra' => $row['Extra'] ?? ''
            ];
        }
        
        error_log("🔍 인덱스 분석: " . json_encode($indexAnalysis, JSON_UNESCAPED_UNICODE));
        
        return $indexAnalysis;
    }
    
    /**
     * FULLTEXT 인덱스 상태 확인 (Database 클래스 사용)
     */
    public static function checkFulltextIndex($db, $tableName = 'posts') {
        $sql = "
            SELECT 
                INDEX_NAME,
                COLUMN_NAME,
                INDEX_TYPE,
                CARDINALITY
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = ?
              AND INDEX_TYPE = 'FULLTEXT'
            ORDER BY INDEX_NAME, SEQ_IN_INDEX
        ";
        
        $result = $db->fetchAll($sql, [$tableName]);
        
        error_log("📋 FULLTEXT 인덱스 상태: " . json_encode($result, JSON_UNESCAPED_UNICODE));
        
        return $result;
    }
    
    /**
     * 테이블 통계 정보 확인 (Database 클래스 사용)
     */
    public static function checkTableStats($db, $tableName = 'posts') {
        $sql = "
            SELECT 
                COUNT(*) as total_rows,
                AVG(CHAR_LENGTH(title)) as avg_title_length,
                AVG(CHAR_LENGTH(content)) as avg_content_length,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as published_count
            FROM $tableName
        ";
        
        $result = $db->fetch($sql);
        
        error_log("📈 테이블 통계 ($tableName): " . json_encode($result, JSON_UNESCAPED_UNICODE));
        
        return $result;
    }
    
    /**
     * 리셋
     */
    public static function reset() {
        self::$timers = [];
        self::$queries = [];
        self::$memorySnapshots = [];
    }
}