<?php
/**
 * 행사 이미지 CSS 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>행사 이미지 CSS 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    // 1. 행사 190의 이미지 정보 조회
    $db = Database::getInstance();
    $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = 190");
    
    echo "<h2>📊 행사 190 이미지 정보</h2>";
    echo "<p>총 이미지 수: " . count($images) . "개</p>";
    
    if (count($images) > 0) {
        echo "<ul>";
        foreach ($images as $image) {
            echo "<li>";
            echo "ID: " . $image['id'] . " | ";
            echo "경로: " . htmlspecialchars($image['image_path']) . " | ";
            echo "순서: " . $image['order_index'] . " | ";
            echo "상태: " . $image['status'];
            echo "</li>";
        }
        echo "</ul>";
    }
    
    // 2. 현재 CSS 스타일 표시
    echo "<h2>🎨 현재 CSS 스타일</h2>";
    echo "<style>";
    echo "
    .current-image-preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 8px;
        background: #f9f9f9;
    }
    
    .current-image-preview-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .current-image-preview-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }
    
    .current-remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }
    
    .improved-image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
        border: 1px solid #4A90E2;
        padding: 20px;
        border-radius: 8px;
        background: #f8fafc;
    }
    
    .improved-image-preview-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        width: 180px;
        height: 140px;
    }
    
    .improved-image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .improved-remove-image {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    
    .improved-remove-image:hover {
        background: rgba(220, 38, 38, 0.9);
        transform: scale(1.1);
    }
    
    @media (max-width: 768px) {
        .improved-image-preview-container {
            gap: 10px;
        }
        
        .improved-image-preview-item {
            width: 140px;
            height: 110px;
        }
    }
    ";
    echo "</style>";
    
    // 3. 현재 스타일로 이미지 표시
    echo "<h2>❌ 현재 스타일 (문제 있는 버전)</h2>";
    echo "<div class='current-image-preview-container'>";
    if (count($images) > 0) {
        foreach ($images as $image) {
            echo "<div class='current-image-preview-item'>";
            echo "<img src='" . htmlspecialchars($image['image_path']) . "' alt=''>";
            echo "<button type='button' class='current-remove-image'>×</button>";
            echo "</div>";
        }
    } else {
        echo "<p>표시할 이미지가 없습니다.</p>";
    }
    echo "</div>";
    
    // 4. 개선된 스타일로 이미지 표시
    echo "<h2>✅ 개선된 스타일 (수정 버전)</h2>";
    echo "<div class='improved-image-preview-container'>";
    if (count($images) > 0) {
        foreach ($images as $image) {
            echo "<div class='improved-image-preview-item'>";
            echo "<img src='" . htmlspecialchars($image['image_path']) . "' alt=''>";
            echo "<button type='button' class='improved-remove-image'>×</button>";
            echo "</div>";
        }
    } else {
        echo "<p>표시할 이미지가 없습니다.</p>";
    }
    echo "</div>";
    
    // 5. 차이점 설명
    echo "<h2>🔍 주요 개선사항</h2>";
    echo "<ul>";
    echo "<li><strong>레이아웃 변경</strong>: Grid에서 Flexbox로 변경하여 일관성 있는 크기 유지</li>";
    echo "<li><strong>고정 크기</strong>: 이미지 컨테이너 크기를 고정하여 균일한 표시</li>";
    echo "<li><strong>그림자 강화</strong>: 더 뚜렷한 그림자로 이미지 분리감 향상</li>";
    echo "<li><strong>버튼 개선</strong>: 삭제 버튼 크기 증가 및 호버 효과 추가</li>";
    echo "<li><strong>색상 개선</strong>: 컨테이너 테두리 색상을 브랜드 색상으로 변경</li>";
    echo "<li><strong>반응형 개선</strong>: 모바일에서 더 나은 크기 조절</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>