<?php
/**
 * 2025년 6월 행사 일정 샘플 데이터 추가
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>🎉 2025년 6월 행사 일정 샘플 데이터 추가</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 기존 6월 행사 데이터 확인
    $existingCount = $pdo->query("
        SELECT COUNT(*) FROM lectures 
        WHERE content_type = 'event' 
        AND start_date BETWEEN '2025-06-01' AND '2025-06-30'
    ")->fetchColumn();
    
    echo "<p>현재 2025년 6월 행사 수: {$existingCount}개</p>\n";
    
    if ($existingCount > 0) {
        echo "<p>✅ 이미 6월 행사 데이터가 존재합니다.</p>\n";
        echo "<p>기존 데이터를 삭제하고 새로 추가하시겠습니까?</p>\n";
        echo "<p><a href='?force=1' style='background:#dc2626;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>기존 데이터 삭제 후 새로 추가</a></p>\n";
        
        if (!isset($_GET['force'])) {
            exit;
        } else {
            // 기존 6월 행사 데이터 삭제
            $deleteStmt = $pdo->prepare("DELETE FROM lectures WHERE content_type = 'event' AND start_date BETWEEN '2025-06-01' AND '2025-06-30'");
            $deleteStmt->execute();
            echo "<p>🗑️ 기존 6월 행사 데이터 삭제 완료</p>\n";
        }
    }
    
    // 관리자 사용자 ID 확인 (첫 번째 사용자 사용)
    $firstUser = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch();
    if (!$firstUser) {
        echo "<p>❌ 사용자가 없습니다. 먼저 사용자를 생성해주세요.</p>\n";
        exit;
    }
    
    $userId = $firstUser['id'];
    echo "<p>사용자 ID {$userId}를 사용하여 6월 행사 샘플 데이터를 생성합니다.</p>\n";
    
    // 2025년 6월 행사 샘플 데이터
    $juneEvents = [
        [
            'title' => '여름 마케팅 전략 워크샵',
            'description' => '뜨거운 여름 시즌을 공략하는 마케팅 전략을 배우는 실전 워크샵입니다. 

🎯 **주요 학습 내용**
• 여름 시즌 소비자 행동 분석 및 인사이트 도출
• 휴가철 타겟팅 전략 및 캠페인 기획 방법론
• 여름 상품/서비스 포지셔닝 및 브랜딩 전략
• 시즌 한정 프로모션 기획 및 실행 노하우
• SNS를 활용한 여름 마케팅 콘텐츠 제작법

💼 **실무 중심 커리큘럼**
오전에는 이론 강의와 케이스 스터디를 통해 여름 마케팅의 핵심 원리를 학습하고, 오후에는 팀별 실습을 통해 실제 캠페인을 기획해보는 시간을 갖습니다. 

🎁 **참가 혜택**
• 여름 마케팅 전략 가이드북 제공
• 성공 사례 분석 리포트 (50페이지)
• 실습용 마케팅 템플릿 10종 세트
• 3개월간 온라인 커뮤니티 멤버십

🤝 **네트워킹 세션**
워크샵 후 다양한 업계의 마케터들과 교류할 수 있는 네트워킹 시간이 준비되어 있습니다. 실무 경험을 공유하고 새로운 인사이트를 얻어가세요.',
            'instructor_name' => '김여름',
            'instructor_info' => '現 마케팅컨설팅그룹 대표, 시즌 마케팅 전문가로 10년 이상 여름 시즌 캠페인을 성공시킨 경험을 보유하고 있습니다. 삼성전자, LG생활건강, 롯데제과 등 대기업 마케팅 컨설팅 다수 진행. 『시즌 마케팅의 모든 것』 저자.',
            'start_date' => '2025-06-05',
            'end_date' => '2025-06-05',
            'start_time' => '10:00:00',
            'end_time' => '16:00:00',
            'location_type' => 'offline',
            'venue_name' => '강남 마케팅 센터',
            'venue_address' => '서울특별시 강남구 테헤란로 456 (역삼동, 마케팅타워 12층)',
            'max_participants' => 40,
            'registration_fee' => 120000,
            'category' => 'workshop',
            'content_type' => 'event',
            'event_scale' => 'medium',
            'has_networking' => true,
            'sponsor_info' => '주최: 탑마케팅, 후원: 네이버 비즈니스, 카카오 비즈니스',
            'dress_code' => 'business_casual',
            'parking_info' => '건물 지하주차장 4시간 무료 (등록시 주차권 제공)',
            'status' => 'published'
        ],
        [
            'title' => '소셜미디어 인플루언서 마케팅 세미나',
            'description' => '인플루언서와 함께하는 마케팅 전략을 배우는 세미나입니다. 인플루언서 선정부터 협업, 성과 측정까지 전 과정을 다룹니다.',
            'instructor_name' => '박인플루',
            'instructor_info' => '대형 MCN 출신으로 수백 명의 인플루언서와 협업한 경험이 있는 전문가입니다.',
            'start_date' => '2025-06-12',
            'end_date' => '2025-06-12',
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'location_type' => 'hybrid',
            'venue_name' => '서초 컨퍼런스홀',
            'venue_address' => '서울특별시 서초구 서초대로 789',
            'online_link' => 'https://zoom.us/j/123456789',
            'max_participants' => 80,
            'registration_fee' => 75000,
            'category' => 'seminar',
            'content_type' => 'event',
            'event_scale' => 'medium',
            'has_networking' => true,
            'sponsor_info' => '후원: 네이버, 카카오',
            'dress_code' => 'casual',
            'parking_info' => '주변 공영주차장 이용',
            'status' => 'published'
        ],
        [
            'title' => '스타트업 마케팅 부트캠프',
            'description' => '스타트업을 위한 집중 마케팅 교육 프로그램입니다. 제한된 예산으로 최대 효과를 내는 마케팅 전략과 실행 방법을 배웁니다.',
            'instructor_name' => '이스타트',
            'instructor_info' => '3번의 스타트업 창업 경험과 마케팅 컨설팅 전문가입니다.',
            'start_date' => '2025-06-18',
            'end_date' => '2025-06-19',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'location_type' => 'offline',
            'venue_name' => '판교 스타트업 캠퍼스',
            'venue_address' => '경기도 성남시 분당구 판교역로 166',
            'max_participants' => 30,
            'registration_fee' => 180000,
            'category' => 'workshop',
            'content_type' => 'event',
            'event_scale' => 'small',
            'has_networking' => true,
            'sponsor_info' => '후원: 중소벤처기업부, 판교테크노밸리',
            'dress_code' => 'casual',
            'parking_info' => '캠퍼스 내 주차장 무료',
            'status' => 'published'
        ],
        [
            'title' => '글로벌 이커머스 진출 전략 컨퍼런스',
            'description' => '해외 이커머스 시장 진출을 위한 대규모 컨퍼런스입니다. 아마존, 이베이, 알리바바 등 글로벌 플랫폼 진출 전략을 다룹니다.',
            'instructor_name' => '글로벌 이커머스 협회',
            'instructor_info' => '해외 진출을 성공한 국내 기업들과 글로벌 플랫폼 전문가들이 함께 합니다.',
            'start_date' => '2025-06-25',
            'end_date' => '2025-06-25',
            'start_time' => '09:30:00',
            'end_time' => '17:30:00',
            'location_type' => 'offline',
            'venue_name' => '잠실 롯데월드타워',
            'venue_address' => '서울특별시 송파구 올림픽로 300',
            'max_participants' => 200,
            'registration_fee' => 250000,
            'category' => 'conference',
            'content_type' => 'event',
            'event_scale' => 'large',
            'has_networking' => true,
            'sponsor_info' => '주최: 한국무역협회, 후원: 삼성전자, LG전자, 현대자동차',
            'dress_code' => 'business',
            'parking_info' => '롯데월드타워 지하주차장 (유료)',
            'status' => 'published'
        ],
        [
            'title' => 'AI 마케팅 혁신 포럼',
            'description' => '인공지능을 활용한 마케팅 혁신 사례와 미래 전망을 다루는 포럼입니다. ChatGPT, 머신러닝을 활용한 개인화 마케팅 등을 소개합니다.',
            'instructor_name' => '정AI마케터',
            'instructor_info' => 'AI 마케팅 솔루션 개발자이자 다수 기업의 AI 도입 컨설턴트입니다.',
            'start_date' => '2025-06-28',
            'end_date' => '2025-06-28',
            'start_time' => '13:00:00',
            'end_time' => '18:00:00',
            'location_type' => 'online',
            'online_link' => 'https://youtube.com/live/ai-marketing-2025',
            'max_participants' => null,
            'registration_fee' => 50000,
            'category' => 'seminar',
            'content_type' => 'event',
            'event_scale' => 'large',
            'has_networking' => false,
            'sponsor_info' => '후원: 네이버 클라우드, AWS',
            'status' => 'published'
        ]
    ];
    
    $sql = "INSERT INTO lectures (
        user_id, title, description, instructor_name, instructor_info,
        start_date, end_date, start_time, end_time,
        location_type, venue_name, venue_address, online_link,
        max_participants, registration_fee, category, content_type,
        event_scale, has_networking, sponsor_info, dress_code, parking_info,
        status, created_at
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    
    $successCount = 0;
    foreach ($juneEvents as $event) {
        try {
            $params = [
                $userId,
                $event['title'],
                $event['description'],
                $event['instructor_name'],
                $event['instructor_info'],
                $event['start_date'],
                $event['end_date'],
                $event['start_time'],
                $event['end_time'],
                $event['location_type'],
                $event['venue_name'] ?? null,
                $event['venue_address'] ?? null,
                $event['online_link'] ?? null,
                $event['max_participants'] ?? null,
                $event['registration_fee'] ?? null,
                $event['category'],
                $event['content_type'],
                $event['event_scale'] ?? null,
                $event['has_networking'] ? 1 : 0,
                $event['sponsor_info'] ?? null,
                $event['dress_code'] ?? null,
                $event['parking_info'] ?? null,
                $event['status']
            ];
            
            $stmt->execute($params);
            $successCount++;
            echo "<p>✅ 행사 추가됨: {$event['title']} ({$event['start_date']})</p>\n";
        } catch (Exception $e) {
            echo "<p>❌ 행사 추가 실패 ({$event['title']}): " . $e->getMessage() . "</p>\n";
        }
    }
    
    echo "<h2>🎊 완료!</h2>\n";
    echo "<p><strong>{$successCount}개의 6월 행사가 성공적으로 추가되었습니다.</strong></p>\n";
    echo "<p><a href='/events?year=2025&month=6' style='background:#4A90E2;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;'>➡️ 6월 행사 일정 보기</a></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ 오류: " . $e->getMessage() . "</p>\n";
    error_log("add_june_events.php 오류: " . $e->getMessage());
}
?>