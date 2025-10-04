#!/bin/bash

FIREBASE_URL="https://topmkt-832f2-default-rtdb.asia-southeast1.firebasedatabase.app"
USER_ID="3"

echo "=== Firebase 데이터 확인 ==="
echo "사용자 ID: ${USER_ID} (우리집탄이)"
echo ""

echo "1. userRooms/${USER_ID} 확인 중..."
curl -s "${FIREBASE_URL}/userRooms/${USER_ID}.json" | python3 -m json.tool
echo ""
echo ""

echo "2. chatRooms 전체 목록 확인 중..."
curl -s "${FIREBASE_URL}/chatRooms.json?shallow=true" | python3 -m json.tool
echo ""

