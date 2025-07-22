<?php
// 간단한 데이터베이스 연결 테스트 - 다양한 방법 시도

$configs = [
    'TCP localhost' => 'mysql:host=localhost;port=3306;dbname=topmkt;charset=utf8mb4',
    'TCP 127.0.0.1' => 'mysql:host=127.0.0.1;port=3306;dbname=topmkt;charset=utf8mb4',
    'Socket /var/lib/mysql/mysql.sock' => 'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=topmkt;charset=utf8mb4',
    'Socket /run/mysqld/mysqld.sock' => 'mysql:unix_socket=/run/mysqld/mysqld.sock;dbname=topmkt;charset=utf8mb4',
    'Socket /tmp/mysql.sock' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=topmkt;charset=utf8mb4',
];

$username = 'root';
$password = 'Dnlszkem1!';

foreach ($configs as $name => $dsn) {
    echo "🔍 시도: $name\n";
    echo "   DSN: $dsn\n";
    
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "✅ 성공! 연결되었습니다.\n";
        
        // 간단한 쿼리 테스트
        $result = $pdo->query("SELECT COUNT(*) as count FROM lectures")->fetch();
        echo "📊 lectures 테이블 총 레코드 수: " . $result['count'] . "\n";
        
        $pdo = null;
        break; // 성공하면 종료
        
    } catch (Exception $e) {
        echo "❌ 실패: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}
?>