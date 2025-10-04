<?php
/**
 * 실제 웹 요청 디버깅
 */

// 회원탈퇴 API 요청 시뮬레이션
$url = 'https://www.topmktx.com/api/user/delete-account';
$data = [
    'password' => 'Dnlszkem1!',
    'reason' => '테스트 탈퇴',
    'csrf_token' => 'test-token'
];

echo "=== 웹 요청 디버깅 ===\n\n";
echo "1. 요청 URL: $url\n";
echo "2. 요청 데이터: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";

// cURL로 요청 보내기
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Debug Script'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "3. HTTP 상태 코드: $httpCode\n";
echo "4. 응답 헤더:\n$headers\n";
echo "5. 응답 본문:\n$body\n";

// JSON 파싱 시도
$jsonResponse = json_decode($body, true);
if ($jsonResponse) {
    echo "6. 파싱된 JSON:\n";
    print_r($jsonResponse);
} else {
    echo "6. JSON 파싱 실패\n";
}
?>