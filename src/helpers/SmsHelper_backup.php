<?php
/**
 * SMS 관련 헬퍼 함수들
 */

/**
 * SMS 헬퍼 클래스
 */
class SmsHelper {
    
    /**
     * SMS 발송
     * 
     * @param string $phone 수신자 전화번호
     * @param string $message 메시지 내용
     * @param string $title 제목 (장문일 경우)
     * @return array 발송 결과
     */
    public function send($phone, $message, $title = '') {
        return sendSms($phone, $message, $title);
    }
    
    /**
     * 전화번호 포맷팅
     * 
     * @param string $phone 전화번호
     * @return string 포맷팅된 전화번호
     */
    public function formatPhone($phone) {
        return formatPhone($phone);
    }
    
    /**
     * 전화번호 유효성 검사
     * 
     * @param string $phone 전화번호
     * @return bool 유효 여부
     */
    public function isValidPhone($phone) {
        return isValidPhone($phone);
    }
}

/**
 * 간편 SMS 발송 함수
 * 
 * @param string $phone 수신자 전화번호
 * @param string $message 메시지 내용
 * @param string $title 제목 (장문일 경우)
 * @return array 발송 결과
 */
function sendSms($phone, $message, $title = '') 
{
    require_once SRC_PATH . '/services/SmsService.php';
    
    $smsService = new SmsService();
    $formattedPhone = $smsService->formatPhone($phone);
    
    return $smsService->sendSms($formattedPhone, $message, $title);
}

/**
 * 인증번호 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $authCode 인증번호
 * @return array 발송 결과
 */
function sendAuthCodeSms($phone, $authCode) 
{
    $message = "[탑마케팅] 인증번호는 [{$authCode}]입니다. 정확히 입력해주세요.";
    return sendSms($phone, $message);
}

/**
 * 비밀번호 재설정 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $tempPassword 임시 비밀번호
 * @return array 발송 결과
 */
function sendPasswordResetSms($phone, $tempPassword) 
{
    $message = "[탑마케팅] 임시 비밀번호는 [{$tempPassword}]입니다. 로그인 후 비밀번호를 변경해주세요.";
    return sendSms($phone, $message);
}

/**
 * 회원가입 환영 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $username 사용자명
 * @return array 발송 결과
 */
function sendWelcomeSms($phone, $username) 
{
    // 단문 제한(90바이트, 한글 약 45자)에 맞게 간결하게 작성
    $message = "[탑마케팅] {$username}님 가입완료! 성공적인 마케팅 여정을 시작하세요.";
    return sendSms($phone, $message);
}

/**
 * 중요 알림 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $title 알림 제목
 * @param string $content 알림 내용
 * @return array 발송 결과
 */
function sendNotificationSms($phone, $title, $content) 
{
    $message = "[탑마케팅] {$title}\n\n{$content}";
    return sendSms($phone, $message, $title);
}

/**
 * 이벤트 알림 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $eventName 이벤트명
 * @param string $eventDate 이벤트 날짜
 * @return array 발송 결과
 */
function sendEventSms($phone, $eventName, $eventDate) 
{
    $message = "[탑마케팅] {$eventName} 이벤트가 {$eventDate}에 진행됩니다. 많은 참여 바랍니다!";
    return sendSms($phone, $message, $eventName);
}

/**
 * 대량 SMS 발송
 * 
 * @param array $recipients 수신자 목록 [['phone' => '010-1234-5678', 'message' => '메시지']]
 * @param string $msgType 메시지 타입 (SMS, LMS, MMS)
 * @param string $title 제목
 * @return array 발송 결과
 */
function sendBulkSms($recipients, $msgType = 'SMS', $title = '') 
{
    require_once SRC_PATH . '/services/SmsService.php';
    
    $smsService = new SmsService();
    
    // 전화번호 포맷팅
    foreach ($recipients as &$recipient) {
        $recipient['phone'] = $smsService->formatPhone($recipient['phone']);
    }
    
    return $smsService->sendBulkSms($recipients, $msgType, $title);
}

/**
 * SMS 발송 가능 건수 조회
 * 
 * @return array 잔여 건수 정보
 */
function getSmsRemainCount() 
{
    require_once SRC_PATH . '/services/SmsService.php';
    
    $smsService = new SmsService();
    return $smsService->getRemainCount();
}

/**
 * SMS 발송 결과 조회
 * 
 * @param int $msgId 메시지 ID
 * @return array 발송 결과 상세
 */
function getSmsResult($msgId) 
{
    require_once SRC_PATH . '/services/SmsService.php';
    
    $smsService = new SmsService();
    return $smsService->getSmsDetail($msgId);
}

/**
 * 전화번호 유효성 검사
 * 
 * @param string $phone 전화번호
 * @return bool 유효 여부
 */
function isValidPhone($phone) 
{
    require_once SRC_PATH . '/services/SmsService.php';
    
    $smsService = new SmsService();
    $formattedPhone = $smsService->formatPhone($phone);
    
    return $smsService->isValidPhone($formattedPhone);
}

/**
 * 전화번호 포맷팅
 * 
 * @param string $phone 전화번호
 * @return string 포맷팅된 전화번호
 */
function formatPhone($phone) 
{
    require_once SRC_PATH . '/services/SmsService.php';
    
    $smsService = new SmsService();
    return $smsService->formatPhone($phone);
}

/**
 * SMS 템플릿 생성
 * 
 * @param string $template 템플릿 이름
 * @param array $params 템플릿 매개변수
 * @return string 생성된 메시지
 */
function createSmsTemplate($template, $params = []) 
{
    $templates = [
        'auth_code' => '[탑마케팅] 인증번호는 [{{code}}]입니다. 정확히 입력해주세요.',
        'password_reset' => '[탑마케팅] 임시 비밀번호는 [{{password}}]입니다. 로그인 후 비밀번호를 변경해주세요.',
        'welcome' => '[탑마케팅] {{username}}님 가입완료! 성공적인 마케팅 여정을 시작하세요.',
        'event_reminder' => '[탑마케팅] {{event_name}} 이벤트가 {{event_date}}에 진행됩니다. 많은 참여 바랍니다!',
        'meeting_reminder' => '[탑마케팅] {{meeting_title}} 회의가 {{meeting_time}}에 시작됩니다. 준비해주세요.',
        'payment_confirm' => '[탑마케팅] {{amount}}원 결제가 완료되었습니다. 이용해주셔서 감사합니다.',
        'order_confirm' => '[탑마케팅] 주문번호 {{order_id}} 주문이 접수되었습니다. 빠른 처리 도와드리겠습니다.'
    ];
    
    if (!isset($templates[$template])) {
        return '';
    }
    
    $message = $templates[$template];
    
    // 템플릿 매개변수 치환
    foreach ($params as $key => $value) {
        $message = str_replace('{{' . $key . '}}', $value, $message);
    }
    
    return $message;
}

/**
 * 강의 신청 확인 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @return array 발송 결과
 */
function sendLectureApplicationSms($phone) 
{
    $message = "[탑마케팅] 강의 신청이 접수되었습니다. 승인 결과는 1~2일 내 안내드리겠습니다.";
    return sendSms($phone, $message);
}

/**
 * 강의 신청 승인 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $lectureTitle 강의 제목
 * @param string $lectureDate 강의 날짜
 * @return array 발송 결과
 */
function sendLectureApprovalSms($phone, $lectureTitle, $lectureDate) 
{
    $message = "[탑마케팅] 강의 신청이 승인되었습니다. {$lectureTitle} ({$lectureDate}) 참석 부탁드립니다.";
    return sendSms($phone, $message);
}

/**
 * 강의 신청 거절 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $lectureTitle 강의 제목
 * @param string $reason 거절 사유
 * @return array 발송 결과
 */
function sendLectureRejectionSms($phone, $lectureTitle, $reason = '') 
{
    $message = "[탑마케팅] 강의 신청이 취소되었습니다. {$lectureTitle}";
    if ($reason) {
        $message .= " 사유: {$reason}";
    }
    return sendSms($phone, $message);
}

/**
 * 로그 SMS 발송 (관리자용)
 * 
 * @param string $level 로그 레벨 (ERROR, WARNING, INFO)
 * @param string $message 로그 메시지
 * @param array $adminPhones 관리자 전화번호 목록
 * @return array 발송 결과
 */
function sendLogSms($level, $message, $adminPhones = []) 
{
    // 기본 관리자 번호 (설정에서 가져올 수 있도록 향후 수정)
    if (empty($adminPhones)) {
        $adminPhones = ['010-2659-1346']; // 개발자 번호
    }
    
    $logMessage = "[탑마케팅] [{$level}] {$message}";
    
    $results = [];
    foreach ($adminPhones as $phone) {
        $results[] = sendSms($phone, $logMessage);
    }
    
    return $results;
}

/**
 * 마케팅 동의 확인 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $username 사용자명
 * @return array 발송 결과
 */
function sendMarketingConsentSms($phone, $username) 
{
    $message = "[탑마케팅] {$username}님, 마케팅 정보 수신에 동의해주셔서 감사합니다. 유용한 정보를 제공해드리겠습니다.";
    return sendSms($phone, $message);
}

/**
 * 예약 확인 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $reservationType 예약 유형
 * @param string $reservationDate 예약 날짜
 * @param string $reservationTime 예약 시간
 * @return array 발송 결과
 */
function sendReservationSms($phone, $reservationType, $reservationDate, $reservationTime) 
{
    $message = "[탑마케팅] {$reservationType} 예약이 확정되었습니다. 일시: {$reservationDate} {$reservationTime}";
    return sendSms($phone, $message);
}
