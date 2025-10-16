<?php
/**
 * FCM (Firebase Cloud Messaging) Helper
 * Firebase Cloud Messaging V1 API 푸시 알림 전송
 *
 * 작성일: 2025-10-16
 */

require_once ROOT_PATH . '/vendor/autoload.php';
require_once SRC_PATH . '/helpers/WebLogger.php';

use Google\Auth\Credentials\ServiceAccountCredentials;

class FcmHelper
{
    /**
     * OAuth 2.0 Access Token 생성
     *
     * @return string|false Access Token 또는 false
     */
    private static function getAccessToken()
    {
        try {
            // Firebase 서비스 계정 JSON 키 로드
            $serviceAccountPath = FCM_SERVICE_ACCOUNT_PATH;

            if (!file_exists($serviceAccountPath)) {
                WebLogger::error('Firebase 서비스 계정 키 파일이 존재하지 않습니다', [
                    'path' => $serviceAccountPath
                ]);
                return false;
            }

            // Google Auth Library로 OAuth 2.0 토큰 생성
            $credentials = new ServiceAccountCredentials(
                FCM_SCOPE,
                $serviceAccountPath
            );

            $token = $credentials->fetchAuthToken();

            if (!isset($token['access_token'])) {
                WebLogger::error('OAuth 2.0 토큰 생성 실패', [
                    'token_response' => $token
                ]);
                return false;
            }

            return $token['access_token'];
        } catch (Exception $e) {
            WebLogger::error('OAuth 2.0 토큰 생성 예외 발생', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * FCM V1 API로 푸시 알림 전송
     *
     * @param string $fcmToken FCM 토큰
     * @param string $title 알림 제목
     * @param string $body 알림 내용
     * @param array $data 추가 데이터 (optional)
     * @return array 전송 결과 ['success' => bool, 'message' => string, 'response' => array]
     */
    public static function sendPush($fcmToken, $title, $body, $data = [])
    {
        try {
            // 1. OAuth 2.0 Access Token 생성
            $accessToken = self::getAccessToken();

            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'OAuth 2.0 토큰 생성 실패',
                    'response' => null
                ];
            }

            // 2. FCM V1 API 요청 페이로드 구성
            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'android' => [
                        'notification' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default'
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1
                            ]
                        ]
                    ]
                ]
            ];

            // 추가 데이터가 있으면 포함
            if (!empty($data)) {
                $payload['message']['data'] = $data;
            }

            // 3. FCM V1 API 호출
            $ch = curl_init(FCM_API_URL_V1);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // 4. 응답 처리
            if ($curlError) {
                WebLogger::error('FCM API 호출 cURL 오류', [
                    'error' => $curlError
                ]);
                return [
                    'success' => false,
                    'message' => 'FCM API 호출 실패: ' . $curlError,
                    'response' => null
                ];
            }

            $responseData = json_decode($response, true);

            if ($httpCode === 200) {
                WebLogger::info('FCM 푸시 전송 성공', [
                    'title' => $title,
                    'token_length' => strlen($fcmToken),
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'FCM 푸시 전송 성공',
                    'response' => $responseData
                ];
            } else {
                // 실패 처리
                $errorMessage = $responseData['error']['message'] ?? '알 수 없는 오류';
                $errorCode = $responseData['error']['code'] ?? $httpCode;

                WebLogger::error('FCM 푸시 전송 실패', [
                    'http_code' => $httpCode,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'response' => $responseData
                ]);

                // 토큰 만료 등의 오류인 경우 토큰 비활성화
                if (self::isTokenInvalid($errorCode, $errorMessage)) {
                    self::handleInvalidToken($fcmToken);
                }

                return [
                    'success' => false,
                    'message' => 'FCM 푸시 전송 실패: ' . $errorMessage,
                    'response' => $responseData
                ];
            }
        } catch (Exception $e) {
            WebLogger::error('FCM 푸시 전송 예외 발생', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'FCM 푸시 전송 예외: ' . $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * 여러 토큰에 동시에 푸시 전송 (배치 처리)
     *
     * @param array $fcmTokens FCM 토큰 배열
     * @param string $title 알림 제목
     * @param string $body 알림 내용
     * @param array $data 추가 데이터 (optional)
     * @return array 결과 통계 ['total' => int, 'success' => int, 'failed' => int]
     */
    public static function sendBulkPush($fcmTokens, $title, $body, $data = [])
    {
        $stats = [
            'total' => count($fcmTokens),
            'success' => 0,
            'failed' => 0
        ];

        foreach ($fcmTokens as $token) {
            $fcmToken = is_array($token) ? $token['fcm_token'] : $token;

            $result = self::sendPush($fcmToken, $title, $body, $data);

            if ($result['success']) {
                $stats['success']++;
            } else {
                $stats['failed']++;
            }

            // API 레이트 리밋 방지 (선택사항)
            usleep(100000); // 0.1초 대기
        }

        WebLogger::info('FCM 대량 푸시 전송 완료', $stats);

        return $stats;
    }

    /**
     * 토큰 유효성 오류 확인
     *
     * @param int|string $errorCode 오류 코드
     * @param string $errorMessage 오류 메시지
     * @return bool 토큰 무효화 여부
     */
    private static function isTokenInvalid($errorCode, $errorMessage)
    {
        $invalidErrors = [
            'INVALID_ARGUMENT',
            'UNREGISTERED',
            'NOT_FOUND',
            'INVALID_REGISTRATION'
        ];

        foreach ($invalidErrors as $error) {
            if (stripos($errorMessage, $error) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 무효한 토큰 처리 (비활성화)
     *
     * @param string $fcmToken FCM 토큰
     */
    private static function handleInvalidToken($fcmToken)
    {
        try {
            require_once SRC_PATH . '/models/FcmToken.php';
            $fcmTokenModel = new FcmToken();
            $fcmTokenModel->deactivateToken($fcmToken);

            WebLogger::info('무효한 FCM 토큰 비활성화', [
                'token_length' => strlen($fcmToken)
            ]);
        } catch (Exception $e) {
            WebLogger::error('무효한 FCM 토큰 비활성화 실패', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 알림 타입별 푸시 전송 (알림 설정 통합)
     *
     * @param string $notificationType 알림 타입 (comments|likes|lectures_events|registration|notices)
     * @param string $title 알림 제목
     * @param string $body 알림 내용
     * @param array $data 추가 데이터 (optional)
     * @return array 결과 통계
     */
    public static function sendByNotificationType($notificationType, $title, $body, $data = [])
    {
        try {
            require_once SRC_PATH . '/models/FcmToken.php';
            $fcmTokenModel = new FcmToken();

            // 알림 설정이 활성화된 사용자의 토큰 조회
            $tokens = $fcmTokenModel->getTokensByNotificationType($notificationType);

            if (empty($tokens)) {
                WebLogger::info('알림 타입별 푸시 전송: 대상 토큰 없음', [
                    'notification_type' => $notificationType
                ]);

                return [
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0
                ];
            }

            // 대량 푸시 전송
            return self::sendBulkPush($tokens, $title, $body, $data);
        } catch (Exception $e) {
            WebLogger::error('알림 타입별 푸시 전송 예외', [
                'notification_type' => $notificationType,
                'error' => $e->getMessage()
            ]);

            return [
                'total' => 0,
                'success' => 0,
                'failed' => 0
            ];
        }
    }
}
