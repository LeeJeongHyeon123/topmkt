<?php
/**
 * MailHelper 클래스
 * 이메일 전송 관련 공통 기능을 제공합니다.
 */

class MailHelper {

    private static $config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'from_email' => 'noreply@topmktx.com',
        'from_name' => '탑마케팅'
    ];

    /**
     * 이메일 설정
     */
    public static function setConfig($config) {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * 간단한 이메일 전송
     */
    public static function send($to, $subject, $body, $options = []) {
        $headers = [
            'From: ' . self::$config['from_name'] . ' <' . self::$config['from_email'] . '>',
            'Reply-To: ' . self::$config['from_email'],
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        if (isset($options['cc'])) {
            $headers[] = 'Cc: ' . $options['cc'];
        }

        if (isset($options['bcc'])) {
            $headers[] = 'Bcc: ' . $options['bcc'];
        }

        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * HTML 이메일 템플릿
     */
    public static function createHtmlEmail($title, $content, $buttonText = null, $buttonUrl = null) {
        $buttonHtml = '';
        if ($buttonText && $buttonUrl) {
            $buttonHtml = "
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$buttonUrl}' style='
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 12px 30px;
                        text-decoration: none;
                        border-radius: 6px;
                        font-weight: 600;
                        display: inline-block;
                    '>{$buttonText}</a>
                </div>
            ";
        }

        return "
            <!DOCTYPE html>
            <html lang='ko'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>{$title}</title>
            </head>
            <body style='
                font-family: \"Noto Sans KR\", sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f8fafc;
            '>
                <div style='
                    max-width: 600px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                '>
                    <!-- 헤더 -->
                    <div style='
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 30px 20px;
                        text-align: center;
                    '>
                        <h1 style='margin: 0; font-size: 24px;'>{$title}</h1>
                    </div>

                    <!-- 본문 -->
                    <div style='padding: 30px 20px;'>
                        {$content}
                        {$buttonHtml}
                    </div>

                    <!-- 푸터 -->
                    <div style='
                        background: #f8fafc;
                        padding: 20px;
                        text-align: center;
                        color: #718096;
                        font-size: 12px;
                        border-top: 1px solid #e2e8f0;
                    '>
                        <p>이 이메일은 탑마케팅에서 발송되었습니다.</p>
                        <p>문의사항이 있으시면 <a href='mailto:jh@wincard.kr'>jh@wincard.kr</a>로 연락주세요.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * 사용자 등록 확인 이메일
     */
    public static function sendWelcomeEmail($email, $nickname) {
        $title = '탑마케팅에 오신 것을 환영합니다!';
        $content = "
            <h2 style='color: #2d3748; margin-bottom: 20px;'>안녕하세요, {$nickname}님!</h2>
            <p style='color: #4a5568; line-height: 1.6; margin-bottom: 20px;'>
                탑마케팅 회원가입이 완료되었습니다. 이제 다양한 마케팅 교육과 커뮤니티 활동에 참여하실 수 있습니다.
            </p>
            <p style='color: #4a5568; line-height: 1.6; margin-bottom: 20px;'>
                <strong>즐겨찾기 추천:</strong><br>
                • 강의 일정에서 관심있는 교육을 찾아보세요<br>
                • 커뮤니티에서 다른 회원들과 소통하세요<br>
                • 행사 일정에서 네트워킹 기회를 확인하세요
            </p>
        ";

        return self::send($email, $title, self::createHtmlEmail($title, $content, '탑마케팅 시작하기', 'https://www.topmktx.com'));
    }

    /**
     * 강의 신청 확인 이메일
     */
    public static function sendLectureRegistrationEmail($email, $nickname, $lectureTitle) {
        $title = '강의 신청이 접수되었습니다';
        $content = "
            <h2 style='color: #2d3748; margin-bottom: 20px;'>안녕하세요, {$nickname}님!</h2>
            <p style='color: #4a5568; line-height: 1.6; margin-bottom: 20px;'>
                <strong>{$lectureTitle}</strong> 강의 신청이 정상적으로 접수되었습니다.
            </p>
            <p style='color: #4a5568; line-height: 1.6; margin-bottom: 20px;'>
                신청 내용은 마이페이지 > 신청 관리에서 확인하실 수 있습니다.<br>
                강의 시작 전 추가 안내사항이 이메일로 발송될 예정입니다.
            </p>
        ";

        return self::send($email, $title, self::createHtmlEmail($title, $content, '신청 관리', 'https://www.topmktx.com/registrations'));
    }

    /**
     * 비밀번호 재설정 이메일
     */
    public static function sendPasswordResetEmail($email, $resetUrl) {
        $title = '비밀번호 재설정 안내';
        $content = "
            <h2 style='color: #2d3748; margin-bottom: 20px;'>비밀번호 재설정</h2>
            <p style='color: #4a5568; line-height: 1.6; margin-bottom: 20px;'>
                비밀번호 재설정을 요청하셨습니다. 아래 버튼을 클릭하여 비밀번호를 재설정해주세요.
            </p>
            <p style='color: #e53e3e; font-size: 0.9rem; margin-bottom: 20px;'>
                ⚠️ 이 링크는 1시간 후에 만료됩니다.
            </p>
        ";

        return self::send($email, $title, self::createHtmlEmail($title, $content, '비밀번호 재설정', $resetUrl));
    }
}
?>
