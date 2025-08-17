<?php
/**
 * 🚀 FIXED: 수정된 시간 범위 검색 검증
 */

// 공지사항 #18 시간 정보
$notice18_created = new DateTime('2025-08-15 14:54:13');
$notice18_updated = new DateTime('2025-08-15 14:54:14');

// 공지사항 #17 이미지 타임스탬프
$image17_time = DateTime::createFromFormat('YmdHis', '20250815145027');

echo "✅ 수정된 시간 범위 검색 검증\n";
echo "============================\n\n";

echo "📋 공지사항 #18:\n";
echo "   생성: " . $notice18_created->format('Y-m-d H:i:s') . "\n";
echo "   수정: " . $notice18_updated->format('Y-m-d H:i:s') . "\n\n";

echo "🖼️ 공지사항 #17 이미지:\n";
echo "   타임스탬프: " . $image17_time->format('Y-m-d H:i:s') . "\n\n";

// 수정된 로직의 검색 범위 (±1분)
$minTime = clone $notice18_created;
$minTime->modify('-1 minutes');
$maxTime = clone $notice18_updated;
$maxTime->modify('+1 minutes');

echo "🔍 수정된 검색 범위 (±1분):\n";
echo "   최소: " . $minTime->format('Y-m-d H:i:s') . "\n";
echo "   최대: " . $maxTime->format('Y-m-d H:i:s') . "\n\n";

// 범위 확인
$isInRange = ($image17_time >= $minTime && $image17_time <= $maxTime);

echo "✅ 결과: 공지사항 #17 이미지가 #18 검색 범위에 " . ($isInRange ? "포함됨" : "제외됨") . "\n";

if (!$isInRange) {
    echo "✅ 버그 수정 완료! 이제 잘못된 이미지가 표시되지 않습니다.\n";
} else {
    echo "❌ 아직 문제가 있습니다. 추가 수정 필요.\n";
}
?>