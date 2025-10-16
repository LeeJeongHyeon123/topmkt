<?php
/**
 * 86번 강의 강사 업데이트 - 빠른 실행
 */

// 프로젝트 루트 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 설정 파일 로드
require_once SRC_PATH . '/config/database.php';

echo "<h2>🚀 86번 강의 강사 3명으로 업데이트</h2>";

if (isset($_POST['update'])) {
    try {
        // 데이터베이스 연결
        $db = Database::getInstance();
        
        $newInstructorName = '김마케팅, 박소셜, 이데이터';
        $newInstructorInfo = '김마케팅은 10년 경력의 디지털 마케팅 전문가로, 다수 기업의 온라인 마케팅 전략 수립 및 브랜드 성장을 이끌어왔습니다. 구글 애즈, 네이버 광고, 페이스북 마케팅 전문가로 ROI 극대화에 탁월한 능력을 보유하고 있습니다.|||박소셜은 8년 경력의 SNS 마케팅 및 인플루언서 마케팅 전문가입니다. 바이럴 캠페인 기획과 브랜드 스토리텔링 분야에서 뛰어난 성과를 거두었으며, 젊은 세대와의 소통에 특화된 마케팅 전략을 구사합니다.|||이데이터는 6년 경력의 빅데이터 분석 및 마케팅 인사이트 전문가입니다. 고객 데이터 분석을 통한 개인화 마케팅과 AI 마케팅 도구 활용에 능숙하며, 데이터 기반 의사결정을 통해 마케팅 효율성을 극대화하는 전문가입니다.';
        
        $rowsAffected = $db->execute(
            'UPDATE lectures SET instructor_name = ?, instructor_info = ? WHERE id = 86',
            [$newInstructorName, $newInstructorInfo]
        );
        
        if ($rowsAffected > 0) {
            echo "<div style='background: #f0fff4; padding: 20px; border-radius: 8px; border-left: 4px solid #48bb78; margin: 20px 0;'>";
            echo "✅ <strong>업데이트 성공!</strong><br><br>";
            echo "<strong>새로운 강사 구성:</strong><br>";
            echo "1. 김마케팅 - 디지털 마케팅 전문가<br>";
            echo "2. 박소셜 - SNS 마케팅 전문가<br>";
            echo "3. 이데이터 - 빅데이터 분석 전문가<br><br>";
            echo "🎉 <a href='/lectures/86' target='_blank' style='color: #667eea; font-weight: bold;'>강의 페이지에서 확인하기</a>";
            echo "</div>";
        } else {
            echo "<div style='background: #fee; padding: 20px; border-radius: 8px; border-left: 4px solid #e53e3e;'>❌ 업데이트 실패</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #fee; padding: 20px; border-radius: 8px; border-left: 4px solid #e53e3e;'>";
        echo "❌ <strong>연결 오류:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<strong>현재 작업:</strong><br>";
    echo "86번 강의의 강사를 1명에서 3명으로 변경합니다.<br><br>";
    echo "<strong>새로운 강사 구성:</strong><br>";
    echo "1. <strong>김마케팅</strong> - 디지털 마케팅 전문가 (10년 경력)<br>";
    echo "2. <strong>박소셜</strong> - SNS 마케팅 전문가 (8년 경력)<br>";
    echo "3. <strong>이데이터</strong> - 빅데이터 분석 전문가 (6년 경력)<br>";
    echo "</div>";
    
    echo '<form method="post" style="margin: 20px 0;">';
    echo '<button type="submit" name="update" style="background: #667eea; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">';
    echo '🚀 강사 3명으로 업데이트하기';
    echo '</button>';
    echo '</form>';
}
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}
h2 {
    color: #2d3748;
    text-align: center;
}
</style>