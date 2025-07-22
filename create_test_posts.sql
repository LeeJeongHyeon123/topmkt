-- 커뮤니티에 1,000,000개 게시글 생성 스크립트
-- 실행 명령어: SOURCE /var/www/html/topmkt/create_test_posts.sql;

-- 트랜잭션 시작
START TRANSACTION;

-- 변수 선언을 위한 프로시저 생성
DELIMITER //

CREATE PROCEDURE CreateTestPosts()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE user_id_to_use INT DEFAULT 4; -- 우리집탄이 유저 ID 사용
    DECLARE post_title VARCHAR(255);
    DECLARE post_content TEXT;
    DECLARE random_category INT;
    DECLARE random_days INT;
    DECLARE random_hours INT;
    
    -- 1,000,000개의 게시글 생성
    WHILE i <= 1000000 DO
        -- 카테고리별 다양한 제목 생성
        SET random_category = FLOOR(RAND() * 10) + 1;
        
        SET post_title = CASE random_category
            WHEN 1 THEN CONCAT('디지털 마케팅 전략 #', i, ' - SNS 마케팅의 핵심 포인트')
            WHEN 2 THEN CONCAT('네트워크 마케팅 성공사례 #', i, ' - 실전 경험 공유')
            WHEN 3 THEN CONCAT('마케팅 트렌드 분석 #', i, ' - 2024년 최신 동향')
            WHEN 4 THEN CONCAT('고객 관리 노하우 #', i, ' - CRM 시스템 활용법')
            WHEN 5 THEN CONCAT('브랜딩 전략 수립 #', i, ' - 브랜드 아이덴티티 구축')
            WHEN 6 THEN CONCAT('온라인 마케팅 실무 #', i, ' - 디지털 광고 최적화')
            WHEN 7 THEN CONCAT('마케팅 데이터 분석 #', i, ' - GA4 활용 가이드')
            WHEN 8 THEN CONCAT('콘텐츠 마케팅 기법 #', i, ' - 바이럴 콘텐츠 제작법')
            WHEN 9 THEN CONCAT('마케팅 ROI 측정 #', i, ' - 성과 지표 관리 방법')
            ELSE CONCAT('마케팅 질문과 답변 #', i, ' - 커뮤니티 토론')
        END;
        
        -- 다양한 길이의 게시글 내용 생성
        SET post_content = CASE 
            WHEN i % 7 = 0 THEN CONCAT(
                '안녕하세요! 마케팅 전문가 여러분,\n\n',
                '게시글 #', i, '번입니다. 오늘은 정말 중요한 마케팅 전략에 대해 이야기해보려고 합니다.\n\n',
                '최근 마케팅 업계에서는 개인화된 고객 경험이 무엇보다 중요해지고 있습니다. 특히 데이터 기반의 의사결정과 AI를 활용한 타겟팅이 큰 화두가 되고 있죠.\n\n',
                '제가 현업에서 경험한 바로는, 다음과 같은 요소들이 성공적인 마케팅 캠페인의 핵심입니다:\n\n',
                '1. 명확한 타겟 고객 정의\n',
                '2. 데이터 기반 의사결정\n',
                '3. 멀티채널 접근 전략\n',
                '4. 지속적인 테스트와 최적화\n',
                '5. ROI 측정 및 분석\n\n',
                '여러분의 경험과 의견도 궁금합니다. 댓글로 많은 이야기 나누어요!'
            )
            WHEN i % 5 = 0 THEN CONCAT(
                '마케팅 인사이트 #', i, '\n\n',
                '효과적인 마케팅을 위해서는 고객의 니즈를 정확히 파악하는 것이 중요합니다.\n\n',
                '최근 진행한 프로젝트에서 얻은 교훈을 공유해드리겠습니다. 고객 여정 맵핑을 통해 터치포인트별 최적화를 진행했고, 그 결과 전환율이 40% 향상되었습니다.\n\n',
                '특히 모바일 환경에서의 사용자 경험 개선이 가장 큰 효과를 보였네요. 여러분도 비슷한 경험이 있으신가요?'
            )
            WHEN i % 3 = 0 THEN CONCAT(
                '마케팅 팁 #', i, '\n\n',
                '간단하지만 효과적인 마케팅 전략을 소개합니다.\n\n',
                '고객과의 지속적인 커뮤니케이션이 브랜드 로열티 향상에 큰 도움이 됩니다. 뉴스레터, 소셜미디어, 개인화된 메시지 등을 적절히 조합하여 활용해보세요.'
            )
            WHEN i % 2 = 0 THEN CONCAT(
                '마케팅 질문 #', i, '\n\n',
                '마케팅 예산 배분에 대해 고민이 많습니다. 디지털 광고와 오프라인 마케팅의 비율을 어떻게 잡는 것이 좋을까요?\n\n',
                '업종별로 다르겠지만, 여러분의 경험을 공유해주시면 큰 도움이 될 것 같습니다.'
            )
            ELSE CONCAT(
                '마케팅 정보 #', i, '\n\n',
                '유용한 마케팅 정보를 공유합니다. 최신 트렌드와 실무 노하우를 지속적으로 업데이트하겠습니다.\n\n',
                '마케팅은 끊임없이 변화하는 분야입니다. 함께 성장해나가요!'
            )
        END;
        
        -- 랜덤한 과거 날짜 생성 (최근 365일 내)
        SET random_days = FLOOR(RAND() * 365);
        SET random_hours = FLOOR(RAND() * 24);
        
        -- 게시글 삽입
        INSERT INTO posts (
            user_id, 
            title, 
            content, 
            view_count, 
            like_count, 
            comment_count,
            status,
            created_at, 
            updated_at
        ) VALUES (
            user_id_to_use,
            post_title,
            post_content,
            FLOOR(RAND() * 1000) + 1, -- 조회수 1-1000 랜덤
            FLOOR(RAND() * 50), -- 좋아요 0-49 랜덤
            0, -- 댓글 수는 0으로 시작
            'published',
            DATE_SUB(NOW(), INTERVAL random_days DAY) + INTERVAL random_hours HOUR,
            DATE_SUB(NOW(), INTERVAL random_days DAY) + INTERVAL random_hours HOUR
        );
        
        SET i = i + 1;
        
        -- 10,000개마다 진행상황 출력
        IF i % 10000 = 0 THEN
            SELECT CONCAT('진행중... ', FORMAT(i, 0), ' / 1,000,000 완료 (', ROUND(i/10000, 1), '%)') AS progress;
        END IF;
        
        -- 50,000개마다 중간 커밋 (메모리 절약)
        IF i % 50000 = 0 THEN
            COMMIT;
            START TRANSACTION;
        END IF;
    END WHILE;
    
    SELECT '게시글 1,000,000개 생성 완료!' AS message;
    
END//

DELIMITER ;

-- 프로시저 실행
CALL CreateTestPosts();

-- 프로시저 삭제
DROP PROCEDURE CreateTestPosts;

-- 게시글 통계 확인
SELECT 
    COUNT(*) as total_posts,
    AVG(view_count) as avg_views,
    AVG(like_count) as avg_likes,
    MIN(created_at) as oldest_post,
    MAX(created_at) as newest_post
FROM posts 
WHERE status = 'published';

-- 최신 10개 게시글 확인
SELECT id, title, view_count, like_count, created_at 
FROM posts 
WHERE status = 'published' 
ORDER BY created_at DESC 
LIMIT 10;

-- 트랜잭션 커밋
COMMIT;

SELECT '게시글 생성 완료! 커뮤니티 페이지에서 확인해보세요.' AS final_message;