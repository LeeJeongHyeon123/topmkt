<?php
/**
 * 강의 167번 신청 내역 확인 및 상태 변경
 */

// 환경 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/paths.php';
require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

// 웹 환경에서만 실행 가능
if (php_sapi_name() === 'cli') {
    echo "이 스크립트는 웹 브라우저에서만 실행 가능합니다.\n";
    echo "다음 URL로 접속하세요: https://www.topmktx.com/check_registration.php\n";
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "=== 강의 167번 신청 내역 확인 ===\n\n";
    
    // 현재 신청 내역 조회 + 승인된 신청 수 계산
    $query = "
        SELECT 
            r.id, r.participant_name, r.participant_email, r.participant_phone,
            r.status, r.created_at, r.processed_at, r.processed_by,
            l.title as lecture_title
        FROM lecture_registrations r
        JOIN lectures l ON r.lecture_id = l.id
        WHERE r.lecture_id = 167
        ORDER BY r.created_at DESC
    ";
    
    // 승인된 신청 수 계산
    $countQuery = "
        SELECT 
            COUNT(CASE WHEN lr.status = 'approved' THEN 1 END) as approved_count,
            COUNT(CASE WHEN lr.status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN lr.status = 'rejected' THEN 1 END) as rejected_count,
            COUNT(CASE WHEN lr.status = 'cancelled' THEN 1 END) as cancelled_count,
            l.max_participants
        FROM lectures l
        LEFT JOIN lecture_registrations lr ON l.id = lr.lecture_id
        WHERE l.id = 167
        GROUP BY l.id, l.max_participants
    ";
    
    // 먼저 승인된 신청 수 확인
    $countResult = $conn->query($countQuery);
    if ($countResult && $countResult->num_rows > 0) {
        $counts = $countResult->fetch_assoc();
        echo "📊 신청 상태별 통계:\n";
        echo "승인됨: {$counts['approved_count']}명\n";
        echo "대기중: {$counts['pending_count']}명\n";
        echo "거절됨: {$counts['rejected_count']}명\n";
        echo "취소됨: {$counts['cancelled_count']}명\n";
        echo "정원: " . ($counts['max_participants'] ?? '무제한') . "\n\n";
    }
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "📋 현재 신청 내역:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-5s %-15s %-25s %-15s %-20s\n", "ID", "이름", "이메일", "상태", "신청일시");
        echo str_repeat("-", 80) . "\n";
        
        $registrations = [];
        while ($row = $result->fetch_assoc()) {
            $registrations[] = $row;
            printf("%-5s %-15s %-25s %-15s %-20s\n", 
                $row['id'], 
                $row['participant_name'], 
                $row['participant_email'], 
                $row['status'], 
                $row['created_at']
            );
        }
        
        echo "\n";
        
        // pending이 아닌 신청들을 pending으로 변경
        $pendingCount = 0;
        foreach ($registrations as $reg) {
            if ($reg['status'] !== 'pending') {
                $updateQuery = "
                    UPDATE lecture_registrations 
                    SET status = 'pending', processed_at = NULL, processed_by = NULL
                    WHERE id = ?
                ";
                
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("i", $reg['id']);
                
                if ($stmt->execute()) {
                    echo "✅ 신청 ID {$reg['id']} ({$reg['participant_name']}) 상태를 '{$reg['status']}' → 'pending'으로 변경\n";
                    $pendingCount++;
                } else {
                    echo "❌ 신청 ID {$reg['id']} 상태 변경 실패: " . $stmt->error . "\n";
                }
            } else {
                echo "ℹ️ 신청 ID {$reg['id']} ({$reg['participant_name']}) 이미 pending 상태\n";
            }
        }
        
        echo "\n=== 변경 완료 ===\n";
        echo "총 {$pendingCount}개 신청의 상태를 pending으로 변경했습니다.\n\n";
        
        // 변경 후 상태 재확인
        echo "📋 변경 후 신청 내역:\n";
        echo str_repeat("-", 80) . "\n";
        
        $result2 = $conn->query($query);
        if ($result2 && $result2->num_rows > 0) {
            printf("%-5s %-15s %-25s %-15s %-20s\n", "ID", "이름", "이메일", "상태", "신청일시");
            echo str_repeat("-", 80) . "\n";
            
            while ($row = $result2->fetch_assoc()) {
                printf("%-5s %-15s %-25s %-15s %-20s\n", 
                    $row['id'], 
                    $row['participant_name'], 
                    $row['participant_email'], 
                    $row['status'], 
                    $row['created_at']
                );
            }
        }
        
    } else {
        echo "❌ 강의 167번에 대한 신청 내역이 없습니다.\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>