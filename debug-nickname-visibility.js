// 🔍 채팅방 닉네임 표시 문제 디버깅 코드
// 브라우저 콘솔에 복사해서 붙여넣기 하세요

console.log("🔍 ===== 채팅방 닉네임 디버깅 시작 =====");

// 1. 기본 정보 확인
console.log("📍 1. 기본 정보");
console.log("현재 활성 채팅방 ID:", activeRoomId);
console.log("현재 사용자 ID:", currentUserId);
console.log("대화 상대 사용자 ID:", currentPartnerUserId);

// 2. HTML 요소 상태 확인
console.log("\n📍 2. HTML 요소 상태");
const nameElement = document.getElementById('chatPartnerName');
if (nameElement) {
    console.log("요소 존재:", true);
    console.log("텍스트 내용:", `"${nameElement.textContent}"`);
    console.log("innerHTML:", nameElement.innerHTML);
    console.log("부모 요소:", nameElement.parentElement);
} else {
    console.log("❌ chatPartnerName 요소를 찾을 수 없음!");
}

// 3. CSS 스타일 확인
console.log("\n📍 3. CSS 스타일 상태");
if (nameElement) {
    const rect = nameElement.getBoundingClientRect();
    const computedStyle = window.getComputedStyle(nameElement);

    console.log("요소 크기:", `${rect.width}px × ${rect.height}px`);
    console.log("요소 위치:", `(${Math.round(rect.x)}, ${Math.round(rect.y)})`);
    console.log("display:", computedStyle.display);
    console.log("visibility:", computedStyle.visibility);
    console.log("opacity:", computedStyle.opacity);
    console.log("color:", computedStyle.color);
    console.log("font-size:", computedStyle.fontSize);
    console.log("font-weight:", computedStyle.fontWeight);
    console.log("z-index:", computedStyle.zIndex);
    console.log("position:", computedStyle.position);

    // 실제로 보이는지 계산
    const isVisible = rect.width > 0 && rect.height > 0 &&
                     computedStyle.display !== 'none' &&
                     computedStyle.visibility !== 'hidden' &&
                     parseFloat(computedStyle.opacity) > 0;
    console.log("실제 표시 여부:", isVisible);
}

// 4. 사용자 정보 확인
console.log("\n📍 4. 사용자 정보 상태");
console.log("전체 users 객체:", users);
if (currentPartnerUserId) {
    console.log(`상대방(${currentPartnerUserId}) 정보:`, users[currentPartnerUserId]);
    if (users[currentPartnerUserId]) {
        console.log("상대방 닉네임:", users[currentPartnerUserId].nickname);
        console.log("상대방 프로필 이미지:", users[currentPartnerUserId].profile_image);
    } else {
        console.log("❌ 상대방 사용자 정보가 users 객체에 없음!");
    }
} else {
    console.log("❌ currentPartnerUserId가 설정되지 않음!");
}

// 5. 채팅방 데이터 확인
console.log("\n📍 5. 채팅방 데이터");
if (activeRoomId && chatRooms[activeRoomId]) {
    console.log("현재 채팅방 데이터:", chatRooms[activeRoomId]);
    console.log("채팅방 타입:", chatRooms[activeRoomId].type);
    console.log("참여자 목록:", chatRooms[activeRoomId].participants);

    if (chatRooms[activeRoomId].participants) {
        const participantIds = Object.keys(chatRooms[activeRoomId].participants);
        console.log("참여자 ID들:", participantIds);
        const otherUserId = participantIds.find(id => id != currentUserId);
        console.log("계산된 상대방 ID:", otherUserId);
    }
} else {
    console.log("❌ 현재 채팅방 데이터를 찾을 수 없음!");
}

// 6. 주변 요소들 확인 (가림 요소 찾기)
console.log("\n📍 6. 주변 요소 및 가림 요소 확인");
if (nameElement) {
    const rect = nameElement.getBoundingClientRect();
    const centerX = rect.x + rect.width / 2;
    const centerY = rect.y + rect.height / 2;

    console.log("요소 중심점:", `(${Math.round(centerX)}, ${Math.round(centerY)})`);

    // 해당 위치의 최상위 요소 확인
    const topElement = document.elementFromPoint(centerX, centerY);
    console.log("중심점의 최상위 요소:", topElement);
    console.log("최상위 요소가 chatPartnerName인가?:", topElement === nameElement);

    if (topElement !== nameElement) {
        console.log("⚠️ 다른 요소가 닉네임을 가리고 있을 가능성!");
        console.log("가리는 요소의 클래스:", topElement?.className);
        console.log("가리는 요소의 ID:", topElement?.id);
        console.log("가리는 요소의 스타일:", window.getComputedStyle(topElement));
    }
}

// 7. 부모 컨테이너 확인
console.log("\n📍 7. 부모 컨테이너 상태");
const partnerInfo = document.querySelector('.chat-partner-info');
if (partnerInfo) {
    const partnerRect = partnerInfo.getBoundingClientRect();
    const partnerStyle = window.getComputedStyle(partnerInfo);
    console.log("chat-partner-info 크기:", `${partnerRect.width}px × ${partnerRect.height}px`);
    console.log("chat-partner-info display:", partnerStyle.display);
    console.log("chat-partner-info overflow:", partnerStyle.overflow);
} else {
    console.log("❌ chat-partner-info 요소를 찾을 수 없음!");
}

console.log("\n🔍 ===== 디버깅 완료 =====");
console.log("위 정보를 모두 복사해서 개발자에게 전달해주세요!");