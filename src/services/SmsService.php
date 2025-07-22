<?php
/**
 * SMS 발송 서비스 클래스
 * 알리고 API를 사용하여 문자메시지를 발송합니다.
 */

class SmsService 
{
    private $apiKey;
    private $userId;
    private $sender;
    private $apiUrl;
    private $testMode;

    public function __construct() 
    {
        $this->apiKey = ALIGO_API_KEY;
        $this->userId = ALIGO_USER_ID;
        $this->sender = ALIGO_SENDER;
        $this->apiUrl = ALIGO_API_URL;
        $this->testMode = false; // 항상 실제 발송 모드
    }

    /**
     * 단일 문자 발송
     * 
     * @param string $receiver 수신자 전화번호 (010-1234-5678 형식)
     * @param string $message 메시지 내용 (최대 2,000Byte)
     * @param string $title 문자제목 (LMS, MMS만 허용, 최대 44Byte)
     * @param string $msgType SMS(단문), LMS(장문), MMS(그림문자) - 생략시 자동판별
     * @return array 발송 결과
     */
    public function sendSms($receiver, $message, $title = '', $msgType = '') 
    {
        try {
            // 입력값 검증
            if (empty($receiver)) {
                return ['success' => false, 'message' => '수신자 전화번호가 필요합니다.'];
            }
            
            if (empty($message)) {
                return ['success' => false, 'message' => '메시지 내용이 필요합니다.'];
            }
            
            // 전화번호 포맷팅 및 검증
            $receiver = $this->formatPhone($receiver);
            if (!$this->isValidPhone($receiver)) {
                return ['success' => false, 'message' => '유효하지 않은 전화번호 형식입니다.'];
            }
            
            // 메시지 타입 자동 판별
            if (empty($msgType)) {
                $msgType = $this->detectMessageType($message);
            }
            
            // API 키 및 사용자 ID 체크
            if (empty($this->apiKey) || empty($this->userId)) {
                return ['success' => false, 'message' => 'API 키 또는 사용자 ID가 설정되지 않았습니다. 알리고 계정 설정을 확인해주세요.', 'error_code' => 'MISSING_CREDENTIALS'];
            }
            
            // 실제 API 호출
            $postData = [
                'key' => $this->apiKey,
                'userid' => $this->userId,
                'sender' => $this->sender,
                'receiver' => $receiver,
                'msg' => $message,
                'msg_type' => $msgType,
                'testmode_yn' => $this->testMode ? 'Y' : 'N'
            ];
            
            // 제목이 있으면 추가 (LMS, MMS용)
            if (!empty($title) && in_array($msgType, ['LMS', 'MMS'])) {
                $postData['title'] = $title;
            }
            
            $response = $this->callApi($this->apiUrl, $postData);
            
            // 응답 처리
            if ($response && isset($response['result_code'])) {
                $success = in_array($response['result_code'], ['1', 1]);
                
                return [
                    'success' => $success,
                    'message' => $success ? 'SMS 발송이 완료되었습니다.' : ($response['message'] ?? 'SMS 발송에 실패했습니다.'),
                    'data' => $response
                ];
            } else {
                return ['success' => false, 'message' => 'API 응답 오류가 발생했습니다.'];
            }
            
        } catch (Exception $e) {
            error_log('SMS 발송 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * 대량 문자 발송 (최대 500명)
     * 
     * @param array $recipients 수신자 정보 배열 [['phone' => '010-1234-5678', 'message' => '메시지']]
     * @param string $msgType SMS(단문), LMS(장문), MMS(그림문자)
     * @param string $title 공통 제목 (LMS, MMS만 허용)
     * @return array 발송 결과
     */
    public function sendBulkSms($recipients, $msgType = 'SMS', $title = '') 
    {
        try {
            if (empty($recipients) || !is_array($recipients)) {
                return ['success' => false, 'message' => '수신자 목록이 필요합니다.'];
            }

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($recipients as $recipient) {
                $phone = $recipient['phone'] ?? '';
                $message = $recipient['message'] ?? '';
                
                $result = $this->sendSms($phone, $message, $title, $msgType);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
                
                $results[] = [
                    'phone' => $phone,
                    'result' => $result
                ];
            }

            return [
                'success' => $successCount > 0,
                'message' => "대량 발송 완료: 성공 {$successCount}건, 실패 {$errorCount}건",
                'data' => [
                    'total_count' => count($recipients),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'results' => $results
                ]
            ];

        } catch (Exception $e) {
            error_log('대량 SMS 발송 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => '대량 SMS 발송 중 오류가 발생했습니다.'];
        }
    }

    /**
     * 발송 가능 건수 조회
     * 
     * @return array 잔여 건수 정보
     */
    public function getRemainCount() 
    {
        try {
            // API 키 체크
            if (empty($this->apiKey) || empty($this->userId)) {
                return ['success' => false, 'message' => 'API 키 또는 사용자 ID가 설정되지 않았습니다. 알리고 계정 설정을 확인해주세요.'];
            }

            $postData = [
                'key' => $this->apiKey,
                'userid' => $this->userId
            ];

            $response = $this->callApi(ALIGO_REMAIN_URL, $postData);

            if ($response && isset($response['result_code'])) {
                $success = in_array($response['result_code'], ['1', 1]);
                
                return [
                    'success' => $success,
                    'message' => $success ? '잔여 건수 조회 완료' : '잔여 건수 조회 실패',
                    'data' => $response
                ];
            } else {
                return ['success' => false, 'message' => 'API 응답 오류가 발생했습니다.'];
            }

        } catch (Exception $e) {
            error_log('잔여 건수 조회 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => '잔여 건수 조회 중 오류가 발생했습니다.'];
        }
    }

    /**
     * SMS 발송 결과 상세 조회
     * 
     * @param string $msgId 메시지 ID
     * @return array 발송 결과 상세
     */
    public function getSmsDetail($msgId) 
    {
        try {
            if (empty($msgId)) {
                return ['success' => false, 'message' => '메시지 ID가 필요합니다.'];
            }

            // API 키 체크
            if (empty($this->apiKey) || empty($this->userId)) {
                return ['success' => false, 'message' => 'API 키 또는 사용자 ID가 설정되지 않았습니다. 알리고 계정 설정을 확인해주세요.'];
            }

            $postData = [
                'key' => $this->apiKey,
                'userid' => $this->userId,
                'mid' => $msgId
            ];

            $response = $this->callApi('https://apis.aligo.in/sms_list/', $postData);

            if ($response && isset($response['result_code'])) {
                $success = in_array($response['result_code'], ['1', 1]);
                
                return [
                    'success' => $success,
                    'message' => $success ? 'SMS 상세 조회 완료' : 'SMS 상세 조회 실패',
                    'data' => $response
                ];
            } else {
                return ['success' => false, 'message' => 'API 응답 오류가 발생했습니다.'];
            }

        } catch (Exception $e) {
            error_log('SMS 상세 조회 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => 'SMS 상세 조회 중 오류가 발생했습니다.'];
        }
    }

    /**
     * 전송 내역 조회
     * 
     * @param string $startDate 조회 시작일 (YYYYMMDD)
     * @param int $limitDay 조회 기간 (일)
     * @param int $page 페이지 번호 (기본: 1)
     * @param int $pageSize 페이지당 출력 개수 (기본: 30, 최대: 500)
     * @return array 전송 내역 목록
     */
    public function getSmsList($startDate = '', $limitDay = 7, $page = 1, $pageSize = 30) 
    {
        $postData = [
            'key' => $this->apiKey,
            'user_id' => $this->userId,
            'page' => $page,
            'page_size' => min($pageSize, 500),
            'limit_day' => $limitDay
        ];

        if (!empty($startDate)) {
            $postData['start_date'] = $startDate;
        }

        return $this->callApi('/list/', $postData);
    }

    /**
     * 예약 문자 취소
     * 
     * @param int $msgId 메시지 ID
     * @return array 취소 결과
     */
    public function cancelReservation($msgId) 
    {
        $postData = [
            'key' => $this->apiKey,
            'user_id' => $this->userId,
            'mid' => $msgId
        ];

        return $this->callApi('/cancel/', $postData);
    }

    /**
     * 휴대폰 번호 유효성 검사
     * 
     * @param string $phone 전화번호
     * @return bool 유효 여부
     */
    public function isValidPhone($phone) 
    {
        // 한국 휴대폰 번호 패턴
        $pattern = '/^01[016789]-\d{3,4}-\d{4}$/';
        return preg_match($pattern, $phone);
    }

    /**
     * 휴대폰 번호 포맷팅
     * 
     * @param string $phone 전화번호
     * @return string 포맷팅된 전화번호
     */
    public function formatPhone($phone) 
    {
        // 숫자만 추출
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // 국제번호 형식 처리 (+82)
        if (substr($phone, 0, 3) === '821') {
            $phone = '0' . substr($phone, 3);
        } elseif (substr($phone, 0, 2) === '82') {
            $phone = '0' . substr($phone, 2);
        }
        
        // 하이픈 추가
        if (strlen($phone) === 11) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
        } elseif (strlen($phone) === 10) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
        }
        
        return $phone;
    }

    /**
     * 입력값 검증
     * 
     * @param string $receiver 수신자 번호
     * @param string $message 메시지
     * @return array 검증 결과
     */
    private function validateInput($receiver, $message) 
    {
        // API 키 확인
        if (empty($this->apiKey) || empty($this->userId)) {
            return [
                'success' => false,
                'message' => 'API 키 또는 사용자 ID가 설정되지 않았습니다.',
                'error_code' => 'MISSING_CREDENTIALS'
            ];
        }

        // 수신자 번호 검증
        $formattedPhone = $this->formatPhone($receiver);
        if (!$this->isValidPhone($formattedPhone)) {
            return [
                'success' => false,
                'message' => '올바른 휴대폰 번호 형식이 아닙니다. (예: 010-1234-5678)',
                'error_code' => 'INVALID_PHONE_FORMAT'
            ];
        }

        // 메시지 길이 검증
        if (empty($message)) {
            return [
                'success' => false,
                'message' => '메시지 내용이 없습니다.',
                'error_code' => 'EMPTY_MESSAGE'
            ];
        }

        if (strlen($message) > 2000) {
            return [
                'success' => false,
                'message' => '메시지는 최대 2,000바이트까지 입력 가능합니다.',
                'error_code' => 'MESSAGE_TOO_LONG'
            ];
        }

        return ['success' => true];
    }

    /**
     * 메시지 타입 자동 결정
     * 
     * @param string $message 메시지 내용
     * @return string SMS, LMS, MMS 중 하나
     */
    private function determineMessageType($message) 
    {
        $byteLength = strlen($message);
        
        // 90바이트 이하면 SMS, 초과하면 LMS
        return $byteLength <= 90 ? 'SMS' : 'LMS';
    }

    /**
     * 메시지 타입 자동 판별
     * 
     * @param string $message 메시지 내용
     * @return string 메시지 타입 (SMS, LMS, MMS)
     */
    private function detectMessageType($message) 
    {
        $length = mb_strlen($message, 'UTF-8');
        
        if ($length <= 90) {
            return 'SMS';
        } else {
            return 'LMS';
        }
    }

    /**
     * API 호출
     * 
     * @param string $url 완전한 API URL
     * @param array $data 전송 데이터
     * @return array|null API 응답
     */
    private function callApi($url, $data) 
    {
        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: TopMkt SMS Service/1.0'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);
            
            if ($error) {
                error_log('CURL 오류: ' . $error);
                return ['success' => false, 'message' => 'API 호출 중 네트워크 오류가 발생했습니다: ' . $error];
            }
            
            if ($httpCode !== 200) {
                error_log('HTTP 오류: ' . $httpCode);
                return ['success' => false, 'message' => 'API 호출 실패: HTTP ' . $httpCode];
            }
            
            $decodedResponse = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON 파싱 오류: ' . json_last_error_msg());
                return ['success' => false, 'message' => 'API 응답 파싱 오류'];
            }
            
            return $decodedResponse;
            
        } catch (Exception $e) {
            error_log('API 호출 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => 'API 호출 중 오류가 발생했습니다'];
        }
    }

    /**
     * 로그 기록 (향후 구현)
     * 
     * @param string $level 로그 레벨
     * @param string $message 로그 메시지
     * @param array $context 추가 컨텍스트
     */
    private function log($level, $message, $context = []) 
    {
        // TODO: 로깅 시스템 구현
        if (APP_DEBUG) {
            error_log("[$level] SMS Service: $message " . json_encode($context));
        }
    }
} 