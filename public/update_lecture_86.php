<?php
/**
 * 86번 강의 강사 정보를 3명으로 업데이트하는 스크립트
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h2>86번 강의 강사 정보 업데이트</h2>";

try {
    // 설정 파일 로드
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    
    echo "<h3>📋 현재 86번 강의 정보 확인</h3>";
    
    // 현재 정보 조회
    $currentLecture = $db->fetch("SELECT id, title, instructor_name, instructor_info FROM lectures WHERE id = 86");
    
    if ($currentLecture) {
        echo "<div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<strong>강의 제목:</strong> " . htmlspecialchars($currentLecture['title']) . "<br>";
        echo "<strong>현재 강사:</strong> " . htmlspecialchars($currentLecture['instructor_name']) . "<br>";
        echo "<strong>현재 소개:</strong> " . htmlspecialchars(substr($currentLecture['instructor_info'], 0, 100)) . "...<br>";
        echo "</div>";
        
        echo "<h3>🚀 3명의 강사로 업데이트</h3>";
        
        // 업데이트 실행
        $newInstructorName = '김마케팅, 박소셜, 이데이터';
        $newInstructorInfo = '김마케팅은 10년 경력의 디지털 마케팅 전문가로, 다수 기업의 온라인 마케팅 전략 수립 및 브랜드 성장을 이끌어왔습니다. 구글 애즈, 네이버 광고, 페이스북 마케팅 전문가로 ROI 극대화에 탁월한 능력을 보유하고 있습니다.|||박소셜은 8년 경력의 SNS 마케팅 및 인플루언서 마케팅 전문가입니다. 바이럴 캠페인 기획과 브랜드 스토리텔링 분야에서 뛰어난 성과를 거두었으며, 젊은 세대와의 소통에 특화된 마케팅 전략을 구사합니다.|||이데이터는 6년 경력의 빅데이터 분석 및 마케팅 인사이트 전문가입니다. 고객 데이터 분석을 통한 개인화 마케팅과 AI 마케팅 도구 활용에 능숙하며, 데이터 기반 의사결정을 통해 마케팅 효율성을 극대화하는 전문가입니다.';
        
        $updateSql = "UPDATE lectures SET 
                        instructor_name = :instructor_name,
                        instructor_info = :instructor_info
                      WHERE id = 86";
        
        $result = $db->execute($updateSql, [
            ':instructor_name' => $newInstructorName,
            ':instructor_info' => $newInstructorInfo
        ]);
        
        if ($result) {
            echo "<div style='background: #f0fff4; padding: 15px; border-radius: 8px; border-left: 4px solid #48bb78; margin: 10px 0;'>";
            echo "✅ <strong>업데이트 완료!</strong><br>";
            echo "강사 정보가 3명으로 성공적으로 업데이트되었습니다.<br>";
            echo "</div>";
            
            // 업데이트된 정보 조회
            $updatedLecture = $db->fetch("SELECT instructor_name, instructor_info FROM lectures WHERE id = 86");
            
            echo "<h3>📝 업데이트된 강사 정보</h3>";
            echo "<div style='background: #fff5f5; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
            echo "<strong>새 강사명:</strong> " . htmlspecialchars($updatedLecture['instructor_name']) . "<br><br>";
            
            $instructorInfos = explode('|||', $updatedLecture['instructor_info']);
            $instructorNames = explode(',', $updatedLecture['instructor_name']);
            
            foreach ($instructorNames as $index => $name) {
                echo "<strong>" . htmlspecialchars(trim($name)) . ":</strong><br>";
                if (isset($instructorInfos[$index])) {
                    echo htmlspecialchars(trim($instructorInfos[$index])) . "<br><br>";
                }
            }
            echo "</div>";
            
            echo "<div style='background: #e6f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "🎉 <strong>완료!</strong><br>";
            echo "이제 <a href='/lectures/86' target='_blank' style='color: #667eea; font-weight: bold;'>https://www.topmktx.com/lectures/86</a> 페이지에서<br>";
            echo "3명의 강사가 각각의 이미지와 함께 표시되는 것을 확인할 수 있습니다!";
            echo "</div>";
            
        } else {
            echo "<div style='background: #fee; padding: 15px; border-radius: 8px; border-left: 4px solid #e53e3e; margin: 10px 0;'>";
            echo "❌ <strong>업데이트 실패</strong><br>";
            echo "데이터베이스 업데이트 중 오류가 발생했습니다.";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #fee; padding: 15px; border-radius: 8px; border-left: 4px solid #e53e3e; margin: 10px 0;'>";
        echo "❌ <strong>86번 강의를 찾을 수 없습니다.</strong><br>";
        echo "강의가 존재하지 않거나 ID가 다를 수 있습니다.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee; padding: 15px; border-radius: 8px; border-left: 4px solid #e53e3e; margin: 10px 0;'>";
    echo "❌ <strong>오류 발생:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}
h2, h3 {
    color: #2d3748;
}
</style>