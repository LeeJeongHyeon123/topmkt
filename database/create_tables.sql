-- 탑마케팅 데이터베이스 테이블 생성 스크립트
-- MariaDB/MySQL 용

-- 데이터베이스 생성 (필요한 경우)
-- CREATE DATABASE IF NOT EXISTS topmkt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE TOPMKT;

-- 1. 회원 테이블 (users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(13) NOT NULL UNIQUE COMMENT '휴대폰 번호 (010-1234-5678)',
    nickname VARCHAR(20) NOT NULL UNIQUE COMMENT '닉네임',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT '이메일',
    password VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호',
    
    -- 인증 관련
    phone_verified BOOLEAN DEFAULT FALSE COMMENT '휴대폰 인증 여부',
    phone_verified_at TIMESTAMP NULL COMMENT '휴대폰 인증 완료 시간',
    email_verified BOOLEAN DEFAULT FALSE COMMENT '이메일 인증 여부',
    email_verified_at TIMESTAMP NULL COMMENT '이메일 인증 완료 시간',
    
    -- 약관 동의
    terms_agreed BOOLEAN DEFAULT FALSE COMMENT '이용약관 동의',
    privacy_agreed BOOLEAN DEFAULT FALSE COMMENT '개인정보처리방침 동의',
    marketing_agreed BOOLEAN DEFAULT FALSE COMMENT '마케팅 정보 수신 동의',
    
    -- 사용자 정보
    status ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED', 'DELETED') DEFAULT 'ACTIVE' COMMENT '계정 상태',
    role ENUM('GENERAL', 'PREMIUM', 'ADMIN', 'SUPER_ADMIN') DEFAULT 'GENERAL' COMMENT '사용자 역할',
    
    -- 프로필 정보
    profile_image VARCHAR(255) NULL COMMENT '프로필 이미지 경로',
    bio TEXT NULL COMMENT '자기소개',
    birth_date DATE NULL COMMENT '생년월일',
    gender ENUM('M', 'F', 'OTHER') NULL COMMENT '성별',
    
    -- 로그인 관련
    last_login_at TIMESTAMP NULL COMMENT '마지막 로그인 시간',
    last_login_ip VARCHAR(45) NULL COMMENT '마지막 로그인 IP',
    login_count INT DEFAULT 0 COMMENT '총 로그인 횟수',
    
    -- 보안 관련
    password_changed_at TIMESTAMP NULL COMMENT '비밀번호 변경 시간',
    failed_login_attempts INT DEFAULT 0 COMMENT '로그인 실패 횟수',
    locked_until TIMESTAMP NULL COMMENT '계정 잠금 해제 시간',
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '가입일',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '정보 수정일',
    deleted_at TIMESTAMP NULL COMMENT '탈퇴일'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='회원 정보';

-- 2. 회원 세션 테이블 (user_sessions)
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY COMMENT '세션 ID',
    user_id INT NOT NULL COMMENT '사용자 ID',
    ip_address VARCHAR(45) NOT NULL COMMENT 'IP 주소',
    user_agent TEXT NULL COMMENT 'User Agent',
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '마지막 활동 시간',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '세션 생성 시간',
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 세션 관리';

-- 3. 회원 로그 테이블 (user_logs)
CREATE TABLE IF NOT EXISTS user_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL COMMENT '사용자 ID (비회원 활동도 기록)',
    action VARCHAR(50) NOT NULL COMMENT '활동 유형 (LOGIN, LOGOUT, SIGNUP, etc.)',
    description TEXT NULL COMMENT '상세 설명',
    ip_address VARCHAR(45) NOT NULL COMMENT 'IP 주소',
    user_agent TEXT NULL COMMENT 'User Agent',
    extra_data JSON NULL COMMENT '추가 데이터',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '기록 시간',
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 활동 로그';

-- 4. 인증번호 임시 저장 테이블 (verification_codes)
CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(13) NOT NULL COMMENT '휴대폰 번호',
    code VARCHAR(6) NOT NULL COMMENT '인증번호',
    type ENUM('SIGNUP', 'LOGIN', 'PASSWORD_RESET') NOT NULL COMMENT '인증 유형',
    attempts INT DEFAULT 0 COMMENT '시도 횟수',
    is_used BOOLEAN DEFAULT FALSE COMMENT '사용 여부',
    expires_at TIMESTAMP NOT NULL COMMENT '만료 시간',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 시간',
    
    INDEX idx_phone (phone),
    INDEX idx_code (code),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='인증번호 임시 저장';

-- 5. 커뮤니티 게시글 테이블 (posts) - 향후 확장용
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT '작성자 ID',
    category_id INT NULL COMMENT '카테고리 ID',
    title VARCHAR(200) NOT NULL COMMENT '제목',
    content TEXT NOT NULL COMMENT '내용',
    view_count INT DEFAULT 0 COMMENT '조회수',
    like_count INT DEFAULT 0 COMMENT '좋아요 수',
    comment_count INT DEFAULT 0 COMMENT '댓글 수',
    is_pinned BOOLEAN DEFAULT FALSE COMMENT '공지 고정 여부',
    is_hidden BOOLEAN DEFAULT FALSE COMMENT '숨김 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '작성일',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='커뮤니티 게시글';

-- 6. 댓글 테이블 (comments) - 향후 확장용
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL COMMENT '게시글 ID',
    user_id INT NOT NULL COMMENT '작성자 ID',
    parent_id INT NULL COMMENT '부모 댓글 ID (대댓글)',
    content TEXT NOT NULL COMMENT '댓글 내용',
    like_count INT DEFAULT 0 COMMENT '좋아요 수',
    is_hidden BOOLEAN DEFAULT FALSE COMMENT '숨김 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '작성일',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='댓글';

-- 7. 설정 테이블 (settings) - 시스템 설정용
CREATE TABLE IF NOT EXISTS settings (
    key_name VARCHAR(100) PRIMARY KEY COMMENT '설정 키',
    value TEXT NULL COMMENT '설정 값',
    description VARCHAR(255) NULL COMMENT '설정 설명',
    type ENUM('STRING', 'INTEGER', 'BOOLEAN', 'JSON') DEFAULT 'STRING' COMMENT '값 타입',
    is_public BOOLEAN DEFAULT FALSE COMMENT '공개 설정 여부',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='시스템 설정';

-- 기본 설정 값 삽입
INSERT INTO settings (key_name, value, description, type, is_public) VALUES
('site_name', '탑마케팅', '사이트 이름', 'STRING', TRUE),
('site_description', '글로벌 네트워크 마케팅 커뮤니티', '사이트 설명', 'STRING', TRUE),
('max_login_attempts', '5', '최대 로그인 시도 횟수', 'INTEGER', FALSE),
('session_timeout', '7200', '세션 타임아웃 (초)', 'INTEGER', FALSE),
('signup_enabled', 'true', '회원가입 활성화 여부', 'BOOLEAN', TRUE),
('maintenance_mode', 'false', '점검 모드 여부', 'BOOLEAN', TRUE)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 인덱스 추가 (성능 최적화)
ALTER TABLE users ADD INDEX idx_phone (phone);
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_nickname (nickname);
ALTER TABLE users ADD INDEX idx_status (status);
ALTER TABLE users ADD INDEX idx_created_at (created_at);

-- 초기 관리자 계정 생성 (비밀번호: admin123!)
-- 실제 운영에서는 보안상 삭제하고 별도로 생성
INSERT INTO users (
    phone, nickname, email, password, 
    phone_verified, phone_verified_at,
    terms_agreed, privacy_agreed,
    role, status,
    created_at
) VALUES (
    '010-0000-0000', 
    '관리자', 
    'admin@topmktx.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- admin123!
    TRUE,
    NOW(),
    TRUE,
    TRUE,
    'SUPER_ADMIN',
    'ACTIVE',
    NOW()
) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 완료 메시지
SELECT 'Database tables created successfully!' as message; 