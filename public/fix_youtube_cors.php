<?php
/**
 * YouTube CORS 오류 해결
 * - Privacy-Enhanced Mode 사용 (youtube-nocookie.com)
 * - 광고 및 트래킹 관련 파라미터 추가
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>🎬 YouTube CORS 오류 해결</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<h2>1. 현재 YouTube URL 확인</h2>\n";
    
    // 현재 YouTube URL 조회
    $currentSql = "SELECT youtube_video FROM lectures WHERE id = 122 AND content_type = 'event'";
    $currentUrl = $pdo->query($currentSql)->fetchColumn();
    
    echo "<p>현재 URL: <code>{$currentUrl}</code></p>\n";
    
    if (empty($currentUrl)) {
        echo "<p>❌ YouTube URL이 설정되지 않았습니다.</p>\n";
        exit;
    }
    
    echo "<h2>2. Privacy-Enhanced Mode로 변경</h2>\n";
    
    // YouTube URL을 Privacy-Enhanced Mode로 변경
    $originalVideoId = 'xIBjDGPDPw0'; // 기존 비디오 ID
    
    // Privacy-Enhanced Mode URL with anti-tracking parameters
    $newYouTubeUrl = "https://www.youtube-nocookie.com/embed/{$originalVideoId}?" . http_build_query([
        'rel' => '0',           // 관련 동영상 비활성화
        'modestbranding' => '1', // YouTube 로고 최소화
        'controls' => '1',       // 플레이어 컨트롤 표시
        'showinfo' => '0',       // 동영상 정보 비활성화
        'iv_load_policy' => '3', // 주석 비활성화
        'disablekb' => '0',      // 키보드 단축키 활성화
        'fs' => '1',             // 전체화면 버튼 활성화
        'cc_load_policy' => '0', // 자막 기본 비활성화
        'hl' => 'ko',            // 언어 설정
        'origin' => 'https://www.topmktx.com', // Origin 명시
        'enablejsapi' => '0',    // JavaScript API 비활성화 (CORS 방지)
        'playsinline' => '1'     // 인라인 재생
    ]);
    
    echo "<p>새 URL: <code>{$newYouTubeUrl}</code></p>\n";
    
    echo "<h2>3. 데이터베이스 업데이트</h2>\n";
    
    // URL 업데이트
    $updateSql = "UPDATE lectures SET youtube_video = ? WHERE id = 122 AND content_type = 'event'";
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([$newYouTubeUrl]);
    
    if ($result) {
        echo "<p>✅ YouTube URL이 Privacy-Enhanced Mode로 업데이트되었습니다.</p>\n";
    } else {
        echo "<p>❌ 업데이트 실패</p>\n";
        exit;
    }
    
    echo "<h2>4. 변경사항 확인</h2>\n";
    
    // 업데이트 확인
    $verifyUrl = $pdo->query($currentSql)->fetchColumn();
    echo "<p>업데이트된 URL: <code>{$verifyUrl}</code></p>\n";
    
    echo "<h2>🎉 완료!</h2>\n";
    echo "<p><strong>적용된 개선사항:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ Privacy-Enhanced Mode (youtube-nocookie.com) 사용</li>\n";
    echo "<li>✅ 관련 동영상 표시 비활성화</li>\n";
    echo "<li>✅ YouTube 브랜딩 최소화</li>\n";
    echo "<li>✅ 광고 트래킹 감소</li>\n";
    echo "<li>✅ JavaScript API 비활성화로 CORS 방지</li>\n";
    echo "<li>✅ Origin 명시로 보안 강화</li>\n";
    echo "</ul>\n";
    
    echo "<p><strong>예상 효과:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>🚫 Google DoubleClick 광고 요청 감소</li>\n";
    echo "<li>🚫 CORS 오류 최소화</li>\n";
    echo "<li>⚡ 더 빠른 로딩 속도</li>\n";
    echo "<li>🔒 개인정보 보호 강화</li>\n";
    echo "</ul>\n";
    
    echo "<p><a href='/events/detail?id=122' style='background:#4A90E2;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;'>➡️ 수정된 동영상 확인하기</a></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ 오류: " . $e->getMessage() . "</p>\n";
    error_log("fix_youtube_cors.php 오류: " . $e->getMessage());
}
?>