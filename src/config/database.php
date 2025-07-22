<?php
/**
 * 탑마케팅 데이터베이스 설정 파일
 */

/**
 * 데이터베이스 연결 설정 (MySQLi + Socket 방식)
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // 데이터베이스 설정 (환경변수 우선, 기본값 fallback)
    private const SOCKET_PATH = '/var/lib/mysql/mysql.sock'; // MySQL 소켓 파일 경로
    private const DB_NAME = 'TOPMKT';
    private const USERNAME = 'root';
    private const PASSWORD = ''; // 환경변수에서 로드
    private const CHARSET = 'utf8mb4';
    
    private function __construct() {
        try {
            // MySQLi 확장 설치 여부 확인
            if (!extension_loaded('mysqli')) {
                throw new Exception('MySQLi 확장이 설치되어 있지 않습니다. 시스템 관리자에게 문의하세요.');
            }
            
            // 프로젝트별 로그 경로 설정
            $projectLogPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
            ini_set('log_errors', 1);
            ini_set('error_log', $projectLogPath);
            
            // 환경변수에서 데이터베이스 설정 로드 (보안 강화)
            $socket_path = $_ENV['DB_SOCKET'] ?? self::SOCKET_PATH;
            $dbname = $_ENV['DB_NAME'] ?? self::DB_NAME;
            $username = $_ENV['DB_USERNAME'] ?? self::USERNAME;
            $password = $_ENV['DB_PASSWORD'] ?? 'Dnlszkem1!'; // 새로운 비밀번호로 업데이트
            
            // MySQLi TCP 연결 (로컬 MySQL 서버)
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1'; // 로컬 데이터베이스 서버
            $this->connection = new mysqli($host, $username, $password, $dbname, 3306);
            
            if ($this->connection->connect_error) {
                throw new Exception('데이터베이스 연결에 실패했습니다: ' . $this->connection->connect_error);
            }
            
            // 문자셋 설정 - 한국어 지원을 위한 강화된 utf8mb4 설정
            $this->connection->set_charset(self::CHARSET);
            
            // UTF-8 세션 변수 강제 설정 (한국어 인코딩 문제 해결)
            $this->connection->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->connection->query("SET character_set_client = utf8mb4");
            $this->connection->query("SET character_set_connection = utf8mb4");
            $this->connection->query("SET character_set_results = utf8mb4");
            $this->connection->query("SET character_set_database = utf8mb4");
            $this->connection->query("SET character_set_server = utf8mb4");
            $this->connection->query("SET collation_connection = utf8mb4_unicode_ci");
            $this->connection->query("SET collation_database = utf8mb4_unicode_ci");
            $this->connection->query("SET collation_server = utf8mb4_unicode_ci");
            
        } catch (Exception $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('데이터베이스 연결에 실패했습니다.');
        }
    }
    
    /**
     * 싱글톤 인스턴스 반환
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * MySQLi 연결 객체 반환
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * 쿼리 실행 (SELECT)
     */
    public function query($sql, $params = []) {
        try {
            if (is_array($params) && count($params) > 0) {
                // PDO 스타일 파라미터(:param)를 MySQLi 스타일(?)로 변환
                $convertedSql = $sql;
                $convertedParams = [];
                
                foreach ($params as $key => $value) {
                    if (strpos($key, ':') === 0) {
                        // :param 형태의 파라미터를 ?로 변환
                        $convertedSql = str_replace($key, '?', $convertedSql);
                        $convertedParams[] = $value;
                    } else {
                        $convertedParams[] = $value;
                    }
                }
                
                $stmt = $this->connection->prepare($convertedSql);
                if (!$stmt) {
                    throw new Exception('Prepare failed: ' . $this->connection->error);
                }
                
                // 파라미터 바인딩
                if (!empty($convertedParams)) {
                    $types = '';
                    foreach ($convertedParams as $param) {
                        if (is_null($param)) {
                            $types .= 's'; // NULL을 문자열로 처리
                        } elseif (is_int($param)) {
                            $types .= 'i';
                        } elseif (is_float($param)) {
                            $types .= 'd';
                        } else {
                            $types .= 's';
                        }
                    }
                    
                    // 디버깅: 파라미터 바인딩 로그
                    error_log("=== MySQLi 파라미터 바인딩 ===");
                    error_log("변환된 SQL: " . $convertedSql);
                    error_log("바인딩 타입: " . $types);
                    error_log("바인딩 값들: " . json_encode($convertedParams));
                    
                    // 글로벌 변수에 바인딩 정보 저장 (모든 쿼리)
                    $GLOBALS['debug_last_binding'] = [
                        'sql' => $convertedSql,
                        'types' => $types,
                        'params' => $convertedParams,
                        'is_update' => stripos($convertedSql, 'UPDATE') === 0
                    ];
                    
                    // UPDATE 쿼리 전용 (공백 제거 후 체크)
                    if (stripos(trim($convertedSql), 'UPDATE') === 0) {
                        $GLOBALS['debug_update_binding'] = [
                            'sql' => $convertedSql,
                            'types' => $types,
                            'params' => $convertedParams
                        ];
                    }
                    
                    $stmt->bind_param($types, ...$convertedParams);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                return $stmt;
            } else {
                // 파라미터 없이 실행되는 경우 로깅
                if (stripos(trim($sql), 'UPDATE') === 0) {
                    $GLOBALS['debug_update_binding'] = [
                        'sql' => $sql,
                        'types' => 'NO_PARAMS',
                        'params' => 'NO_PARAMS',
                        'reason' => 'empty_params_array'
                    ];
                }
                
                $result = $this->connection->query($sql);
                if (!$result) {
                    throw new Exception('Query failed: ' . $this->connection->error);
                }
                return $result;
            }
        } catch (Exception $e) {
            $errorMsg = 'Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql;
            error_log($errorMsg);
            
            // WebLogger 사용 가능하면 더 상세한 로깅
            if (class_exists('WebLogger')) {
                WebLogger::error('Database Query Failed', [
                    'error_message' => $e->getMessage(),
                    'sql_query' => $sql,
                    'params' => $params ?? 'NO_PARAMS',
                    'error_code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
            
            throw new Exception('데이터베이스 쿼리 실행에 실패했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 단일 행 조회
     */
    public function fetch($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        if ($result instanceof mysqli_stmt) {
            $result_set = $result->get_result();
            return $result_set ? $result_set->fetch_assoc() : false;
        } else {
            return $result->fetch_assoc();
        }
    }
    
    /**
     * 다중 행 조회
     */
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $rows = [];
        
        if ($result instanceof mysqli_stmt) {
            $result_set = $result->get_result();
            if ($result_set) {
                while ($row = $result_set->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
        } else {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }
    
    /**
     * INSERT/UPDATE/DELETE 실행
     */
    public function execute($sql, $params = []) {
        try {
            $result = $this->query($sql, $params);
            $affected = $this->connection->affected_rows;
            
            // 디버깅 정보 로깅
            error_log("Database execute - affected_rows: " . $affected);
            error_log("Database execute - connection error: " . $this->connection->error);
            
            return $affected;
        } catch (Exception $e) {
            error_log("Database execute exception: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 마지막 삽입 ID 반환
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * 트랜잭션 시작
     */
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }
    
    /**
     * 트랜잭션 커밋
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * 트랜잭션 롤백
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * 연결 해제 방지
     */
    private function __clone() {}
    
    /**
     * prepare 메서드 추가 (MySQLi 메서드 위임)
     */
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    /**
     * 직렬화 방지
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
} 