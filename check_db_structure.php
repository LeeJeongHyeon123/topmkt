<?php
/**
 * 데이터베이스 테이블 구조 확인 스크립트
 */

// 경로 설정
$rootPath = __DIR__;
$srcPath = $rootPath . '/src';

// 설정 파일 로드
require_once $srcPath . '/config/database.php';

echo "=== MySQL 데이터베이스 구조 확인 ===\n\n";

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    // 1. 모든 테이블 목록
    echo "1. 전체 테이블 목록:\n";
    echo "================\n";
    $result = $connection->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
        echo "- " . $row[0] . "\n";
    }
    echo "\n";
    
    // 2. lectures 테이블 구조 확인
    if (in_array('lectures', $tables)) {
        echo "2. lectures 테이블 구조:\n";
        echo "======================\n";
        $result = $connection->query("DESCRIBE lectures");
        while ($row = $result->fetch_assoc()) {
            echo sprintf("- %-25s %-20s %-8s %-8s %s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key'], 
                $row['Default'] ?? 'NULL'
            );
        }
        echo "\n";
        
        // lectures 테이블의 참가자 관련 필드 확인
        echo "3. lectures 테이블의 참가자 관련 필드:\n";
        echo "=====================================\n";
        $participant_fields = [
            'max_participants', 
            'current_participants', 
            'registration_count'
        ];
        
        $result = $connection->query("DESCRIBE lectures");
        $found_fields = [];
        while ($row = $result->fetch_assoc()) {
            if (in_array($row['Field'], $participant_fields)) {
                $found_fields[] = $row['Field'];
                echo "✓ " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
        }
        
        $missing_fields = array_diff($participant_fields, $found_fields);
        if (!empty($missing_fields)) {
            echo "\n누락된 참가자 관련 필드:\n";
            foreach ($missing_fields as $field) {
                echo "✗ " . $field . "\n";
            }
        }
        echo "\n";
    } else {
        echo "2. lectures 테이블이 존재하지 않습니다.\n\n";
    }
    
    // 3. 신청/등록 관련 테이블 확인
    echo "4. 신청/등록 관련 테이블 확인:\n";
    echo "=============================\n";
    $registration_tables = [
        'registrations',
        'applications', 
        'participants',
        'lecture_registrations',
        'event_registrations'
    ];
    
    $found_registration_tables = [];
    foreach ($registration_tables as $table) {
        if (in_array($table, $tables)) {
            $found_registration_tables[] = $table;
            echo "✓ " . $table . " 테이블 존재\n";
            
            // 테이블 구조 간단히 확인
            $result = $connection->query("DESCRIBE $table");
            while ($row = $result->fetch_assoc()) {
                echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
            echo "\n";
        }
    }
    
    if (empty($found_registration_tables)) {
        echo "신청/등록 관련 테이블이 없습니다.\n\n";
    }
    
    // 4. users 테이블 구조 확인
    if (in_array('users', $tables)) {
        echo "5. users 테이블 구조:\n";
        echo "====================\n";
        $result = $connection->query("DESCRIBE users");
        while ($row = $result->fetch_assoc()) {
            echo sprintf("- %-25s %-30s %-8s %-8s %s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key'], 
                $row['Default'] ?? 'NULL'
            );
        }
    } else {
        echo "5. users 테이블이 존재하지 않습니다.\n";
    }
    
} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>