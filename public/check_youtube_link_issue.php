<?php
/**
 * 유튜브 링크 저장 문제 조사
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>유튜브 링크 저장 문제 조사</h1>";

try {
    $db = Database::getInstance();
    $eventId = 194;
    
    // 1. 행사 194 현재 상태 확인
    echo "<h2>📋 행사 194 현재 상태</h2>";
    $event = $db->fetch("SELECT * FROM lectures WHERE id = ? AND content_type = 'event'", [$eventId]);
    
    if ($event) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>필드</th><th>값</th></tr>";
        
        $youtubeFields = [
            'id' => 'ID',
            'title' => '제목',
            'youtube_link' => '유튜브 링크 (youtube_link)',
            'youtube_url' => '유튜브 URL (youtube_url)',  
            'youtube_video' => '유튜브 비디오 (youtube_video)',
            'online_link' => '온라인 링크',
            'description' => '설명 (처음 100자)',
            'updated_at' => '최종 수정일'
        ];
        
        foreach ($youtubeFields as $field => $label) {
            $value = $event[$field] ?? '';
            if ($field === 'description' && $value) {
                $value = substr($value, 0, 100) . '...';
            }
            echo "<tr>";
            echo "<td><strong>" . $label . "</strong></td>";
            echo "<td>" . ($value ? htmlspecialchars($value) : "<span style='color: #999;'>없음</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>행사 194를 찾을 수 없습니다.</p>";
        exit;
    }
    
    // 2. 데이터베이스 테이블 구조 확인
    echo "<h2>🗃️ lectures 테이블 유튜브 관련 컬럼 확인</h2>";
    $columns = $db->fetchAll("DESCRIBE lectures");
    
    $youtubeColumns = array_filter($columns, function($col) {
        return stripos($col['Field'], 'youtube') !== false;
    });
    
    if (count($youtubeColumns) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>컬럼명</th><th>타입</th><th>NULL 허용</th><th>기본값</th></tr>";
        
        foreach ($youtubeColumns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>테이블에 youtube 관련 컬럼이 없습니다.</p>";
    }
    
    // 3. HTML 폼 필드명 확인
    echo "<h2>📝 HTML 폼 필드명 확인</h2>";
    $createPhpPath = SRC_PATH . '/views/events/create.php';
    if (file_exists($createPhpPath)) {
        $content = file_get_contents($createPhpPath);
        
        // 유튜브 관련 필드 추출
        preg_match_all('/name=["\']([^"\']*youtube[^"\']*)["\']/', $content, $matches);
        $youtubeFieldNames = array_unique($matches[1]);
        
        preg_match_all('/id=["\']([^"\']*youtube[^"\']*)["\']/', $content, $idMatches);
        $youtubeFieldIds = array_unique($idMatches[1]);
        
        echo "<h3>폼에서 발견된 유튜브 관련 필드:</h3>";
        echo "<ul>";
        foreach ($youtubeFieldNames as $fieldName) {
            echo "<li><strong>name:</strong> " . htmlspecialchars($fieldName) . "</li>";
        }
        foreach ($youtubeFieldIds as $fieldId) {
            echo "<li><strong>id:</strong> " . htmlspecialchars($fieldId) . "</li>";
        }
        echo "</ul>";
        
        // 실제 HTML 코드 추출
        if (preg_match('/<input[^>]*youtube[^>]*>/i', $content, $htmlMatch)) {
            echo "<h3>실제 HTML 코드:</h3>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>";
            echo htmlspecialchars($htmlMatch[0]);
            echo "</pre>";
        }
    }
    
    // 4. EventController 업데이트 로직 확인
    echo "<h2>🔧 EventController 업데이트 로직 확인</h2>";
    $controllerPath = SRC_PATH . '/controllers/EventController.php';
    if (file_exists($controllerPath)) {
        $controllerContent = file_get_contents($controllerPath);
        
        // updateEvent 메소드 찾기
        if (preg_match('/function updateEvent\([^}]+\}/s', $controllerContent, $updateMatch)) {
            echo "<h3>updateEvent 메소드 (처음 500자):</h3>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;'>";
            echo htmlspecialchars(substr($updateMatch[0], 0, 500));
            echo "...</pre>";
        }
        
        // 유튜브 관련 처리 코드 찾기
        preg_match_all('/youtube[^;]*;/', $controllerContent, $youtubeMatches);
        if (count($youtubeMatches[0]) > 0) {
            echo "<h3>유튜브 관련 처리 코드:</h3>";
            echo "<ul>";
            foreach ($youtubeMatches[0] as $match) {
                echo "<li><code>" . htmlspecialchars($match) . "</code></li>";
            }
            echo "</ul>";
        }
    }
    
    // 5. 필드명 매핑 분석
    echo "<h2>🔍 필드명 매핑 분석</h2>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>발견된 불일치:</h3>";
    echo "<ul>";
    
    if (in_array('youtube_video', $youtubeFieldNames)) {
        echo "<li><strong>HTML 필드명:</strong> youtube_video</li>";
    }
    
    echo "<li><strong>데이터베이스 컬럼:</strong> ";
    if (count($youtubeColumns) > 0) {
        foreach ($youtubeColumns as $col) {
            echo $col['Field'] . " ";
        }
    } else {
        echo "없음";
    }
    echo "</li>";
    
    echo "<li><strong>PHP 코드에서 참조:</strong> 확인 필요</li>";
    echo "</ul>";
    echo "</div>";
    
    // 6. 해결 방안 제시
    echo "<h2>💡 해결 방안</h2>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>권장 해결 방법:</h3>";
    echo "<ol>";
    echo "<li><strong>필드명 통일:</strong> HTML과 DB 컬럼명을 일치시키기</li>";
    echo "<li><strong>업데이트 로직 확인:</strong> POST 데이터에서 올바른 필드명 사용</li>";
    echo "<li><strong>SQL 쿼리 확인:</strong> UPDATE 문에 유튜브 필드 포함 여부</li>";
    echo "<li><strong>유효성 검사:</strong> 유튜브 URL 형식 검증</li>";
    echo "</ol>";
    echo "</div>";
    
    // 7. 테스트 시나리오
    echo "<h2>🧪 테스트 시나리오</h2>";
    
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>테스트 방법:</h3>";
    echo "<ol>";
    echo "<li>행사 194 편집 페이지 접속</li>";
    echo "<li>유튜브 링크 필드에 테스트 URL 입력: <code>https://www.youtube.com/watch?v=test123</code></li>";
    echo "<li>저장 후 데이터베이스 확인</li>";
    echo "<li>페이지 새로고침하여 값이 유지되는지 확인</li>";
    echo "</ol>";
    echo "</div>";
    
    // 8. 디버깅 정보
    echo "<h2>🐛 디버깅 정보</h2>";
    echo "<div style='background: #fee2e2; border: 1px solid #dc2626; border-radius: 6px; padding: 15px; margin: 20px 0;'>";
    echo "<h3>확인할 로그:</h3>";
    echo "<ul>";
    echo "<li>수정 시 POST 데이터 로그</li>";
    echo "<li>UPDATE SQL 쿼리 로그</li>";
    echo "<li>필드 매핑 관련 오류 메시지</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>