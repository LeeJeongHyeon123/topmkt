<?php
/**
 * 샘플 강의 데이터 추가
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>📚 샘플 강의 데이터 추가</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 기존 데이터 확인
    $existingCount = $pdo->query("SELECT COUNT(*) FROM lectures")->fetchColumn();
    echo "<p>현재 강의 수: {$existingCount}개</p>\n";
    
    if ($existingCount > 0) {
        echo "<p>✅ 이미 강의 데이터가 존재합니다.</p>\n";
        exit;
    }
    
    // 관리자 사용자 ID 확인 (첫 번째 사용자 사용)
    $firstUser = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch();
    if (!$firstUser) {
        echo "<p>❌ 사용자가 없습니다. 먼저 사용자를 생성해주세요.</p>\n";
        exit;
    }
    
    $userId = $firstUser['id'];
    echo "<p>사용자 ID {$userId}를 사용하여 샘플 데이터를 생성합니다.</p>\n";
    
    // 샘플 강의 데이터
    $sampleLectures = [
        [
            'title' => '디지털 마케팅 전략 세미나',
            'description' => '2025년 최신 디지털 마케팅 트렌드와 실전 전략을 배우는 세미나입니다. SNS 마케팅, 콘텐츠 마케팅, 데이터 분석 등 실무에 바로 적용할 수 있는 내용을 다룹니다.',
            'instructor_name' => '김마케팅',
            'instructor_info' => '10년 이상의 디지털 마케팅 경험을 보유한 전문가로, 다수의 기업 컨설팅을 진행했습니다.',
            'start_date' => '2025-07-15',
            'end_date' => '2025-07-15',
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'location_type' => 'offline',
            'venue_name' => '서울 강남구 세미나실',
            'venue_address' => '서울특별시 강남구 테헤란로 123',
            'max_participants' => 30,
            'registration_fee' => 50000,
            'category' => 'seminar',
            'status' => 'published'
        ],
        [
            'title' => '온라인 SNS 마케팅 워크샵',
            'description' => '인스타그램, 페이스북, 틱톡 등 SNS를 활용한 마케팅 실무 워크샵입니다. 실제 계정을 만들어보고 콘텐츠를 제작해보는 실습 중심의 프로그램입니다.',
            'instructor_name' => '박소셜',
            'instructor_info' => 'SNS 마케팅 전문가로 여러 브랜드의 소셜미디어 전략을 담당하고 있습니다.',
            'start_date' => '2025-07-22',
            'end_date' => '2025-07-22',
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'location_type' => 'online',
            'online_link' => 'https://zoom.us/j/123456789',
            'max_participants' => null,
            'registration_fee' => 30000,
            'category' => 'workshop',
            'status' => 'published'
        ],
        [
            'title' => '글로벌 네트워크 마케팅 컨퍼런스 2025',
            'description' => '전 세계 네트워크 마케팅 리더들이 모이는 대규모 컨퍼런스입니다. 최신 트렌드, 성공 사례, 네트워킹 기회를 제공합니다.',
            'instructor_name' => '이글로벌',
            'instructor_info' => '국제 네트워크 마케팅 협회 이사로 활동하며 글로벌 마케팅 전략을 전파하고 있습니다.',
            'start_date' => '2025-08-05',
            'end_date' => '2025-08-06',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'location_type' => 'hybrid',
            'venue_name' => '코엑스 컨벤션센터',
            'venue_address' => '서울특별시 강남구 영동대로 513',
            'online_link' => 'https://youtube.com/live/conference2025',
            'max_participants' => 500,
            'registration_fee' => 150000,
            'category' => 'conference',
            'status' => 'published'
        ]
    ];
    
    $sql = "INSERT INTO lectures (
        user_id, title, description, instructor_name, instructor_info,
        start_date, end_date, start_time, end_time,
        location_type, venue_name, venue_address, online_link,
        max_participants, registration_fee, category, status,
        created_at
    ) VALUES (
        :user_id, :title, :description, :instructor_name, :instructor_info,
        :start_date, :end_date, :start_time, :end_time,
        :location_type, :venue_name, :venue_address, :online_link,
        :max_participants, :registration_fee, :category, :status,
        NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($sampleLectures as $lecture) {
        $params = [
            ':user_id' => $userId,
            ':title' => $lecture['title'],
            ':description' => $lecture['description'],
            ':instructor_name' => $lecture['instructor_name'],
            ':instructor_info' => $lecture['instructor_info'],
            ':start_date' => $lecture['start_date'],
            ':end_date' => $lecture['end_date'],
            ':start_time' => $lecture['start_time'],
            ':end_time' => $lecture['end_time'],
            ':location_type' => $lecture['location_type'],
            ':venue_name' => $lecture['venue_name'] ?? null,
            ':venue_address' => $lecture['venue_address'] ?? null,
            ':online_link' => $lecture['online_link'] ?? null,
            ':max_participants' => $lecture['max_participants'],
            ':registration_fee' => $lecture['registration_fee'],
            ':category' => $lecture['category'],
            ':status' => $lecture['status']
        ];
        
        $stmt->execute($params);
        echo "✅ '{$lecture['title']}' 강의 추가 완료<br>\n";
    }
    
    $newCount = $pdo->query("SELECT COUNT(*) FROM lectures")->fetchColumn();
    echo "<h2>🎉 완료!</h2>\n";
    echo "<p>총 {$newCount}개의 강의가 등록되었습니다.</p>\n";
    echo "<p><a href='/lectures'>강의 일정 페이지로 이동</a></p>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>\n";
    echo "<p style='color: red;'>{$e->getMessage()}</p>\n";
}
?>