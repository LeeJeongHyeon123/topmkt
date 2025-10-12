-- 탑마케팅 데이터베이스 인덱스 최적화 스크립트

-- users 테이블 인덱스 추가/최적화
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_role_status (role, status);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_phone_email (phone, email);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_last_login (last_login);

-- lectures 테이블 인덱스 추가/최적화
ALTER TABLE lectures ADD INDEX IF NOT EXISTS idx_lectures_user_status_date (user_id, status, start_date);
ALTER TABLE lectures ADD INDEX IF NOT EXISTS idx_lectures_category_date (category, start_date);
ALTER TABLE lectures ADD INDEX IF NOT EXISTS idx_lectures_organizer_status (organizer_id, status);

-- lecture_registrations 테이블 인덱스 추가/최적화
ALTER TABLE lecture_registrations ADD INDEX IF NOT EXISTS idx_registrations_user_status (user_id, status);
ALTER TABLE lecture_registrations ADD INDEX IF NOT EXISTS idx_registrations_lecture_status (lecture_id, status);
ALTER TABLE lecture_registrations ADD INDEX IF NOT EXISTS idx_registrations_date_status (registration_date, status);
ALTER TABLE lecture_registrations ADD INDEX IF NOT EXISTS idx_registrations_waiting (is_waiting_list, status, waiting_order);

-- posts 테이블 인덱스 추가/최적화
ALTER TABLE posts ADD INDEX IF NOT EXISTS idx_posts_user_status (user_id, status);
ALTER TABLE posts ADD INDEX IF NOT EXISTS idx_posts_created_status (created_at, status);
ALTER TABLE posts ADD INDEX IF NOT EXISTS idx_posts_category (category_id);

-- comments 테이블 인덱스 추가/최적화
ALTER TABLE comments ADD INDEX IF NOT EXISTS idx_comments_post_status (post_id, status);
ALTER TABLE comments ADD INDEX IF NOT EXISTS idx_comments_user_status (user_id, status);
ALTER TABLE comments ADD INDEX IF NOT EXISTS idx_comments_created_status (created_at, status);

-- notices 테이블 인덱스 추가/최적화
ALTER TABLE notices ADD INDEX IF NOT EXISTS idx_notices_user_status (user_id, status);
ALTER TABLE notices ADD INDEX IF NOT EXISTS idx_notices_created_status (created_at, status);
ALTER TABLE notices ADD INDEX IF NOT EXISTS idx_notices_view_count (view_count);

-- events 테이블 인덱스 추가/최적화
ALTER TABLE events ADD INDEX IF NOT EXISTS idx_events_user_status (user_id, status);
ALTER TABLE events ADD INDEX IF NOT EXISTS idx_events_start_date (start_date);
ALTER TABLE events ADD INDEX IF NOT EXISTS idx_events_category (category_id);

-- event_registrations 테이블 인덱스 추가/최적화
ALTER TABLE event_registrations ADD INDEX IF NOT EXISTS idx_event_reg_user_status (user_id, status);
ALTER TABLE event_registrations ADD INDEX IF NOT EXISTS idx_event_reg_event_status (event_id, status);

-- company_profiles 테이블 인덱스 추가/최적화
ALTER TABLE company_profiles ADD INDEX IF NOT EXISTS idx_company_profiles_user_status (user_id, status);
ALTER TABLE company_profiles ADD INDEX IF NOT EXISTS idx_company_profiles_status_created (status, created_at);

-- user_sessions 테이블 인덱스 추가/최적화
ALTER TABLE user_sessions ADD INDEX IF NOT EXISTS idx_user_sessions_user_activity (user_id, last_activity);
ALTER TABLE user_sessions ADD INDEX IF NOT EXISTS idx_user_sessions_activity (last_activity);

-- user_logs 테이블 인덱스 추가/최적화
ALTER TABLE user_logs ADD INDEX IF NOT EXISTS idx_user_logs_user_action (user_id, action);
ALTER TABLE user_logs ADD INDEX IF NOT EXISTS idx_user_logs_action_date (action, created_at);

-- settings 테이블 인덱스 추가/최적화
ALTER TABLE settings ADD INDEX IF NOT EXISTS idx_settings_key_type (key_name, type);

-- 복합 인덱스 성능 최적화 확인 쿼리
EXPLAIN SELECT u.id, u.nickname, u.email, u.phone, u.status, u.role
FROM users u
WHERE u.status = 'active' AND u.role = 'ROLE_CORPORATE';

EXPLAIN SELECT l.id, l.title, l.start_date, l.status, l.category
FROM lectures l
WHERE l.status = 'published' AND l.start_date >= CURDATE()
ORDER BY l.start_date ASC;

EXPLAIN SELECT lr.id, lr.lecture_id, lr.user_id, lr.status, lr.registration_date
FROM lecture_registrations lr
WHERE lr.lecture_id = 1 AND lr.status = 'approved'
ORDER BY lr.registration_date DESC;

-- 인덱스 사용 통계 확인
SHOW INDEX FROM users;
SHOW INDEX FROM lectures;
SHOW INDEX FROM lecture_registrations;
