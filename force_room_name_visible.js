// 🔥 강제로 room-name 요소를 보이게 만드는 스크립트
// 브라우저 콘솔에 복사해서 붙여넣기

console.log("🔥 room-name 요소 강제 표시 시작");

// 정확한 선택자로 요소 찾기
const roomNameElement = document.querySelector('#chatRoomsList > div:nth-child(1) > div > div.room-details > div.room-header > div.room-name');

if (roomNameElement) {
    console.log("✅ 요소 찾음:", roomNameElement);
    console.log("현재 텍스트:", roomNameElement.textContent);

    // 현재 스타일 상태 확인
    const computedStyle = window.getComputedStyle(roomNameElement);
    console.log("현재 스타일:");
    console.log("- display:", computedStyle.display);
    console.log("- visibility:", computedStyle.visibility);
    console.log("- opacity:", computedStyle.opacity);
    console.log("- width:", computedStyle.width);
    console.log("- height:", computedStyle.height);
    console.log("- max-width:", computedStyle.maxWidth);
    console.log("- overflow:", computedStyle.overflow);
    console.log("- color:", computedStyle.color);
    console.log("- font-size:", computedStyle.fontSize);
    console.log("- white-space:", computedStyle.whiteSpace);

    // 🔥 강제로 보이게 만들기
    roomNameElement.style.cssText = `
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        color: #ff0000 !important;
        font-size: 16px !important;
        font-weight: bold !important;
        background: yellow !important;
        padding: 5px !important;
        border: 2px solid red !important;
        max-width: none !important;
        width: auto !important;
        min-width: 100px !important;
        overflow: visible !important;
        white-space: nowrap !important;
        text-overflow: none !important;
        z-index: 99999 !important;
        position: relative !important;
    `;

    // 텍스트도 강제로 설정
    roomNameElement.textContent = "🔥 강제 표시 테스트 - 안계현";

    console.log("🔥 강제 스타일 적용 완료!");
    console.log("이제 빨간 글씨에 노란 배경으로 보여야 합니다.");

    // 부모 요소들도 확인
    let parent = roomNameElement.parentElement;
    let level = 1;
    while (parent && level <= 5) {
        const parentStyle = window.getComputedStyle(parent);
        console.log(`부모 ${level} (${parent.className}):`);
        console.log(`- overflow: ${parentStyle.overflow}`);
        console.log(`- display: ${parentStyle.display}`);
        console.log(`- position: ${parentStyle.position}`);

        // 부모의 overflow hidden 해제
        if (parentStyle.overflow === 'hidden') {
            parent.style.overflow = 'visible';
            console.log(`부모 ${level} overflow를 visible로 변경`);
        }

        parent = parent.parentElement;
        level++;
    }

} else {
    console.log("❌ 해당 선택자로 요소를 찾을 수 없음!");

    // 대체 방법으로 모든 room-name 요소 찾기
    const allRoomNames = document.querySelectorAll('.room-name');
    console.log(`전체 room-name 요소 개수: ${allRoomNames.length}`);

    allRoomNames.forEach((element, index) => {
        console.log(`room-name ${index}:`, element.textContent);

        // 모든 요소를 강제로 보이게 만들기
        element.style.cssText = `
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            color: #ff0000 !important;
            font-size: 16px !important;
            font-weight: bold !important;
            background: yellow !important;
            padding: 5px !important;
            border: 2px solid red !important;
            max-width: none !important;
            width: auto !important;
            min-width: 100px !important;
            overflow: visible !important;
            white-space: nowrap !important;
            text-overflow: none !important;
            z-index: 99999 !important;
            position: relative !important;
        `;

        element.textContent = `🔥 강제표시${index} - ${element.textContent || '텍스트없음'}`;
    });
}

console.log("🔥 강제 표시 스크립트 완료");