<?php
echo "<h1>DocumentRoot 확인</h1>";
echo "<p><strong>__FILE__:</strong> " . __FILE__ . "</p>";
echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>SCRIPT_FILENAME:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>PHP_SELF:</strong> " . $_SERVER['PHP_SELF'] . "</p>";

echo "<h2>PHP 파일 경로 테스트</h2>";
$indexPath = __DIR__ . '/index.php';
echo "<p><strong>index.php 존재:</strong> " . (file_exists($indexPath) ? '✅ 예' : '❌ 아니오') . "</p>";
echo "<p><strong>index.php 경로:</strong> $indexPath</p>";

echo "<h2>라우팅 테스트</h2>";
echo "<p><a href='/auth/forgot-password'>forgot-password 페이지 테스트</a></p>";
?>