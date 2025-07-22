<?php
// 간단한 PHP 정보 확인
echo "<h1>PHP 정보</h1>";
echo "<h2>PHP 버전</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";

echo "<h2>MySQLi 확장 확인</h2>";
if (extension_loaded('mysqli')) {
    echo "✅ MySQLi 확장: 설치됨<br>";
} else {
    echo "❌ MySQLi 확장: 설치되지 않음<br>";
}

echo "<h2>설치된 확장 목록</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    if (stripos($ext, 'mysql') !== false || stripos($ext, 'curl') !== false) {
        echo "<strong>" . $ext . "</strong><br>";
    } else {
        echo $ext . "<br>";
    }
}

echo "<h2>MySQLi 클래스 확인</h2>";
if (class_exists('mysqli')) {
    echo "✅ MySQLi 클래스: 사용 가능<br>";
} else {
    echo "❌ MySQLi 클래스: 사용 불가<br>";
}

echo "<h2>PHP 설정 파일</h2>";
echo "Loaded php.ini: " . php_ini_loaded_file() . "<br>";
$additional = php_ini_scanned_files();
if ($additional) {
    echo "Additional ini files: " . $additional . "<br>";
}
?>