<?php
/**
 * 🎯 최종 강의 페이지 테스트
 * 모든 수정사항 적용 후 최종 확인
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<h1 style='color:#0f0;font-family:monospace;'>🎯 최종 강의 페이지 테스트</h1>";
echo "<div style='font-family:monospace;background:#000;color:#0f0;padding:20px;'>";

echo "<h2>✅ 수정 완료 사항:</h2>";
echo "<ul>";
echo "<li>AuthMiddleware::getUserRole() 메소드 추가</li>";
echo "<li>APP_NAME 상수 → '탑마케팅' 직접 대체</li>";
echo "<li>title 변수 처리 개선</li>";
echo "</ul>";

echo "<h2>🧪 최종 테스트 실행:</h2>";

// 실제 강의 페이지로 리다이렉트
header('Location: /lectures');
exit;
?>