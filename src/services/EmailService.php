<?php
/**
 * 이메일 발송 서비스 클래스
 */

class EmailService
{
    private $fromEmail;
    private $fromName;
    
    public function __construct()
    {
        $this->fromEmail = 'noreply@topmktx.com';
        $this->fromName = '탑마케팅';
    }
    
    /**
     * 신청 승인 이메일 발송
     */
    public function sendApprovalNotification($registration, $lecture)
    {
        $subject = "[탑마케팅] 강의 신청이 승인되었습니다 - " . $lecture['title'];
        
        $message = $this->getApprovalEmailTemplate($registration, $lecture);
        
        return $this->sendEmail(
            $registration['participant_email'],
            $registration['participant_name'],
            $subject,
            $message
        );
    }
    
    /**
     * 신청 거절 이메일 발송
     */
    public function sendRejectionNotification($registration, $lecture)
    {
        $subject = "[탑마케팅] 강의 신청 처리 결과 안내 - " . $lecture['title'];
        
        $message = $this->getRejectionEmailTemplate($registration, $lecture);
        
        return $this->sendEmail(
            $registration['participant_email'],
            $registration['participant_name'],
            $subject,
            $message
        );
    }
    
    /**
     * 신청 확인 이메일 발송
     */
    public function sendApplicationConfirmation($registration, $lecture)
    {
        $subject = "[탑마케팅] 강의 신청 접수 완료 - " . $lecture['title'];
        
        $message = $this->getConfirmationEmailTemplate($registration, $lecture);
        
        return $this->sendEmail(
            $registration['participant_email'],
            $registration['participant_name'],
            $subject,
            $message
        );
    }
    
    /**
     * 이메일 발송 실행
     */
    private function sendEmail($toEmail, $toName, $subject, $htmlMessage)
    {
        try {
            // HTML 이메일을 위한 헤더 설정
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
                'Reply-To: ' . $this->fromEmail,
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $headerString = implode("\r\n", $headers);
            
            // 이메일 발송
            $result = mail($toEmail, $subject, $htmlMessage, $headerString);
            
            if ($result) {
                error_log("이메일 발송 성공: {$toEmail} - {$subject}");
                return true;
            } else {
                error_log("이메일 발송 실패: {$toEmail} - {$subject}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("이메일 발송 오류: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 승인 이메일 템플릿
     */
    private function getApprovalEmailTemplate($registration, $lecture)
    {
        $lectureDate = date('Y년 m월 d일 H:i', strtotime($lecture['start_date'] . ' ' . $lecture['start_time']));
        $lectureEndDate = date('Y년 m월 d일 H:i', strtotime($lecture['end_date'] . ' ' . $lecture['end_time']));
        
        return "
        <!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Noto Sans KR', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #fff; padding: 30px 20px; border: 1px solid #e2e8f0; }
                .footer { background: #f8fafc; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 14px; color: #718096; }
                .highlight { background: #c6f6d5; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #48bb78; }
                .info-box { background: #f8fafc; padding: 20px; border-radius: 6px; margin: 20px 0; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 강의 신청이 승인되었습니다!</h1>
                </div>
                
                <div class='content'>
                    <p>안녕하세요, <strong>" . htmlspecialchars($registration['participant_name']) . "</strong>님!</p>
                    
                    <div class='highlight'>
                        <h3>✅ 신청 승인 완료</h3>
                        <p>귀하의 강의 신청이 승인되었습니다. 강의에 참여하실 수 있습니다.</p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>📚 강의 정보</h3>
                        <p><strong>강의명:</strong> " . htmlspecialchars($lecture['title']) . "</p>
                        <p><strong>시작일시:</strong> {$lectureDate}</p>
                        <p><strong>종료일시:</strong> {$lectureEndDate}</p>
                        " . (!empty($lecture['location']) ? "<p><strong>장소:</strong> " . htmlspecialchars($lecture['location']) . "</p>" : "") . "
                    </div>
                    
                    " . (!empty($registration['admin_notes']) ? "
                    <div class='info-box'>
                        <h3>💬 주최자 메시지</h3>
                        <p>" . nl2br(htmlspecialchars($registration['admin_notes'])) . "</p>
                    </div>
                    " : "") . "
                    
                    <p>강의 참여를 위한 추가 안내사항이 있다면 별도로 연락드리겠습니다.</p>
                    
                    <a href='https://www.topmktx.com/lectures/" . $lecture['id'] . "' class='button'>강의 상세보기</a>
                </div>
                
                <div class='footer'>
                    <p>본 메일은 발신전용입니다. 문의사항이 있으시면 웹사이트를 통해 연락해주세요.</p>
                    <p>&copy; 2025 탑마케팅. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * 거절 이메일 템플릿
     */
    private function getRejectionEmailTemplate($registration, $lecture)
    {
        return "
        <!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Noto Sans KR', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #fff; padding: 30px 20px; border: 1px solid #e2e8f0; }
                .footer { background: #f8fafc; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 14px; color: #718096; }
                .notice { background: #fed7d7; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #f56565; }
                .info-box { background: #f8fafc; padding: 20px; border-radius: 6px; margin: 20px 0; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📋 강의 신청 처리 결과 안내</h1>
                </div>
                
                <div class='content'>
                    <p>안녕하세요, <strong>" . htmlspecialchars($registration['participant_name']) . "</strong>님!</p>
                    
                    <div class='notice'>
                        <h3>😔 신청 처리 결과</h3>
                        <p>아쉽게도 이번 강의 신청이 승인되지 않았습니다.</p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>📚 신청하신 강의</h3>
                        <p><strong>강의명:</strong> " . htmlspecialchars($lecture['title']) . "</p>
                    </div>
                    
                    " . (!empty($registration['admin_notes']) ? "
                    <div class='info-box'>
                        <h3>💬 주최자 메시지</h3>
                        <p>" . nl2br(htmlspecialchars($registration['admin_notes'])) . "</p>
                    </div>
                    " : "") . "
                    
                    <p>다른 강의에도 많은 관심 부탁드리며, 앞으로도 탑마케팅과 함께해주세요.</p>
                    
                    <a href='https://www.topmktx.com/lectures' class='button'>다른 강의 둘러보기</a>
                </div>
                
                <div class='footer'>
                    <p>본 메일은 발신전용입니다. 문의사항이 있으시면 웹사이트를 통해 연락해주세요.</p>
                    <p>&copy; 2025 탑마케팅. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * 신청 확인 이메일 템플릿
     */
    private function getConfirmationEmailTemplate($registration, $lecture)
    {
        $statusText = [
            'pending' => '승인 대기중',
            'approved' => '승인됨',
            'waiting' => '대기자 목록'
        ][$registration['status']] ?? $registration['status'];
        
        return "
        <!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Noto Sans KR', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #fff; padding: 30px 20px; border: 1px solid #e2e8f0; }
                .footer { background: #f8fafc; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 14px; color: #718096; }
                .success { background: #c6f6d5; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #48bb78; }
                .info-box { background: #f8fafc; padding: 20px; border-radius: 6px; margin: 20px 0; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📝 강의 신청이 접수되었습니다</h1>
                </div>
                
                <div class='content'>
                    <p>안녕하세요, <strong>" . htmlspecialchars($registration['participant_name']) . "</strong>님!</p>
                    
                    <div class='success'>
                        <h3>✅ 신청 접수 완료</h3>
                        <p>강의 신청이 성공적으로 접수되었습니다.</p>
                        <p><strong>현재 상태:</strong> {$statusText}</p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>📚 신청 정보</h3>
                        <p><strong>강의명:</strong> " . htmlspecialchars($lecture['title']) . "</p>
                        <p><strong>신청일:</strong> " . date('Y년 m월 d일 H:i', strtotime($registration['created_at'])) . "</p>
                    </div>
                    
                    " . ($lecture['auto_approval'] ? "
                    <p>이 강의는 자동 승인으로 설정되어 있어 즉시 참여 가능합니다.</p>
                    " : "
                    <p>주최자의 승인 후 참여 확정됩니다. 승인 결과는 이메일로 안내드리겠습니다.</p>
                    ") . "
                    
                    <a href='https://www.topmktx.com/lectures/" . $lecture['id'] . "' class='button'>강의 상세보기</a>
                </div>
                
                <div class='footer'>
                    <p>본 메일은 발신전용입니다. 문의사항이 있으시면 웹사이트를 통해 연락해주세요.</p>
                    <p>&copy; 2025 탑마케팅. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>