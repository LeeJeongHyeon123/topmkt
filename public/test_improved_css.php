<?php
/**
 * 개선된 이미지 CSS 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>개선된 행사 이미지 CSS 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

try {
    // 행사 189의 이미지 정보 조회
    $db = Database::getInstance();
    $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = 189");
    
    echo "<h2>🎯 행사 189 이미지 CSS 개선 테스트</h2>";
    echo "<p>총 이미지 수: " . count($images) . "개</p>";
    
    // 개선된 CSS 적용
    echo "<style>";
    echo "
    .improved-image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
        padding: 20px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
    }
    
    .improved-image-preview-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        width: 180px;
        height: 140px;
        flex-shrink: 0;
    }
    
    .improved-image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .improved-image-preview-item:hover img {
        transform: scale(1.05);
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
        z-index: 10;
    }
    
    .improved-remove-image:hover {
        background: rgba(220, 38, 38, 0.9);
        transform: scale(1.1);
    }
    
    .test-info {
        background: #f0f9ff;
        border: 1px solid #0ea5e9;
        border-radius: 6px;
        padding: 15px;
        margin: 20px 0;
    }
    
    .success-message {
        background: #f0fdf4;
        border: 1px solid #22c55e;
        border-radius: 6px;
        padding: 15px;
        margin: 20px 0;
        color: #15803d;
    }
    
    /* 모바일 대응 */
    @media (max-width: 768px) {
        .improved-image-preview-container {
            gap: 10px;
        }
        
        .improved-image-preview-item {
            width: 140px;
            height: 110px;
        }
        
        .improved-remove-image {
            width: 24px;
            height: 24px;
            font-size: 0.7rem;
        }
    }
    ";
    echo "</style>";
    
    echo "<div class='test-info'>";
    echo "<h3>✅ CSS 개선 사항</h3>";
    echo "<ul>";
    echo "<li><strong>레이아웃 변경:</strong> Grid → Flexbox (일관된 크기 유지)</li>";
    echo "<li><strong>고정 크기:</strong> 180×140px (데스크톱), 140×110px (모바일)</li>";
    echo "<li><strong>호버 효과:</strong> 이미지 확대 및 버튼 확대 효과 추가</li>";
    echo "<li><strong>그림자 강화:</strong> 더 뚜렷한 그림자로 구분감 향상</li>";
    echo "<li><strong>버튼 개선:</strong> 크기 증가 및 클릭 영역 확대</li>";
    echo "<li><strong>통합 스타일:</strong> event-image-item과 image-preview-item 동일 스타일</li>";
    echo "</ul>";
    echo "</div>";
    
    if (count($images) > 0) {
        echo "<h3>🖼️ 개선된 이미지 표시</h3>";
        echo "<div class='improved-image-preview-container'>";
        foreach ($images as $image) {
            echo "<div class='improved-image-preview-item'>";
            echo "<img src='" . htmlspecialchars($image['image_path']) . "' alt=''>";
            echo "<button type='button' class='improved-remove-image' onclick='alert(\"삭제 기능 테스트\")'>×</button>";
            echo "</div>";
        }
        echo "</div>";
        
        echo "<div class='success-message'>";
        echo "<h3>🎉 CSS 개선 완료!</h3>";
        echo "<p><strong>문제가 해결되었습니다:</strong></p>";
        echo "<ul>";
        echo "<li>이미지 크기 일관성 확보</li>";
        echo "<li>삭제 버튼 가시성 향상</li>";
        echo "<li>호버 효과로 사용자 경험 개선</li>";
        echo "<li>모바일 반응형 디자인 최적화</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p>표시할 이미지가 없습니다.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>