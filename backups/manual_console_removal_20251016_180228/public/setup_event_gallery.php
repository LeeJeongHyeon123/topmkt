<?php
/**
 * 행사 이미지 갤러리 기능 설정
 * - event_images 테이블 생성
 * - 행사 122번에 샘플 이미지 추가
 * - YouTube 동영상 필드 추가
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>🖼️ 행사 이미지 갤러리 기능 설정</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<h2>1. event_images 테이블 생성</h2>\n";
    
    // event_images 테이블 생성
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS event_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            alt_text VARCHAR(200),
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES lectures(id) ON DELETE CASCADE,
            INDEX idx_event_id (event_id),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTableSql);
    echo "<p>✅ event_images 테이블이 생성되었습니다.</p>\n";
    
    echo "<h2>2. YouTube 동영상 필드 추가</h2>\n";
    
    // YouTube 동영상 필드가 없으면 추가
    try {
        $checkColumnSql = "SHOW COLUMNS FROM lectures LIKE 'youtube_video'";
        $columnExists = $pdo->query($checkColumnSql)->fetch();
        
        if (!$columnExists) {
            $addColumnSql = "ALTER TABLE lectures ADD COLUMN youtube_video VARCHAR(500) NULL COMMENT 'YouTube 동영상 URL'";
            $pdo->exec($addColumnSql);
            echo "<p>✅ lectures 테이블에 youtube_video 컬럼이 추가되었습니다.</p>\n";
        } else {
            echo "<p>ℹ️ youtube_video 컬럼이 이미 존재합니다.</p>\n";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ YouTube 컬럼 추가 실패: " . $e->getMessage() . "</p>\n";
    }
    
    echo "<h2>3. 행사 122번 설정</h2>\n";
    
    // 행사 122번에 강사 프로필 이미지 경로 업데이트
    $updateInstructorImageSql = "
        UPDATE lectures SET 
            instructor_image = '/assets/uploads/instructor-profile-122.jpg'
        WHERE id = 122 AND content_type = 'event'
    ";
    
    try {
        // instructor_image 컬럼이 없으면 추가
        $checkInstructorImageSql = "SHOW COLUMNS FROM lectures LIKE 'instructor_image'";
        $instructorImageExists = $pdo->query($checkInstructorImageSql)->fetch();
        
        if (!$instructorImageExists) {
            $addInstructorImageSql = "ALTER TABLE lectures ADD COLUMN instructor_image VARCHAR(500) NULL COMMENT '강사 프로필 이미지'";
            $pdo->exec($addInstructorImageSql);
            echo "<p>✅ instructor_image 컬럼이 추가되었습니다.</p>\n";
        }
        
        $pdo->exec($updateInstructorImageSql);
        echo "<p>✅ 행사 122번에 강사 프로필 이미지가 설정되었습니다.</p>\n";
        
    } catch (Exception $e) {
        echo "<p>⚠️ 강사 이미지 설정 실패: " . $e->getMessage() . "</p>\n";
    }
    
    // YouTube 동영상 추가
    $updateYouTubeSql = "
        UPDATE lectures SET 
            youtube_video = 'https://www.youtube.com/embed/xIBjDGPDPw0'
        WHERE id = 122 AND content_type = 'event'
    ";
    
    $pdo->exec($updateYouTubeSql);
    echo "<p>✅ 행사 122번에 YouTube 동영상이 설정되었습니다.</p>\n";
    
    echo "<h2>4. 샘플 이미지 추가</h2>\n";
    
    // 기존 이미지 삭제
    $deleteImagesSql = "DELETE FROM event_images WHERE event_id = 122";
    $pdo->exec($deleteImagesSql);
    
    // 샘플 이미지 데이터
    $sampleImages = [
        [
            'image_path' => '/assets/uploads/events/marketing-workshop-main.jpg',
            'alt_text' => '여름 마케팅 전략 워크샵 메인 이미지',
            'sort_order' => 1
        ],
        [
            'image_path' => '/assets/uploads/events/marketing-workshop-audience.jpg', 
            'alt_text' => '워크샵 참가자들 모습',
            'sort_order' => 2
        ],
        [
            'image_path' => '/assets/uploads/events/marketing-workshop-presentation.jpg',
            'alt_text' => '강의 진행 모습',
            'sort_order' => 3
        ],
        [
            'image_path' => '/assets/uploads/events/marketing-workshop-networking.jpg',
            'alt_text' => '네트워킹 세션 모습',
            'sort_order' => 4
        ]
    ];
    
    $insertImageSql = "
        INSERT INTO event_images (event_id, image_path, alt_text, sort_order) 
        VALUES (122, ?, ?, ?)
    ";
    
    $stmt = $pdo->prepare($insertImageSql);
    $imageCount = 0;
    
    foreach ($sampleImages as $image) {
        try {
            $stmt->execute([
                $image['image_path'],
                $image['alt_text'],
                $image['sort_order']
            ]);
            $imageCount++;
            echo "<p>✅ 이미지 추가됨: {$image['alt_text']}</p>\n";
        } catch (Exception $e) {
            echo "<p>❌ 이미지 추가 실패: {$image['alt_text']} - " . $e->getMessage() . "</p>\n";
        }
    }
    
    echo "<h2>🎊 설정 완료!</h2>\n";
    echo "<p><strong>설정된 내용:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ event_images 테이블 생성</li>\n";
    echo "<li>✅ YouTube 동영상 필드 추가</li>\n";
    echo "<li>✅ 강사 프로필 이미지 설정</li>\n";
    echo "<li>✅ YouTube 동영상 URL 설정</li>\n";
    echo "<li>✅ 샘플 이미지 {$imageCount}개 추가</li>\n";
    echo "</ul>\n";
    
    echo "<p><strong>다음 단계:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>1. 행사 상세 페이지에 이미지 갤러리 코드 추가</li>\n";
    echo "<li>2. 행사 상세 페이지에 YouTube 동영상 표시 코드 추가</li>\n";
    echo "<li>3. EventController에 이미지 조회 메서드 추가</li>\n";
    echo "</ul>\n";
    
    echo "<p><a href='/events/detail?id=122' style='background:#4A90E2;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;'>➡️ 행사 122번 보기</a></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ 오류: " . $e->getMessage() . "</p>\n";
    error_log("setup_event_gallery.php 오류: " . $e->getMessage());
}
?>