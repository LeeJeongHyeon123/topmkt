<?php
/**
 * 강의 일정 테이블 생성 스크립트
 * URL: /create_tables.php?token=create_lectures_2025
 */

// 보안을 위한 간단한 토큰 체크
$token = $_GET['token'] ?? '';
if ($token !== 'create_lectures_2025') {
    http_response_code(403);
    die('접근이 거부되었습니다. 올바른 토큰이 필요합니다.');
}

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 필요한 파일 로드
require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

try {
    echo "<h1>🎓 강의 일정 테이블 생성</h1>\n";
    echo "<p>생성 시작 시간: " . date('Y-m-d H:i:s') . "</p>\n";
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 1. lectures 테이블 생성 (기존 테이블이 있으면 건너뛰기)
    echo "<h2>1. lectures 테이블 생성</h2>\n";
    
    // 기존 테이블이 있으면 건너뛰기
    $checkTable = $pdo->query("SHOW TABLES LIKE 'lectures'")->fetch();
    if ($checkTable) {
        echo "ℹ️ 기존 lectures 테이블이 이미 존재합니다 - 건너뛰기<br>\n";
    } else {
    
    $lecturesTable = "
    CREATE TABLE lectures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL COMMENT '작성자 ID (기업회원만 가능)',
        title VARCHAR(200) NOT NULL COMMENT '강의 제목',
        description TEXT NOT NULL COMMENT '강의 설명',
        instructor_name VARCHAR(100) NOT NULL COMMENT '강사명',
        instructor_info TEXT NULL COMMENT '강사 소개',
        
        -- 일정 정보
        start_date DATE NOT NULL COMMENT '시작 날짜',
        end_date DATE NOT NULL COMMENT '종료 날짜',
        start_time TIME NOT NULL COMMENT '시작 시간',
        end_time TIME NOT NULL COMMENT '종료 시간',
        timezone VARCHAR(50) DEFAULT 'Asia/Seoul' COMMENT '시간대',
        
        -- 장소 정보
        location_type ENUM('online', 'offline', 'hybrid') DEFAULT 'offline' COMMENT '진행 방식',
        venue_name VARCHAR(200) NULL COMMENT '장소명',
        venue_address TEXT NULL COMMENT '장소 주소',
        online_link VARCHAR(500) NULL COMMENT '온라인 링크 (Zoom, 유튜브 등)',
        
        -- 참가 관련
        max_participants INT NULL COMMENT '최대 참가자 수 (NULL = 무제한)',
        registration_fee INT DEFAULT 0 COMMENT '참가비 (원)',
        registration_deadline DATETIME NULL COMMENT '등록 마감일',
        
        -- 카테고리 및 태그
        category ENUM('seminar', 'workshop', 'conference', 'webinar', 'training') DEFAULT 'seminar' COMMENT '강의 유형',
        difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'all') DEFAULT 'all' COMMENT '난이도',
        tags JSON NULL COMMENT '태그 (JSON 배열)',
        
        -- 추가 정보
        banner_image VARCHAR(500) NULL COMMENT '배너 이미지 경로',
        attachments JSON NULL COMMENT '첨부파일 (JSON 배열)',
        requirements TEXT NULL COMMENT '참가 요구사항',
        benefits TEXT NULL COMMENT '혜택/수료증 정보',
        
        -- 상태 관리
        status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft' COMMENT '상태',
        is_featured BOOLEAN DEFAULT FALSE COMMENT '추천 강의 여부',
        view_count INT DEFAULT 0 COMMENT '조회수',
        registration_count INT DEFAULT 0 COMMENT '신청자 수',
        
        -- 시스템 필드
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- 인덱스
        INDEX idx_user_id (user_id),
        INDEX idx_start_date (start_date),
        INDEX idx_category (category),
        INDEX idx_status (status),
        INDEX idx_featured (is_featured),
        
        -- 외래키
        CONSTRAINT fk_lectures_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='강의/행사 정보'";
    
    $pdo->exec($lecturesTable);
    echo "✅ lectures 테이블 생성 완료<br>\n";
    
    // 2. lecture_registrations 테이블 생성
    echo "<h2>2. lecture_registrations 테이블 생성</h2>\n";
    $registrationsTable = "
    CREATE TABLE IF NOT EXISTS lecture_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lecture_id INT NOT NULL COMMENT '강의 ID',
        user_id INT NOT NULL COMMENT '신청자 ID',
        
        -- 신청 정보
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '신청일',
        status ENUM('pending', 'confirmed', 'cancelled', 'attended', 'no_show') DEFAULT 'pending' COMMENT '신청 상태',
        
        -- 추가 정보
        participant_name VARCHAR(100) NOT NULL COMMENT '참가자명',
        participant_email VARCHAR(100) NOT NULL COMMENT '참가자 이메일',
        participant_phone VARCHAR(20) NULL COMMENT '참가자 연락처',
        company_name VARCHAR(200) NULL COMMENT '소속회사',
        position VARCHAR(100) NULL COMMENT '직급/직책',
        special_requests TEXT NULL COMMENT '특별 요청사항',
        
        -- 결제 정보 (향후 확장용)
        payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending' COMMENT '결제 상태',
        payment_amount INT DEFAULT 0 COMMENT '결제 금액',
        payment_date TIMESTAMP NULL COMMENT '결제일',
        
        -- 시스템 필드
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- 인덱스
        INDEX idx_lecture_id (lecture_id),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_registration_date (registration_date),
        
        -- 유니크 제약 (중복 신청 방지)
        UNIQUE KEY uk_lecture_user (lecture_id, user_id),
        
        -- 외래키
        CONSTRAINT fk_registrations_lecture_id FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
        CONSTRAINT fk_registrations_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='강의 신청 정보'";
    
    $pdo->exec($registrationsTable);
    echo "✅ lecture_registrations 테이블 생성 완료<br>\n";
    
    // 3. lecture_categories 테이블 생성
    echo "<h2>3. lecture_categories 테이블 생성</h2>\n";
    $categoriesTable = "
    CREATE TABLE IF NOT EXISTS lecture_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE COMMENT '카테고리명',
        description TEXT NULL COMMENT '카테고리 설명',
        color_code VARCHAR(7) DEFAULT '#007bff' COMMENT '색상 코드',
        icon VARCHAR(50) NULL COMMENT '아이콘 클래스',
        sort_order INT DEFAULT 0 COMMENT '정렬 순서',
        is_active BOOLEAN DEFAULT TRUE COMMENT '활성 상태',
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_sort_order (sort_order),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='강의 카테고리'";
    
    $pdo->exec($categoriesTable);
    echo "✅ lecture_categories 테이블 생성 완료<br>\n";
    
    // 4. 기본 카테고리 데이터 삽입
    echo "<h2>4. 기본 카테고리 데이터 삽입</h2>\n";
    
    // 기존 카테고리가 있는지 확인
    $checkCategories = $pdo->query("SELECT COUNT(*) as count FROM lecture_categories")->fetch();
    
    if ($checkCategories['count'] == 0) {
        $categories = [
            ['세미나', '전문가 강연 및 세미나', '#007bff', 'fas fa-microphone', 1],
            ['워크샵', '실습 중심 워크샵', '#28a745', 'fas fa-tools', 2],
            ['컨퍼런스', '대규모 컨퍼런스', '#dc3545', 'fas fa-users', 3],
            ['웨비나', '온라인 웨비나', '#6f42c1', 'fas fa-video', 4],
            ['교육과정', '체계적인 교육과정', '#fd7e14', 'fas fa-graduation-cap', 5]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO lecture_categories (name, description, color_code, icon, sort_order) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($categories as $category) {
            $stmt->execute($category);
            echo "✅ '{$category[0]}' 카테고리 추가 완료<br>\n";
        }
    } else {
        echo "⚠️ 기본 카테고리가 이미 존재합니다. (총 {$checkCategories['count']}개)<br>\n";
    }
    
    // 5. 테이블 생성 확인
    echo "<h2>5. 테이블 생성 확인</h2>\n";
    $tables = ['lectures', 'lecture_registrations', 'lecture_categories'];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($result) {
            echo "✅ $table 테이블 존재 확인<br>\n";
        } else {
            echo "❌ $table 테이블 생성 실패<br>\n";
        }
    }
    
    // 6. 샘플 데이터 추가 (테스트용)
    echo "<h2>6. 샘플 데이터 추가</h2>\n";
    
    // 관리자 사용자 확인 (user_id = 1)
    $adminUser = $pdo->query("SELECT id FROM users WHERE role IN ('ADMIN', 'SUPER_ADMIN') LIMIT 1")->fetch();
    
    if ($adminUser) {
        $sampleLecture = "
        INSERT IGNORE INTO lectures (
            user_id, title, description, instructor_name, 
            start_date, end_date, start_time, end_time,
            location_type, venue_name, category, status
        ) VALUES (
            {$adminUser['id']}, 
            '디지털 마케팅 전략 세미나',
            '2025년 최신 디지털 마케팅 트렌드와 실전 전략을 배우는 세미나입니다. SNS 마케팅, 콘텐츠 마케팅, 데이터 분석 등을 다룹니다.',
            '김마케팅',
            '2025-07-15', '2025-07-15', '14:00:00', '17:00:00',
            'offline', '서울 강남구 세미나실',
            'seminar', 'published'
        )";
        
        $pdo->exec($sampleLecture);
        echo "✅ 샘플 강의 데이터 추가 완료<br>\n";
    } else {
        echo "⚠️ 관리자 계정을 찾을 수 없어 샘플 데이터를 추가하지 않았습니다.<br>\n";
    }
    
    echo "<h2>🎉 모든 테이블 생성 완료!</h2>\n";
    echo "<p>완료 시간: " . date('Y-m-d H:i:s') . "</p>\n";
    
    // 보안을 위해 24시간 후 이 파일을 자동 삭제하도록 안내
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0;'>";
    echo "<strong>⚠️ 보안 안내:</strong><br>";
    echo "테이블 생성이 완료되었습니다. 보안을 위해 이 파일(/create_tables.php)을 삭제하는 것을 권장합니다.<br>";
    echo "또는 24시간 후 자동으로 접근이 차단됩니다.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>\n";
    echo "<p style='color: red;'>오류 메시지: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    error_log("강의 테이블 생성 오류: " . $e->getMessage());
}
?>