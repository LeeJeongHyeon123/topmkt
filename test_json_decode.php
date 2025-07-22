<?php
// 실제 데이터베이스에서 가져온 JSON 문자열 테스트
$jsonString = '[{"original_name":"고화질.jpg","file_name":"1750255023_0__________.jpg","file_path":"\/assets\/uploads\/lectures\/1750255023_0__________.jpg","file_size":494803,"upload_time":"2025-06-18 22:57:03"},{"original_name":"메일택.png","file_name":"1750255023_1__________.png","file_path":"\/assets\/uploads\/lectures\/1750255023_1__________.png","file_size":19548,"upload_time":"2025-06-18 22:57:03"}]';

echo "원본 JSON 문자열:\n";
echo $jsonString . "\n\n";

// JSON 디코드 테스트
$imagesData = json_decode($jsonString, true);

echo "JSON 디코드 결과:\n";
if ($imagesData === null) {
    echo "JSON 디코드 실패: " . json_last_error_msg() . "\n";
} else {
    echo "성공! 디코드된 데이터:\n";
    print_r($imagesData);
    
    echo "\n강의 이미지 변환 결과:\n";
    $formattedImages = array_map(function($image, $index) {
        return [
            'id' => $index + 1,
            'url' => $image['file_path'] ?? '',
            'alt_text' => $image['original_name'] ?? '강의 이미지'
        ];
    }, $imagesData, array_keys($imagesData));
    
    print_r($formattedImages);
}
?>