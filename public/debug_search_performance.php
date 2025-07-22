<?php
/**
 * 웹 기반 검색 성능 디버깅 페이지
 * URL: https://www.topmktx.com/debug_search_performance.php
 */

define('SRC_PATH', dirname(__DIR__) . '/src');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/PerformanceDebugger.php';

// HTML 헤더
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>검색 성능 디버깅 - 탑마케팅</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: #f8f9fa; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #e9ecef; }
        .code { background: #e9ecef; padding: 10px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
        .performance-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 검색 성능 디버깅</h1>
        <p>현재 시간: <?= date('Y-m-d H:i:s') ?></p>

<?php
try {
    $db = Database::getInstance();
    echo '<div class="section"><h2 class="success">✅ 데이터베이스 연결 성공</h2></div>';
    
    // 1. 인덱스 상태 확인
    echo '<div class="section">';
    echo '<h2>📋 현재 인덱스 상태</h2>';
    
    $stmt = $db->prepare("
        SELECT 
            INDEX_NAME,
            COLUMN_NAME,
            INDEX_TYPE,
            CARDINALITY,
            SUB_PART
        FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'posts'
        ORDER BY INDEX_NAME, SEQ_IN_INDEX
    ");
    $stmt->execute();
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($indexes)) {
        echo '<p class="error">❌ posts 테이블에 인덱스가 없음</p>';
    } else {
        echo '<table>';
        echo '<tr><th>인덱스명</th><th>컬럼</th><th>타입</th><th>카디널리티</th><th>서브파트</th></tr>';
        foreach ($indexes as $index) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($index['INDEX_NAME']) . '</td>';
            echo '<td>' . htmlspecialchars($index['COLUMN_NAME']) . '</td>';
            echo '<td>' . htmlspecialchars($index['INDEX_TYPE']) . '</td>';
            echo '<td>' . number_format($index['CARDINALITY']) . '</td>';
            echo '<td>' . ($index['SUB_PART'] ? $index['SUB_PART'] : '-') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    echo '</div>';
    
    // 2. FULLTEXT 인덱스 확인
    echo '<div class="section">';
    echo '<h2>🔍 FULLTEXT 인덱스 확인</h2>';
    
    $fulltextExists = false;
    foreach ($indexes as $index) {
        if ($index['INDEX_TYPE'] === 'FULLTEXT') {
            $fulltextExists = true;
            echo '<p class="success">✅ FULLTEXT 인덱스 발견: ' . htmlspecialchars($index['INDEX_NAME']) . ' (' . htmlspecialchars($index['COLUMN_NAME']) . ')</p>';
        }
    }
    
    if (!$fulltextExists) {
        echo '<p class="error">❌ FULLTEXT 인덱스가 없습니다!</p>';
        echo '<p class="info">다음 SQL을 실행하여 FULLTEXT 인덱스를 생성하세요:</p>';
        echo '<div class="code">CREATE FULLTEXT INDEX idx_posts_fulltext_search ON posts (title, content);</div>';
    }
    echo '</div>';
    
    // 3. 테이블 통계
    echo '<div class="section">';
    echo '<h2>📊 테이블 통계</h2>';
    
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_rows,
            COUNT(CASE WHEN status = 'published' THEN 1 END) as published_count,
            AVG(CHAR_LENGTH(title)) as avg_title_length,
            AVG(CHAR_LENGTH(content)) as avg_content_length
        FROM posts
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>통계 항목</th><th>값</th></tr>';
    echo '<tr><td>총 게시글</td><td>' . number_format($stats['total_rows']) . '개</td></tr>';
    echo '<tr><td>발행된 게시글</td><td>' . number_format($stats['published_count']) . '개</td></tr>';
    echo '<tr><td>평균 제목 길이</td><td>' . round($stats['avg_title_length']) . '자</td></tr>';
    echo '<tr><td>평균 내용 길이</td><td>' . number_format(round($stats['avg_content_length'])) . '자</td></tr>';
    echo '</table>';
    echo '</div>';
    
    // 4. 검색 성능 테스트
    echo '<div class="section">';
    echo '<h2>⚡ 검색 성능 테스트</h2>';
    
    // 일반 목록 조회 테스트
    echo '<h3>1. 일반 목록 조회 (20개)</h3>';
    $sql = "
        SELECT p.id, p.title, LEFT(p.content, 200) as content_preview, p.created_at, u.nickname
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.status = 'published'
        ORDER BY p.created_at DESC 
        LIMIT 20
    ";
    
    $startTime = microtime(true);
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $executionTime = (microtime(true) - $startTime) * 1000;
    
    echo '<div class="performance-box">';
    echo '<strong>결과:</strong> ' . count($results) . '개 조회<br>';
    echo '<strong>실행시간:</strong> ' . round($executionTime, 2) . 'ms';
    echo '</div>';
    
    // EXPLAIN 분석
    echo '<h4>EXPLAIN 분석:</h4>';
    $explainSql = "EXPLAIN " . $sql;
    $stmt = $db->prepare($explainSql);
    $stmt->execute();
    $explain = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>테이블</th><th>타입</th><th>키</th><th>행수</th><th>Extra</th></tr>';
    foreach ($explain as $row) {
        $rowClass = '';
        if ($row['type'] === 'ALL') {
            $rowClass = 'style="background-color: #f8d7da;"'; // 빨간색 배경 (풀스캔 경고)
        }
        echo '<tr ' . $rowClass . '>';
        echo '<td>' . htmlspecialchars($row['table']) . '</td>';
        echo '<td>' . htmlspecialchars($row['type']) . '</td>';
        echo '<td>' . htmlspecialchars($row['key'] ?? 'NULL') . '</td>';
        echo '<td>' . number_format($row['rows']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Extra'] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // 검색 테스트
    if ($fulltextExists) {
        echo '<h3>2. FULLTEXT 검색 테스트 ("마케팅")</h3>';
        $searchSql = "
            SELECT p.id, p.title, LEFT(p.content, 200) as content_preview, p.created_at, u.nickname,
                   MATCH(p.title, p.content) AGAINST('마케팅' IN NATURAL LANGUAGE MODE) as relevance
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'published'
            AND MATCH(p.title, p.content) AGAINST('마케팅' IN NATURAL LANGUAGE MODE)
            ORDER BY relevance DESC
            LIMIT 20
        ";
        
        $startTime = microtime(true);
        $stmt = $db->prepare($searchSql);
        $stmt->execute();
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $searchTime = (microtime(true) - $startTime) * 1000;
        
        echo '<div class="performance-box">';
        echo '<strong>결과:</strong> ' . count($searchResults) . '개 검색 결과<br>';
        echo '<strong>실행시간:</strong> ' . round($searchTime, 2) . 'ms';
        echo '</div>';
        
        // EXPLAIN 분석
        echo '<h4>EXPLAIN 분석:</h4>';
        $explainSearchSql = "EXPLAIN " . $searchSql;
        $stmt = $db->prepare($explainSearchSql);
        $stmt->execute();
        $explainSearch = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<table>';
        echo '<tr><th>테이블</th><th>타입</th><th>키</th><th>행수</th><th>Extra</th></tr>';
        foreach ($explainSearch as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['table']) . '</td>';
            echo '<td>' . htmlspecialchars($row['type']) . '</td>';
            echo '<td>' . htmlspecialchars($row['key'] ?? 'NULL') . '</td>';
            echo '<td>' . number_format($row['rows']) . '</td>';
            echo '<td>' . htmlspecialchars($row['Extra'] ?? '') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
    } else {
        echo '<h3>2. LIKE 검색 테스트 ("마케팅") - FULLTEXT 인덱스 없음</h3>';
        $likeSql = "
            SELECT p.id, p.title, LEFT(p.content, 200) as content_preview, p.created_at, u.nickname
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'published'
            AND (p.title LIKE '%마케팅%' OR p.content LIKE '%마케팅%')
            ORDER BY p.created_at DESC
            LIMIT 20
        ";
        
        $startTime = microtime(true);
        $stmt = $db->prepare($likeSql);
        $stmt->execute();
        $likeResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $likeTime = (microtime(true) - $startTime) * 1000;
        
        echo '<div class="performance-box">';
        echo '<strong>결과:</strong> ' . count($likeResults) . '개 검색 결과<br>';
        echo '<strong>실행시간:</strong> ' . round($likeTime, 2) . 'ms';
        if ($likeTime > 1000) {
            echo ' <span class="error">⚠️ 매우 느림!</span>';
        }
        echo '</div>';
        
        // EXPLAIN 분석
        echo '<h4>EXPLAIN 분석:</h4>';
        $explainLikeSql = "EXPLAIN " . $likeSql;
        $stmt = $db->prepare($explainLikeSql);
        $stmt->execute();
        $explainLike = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<table>';
        echo '<tr><th>테이블</th><th>타입</th><th>키</th><th>행수</th><th>Extra</th></tr>';
        foreach ($explainLike as $row) {
            $rowClass = '';
            if ($row['type'] === 'ALL' || $row['rows'] > 1000) {
                $rowClass = 'style="background-color: #f8d7da;"'; // 빨간색 배경 (성능 경고)
            }
            echo '<tr ' . $rowClass . '>';
            echo '<td>' . htmlspecialchars($row['table']) . '</td>';
            echo '<td>' . htmlspecialchars($row['type']) . '</td>';
            echo '<td>' . htmlspecialchars($row['key'] ?? 'NULL') . '</td>';
            echo '<td>' . number_format($row['rows']) . '</td>';
            echo '<td>' . htmlspecialchars($row['Extra'] ?? '') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '</div>';
    
    // 5. 권장사항
    echo '<div class="section">';
    echo '<h2>💡 성능 개선 권장사항</h2>';
    
    if (!$fulltextExists) {
        echo '<div class="error">';
        echo '<h3>🚨 중요: FULLTEXT 인덱스 생성 필요</h3>';
        echo '<p>현재 LIKE 검색을 사용하고 있어 성능이 매우 느립니다.</p>';
        echo '<p><strong>즉시 실행하세요:</strong></p>';
        echo '<div class="code">SOURCE /var/www/html/topmkt/improve_search_performance.sql;</div>';
        echo '</div>';
    } else {
        echo '<p class="success">✅ FULLTEXT 인덱스가 설정되어 있습니다!</p>';
    }
    
    // 추가 최적화 권장사항
    echo '<h3>추가 최적화 방법:</h3>';
    echo '<ul>';
    echo '<li>검색 결과 캐싱 (현재 5분)</li>';
    echo '<li>검색어 로그 분석으로 인기 검색어 미리 캐싱</li>';
    echo '<li>Elasticsearch 도입 (대용량 데이터 시)</li>';
    echo '<li>검색 결과 페이지네이션 최적화</li>';
    echo '</ul>';
    
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="section error">';
    echo '<h2>❌ 오류 발생</h2>';
    echo '<p>오류 메시지: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>파일: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '</div>';
}
?>

        <div class="section">
            <h2>🔧 수동 실행 명령어</h2>
            <p>SSH로 서버 접속 후 다음 명령어들을 실행하세요:</p>
            
            <h3>1. DB 최적화 스크립트 실행:</h3>
            <div class="code">cd /var/www/html/topmkt
mysql -u root -p << 'EOF'
USE topmkt;
SOURCE improve_search_performance.sql;
EOF</div>

            <h3>2. 현재 검색 성능 확인:</h3>
            <div class="code">tail -f /var/log/httpd/error_log | grep "검색\|성능\|🔍"</div>

            <h3>3. 인덱스 상태 확인:</h3>
            <div class="code">mysql -u root -p -e "USE topmkt; SHOW INDEX FROM posts WHERE Key_name LIKE '%fulltext%';"</div>
        </div>

        <p><small>생성 시간: <?= date('Y-m-d H:i:s') ?> | 자동 새로고침: <span id="countdown">30</span>초</small></p>
    </div>

    <script>
        // 30초마다 자동 새로고침
        let countdown = 30;
        setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            if (countdown <= 0) {
                location.reload();
            }
        }, 1000);
    </script>
</body>
</html>