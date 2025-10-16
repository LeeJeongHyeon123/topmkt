<?php
/**
 * 샘플 행사 데이터 추가
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>🎉 샘플 행사 데이터 추가</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 기존 행사 데이터 확인
    $existingCount = $pdo->query("SELECT COUNT(*) FROM lectures WHERE content_type = 'event'")->fetchColumn();
    echo "<p>현재 행사 수: {$existingCount}개</p>\n";
    
    if ($existingCount > 0) {
        echo "<p>✅ 이미 행사 데이터가 존재합니다.</p>\n";
        exit;
    }
    
    // 관리자 사용자 ID 확인 (첫 번째 사용자 사용)
    $firstUser = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch();
    if (!$firstUser) {
        echo "<p>❌ 사용자가 없습니다. 먼저 사용자를 생성해주세요.</p>\n";
        exit;
    }
    
    $userId = $firstUser['id'];
    echo "<p>사용자 ID {$userId}를 사용하여 샘플 행사 데이터를 생성합니다.</p>\n";
    
    // 샘플 행사 데이터
    $sampleEvents = [
        [
            'title' => '2025 글로벌 마케팅 컨퍼런스',
            'description' => '세계 최고의 마케팅 전문가들이 모이는 대규모 컨퍼런스입니다. 최신 마케팅 트렌드, AI를 활용한 마케팅 전략, 글로벌 시장 진출 전략 등을 다룹니다. 네트워킹 시간과 전시 부스도 준비되어 있습니다.',
            'instructor_name' => '글로벌 마케팅 협회',
            'instructor_info' => '세계 마케팅 전문가들의 연합체로, 매년 글로벌 트렌드를 선도하는 컨퍼런스를 개최합니다.',
            'start_date' => '2025-02-15',
            'end_date' => '2025-02-16',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'location_type' => 'offline',
            'venue_name' => '코엑스 컨벤션센터',
            'venue_address' => '서울특별시 강남구 영동대로 513',
            'max_participants' => 1000,
            'registration_fee' => 250000,
            'category' => 'conference',
            'content_type' => 'event',
            'sponsor_info' => '주최: 글로벌 마케팅 협회, 후원: 삼성전자, LG전자, 현대자동차',
            'dress_code' => 'business',
            'parking_info' => '코엑스 지하주차장 이용 가능 (유료)',
            'status' => 'published'
        ],
        [
            'title' => '스타트업 네트워킹 데이',
            'description' => '스타트업 창업자들과 투자자들이 만나는 네트워킹 이벤트입니다. 피칭 세션, 1:1 미팅, 캐주얼 네트워킹 시간이 준비되어 있습니다.',
            'instructor_name' => '스타트업 얼라이언스',
            'instructor_info' => '국내 최대 스타트업 커뮤니티로, 창업 생태계 발전을 위한 다양한 프로그램을 운영합니다.',
            'start_date' => '2025-02-20',
            'end_date' => '2025-02-20',
            'start_time' => '14:00:00',
            'end_time' => '19:00:00',
            'location_type' => 'offline',
            'venue_name' => '강남 스타트업 허브',
            'venue_address' => '서울특별시 강남구 테헤란로 123',
            'max_participants' => 150,
            'registration_fee' => 30000,
            'category' => 'workshop',
            'content_type' => 'event',
            'event_scale' => 'medium',
            'has_networking' => true,
            'dress_code' => 'business_casual',
            'parking_info' => '건물 지하주차장 2시간 무료',
            'status' => 'published'
        ],
        [
            'title' => '디지털 마케팅 워크샵',
            'description' => '실무진을 위한 집중 디지털 마케팅 워크샵입니다. Google Ads, Facebook 광고, SEO 최적화 등 실전 스킬을 배웁니다.',
            'instructor_name' => '김디지털',
            'instructor_info' => '10년 이상의 디지털 마케팅 경력을 보유한 전문가로, 다수 기업의 디지털 전환을 이끌었습니다.',
            'start_date' => '2025-02-25',
            'end_date' => '2025-02-25',
            'start_time' => '10:00:00',
            'end_time' => '16:00:00',
            'location_type' => 'hybrid',
            'venue_name' => '디지털 마케팅 센터',
            'venue_address' => '서울특별시 서초구 서초대로 123',
            'online_link' => 'https://zoom.us/j/987654321',
            'max_participants' => 50,
            'registration_fee' => 80000,
            'category' => 'workshop',
            'content_type' => 'event',
            'dress_code' => 'casual',
            'parking_info' => '주변 공영주차장 이용',
            'status' => 'published'
        ],
        [
            'title' => '온라인 브랜딩 세미나',
            'description' => '브랜드 구축과 관리에 대한 온라인 세미나입니다. 성공적인 브랜딩 사례와 실전 전략을 공유합니다.',
            'instructor_name' => '박브랜드',
            'instructor_info' => '브랜딩 전문 컨설턴트로 다수의 글로벌 브랜드 런칭을 성공시켰습니다.',
            'start_date' => '2025-03-05',
            'end_date' => '2025-03-05',
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'location_type' => 'online',
            'online_link' => 'https://youtube.com/live/branding2025',
            'max_participants' => null,
            'registration_fee' => 25000,
            'category' => 'seminar',
            'content_type' => 'event',
            'status' => 'published'
        ]
    ];
    
    $sql = "INSERT INTO lectures (
        user_id, title, description, instructor_name, instructor_info,
        start_date, end_date, start_time, end_time,
        location_type, venue_name, venue_address, online_link,
        max_participants, registration_fee, category, content_type,
        sponsor_info, dress_code, parking_info,
        status, created_at
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    
    $successCount = 0;
    foreach ($sampleEvents as $event) {
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
                $event['sponsor_info'] ?? null,
                $event['dress_code'] ?? null,
                $event['parking_info'] ?? null,
                $event['status']
            ];
            
            $stmt->execute($params);
            $successCount++;
            echo "<p>✅ 행사 추가됨: {$event['title']}</p>\n";
        } catch (Exception $e) {
            echo "<p>❌ 행사 추가 실패 ({$event['title']}): " . $e->getMessage() . "</p>\n";
        }
    }
    
    echo "<h2>🎊 완료!</h2>\n";
    echo "<p><strong>{$successCount}개의 샘플 행사가 성공적으로 추가되었습니다.</strong></p>\n";
    echo "<p><a href='/events'>➡️ 행사 일정 페이지로 이동</a></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ 오류: " . $e->getMessage() . "</p>\n";
    error_log("add_sample_events.php 오류: " . $e->getMessage());
}
?>