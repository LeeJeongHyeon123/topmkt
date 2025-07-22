<?php
/**
 * 86번 강의 강사 업데이트 실행 스크립트
 */

echo "<h2>🚀 86번 강의 강사 업데이트 실행</h2>";

try {
    // 데이터베이스 직접 연결
    $pdo = new PDO('mysql:host=localhost;dbname=topmkt;charset=utf8mb4', 'root', 'Dnlszkem1!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 현재 강의 정보 확인
    echo "<h3>📋 현재 86번 강의 정보</h3>";
    $stmt = $pdo->prepare('SELECT id, title, instructor_name FROM lectures WHERE id = 86');
    $stmt->execute();
    $currentLecture = $stmt->fetch();
    
    if ($currentLecture) {
        echo "<div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<strong>강의 제목:</strong> " . htmlspecialchars($currentLecture['title']) . "<br>";
        echo "<strong>현재 강사:</strong> " . htmlspecialchars($currentLecture['instructor_name']) . "<br>";
        echo "</div>";
        
        // 3명의 강사로 업데이트
        echo "<h3>🚀 3명의 강사로 업데이트 실행</h3>";
        
        $newInstructorName = '김마케팅, 박소셜, 이데이터';
        $newInstructorInfo = '김마케팅은 10년 경력의 디지털 마케팅 전문가로, 다수 기업의 온라인 마케팅 전략 수립 및 브랜드 성장을 이끌어왔습니다. 구글 애즈, 네이버 광고, 페이스북 마케팅 전문가로 ROI 극대화에 탁월한 능력을 보유하고 있습니다.|||박소셜은 8년 경력의 SNS 마케팅 및 인플루언서 마케팅 전문가입니다. 바이럴 캠페인 기획과 브랜드 스토리텔링 분야에서 뛰어난 성과를 거두었으며, 젊은 세대와의 소통에 특화된 마케팅 전략을 구사합니다.|||이데이터는 6년 경력의 빅데이터 분석 및 마케팅 인사이트 전문가입니다. 고객 데이터 분석을 통한 개인화 마케팅과 AI 마케팅 도구 활용에 능숙하며, 데이터 기반 의사결정을 통해 마케팅 효율성을 극대화하는 전문가입니다.';
        
        $updateStmt = $pdo->prepare('UPDATE lectures SET instructor_name = ?, instructor_info = ? WHERE id = 86');
        $result = $updateStmt->execute([$newInstructorName, $newInstructorInfo]);
        
        if ($result) {
            echo "<div style='background: #f0fff4; padding: 20px; border-radius: 8px; border-left: 4px solid #48bb78; margin: 20px 0;'>";
            echo "✅ <strong>업데이트 성공!</strong><br><br>";
            
            // 업데이트 확인
            $verifyStmt = $pdo->prepare('SELECT instructor_name FROM lectures WHERE id = 86');
            $verifyStmt->execute();
            $updatedLecture = $verifyStmt->fetch();
            
            echo "<strong>업데이트된 강사명:</strong><br>";
            echo htmlspecialchars($updatedLecture['instructor_name']) . "<br><br>";
            
            echo "<strong>👥 새로운 강사 구성:</strong><br>";
            echo "1. <strong>김마케팅</strong> - 디지털 마케팅 전문가 (10년 경력)<br>";
            echo "2. <strong>박소셜</strong> - SNS 마케팅 전문가 (8년 경력)<br>";
            echo "3. <strong>이데이터</strong> - 빅데이터 분석 전문가 (6년 경력)<br><br>";
            
            echo "🎉 <strong>완료!</strong><br>";
            echo "<a href='/lectures/86' target='_blank' style='color: #667eea; font-weight: bold; font-size: 16px;'>";
            echo "→ 강의 페이지에서 3명의 강사 확인하기</a><br>";
            echo "<small>각 강사의 전문 이미지와 상세 소개가 카드 형태로 표시됩니다</small>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #fee; padding: 20px; border-radius: 8px; border-left: 4px solid #e53e3e;'>";
            echo "❌ <strong>업데이트 실패</strong>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #fee; padding: 20px; border-radius: 8px; border-left: 4px solid #e53e3e;'>";
        echo "❌ <strong>86번 강의를 찾을 수 없습니다.</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee; padding: 20px; border-radius: 8px; border-left: 4px solid #e53e3e;'>";
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

<script>
// 자동으로 업데이트 완료 후 페이지를 새로고침하여 결과 확인
setTimeout(function() {
    if (document.querySelector('.f0fff4')) {
        console.log('✅ 86번 강의 업데이트 완료!');
    }
}, 1000);
</script>