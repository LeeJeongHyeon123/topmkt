<?php
echo "데이터베이스 연결 테스트\n";

// MySQLi 확장 확인
if (!extension_loaded('mysqli')) {
    echo "❌ MySQLi 확장이 로드되지 않음\n";
    exit;
}
echo "✅ MySQLi 확장 로드됨\n";

// 다양한 연결 시도
$connections = [
    ['localhost', 'root', '', 'TOPMKT'],
    ['localhost', 'topmkt', '', 'TOPMKT'],
    ['127.0.0.1', 'root', '', 'TOPMKT'],
    ['localhost', 'root', 'root', 'TOPMKT'],
    ['localhost', 'topmkt', 'topmkt', 'TOPMKT']
];

foreach ($connections as $i => $conn) {
    list($host, $user, $pass, $db) = $conn;
    echo "\n" . ($i+1) . ". 시도: $user@$host -> $db\n";
    
    $mysqli = @new mysqli($host, $user, $pass, $db);
    
    if ($mysqli->connect_error) {
        echo "❌ 실패: " . $mysqli->connect_error . "\n";
    } else {
        echo "✅ 성공!\n";
        echo "서버 정보: " . $mysqli->server_info . "\n";
        
        // 테이블 확인
        $result = $mysqli->query("SHOW TABLES LIKE 'lecture_registrations'");
        if ($result && $result->num_rows > 0) {
            echo "✅ lecture_registrations 테이블 존재\n";
        } else {
            echo "❌ lecture_registrations 테이블 없음\n";
        }
        
        $mysqli->close();
        break;
    }
}

// MySQL 프로세스 확인
echo "\n=== MySQL 프로세스 확인 ===\n";
$processes = shell_exec('ps aux | grep mysql | grep -v grep');
if ($processes) {
    echo "✅ MySQL 프로세스 실행 중:\n" . $processes;
} else {
    echo "❌ MySQL 프로세스 없음\n";
}

// 소켓 파일 확인
echo "\n=== 소켓 파일 확인 ===\n";
$socket_paths = [
    '/var/lib/mysql/mysql.sock',
    '/tmp/mysql.sock',
    '/var/run/mysqld/mysqld.sock'
];

foreach ($socket_paths as $socket) {
    if (file_exists($socket)) {
        echo "✅ 소켓 파일 존재: $socket\n";
    } else {
        echo "❌ 소켓 파일 없음: $socket\n";
    }
}
?>