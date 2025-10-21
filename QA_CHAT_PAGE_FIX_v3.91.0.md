# 채팅 페이지 무한 루프 수정 QA 가이드 (v3.91.0)

## 🔍 수정 내용

### 문제:
- 채팅 페이지 접속 시 페이지가 멈추는 현상
- 브라우저가 응답 없음 상태로 전환
- CPU 사용률 100% 도달

### 근본 원인:
**renderChatRoomItem() 함수의 무한 재귀 호출**

```javascript
// ❌ 이전 코드 (v3.90.9)
loadUserInfo(otherUserId).then(() => {
    renderChatRoomItem(roomId, roomData); // ← 무한 재귀!
})
```

**문제 흐름**:
1. renderChatRoomItem() 호출
2. users[otherUserId] 없으면 loadUserInfo() 호출
3. loadUserInfo() 완료 후 renderChatRoomItem() 재호출
4. users[otherUserId]가 존재하지만 nickname이 없으면 다시 loadUserInfo() 호출
5. 하지만 loadUserInfo()는 users[userId]가 존재하면 early return
6. 결과: 무한 루프 발생!

### 해결 방법:
**renderChatRoomItem() 재귀 호출 완전 제거 + DOM 직접 업데이트**

```javascript
// ✅ 수정된 코드 (v3.91.0)
loadUserInfo(otherUserId).then(() => {
    // 재귀 호출 대신 DOM 직접 업데이트
    if (users[otherUserId]) {
        const nameEl = roomItem.querySelector('.room-name');
        const avatarEl = roomItem.querySelector('.room-avatar');

        if (nameEl) {
            nameEl.textContent = users[otherUserId].nickname || '사용자';
        }

        if (avatarEl && users[otherUserId].profile_image) {
            avatarEl.innerHTML = `<img src="..." />`;
        }
    }
})
```

**추가 개선**:
- 전역 플래그 `window.loadingUsers`로 중복 로딩 방지
- users 객체 존재 시 무조건 사용 (nickname 없어도 기본값 "사용자" 사용)

---

## 📋 QA 체크리스트

### 1. 페이지 로딩 테스트

#### 준비:
1. 브라우저 캐시 완전 삭제 (Ctrl+Shift+Delete)
2. 개발자 도구 열기 (F12)
3. Console 탭, Network 탭 모두 확인

#### 테스트 A: 정상 로딩 확인
```
✅ 테스트 계정: 01067033056 (user_id=4, 우리집탄이)
✅ URL: https://www.topmktx.com/chat
```

**절차**:
1. 로그인 헬퍼로 로그인
   - URL: `https://www.topmktx.com/dev/login_helper.php?user_id=4`
2. 채팅 페이지 접속
   - URL: `https://www.topmktx.com/chat`
3. 10초 대기

**체크 포인트**:
- [ ] 페이지가 10초 내에 완전히 로드됨
- [ ] 채팅방 목록이 정상적으로 표시됨
- [ ] 브라우저가 멈추지 않음
- [ ] CPU 사용률이 정상 범위 내 (< 30%)

#### 테스트 B: API 호출 횟수 확인
**Network 탭에서 확인**:
- [ ] `/api/users/{userId}/profile-image` 호출 횟수 < 20회
- [ ] 동일한 userId로 반복 호출되지 않음
- [ ] 모든 API 응답이 200 또는 404

**Console 탭에서 확인**:
- [ ] 빨간색 에러 없음
- [ ] "무한 루프" 관련 경고 없음

---

### 2. 기능 테스트

#### 테스트 C: 채팅방 목록 렌더링
**절차**:
1. 채팅 페이지 로드 후 채팅방 목록 확인

**체크 포인트**:
- [ ] 모든 채팅방이 표시됨
- [ ] 사용자 닉네임이 정상 표시 (또는 "사용자")
- [ ] 프로필 이미지가 정상 표시 (없으면 기본 아바타)
- [ ] 마지막 메시지가 표시됨

#### 테스트 D: 채팅방 열기
**절차**:
1. 채팅방 목록에서 아무 채팅방이나 클릭
2. 채팅 영역이 열리는지 확인

**체크 포인트**:
- [ ] 채팅방이 즉시 열림 (< 2초)
- [ ] 메시지 목록이 표시됨
- [ ] 상대방 정보가 헤더에 표시됨
- [ ] 입력창이 활성화됨

#### 테스트 E: 메시지 전송
**절차**:
1. 열린 채팅방에서 메시지 입력
2. 전송 버튼 클릭 (또는 Enter)

**체크 포인트**:
- [ ] 메시지가 즉시 전송됨
- [ ] 채팅방 목록의 마지막 메시지가 업데이트됨
- [ ] 페이지가 멈추지 않음

---

### 3. 성능 테스트

#### 테스트 F: 장시간 사용
**절차**:
1. 채팅 페이지를 10분간 열어둠
2. 다른 작업 하다가 다시 확인

**체크 포인트**:
- [ ] 페이지가 여전히 응답함
- [ ] 새 메시지 수신 시 즉시 업데이트됨
- [ ] 메모리 사용량이 과도하게 증가하지 않음

#### 테스트 G: 여러 채팅방 전환
**절차**:
1. 채팅방 A 열기 → 채팅방 B 열기 → 채팅방 C 열기
2. 반복 5회

**체크 포인트**:
- [ ] 모든 전환이 즉시 이루어짐
- [ ] 이전 채팅방의 리스너가 제대로 정리됨
- [ ] Console 에러 없음

---

### 4. 크로스 브라우저 테스트

#### 테스트 H: 다양한 브라우저
**테스트 대상**:
- [ ] Chrome (최신 버전)
- [ ] Firefox (최신 버전)
- [ ] Safari (Mac)
- [ ] Edge (최신 버전)

**체크 포인트** (각 브라우저):
- [ ] 정상 로딩
- [ ] 기능 정상 동작
- [ ] 성능 문제 없음

---

### 5. 회귀 테스트

#### 테스트 I: 이전 버그 재발 방지
**v3.90.8 버그 (Firebase 리스너 무한 증식)**:
- [ ] Network 탭에서 Firebase 요청 반복 확인
- [ ] Firebase 리스너가 증식하지 않음

**v3.90.9 버그 (loadUserInfo 무한 루프)**:
- [ ] `/api/users/{userId}/profile-image` 중복 호출 없음
- [ ] users 객체가 제대로 캐시됨

**v3.91.0 버그 (renderChatRoomItem 무한 재귀)**:
- [ ] renderChatRoomItem() 재귀 호출 없음
- [ ] DOM이 직접 업데이트됨

---

## 📊 성능 기준

### 정상 범위:
- **페이지 로드 시간**: < 5초
- **API 호출 횟수**: < 20회 (10초 동안)
- **콘솔 에러**: 0개
- **메모리 증가량**: < 50MB (10분 동안)
- **CPU 사용률**: < 30% (평균)

### 비정상 범위 (재발 시):
- **페이지 로드 시간**: > 30초 또는 무한 로딩
- **API 호출 횟수**: > 50회 (10초 동안) → 무한 루프 의심
- **콘솔 에러**: 빨간색 에러 발생
- **메모리 증가량**: > 100MB (1분 동안) → 메모리 누수
- **CPU 사용률**: > 80% 지속 → 무한 루프

---

## 🐛 문제 발생 시 대처

### 1. 페이지가 여전히 멈춤
**확인 사항**:
1. 브라우저 캐시 삭제했는지
2. 파일이 제대로 배포되었는지 확인
   ```bash
   grep -n "FIX v3.91.0" /var/www/html/topmkt/src/views/chat/index.php
   ```
3. 다른 JavaScript 에러가 있는지 Console 확인

### 2. API 호출이 과도함
**확인 사항**:
1. Network 탭에서 어떤 userId가 반복 호출되는지 확인
2. 해당 userId의 데이터가 제대로 있는지 DB 확인
   ```sql
   SELECT * FROM users WHERE id = {userId};
   ```

### 3. 특정 채팅방에서만 문제 발생
**확인 사항**:
1. 해당 채팅방의 participants 데이터 확인 (Firebase Console)
2. 상대방이 탈퇴한 회원인지 확인
3. chatRooms 데이터가 올바른지 확인

---

## 🎯 QA 통과 기준

### 최소 요구사항 (필수):
- ✅ 테스트 A, B, C, D, E 모두 통과
- ✅ 성능 기준 정상 범위 내
- ✅ 콘솔 에러 0개

### 권장 사항:
- ✅ 테스트 F, G, H, I 모두 통과
- ✅ 크로스 브라우저 테스트 완료
- ✅ 7일간 프로덕션 모니터링 이상 없음

---

## 📝 QA 결과 리포트

**테스트 일시**: ____________

**테스트 환경**:
- 브라우저: ____________
- OS: ____________
- 테스트 계정: ____________

**결과**:
- [ ] ✅ 통과
- [ ] ❌ 실패 (사유: ___________________________)

**특이사항**:
_______________________________________________________
_______________________________________________________

**테스터**: ____________

---

**작성일**: 2025-10-21
**버전**: v3.91.0
**작성자**: Claude (Anthropic)
