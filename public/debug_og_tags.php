<?php
/**
 * OG 태그 디버깅 스크립트
 * EventController의 OG 태그 생성 로직을 직접 테스트
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/controllers/EventController.php';

echo "<h1>🔍 OG 태그 디버깅 - 행사 122번</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 행사 122번 데이터 조회
    $sql = "SELECT 
                id, title, description, instructor_name, instructor_info,
                start_date, end_date, start_time, end_time,
                location_type, venue_name, venue_address, online_link,
                max_participants, registration_fee, category, status,
                content_type, event_scale, has_networking, sponsor_info,
                dress_code, parking_info, created_at, user_id, instructor_image, youtube_video
            FROM lectures 
            WHERE id = 122 AND content_type = 'event' AND status = 'published'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        echo "<p>❌ 행사 122번을 찾을 수 없습니다.</p>\n";
        exit;
    }
    
    echo "<h2>📊 원본 행사 데이터</h2>\n";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>제목:</strong> " . htmlspecialchars($event['title']) . "<br>\n";
    echo "<strong>설명 (원본):</strong><br>\n";
    echo "<pre style='background: white; padding: 10px; border-radius: 3px; font-size: 12px; max-height: 200px; overflow-y: auto;'>" . htmlspecialchars($event['description']) . "</pre>\n";
    echo "</div>\n";
    
    // OG 태그 생성 로직 테스트
    function generateCleanDescription($description) {
        // 1. Markdown 문법 제거
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $description); // **볼드** 제거
        $text = preg_replace('/\*(.*?)\*/', '$1', $text); // *이탤릭* 제거
        $text = preg_replace('/#{1,6}\s/', '', $text); // # 헤더 제거
        $text = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $text); // [링크](url) 제거
        $text = preg_replace('/```.*?```/s', '', $text); // 코드 블록 제거
        $text = preg_replace('/`(.*?)`/', '$1', $text); // 인라인 코드 제거
        
        // 2. 이모지와 특수 문자 정리
        $text = preg_replace('/[🎯💼🎁🤝📍⭐🔥💡📊🚀]+/', '', $text); // 이모지 제거
        $text = preg_replace('/•\s*/', '- ', $text); // 불릿 포인트 정리
        
        // 3. HTML 태그 제거
        $text = strip_tags($text);
        
        // 4. 연속된 공백과 줄바꿈 정리
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // 5. 첫 번째 문장만 추출하여 깔끔하게
        $sentences = preg_split('/[.!?]\s+/', $text);
        $firstSentence = trim($sentences[0]);
        
        // 6. 길이 제한 (160자)
        if (mb_strlen($firstSentence) > 160) {
            $firstSentence = mb_substr($firstSentence, 0, 157) . '...';
        }
        
        return $firstSentence;
    }
    
    $cleanDescription = generateCleanDescription($event['description']);
    
    echo "<h2>🧹 정제된 설명</h2>\n";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>정제된 설명:</strong><br>\n";
    echo "<code style='background: white; padding: 5px; border-radius: 3px;'>" . htmlspecialchars($cleanDescription) . "</code><br>\n";
    echo "<strong>길이:</strong> " . mb_strlen($cleanDescription) . "자\n";
    echo "</div>\n";
    
    // OG 태그 시뮬레이션
    $ogTitle = $event['title'] . ' - 탑마케팅 행사';
    $ogDescription = $cleanDescription;
    $ogImage = 'https://www.topmktx.com/assets/images/topmkt-og-image.png?v=' . date('Ymd');
    $ogUrl = 'https://www.topmktx.com/events/detail?id=122';
    
    echo "<h2>🏷️ 생성될 OG 태그</h2>\n";
    echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<pre style='background: white; padding: 10px; border-radius: 3px; font-size: 12px;'>";
    echo htmlspecialchars('<meta property="og:type" content="article">') . "\n";
    echo htmlspecialchars('<meta property="og:url" content="' . $ogUrl . '">') . "\n";
    echo htmlspecialchars('<meta property="og:title" content="' . $ogTitle . '">') . "\n";
    echo htmlspecialchars('<meta property="og:description" content="' . $ogDescription . '">') . "\n";
    echo htmlspecialchars('<meta property="og:image" content="' . $ogImage . '">') . "\n";
    echo htmlspecialchars('<meta property="og:site_name" content="탑마케팅">') . "\n";
    echo "</pre>\n";
    echo "</div>\n";
    
    echo "<h2>✅ 검증 결과</h2>\n";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<ul>\n";
    echo "<li><strong>제목 길이:</strong> " . mb_strlen($ogTitle) . "자 " . (mb_strlen($ogTitle) <= 60 ? "✅" : "⚠️ (60자 권장)") . "</li>\n";
    echo "<li><strong>설명 길이:</strong> " . mb_strlen($ogDescription) . "자 " . (mb_strlen($ogDescription) <= 160 ? "✅" : "⚠️ (160자 권장)") . "</li>\n";
    echo "<li><strong>HTML 태그 포함:</strong> " . (strpos($ogDescription, '<') !== false || strpos($ogDescription, '>') !== false ? "❌ 발견됨" : "✅ 없음") . "</li>\n";
    echo "<li><strong>Markdown 문법:</strong> " . (strpos($ogDescription, '**') !== false || strpos($ogDescription, '•') !== false ? "❌ 발견됨" : "✅ 정리됨") . "</li>\n";
    echo "<li><strong>이모지:</strong> " . (preg_match('/[🎯💼🎁🤝📍⭐🔥💡📊🚀]/', $ogDescription) ? "❌ 발견됨" : "✅ 제거됨") . "</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<h2>🔄 카카오 공유 디버거 테스트</h2>\n";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<p><strong>1단계:</strong> <a href='https://developers.kakao.com/tool/debugger/sharing' target='_blank'>카카오 공유 디버거</a>에 접속</p>\n";
    echo "<p><strong>2단계:</strong> 다음 URL 입력:</p>\n";
    echo "<code style='background: white; padding: 8px; border-radius: 3px; display: block; margin: 5px 0;'>{$ogUrl}</code>\n";
    echo "<p><strong>3단계:</strong> '디버그' 버튼 클릭하여 파싱 결과 확인</p>\n";
    echo "<p><strong>4단계:</strong> 문제가 있다면 '캐시 초기화' 버튼 클릭</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<p>❌ 오류: " . $e->getMessage() . "</p>\n";
    error_log("debug_og_tags.php 오류: " . $e->getMessage());
}
?>