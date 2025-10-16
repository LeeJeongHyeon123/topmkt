<?php
echo "현재 PHP 업로드 설정:<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";

// 바이트 단위로 변환
function convertToBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

echo "<br>바이트 단위로 변환:<br>";
echo "upload_max_filesize: " . number_format(convertToBytes(ini_get('upload_max_filesize'))) . " bytes<br>";
echo "post_max_size: " . number_format(convertToBytes(ini_get('post_max_size'))) . " bytes<br>";
?>