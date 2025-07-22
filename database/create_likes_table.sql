-- 좋아요 테이블 생성
-- 실행 방법: MariaDB 콘솔에서 다음 명령어 실행
-- mysql -u root -p
-- USE TOPMKT;
-- SOURCE /var/www/html/topmkt/database/create_likes_table.sql;

CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL COMMENT '게시글 ID',
    user_id INT NOT NULL COMMENT '사용자 ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '좋아요 누른 시간',
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- 중복 좋아요 방지를 위한 유니크 인덱스
    UNIQUE KEY unique_like (post_id, user_id),
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시글 좋아요';

-- 완료 메시지
SELECT 'post_likes 테이블 생성 완료!' as message;