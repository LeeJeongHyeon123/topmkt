#!/bin/bash

FIREBASE_URL="https://topmkt-832f2-default-rtdb.asia-southeast1.firebasedatabase.app"
USER_ID="3"

echo "=== 모든 채팅방에서 user_id: ${USER_ID} 참여 여부 확인 ==="
echo ""

# chatRooms의 모든 방 ID 가져오기
ROOM_IDS=$(curl -s "${FIREBASE_URL}/chatRooms.json?shallow=true" | python3 -c "import sys, json; data=json.load(sys.stdin); print('\n'.join(data.keys()))")

FOUND_COUNT=0

for ROOM_ID in $ROOM_IDS; do
    # 각 방의 participants 확인
    PARTICIPANTS=$(curl -s "${FIREBASE_URL}/chatRooms/${ROOM_ID}/participants.json")
    
    # user_id 3이 포함되어 있는지 확인
    if echo "$PARTICIPANTS" | grep -q '"3"'; then
        FOUND_COUNT=$((FOUND_COUNT + 1))
        echo "✅ 발견: ${ROOM_ID}"
        echo "   Participants: $PARTICIPANTS"
        
        # 마지막 메시지 시간 확인
        LAST_MESSAGE=$(curl -s "${FIREBASE_URL}/chatRooms/${ROOM_ID}.json" | python3 -c "import sys, json; data=json.load(sys.stdin); print(f\"lastMessage: {data.get('lastMessage', 'N/A')}, lastMessageTime: {data.get('lastMessageTime', 'N/A')}\")" 2>/dev/null)
        echo "   ${LAST_MESSAGE}"
        echo ""
    fi
done

echo "=== 총 발견된 채팅방: ${FOUND_COUNT}개 ==="
echo "현재 userRooms/3에 있는 방: 2개"
echo "차이: $((FOUND_COUNT - 2))개 채팅방이 userRooms에서 누락됨"

