<?php
/**
 * 86번 강의 정보 확인 및 수정 방법 제시 스크립트
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>🔍 86번 강의 정보 확인</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 86번 강의 정보 조회
    echo "<h2>1. 86번 강의 현재 정보</h2>\n";
    $stmt = $pdo->prepare('SELECT * FROM lectures WHERE id = ?');
    $stmt->execute([86]);
    $lecture = $stmt->fetch();
    
    if ($lecture) {
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>✅ 86번 강의 발견!</h3>\n";
        echo "<p><strong>ID:</strong> {$lecture['id']}</p>\n";
        echo "<p><strong>제목:</strong> {$lecture['title']}</p>\n";
        echo "<p><strong>현재 강사명:</strong> {$lecture['instructor_name']}</p>\n";
        echo "<p><strong>현재 강사 소개:</strong> " . ($lecture['instructor_info'] ?: '(없음)') . "</p>\n";
        echo "<p><strong>설명:</strong> " . substr($lecture['description'], 0, 100) . "...</p>\n";
        echo "<p><strong>일정:</strong> {$lecture['start_date']} {$lecture['start_time']} ~ {$lecture['end_time']}</p>\n";
        echo "<p><strong>장소:</strong> {$lecture['venue_name']}</p>\n";
        echo "<p><strong>상태:</strong> {$lecture['status']}</p>\n";
        echo "</div>\n";
        
        // 3명의 강사로 수정하는 방법 제시
        echo "<h2>2. 🎯 3명의 강사로 수정하는 방법</h2>\n";
        
        echo "<h3>방법 1: 간단한 방식 (추천)</h3>\n";
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<p><strong>강사명:</strong> 콤마(,)로 구분하여 3명의 이름 나열</p>\n";
        echo "<p><strong>강사 소개:</strong> 슬래시(/)로 구분하여 각자의 전문 분야 설명</p>\n";
        echo "</div>\n";
        
        echo "<h4>🔧 실행할 SQL 쿼리:</h4>\n";
        echo "<textarea style='width: 100%; height: 120px; font-family: monospace;' readonly>\n";
        echo "UPDATE lectures SET \n";
        echo "  instructor_name = '김마케팅, 박소셜, 이데이터',\n";
        echo "  instructor_info = '김마케팅: 10년 경력 디지털 마케팅 전문가, 다수 기업 컨설팅 경험 / 박소셜: SNS 마케팅 및 인플루언서 마케팅 전문가, 바이럴 캠페인 기획 / 이데이터: 빅데이터 분석 및 마케팅 인사이트 전문가, AI 마케팅 도구 활용'\n";
        echo "WHERE id = 86;\n";
        echo "</textarea>\n";
        
        echo "<h3>방법 2: JSON 구조화 방식 (고급)</h3>\n";
        echo "<div style='background: #fff8e1; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<p>강사 정보를 JSON 형태로 구조화하여 저장하는 방법</p>\n";
        echo "<p>향후 강사별 개별 정보 조회가 쉬워집니다.</p>\n";
        echo "</div>\n";
        
        echo "<h4>🔧 실행할 SQL 쿼리:</h4>\n";
        echo "<textarea style='width: 100%; height: 150px; font-family: monospace;' readonly>\n";
        echo "UPDATE lectures SET \n";
        echo "  instructor_name = '김마케팅 외 2명',\n";
        echo "  instructor_info = '{\"instructors\": [{\"name\": \"김마케팅\", \"specialty\": \"디지털 마케팅\", \"experience\": \"10년 경력\", \"description\": \"다수 기업 컨설팅 경험\"}, {\"name\": \"박소셜\", \"specialty\": \"SNS 마케팅\", \"experience\": \"8년 경력\", \"description\": \"바이럴 캠페인 기획 전문\"}, {\"name\": \"이데이터\", \"specialty\": \"데이터 분석\", \"experience\": \"6년 경력\", \"description\": \"AI 마케팅 도구 활용 전문\"}]}'\n";
        echo "WHERE id = 86;\n";
        echo "</textarea>\n";
        
        // 실제 수정 버튼 제공
        echo "<h2>3. 🚀 바로 수정하기</h2>\n";
        echo "<form method='post' style='margin: 20px 0;'>\n";
        echo "<input type='hidden' name='action' value='update_simple'>\n";
        echo "<button type='submit' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>방법 1로 수정하기 (간단한 방식)</button>\n";
        echo "</form>\n";
        
        echo "<form method='post' style='margin: 20px 0;'>\n";
        echo "<input type='hidden' name='action' value='update_json'>\n";
        echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>방법 2로 수정하기 (JSON 방식)</button>\n";
        echo "</form>\n";
        
    } else {
        echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>❌ 86번 강의가 존재하지 않습니다</h3>\n";
        echo "</div>\n";
        
        // 현재 존재하는 강의 목록 확인
        $count = $pdo->query('SELECT COUNT(*) FROM lectures')->fetchColumn();
        echo "<p>현재 강의 총 개수: <strong>{$count}개</strong></p>\n";
        
        if ($count > 0) {
            echo "<h3>📋 기존 강의 목록 (최대 10개)</h3>\n";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr><th>ID</th><th>제목</th><th>강사명</th><th>상태</th></tr>\n";
            
            $stmt = $pdo->query('SELECT id, title, instructor_name, status FROM lectures ORDER BY id DESC LIMIT 10');
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>" . substr($row['title'], 0, 30) . "...</td>";
                echo "<td>{$row['instructor_name']}</td>";
                echo "<td>{$row['status']}</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
        
        // 86번 강의 새로 생성하기
        echo "<h3>💡 86번 강의를 새로 생성하는 방법</h3>\n";
        echo "<form method='post' style='margin: 20px 0;'>\n";
        echo "<input type='hidden' name='action' value='create_new'>\n";
        echo "<button type='submit' style='background: #6f42c1; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>86번으로 새 강의 생성하기 (3명 강사)</button>\n";
        echo "</form>\n";
    }
    
    // POST 요청 처리
    if ($_POST && isset($_POST['action'])) {
        echo "<h2>4. 🔄 수정 결과</h2>\n";
        
        switch ($_POST['action']) {
            case 'update_simple':
                $sql = "UPDATE lectures SET 
                    instructor_name = '김마케팅, 박소셜, 이데이터',
                    instructor_info = '김마케팅: 10년 경력 디지털 마케팅 전문가, 다수 기업 컨설팅 경험 / 박소셜: SNS 마케팅 및 인플루언서 마케팅 전문가, 바이럴 캠페인 기획 / 이데이터: 빅데이터 분석 및 마케팅 인사이트 전문가, AI 마케팅 도구 활용'
                WHERE id = 86";
                
                if ($pdo->exec($sql)) {
                    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>\n";
                    echo "✅ 86번 강의가 성공적으로 3명의 강사로 수정되었습니다! (간단한 방식)\n";
                    echo "</div>\n";
                } else {
                    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>\n";
                    echo "❌ 수정에 실패했습니다.\n";
                    echo "</div>\n";
                }
                break;
                
            case 'update_json':
                $sql = "UPDATE lectures SET 
                    instructor_name = '김마케팅 외 2명',
                    instructor_info = '{\"instructors\": [{\"name\": \"김마케팅\", \"specialty\": \"디지털 마케팅\", \"experience\": \"10년 경력\", \"description\": \"다수 기업 컨설팅 경험\"}, {\"name\": \"박소셜\", \"specialty\": \"SNS 마케팅\", \"experience\": \"8년 경력\", \"description\": \"바이럴 캠페인 기획 전문\"}, {\"name\": \"이데이터\", \"specialty\": \"데이터 분석\", \"experience\": \"6년 경력\", \"description\": \"AI 마케팅 도구 활용 전문\"}]}'
                WHERE id = 86";
                
                if ($pdo->exec($sql)) {
                    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>\n";
                    echo "✅ 86번 강의가 성공적으로 3명의 강사로 수정되었습니다! (JSON 방식)\n";
                    echo "</div>\n";
                } else {
                    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>\n";
                    echo "❌ 수정에 실패했습니다.\n";
                    echo "</div>\n";
                }
                break;
                
            case 'create_new':
                // 기존 사용자 ID 확인
                $firstUser = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch();
                $userId = $firstUser ? $firstUser['id'] : 1;
                
                $sql = "INSERT INTO lectures (
                    id, user_id, title, description, 
                    instructor_name, instructor_info,
                    start_date, end_date, start_time, end_time,
                    location_type, venue_name, venue_address,
                    max_participants, registration_fee, category, status,
                    created_at
                ) VALUES (
                    86, ?, '3인 전문가 공동 강의',
                    '마케팅, SNS, 데이터 분석 전문가가 함께하는 종합 강의입니다. 각 분야의 전문가들이 실무 경험을 바탕으로 한 실전 노하우를 공유합니다.',
                    '김마케팅, 박소셜, 이데이터',
                    '김마케팅: 10년 경력 디지털 마케팅 전문가, 다수 기업 컨설팅 경험 / 박소셜: SNS 마케팅 및 인플루언서 마케팅 전문가, 바이럴 캠페인 기획 / 이데이터: 빅데이터 분석 및 마케팅 인사이트 전문가, AI 마케팅 도구 활용',
                    '2025-08-15', '2025-08-15', '14:00:00', '17:00:00',
                    'offline', '서울 강남구 세미나실', '서울특별시 강남구 테헤란로 123',
                    50, 80000, 'seminar', 'published',
                    NOW()
                )";
                
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$userId])) {
                    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>\n";
                    echo "✅ 86번 강의가 새로 생성되었습니다! (3명의 강사로 구성)\n";
                    echo "</div>\n";
                } else {
                    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>\n";
                    echo "❌ 생성에 실패했습니다. (이미 86번이 존재하거나 다른 오류)\n";
                    echo "</div>\n";
                }
                break;
        }
        
        // 수정 후 다시 조회해서 결과 확인
        echo "<h3>📋 수정 후 86번 강의 정보</h3>\n";
        $stmt = $pdo->prepare('SELECT instructor_name, instructor_info FROM lectures WHERE id = ?');
        $stmt->execute([86]);
        $updatedLecture = $stmt->fetch();
        
        if ($updatedLecture) {
            echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
            echo "<p><strong>수정된 강사명:</strong> {$updatedLecture['instructor_name']}</p>\n";
            echo "<p><strong>수정된 강사 소개:</strong> {$updatedLecture['instructor_info']}</p>\n";
            echo "</div>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>\n";
    echo "<p style='color: red;'>{$e->getMessage()}</p>\n";
}

// 새로고침 버튼
echo "<br><a href='" . $_SERVER['PHP_SELF'] . "' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 새로고침</a>\n";
?>