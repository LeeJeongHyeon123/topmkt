-- 게시글 ID 3에 100,000개 댓글 생성 스크립트
-- 실행 명령어: SOURCE /var/www/html/topmkt/create_test_comments.sql;

-- 트랜잭션 시작
START TRANSACTION;

-- 변수 선언을 위한 프로시저 생성
DELIMITER //

CREATE PROCEDURE CreateTestComments()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE j INT DEFAULT 1;
    DECLARE parent_comment_id INT;
    DECLARE user_id_to_use INT DEFAULT 4; -- 우리집탄이 유저 ID 사용
    DECLARE comment_content TEXT;
    DECLARE reply_content TEXT;
    
    -- 총 70,000개의 일반 댓글과 30,000개의 대댓글 생성
    WHILE i <= 70000 DO
        -- 일반 댓글 내용 생성 (다양한 길이의 내용)
        SET comment_content = CONCAT(
            '테스트 댓글 #', i, ' - ',
            CASE 
                WHEN i % 5 = 0 THEN '이것은 긴 댓글입니다. 마케팅에 대한 다양한 의견을 나누고 싶습니다. 최근 디지털 마케팅 트렌드가 많이 변화하고 있는데, 특히 소셜미디어 마케팅과 인플루언서 마케팅이 중요해지고 있다고 생각합니다. 또한 개인화된 콘텐츠 제공과 데이터 기반 마케팅 전략이 핵심이 되고 있습니다.'
                WHEN i % 4 = 0 THEN '중간 길이의 댓글입니다. 오늘 배운 마케팅 전략이 정말 유용했어요. 실무에 바로 적용해볼 수 있을 것 같습니다. 특히 고객 세그멘테이션 부분이 인상적이었습니다.'
                WHEN i % 3 = 0 THEN '짧은 댓글이지만 의미있는 내용입니다. 마케팅은 결국 사람의 마음을 읽는 것이라고 생각해요.'
                WHEN i % 2 = 0 THEN '좋은 정보 감사합니다! 마케팅 분야에서 일하고 있는데 많은 도움이 되었습니다. 앞으로도 이런 유익한 콘텐츠 부탁드려요.'
                ELSE '기본 댓글 내용입니다. 유용한 정보 공유해주셔서 감사합니다.'
            END
        );
        
        -- 일반 댓글 삽입
        INSERT INTO comments (post_id, user_id, parent_id, content, status, created_at, updated_at) 
        VALUES (3, user_id_to_use, NULL, comment_content, 'active', 
                DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY) + INTERVAL FLOOR(RAND() * 86400) SECOND,
                DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY) + INTERVAL FLOOR(RAND() * 86400) SECOND);
        
        -- 30% 확률로 대댓글 생성 (i가 30000 이하일 때만)
        IF i <= 30000 AND RAND() < 0.3 THEN
            SET parent_comment_id = LAST_INSERT_ID();
            
            -- 대댓글 내용 생성
            SET reply_content = CONCAT(
                '답글 #', i, ' - ',
                CASE 
                    WHEN i % 4 = 0 THEN '윗분 말씀에 완전 공감합니다! 저도 비슷한 경험이 있어서 더욱 와닿네요. 마케팅은 정말 끊임없이 공부해야 하는 분야인 것 같아요. 트렌드 변화도 빠르고 소비자 행동 패턴도 계속 바뀌니까요.'
                    WHEN i % 3 = 0 THEN '좋은 의견이네요. 저는 조금 다른 관점에서 접근해보고 싶어요. 마케팅에서 가장 중요한 것은 진정성이라고 생각합니다.'
                    WHEN i % 2 = 0 THEN '맞습니다! 실제로 현업에서도 이런 전략을 많이 사용하고 있어요. 효과가 정말 좋습니다.'
                    ELSE '동감합니다. 추가로 궁금한 점이 있는데, 혹시 더 자세한 자료가 있을까요?'
                END
            );
            
            INSERT INTO comments (post_id, user_id, parent_id, content, status, created_at, updated_at) 
            VALUES (3, user_id_to_use, parent_comment_id, reply_content, 'active', 
                    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 29) DAY) + INTERVAL FLOOR(RAND() * 86400) SECOND,
                    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 29) DAY) + INTERVAL FLOOR(RAND() * 86400) SECOND);
        END IF;
        
        SET i = i + 1;
        
        -- 1000개마다 진행상황 출력
        IF i % 1000 = 0 THEN
            SELECT CONCAT('진행중... ', i, ' / 70000 완료') AS progress;
        END IF;
    END WHILE;
    
    SELECT '일반 댓글 70,000개 생성 완료!' AS message;
    
END//

DELIMITER ;

-- 프로시저 실행
CALL CreateTestComments();

-- 프로시저 삭제
DROP PROCEDURE CreateTestComments;

-- 댓글 통계 확인
SELECT 
    COUNT(*) as total_comments,
    COUNT(CASE WHEN parent_id IS NULL THEN 1 END) as main_comments,
    COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as replies
FROM comments 
WHERE post_id = 3 AND status = 'active';

-- 트랜잭션 커밋
COMMIT;

SELECT '댓글 생성 완료! 페이지를 새로고침해서 확인해보세요.' AS final_message;