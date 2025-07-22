<?php
// 웹서버를 통한 강의 생성 페이지 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>강의 생성 페이지 테스트</h2>";

// PHP 확장 확인
echo "<h3>1. PHP 확장 확인</h3>";
echo "MySQLi: " . (extension_loaded('mysqli') ? '✅ 설치됨' : '❌ 없음') . "<br>";
echo "PDO: " . (extension_loaded('pdo') ? '✅ 설치됨' : '❌ 없음') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ 설치됨' : '❌ 없음') . "<br>";

// 데이터베이스 연결 테스트
echo "<h3>2. 데이터베이스 연결 테스트</h3>";

// MySQLi 방식
if (extension_loaded('mysqli')) {
    try {
        $mysqli = new mysqli('127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT');
        if ($mysqli->connect_error) {
            echo "MySQLi 연결 실패: " . $mysqli->connect_error . "<br>";
        } else {
            echo "✅ MySQLi 연결 성공<br>";
            $mysqli->close();
        }
    } catch (Exception $e) {
        echo "MySQLi 오류: " . $e->getMessage() . "<br>";
    }
}

// PDO 방식
if (extension_loaded('pdo_mysql')) {
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=topmkt', 'root', 'Dnlszkem1!');
        echo "✅ PDO 연결 성공<br>";
    } catch (Exception $e) {
        echo "PDO 오류: " . $e->getMessage() . "<br>";
    }
}

// 라우팅 테스트
echo "<h3>3. 라우팅 테스트</h3>";
echo "<a href='/lectures/create' target='_blank'>강의 생성 페이지 열기</a><br>";

// 현재 시간
echo "<h3>4. 현재 시간</h3>";
echo "현재 시간: " . date('Y-m-d H:i:s') . "<br>";

// PHP 정보
echo "<h3>5. PHP 정보</h3>";
echo "PHP 버전: " . phpversion() . "<br>";
echo "웹서버: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "<br>";
?>