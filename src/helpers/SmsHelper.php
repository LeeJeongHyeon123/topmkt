<?php
/**
 * SMS 관련 헬퍼 함수들
 */

// 🔥 Ultra Think Mode: SMS 설정을 위한 config.php 포함
require_once SRC_PATH . '/config/config.php';

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
    $message = "[탑마케팅] 강의 신청이 승인되었습니다. 참석 부탁드립니다.";
    return sendSms($phone, $message);
}

/**
 * 강의 신청 거절 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $lectureTitle 강의 제목
 * @param string $reason 거절 사유 (사용하지 않음)
 * @return array 발송 결과
 */
function sendLectureRejectionSms($phone, $lectureTitle, $reason = '') 
{
    $message = "[탑마케팅] 강의 신청이 거절되었습니다.";
    return sendSms($phone, $message);
}

/**
 * 행사 신청 확인 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @return array 발송 결과
 */
function sendEventApplicationSms($phone) 
{
    $message = "[탑마케팅] 행사 신청이 접수되었습니다. 승인 결과는 1~2일 내 안내드리겠습니다.";
    return sendSms($phone, $message);
}

/**
 * 행사 신청 승인 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $eventTitle 행사 제목
 * @param string $eventDate 행사 날짜
 * @return array 발송 결과
 */
function sendEventApprovalSms($phone, $eventTitle, $eventDate) 
{
    $message = "[탑마케팅] 행사 신청이 승인되었습니다. 참석 부탁드립니다.";
    return sendSms($phone, $message);
}

/**
 * 행사 신청 거절 SMS 발송
 * 
 * @param string $phone 수신자 전화번호
 * @param string $eventTitle 행사 제목
 * @param string $reason 거절 사유 (사용하지 않음)
 * @return array 발송 결과
 */
function sendEventRejectionSms($phone, $eventTitle, $reason = '') 
{
    $message = "[탑마케팅] 행사 신청이 거절되었습니다.";
    return sendSms($phone, $message);
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
?>