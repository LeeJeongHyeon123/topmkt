<?php
/**
 * 사용자 프로필 이미지 확인 도구
 */

require_once '../src/config/database.php';

echo "<h1>사용자 프로필 이미지 확인</h1>";

try {
    $db = Database::getInstance();
    
    // 안계현 회원 정보 조회
    $user = $db->fetch("SELECT * FROM users WHERE nickname = ?", ['안계현']);
    
    if (!$user) {
        echo "<p>❌ '안계현' 닉네임의 사용자를 찾을 수 없습니다.</p>";
        
        // 비슷한 닉네임 찾기
        $similarUsers = $db->fetchAll("SELECT id, nickname, name FROM users WHERE nickname LIKE '%계현%' OR name LIKE '%계현%' LIMIT 5");
        
        if ($similarUsers) {
            echo "<h3>비슷한 사용자들:</h3>";
            echo "<ul>";
            foreach ($similarUsers as $similar) {
                echo "<li>ID: {$similar['id']}, 닉네임: {$similar['nickname']}, 이름: {$similar['name']}</li>";
            }
            echo "</ul>";
        }
        exit;
    }
    
    echo "<h2>✅ 사용자 정보</h2>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $user['id'] . "</li>";
    echo "<li><strong>닉네임:</strong> " . htmlspecialchars($user['nickname']) . "</li>";
    echo "<li><strong>이름:</strong> " . htmlspecialchars($user['name']) . "</li>";
    echo "<li><strong>이메일:</strong> " . htmlspecialchars($user['email']) . "</li>";
    echo "<li><strong>가입일:</strong> " . $user['created_at'] . "</li>";
    echo "</ul>";
    
    echo "<h2>📸 프로필 이미지 정보</h2>";
    
    // 프로필 이미지 컬럼 확인
    $profileImageColumns = ['profile_image', 'avatar', 'image', 'profile_picture'];
    $imageInfo = [];
    
    foreach ($profileImageColumns as $column) {
        if (isset($user[$column])) {
            $imageInfo[$column] = $user[$column];
        }
    }
    
    if (empty($imageInfo)) {
        echo "<p>❌ 프로필 이미지 관련 컬럼을 찾을 수 없습니다.</p>";
    } else {
        echo "<ul>";
        foreach ($imageInfo as $column => $value) {
            $status = empty($value) ? "❌ 없음" : "✅ 있음";
            echo "<li><strong>{$column}:</strong> {$status}";
            if (!empty($value)) {
                echo " - " . htmlspecialchars($value);
                
                // 이미지 파일 존재 확인
                if (strpos($value, '/') !== false) {
                    $imagePath = '/workspace/public' . $value;
                    if (file_exists($imagePath)) {
                        $fileSize = filesize($imagePath);
                        echo " (파일 존재, " . number_format($fileSize) . " bytes)";
                    } else {
                        echo " <span style='color: red;'>(파일 없음)</span>";
                    }
                }
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
    // 프로필 이미지 표시 테스트
    if (!empty($imageInfo)) {
        echo "<h2>🖼️ 프로필 이미지 미리보기</h2>";
        
        foreach ($imageInfo as $column => $value) {
            if (!empty($value)) {
                echo "<div style='margin: 10px 0;'>";
                echo "<h4>{$column}:</h4>";
                
                // 상대 경로면 절대 경로로 변환
                $imageUrl = $value;
                if (strpos($value, 'http') !== 0) {
                    $imageUrl = 'https://www.topmktx.com' . $value;
                }
                
                echo "<p>URL: <a href='{$imageUrl}' target='_blank'>{$imageUrl}</a></p>";
                echo "<img src='{$imageUrl}' alt='프로필 이미지' style='max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 8px;' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>";
                echo "<p style='display: none; color: red;'>❌ 이미지를 로드할 수 없습니다.</p>";
                echo "</div>";
            }
        }
    }
    
    // 채팅방에서 해당 사용자 정보 확인
    echo "<h2>💬 채팅 관련 정보</h2>";
    
    // room_4_5에서 참여자 확인
    echo "<p>📂 room_4_5 채팅방 관련:</p>";
    echo "<ul>";
    echo "<li>사용자 ID {$user['id']}가 room_4_5에 참여하는지 확인 필요</li>";
    echo "<li>Firebase에서 해당 사용자의 정보를 가져와야 함</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; }
ul { background: #f5f5f5; padding: 15px; border-radius: 5px; }
img { margin: 10px 0; }
</style>