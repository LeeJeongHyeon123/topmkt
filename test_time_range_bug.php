<?php
/**
 * 🚨 CRITICAL: 시간 범위 검색 버그 검증
 */

// 공지사항 #18 시간 정보
$notice18_created = new DateTime('2025-08-15 14:54:13');
$notice18_updated = new DateTime('2025-08-15 14:54:14');

// 공지사항 #17 이미지 타임스탬프
$image17_time = DateTime::createFromFormat('YmdHis', '20250815145027');

echo "🚨 시간 범위 검색 버그 분석\n";
echo "============================\n\n";

echo "📋 공지사항 #18:\n";
echo "   생성: " . $notice18_created->format('Y-m-d H:i:s') . "\n";
echo "   수정: " . $notice18_updated->format('Y-m-d H:i:s') . "\n\n";

echo "🖼️ 공지사항 #17 이미지:\n";
echo "   타임스탬프: " . $image17_time->format('Y-m-d H:i:s') . "\n\n";

// 현재 로직의 검색 범위
$minTime = clone $notice18_created;
$minTime->modify('-5 minutes');
$maxTime = clone $notice18_updated;
$maxTime->modify('+30 minutes');

echo "🔍 현재 검색 범위 (버그):\n";
echo "   최소: " . $minTime->format('Y-m-d H:i:s') . "\n";
echo "   최대: " . $maxTime->format('Y-m-d H:i:s') . "\n\n";

// 범위 확인
$isInRange = ($image17_time >= $minTime && $image17_time <= $maxTime);

echo "❌ 결과: 공지사항 #17 이미지가 #18 검색 범위에 " . ($isInRange ? "포함됨" : "제외됨") . "\n";
echo "❌ 이것이 잘못된 이미지가 표시되는 원인입니다!\n\n";

echo "💡 해결책:\n";
echo "1. 시간 범위를 더 좁게 제한\n";
echo "2. 공지사항별 고유 식별자 사용\n";
echo "3. 명시적인 이미지-공지사항 연결 테이블 사용\n";
?>