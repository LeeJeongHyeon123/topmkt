<?php
/**
 * 검색 성능 진단 스크립트
 * 실행: php diagnose_search_performance.php
 */

// 기본 설정
define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/PerformanceDebugger.php';

echo "🔍 검색 성능 진단 시작...\n\n";

try {
    $db = Database::getInstance();
    
    // 1. 기본 DB 연결 확인
    echo "1. 데이터베이스 연결 확인\n";
    echo "   ✅ 연결 성공\n\n";
    
    // 2. 테이블 존재 확인
    echo "2. 테이블 존재 확인\n";
    $tables = ['posts', 'users'];
    foreach ($tables as $table) {
        $stmt = $db->prepare("SHOW TABLES LIKE '$table'");
        $stmt->execute();
        if ($stmt->fetch()) {
            echo "   ✅ {$table} 테이블 존재\n";
        } else {
            echo "   ❌ {$table} 테이블 없음\n";
        }
    }
    echo "\n";
    
    // 3. posts 테이블 구조 확인
    echo "3. posts 테이블 구조 확인\n";
    $stmt = $db->prepare("DESCRIBE posts");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "   - {$col['Field']}: {$col['Type']}\n";
    }
    echo "\n";
    
    // 4. 인덱스 상태 확인
    echo "4. 인덱스 상태 확인\n";
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
        echo "   ❌ posts 테이블에 인덱스가 없음\n";
    } else {
        $indexGroups = [];
        foreach ($indexes as $index) {
            $indexGroups[$index['INDEX_NAME']][] = $index;
        }
        
        foreach ($indexGroups as $indexName => $columns) {
            echo "   📋 {$indexName} ({$columns[0]['INDEX_TYPE']})\n";
            foreach ($columns as $col) {
                $subpart = $col['SUB_PART'] ? "({$col['SUB_PART']})" : '';
                echo "      - {$col['COLUMN_NAME']}{$subpart} (카디널리티: {$col['CARDINALITY']})\n";
            }
        }
    }
    echo "\n";
    
    // 5. FULLTEXT 인덱스 확인
    echo "5. FULLTEXT 인덱스 확인\n";
    $stmt = $db->prepare("
        SELECT 
            INDEX_NAME,
            COLUMN_NAME,
            INDEX_TYPE,
            CARDINALITY
        FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'posts'
          AND INDEX_TYPE = 'FULLTEXT'
        ORDER BY INDEX_NAME, SEQ_IN_INDEX
    ");
    $stmt->execute();
    $fulltextIndexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($fulltextIndexes)) {
        echo "   ❌ FULLTEXT 인덱스 없음\n";
        echo "   💡 FULLTEXT 인덱스 생성 필요: CREATE FULLTEXT INDEX idx_posts_fulltext_search ON posts (title, content)\n";
    } else {
        foreach ($fulltextIndexes as $index) {
            echo "   ✅ {$index['INDEX_NAME']}: {$index['COLUMN_NAME']} (카디널리티: {$index['CARDINALITY']})\n";
        }
    }
    echo "\n";
    
    // 6. 테이블 통계 확인
    echo "6. 테이블 통계 확인\n";
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_rows,
            AVG(CHAR_LENGTH(title)) as avg_title_length,
            AVG(CHAR_LENGTH(content)) as avg_content_length,
            COUNT(CASE WHEN status = 'published' THEN 1 END) as published_count
        FROM posts
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 총 게시글: " . number_format($stats['total_rows']) . "개\n";
    echo "   📊 발행된 게시글: " . number_format($stats['published_count']) . "개\n";
    echo "   📊 평균 제목 길이: " . round($stats['avg_title_length']) . "자\n";
    echo "   📊 평균 내용 길이: " . number_format(round($stats['avg_content_length'])) . "자\n";
    echo "\n";
    
    // 7. 검색 쿼리 성능 테스트
    echo "7. 검색 쿼리 성능 테스트\n";
    
    // 일반 목록 조회 테스트
    echo "   📈 일반 목록 조회 테스트...\n";
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
    echo "      결과: " . count($results) . "개, 실행시간: " . round($executionTime, 2) . "ms\n";
    
    // EXPLAIN 분석
    $explainSql = "EXPLAIN " . $sql;
    $stmt = $db->prepare($explainSql);
    $stmt->execute();
    $explain = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($explain as $row) {
        $key = $row['key'] ?? 'NULL';
        echo "      EXPLAIN: table={$row['table']}, type={$row['type']}, key={$key}, rows={$row['rows']}\n";
    }
    
    // FULLTEXT 검색 테스트 (인덱스가 있는 경우)
    if (!empty($fulltextIndexes)) {
        echo "   🔍 FULLTEXT 검색 테스트 ('마케팅')...\n";
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
        echo "      결과: " . count($searchResults) . "개, 실행시간: " . round($searchTime, 2) . "ms\n";
        
        // EXPLAIN 분석
        $explainSearchSql = "EXPLAIN " . $searchSql;
        $stmt = $db->prepare($explainSearchSql);
        $stmt->execute();
        $explainSearch = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($explainSearch as $row) {
            $key = $row['key'] ?? 'NULL';
            echo "      EXPLAIN: table={$row['table']}, type={$row['type']}, key={$key}, rows={$row['rows']}\n";
        }
    } else {
        echo "   ⚠️ FULLTEXT 인덱스가 없어 FULLTEXT 검색 테스트 건너뜀\n";
        
        // 기존 LIKE 검색 테스트
        echo "   🐌 LIKE 검색 테스트 ('마케팅')...\n";
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
        echo "      결과: " . count($likeResults) . "개, 실행시간: " . round($likeTime, 2) . "ms\n";
        
        // EXPLAIN 분석
        $explainLikeSql = "EXPLAIN " . $likeSql;
        $stmt = $db->prepare($explainLikeSql);
        $stmt->execute();
        $explainLike = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($explainLike as $row) {
            $key = $row['key'] ?? 'NULL';
            echo "      EXPLAIN: table={$row['table']}, type={$row['type']}, key={$key}, rows={$row['rows']}\n";
        }
    }
    echo "\n";
    
    // 8. 권장사항
    echo "8. 성능 개선 권장사항\n";
    
    $recommendations = [];
    
    if (empty($fulltextIndexes)) {
        $recommendations[] = "FULLTEXT 인덱스 생성 (검색 성능 대폭 향상)";
        echo "   💡 다음 SQL을 실행하세요:\n";
        echo "      CREATE FULLTEXT INDEX idx_posts_fulltext_search ON posts (title, content);\n";
    }
    
    $hasListIndex = false;
    foreach ($indexGroups as $indexName => $columns) {
        if ($indexName === 'idx_posts_list_performance') {
            $hasListIndex = true;
            break;
        }
    }
    
    if (!$hasListIndex) {
        $recommendations[] = "목록 조회용 복합 인덱스 생성";
        echo "   💡 다음 SQL을 실행하세요:\n";
        echo "      CREATE INDEX idx_posts_list_performance ON posts (status, created_at DESC);\n";
    }
    
    if ($stats['total_rows'] > 10000 && empty($fulltextIndexes)) {
        $recommendations[] = "대용량 데이터를 위한 FULLTEXT 검색 필수";
    }
    
    if (empty($recommendations)) {
        echo "   ✅ 모든 권장 인덱스가 설정되어 있습니다!\n";
    }
    
    echo "\n🎉 진단 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>