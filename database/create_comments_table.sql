-- 댓글 테이블 생성 SQL
-- 데이터베이스: topmkt
-- 사용자가 직접 실행해야 함

USE TOPMKT;

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL DEFAULT NULL,
    content TEXT NOT NULL,
    status ENUM('active', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 인덱스
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
    
    -- 외래키 제약조건 (필요시 주석 해제)
    -- FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    -- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    -- FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;