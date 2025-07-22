<?php

$json = '{"status":"success","data":{"lecture_info":{"id":167,"title":"테스트 강의1","start_date":"2025-07-28","start_time":"20:26:00","end_date":"2025-07-29","end_time":"19:25:00","max_participants":10,"auto_approval":0,"registration_start_date":null,"registration_end_date":null,"allow_waiting_list":0,"lecture_status":"published","current_participants":0},"registration":{"id":30,"status":"rejected","is_waiting_list":0,"waiting_order":null,"created_at":"2025-07-07 23:44:56","processed_at":"2025-07-18 19:28:00","admin_notes":"너 안 됨!!!!"},"user_id":5},"message":"신청 상태 조회 완료","timestamp":"2025-07-19T19:12:18+09:00","request_id":"req_687b6f823a9145.68923443"}';

echo "=== API 응답 분석 ===\n\n";

$data = json_decode($json, true);

echo "status: " . ($data['status'] ?? 'null') . "\n";
echo "message: " . ($data['message'] ?? 'null') . "\n\n";

if (isset($data['data']['registration'])) {
    $reg = $data['data']['registration'];
    echo "=== registration 데이터 ===\n";
    echo "status: " . ($reg['status'] ?? 'null') . "\n";
    echo "admin_notes: " . ($reg['admin_notes'] ?? 'null') . "\n";
    echo "id: " . ($reg['id'] ?? 'null') . "\n";
    echo "created_at: " . ($reg['created_at'] ?? 'null') . "\n";
    echo "processed_at: " . ($reg['processed_at'] ?? 'null') . "\n\n";
}

// detail.php에서 사용하는 방식 시뮬레이션
echo "=== detail.php 처리 시뮬레이션 ===\n";
if ($data['status'] === 'success' && $data['data']) {
    echo "✅ API 성공 응답, data 사용\n";
    
    $registration = $data['data']['registration'] ?? null;
    if ($registration) {
        echo "📊 registration.status: " . $registration['status'] . "\n";
        
        switch ($registration['status']) {
            case 'rejected':
                echo "❌ rejected 상태 처리 시작\n";
                $rejectedMessage = $registration['admin_notes'] ?? '신청이 거절되었습니다. 다시 신청하실 수 있습니다.';
                echo "📝 거절 메시지: " . $rejectedMessage . "\n";
                echo "🎯 showLectureStatusMessage('rejected', 'fa-times-circle', '신청이 거절되었습니다', '" . $rejectedMessage . "') 호출됨\n";
                break;
            default:
                echo "❓ 다른 상태: " . $registration['status'] . "\n";
        }
    } else {
        echo "⚠️ registration 데이터 없음\n";
    }
} else {
    echo "❌ API 응답 오류\n";
}