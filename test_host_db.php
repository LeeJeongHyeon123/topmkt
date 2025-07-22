<?php
// Docker 환경에서 호스트 DB 연결 시도

$host_ips = ['172.17.0.1', 'host.docker.internal', '172.18.0.1'];
$username = 'root';
$password = 'Dnlszkem1!';

foreach ($host_ips as $host) {
    echo "🔍 시도: $host\n";
    
    try {
        $dsn = "mysql:host=$host;port=3306;dbname=topmkt;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        echo "✅ 성공! $host 로 연결되었습니다.\n";
        
        // 간단한 쿼리 테스트
        $result = $pdo->query("SELECT COUNT(*) as count FROM lectures")->fetch();
        echo "📊 lectures 테이블 총 레코드 수: " . $result['count'] . "\n";
        
        // 최근 강의 조회
        $recent = $pdo->query("SELECT id, title, status, created_at FROM lectures ORDER BY created_at DESC LIMIT 3")->fetchAll();
        echo "\n📝 최근 강의 3개:\n";
        foreach ($recent as $lecture) {
            echo "  [" . $lecture['id'] . "] " . $lecture['title'] . " (" . $lecture['status'] . ")\n";
        }
        
        // 임시저장된 강의 조회
        $drafts = $pdo->query("SELECT id, title, user_id FROM lectures WHERE status = 'draft'")->fetchAll();
        echo "\n💾 임시저장된 강의:\n";
        if (empty($drafts)) {
            echo "  - 임시저장된 강의가 없습니다.\n";
        } else {
            foreach ($drafts as $draft) {
                echo "  [" . $draft['id'] . "] " . $draft['title'] . " (사용자: " . $draft['user_id'] . ")\n";
            }
        }
        
        $pdo = null;
        echo "\n✅ 데이터베이스 조회 완료!\n";
        break;
        
    } catch (Exception $e) {
        echo "❌ 실패: " . $e->getMessage() . "\n\n";
    }
}
?>