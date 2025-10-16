# QA 가이드: 알림 설정 시스템 (v3.86.0)

## 개요
FCM 앱 푸시 알림 설정 관리 시스템 QA 가이드입니다.

작성일: 2025-10-16

## 테스트 환경
- **URL**: https://www.topmktx.com
- **테스트 계정**: 일반 회원 계정 필요
- **브라우저**: Chrome, Safari, Firefox (모바일/PC 모두)

---

## 1. 페이지 접근 테스트

### 1.1 로그인 사용자
- [ ] 헤더 메뉴 > 프로필 아이콘 클릭
- [ ] "알림 설정" 메뉴 항목 확인 (bell 아이콘)
- [ ] "알림 설정" 클릭 시 `/notifications/settings` 페이지 이동
- [ ] 페이지 제목 "알림 설정" 표시 확인
- [ ] 보라색 그라디언트 헤더 확인

### 1.2 비로그인 사용자
- [ ] `/notifications/settings` 직접 접근 시도
- [ ] 로그인 페이지로 리다이렉트 확인
- [ ] 로그인 후 원래 페이지로 복귀 확인

---

## 2. UI/UX 테스트

### 2.1 데스크톱 (1920px)
- [ ] 최대 너비 800px 컨테이너 중앙 정렬 확인
- [ ] 그라디언트 헤더 렌더링 확인
- [ ] 6개 Toggle Switch 모두 표시 확인
- [ ] 안내 메시지 박스 표시 확인
- [ ] 저장/취소 버튼 표시 확인

### 2.2 태블릿 (768px)
- [ ] 레이아웃 정상 표시 확인
- [ ] Toggle Switch 정렬 확인
- [ ] 텍스트 가독성 확인

### 2.3 모바일 (375px)
- [ ] 세로 레이아웃 전환 확인
- [ ] Toggle Switch가 아래로 이동 확인
- [ ] 버튼 전체 너비 표시 확인
- [ ] 좌우 스크롤 없음 확인

---

## 3. 기능 테스트

### 3.1 설정 로드
- [ ] 페이지 로드 시 Loading 인디케이터 표시
- [ ] API `/api/notifications/settings` 호출 확인 (개발자 도구 Network 탭)
- [ ] 현재 설정값으로 Toggle Switch 상태 표시
- [ ] 전체 알림 OFF인 경우 개별 토글 비활성화 확인

### 3.2 전체 알림 토글
- [ ] "전체 알림" Toggle Switch 클릭
- [ ] OFF 시 5개 개별 토글 비활성화 (회색 처리)
- [ ] ON 시 5개 개별 토글 활성화
- [ ] 시각적 피드백 확인 (opacity 변화)

### 3.3 개별 알림 토글
- [ ] "댓글, 대댓글 알림" 토글 ON/OFF
- [ ] "좋아요 알림" 토글 ON/OFF
- [ ] "신규 강의, 행사 알림" 토글 ON/OFF
- [ ] "신청 승인, 거절 알림" 토글 ON/OFF
- [ ] "공지사항 알림" 토글 ON/OFF
- [ ] 전체 알림이 OFF인 경우 개별 토글 비활성화 확인

### 3.4 저장 기능
- [ ] 설정 변경 후 "저장" 버튼 클릭
- [ ] Loading 인디케이터 "저장 중..." 표시
- [ ] API `/api/notifications/settings` PUT 호출 확인
- [ ] Toast 성공 메시지 "알림 설정이 저장되었습니다." 표시
- [ ] 1초 후 페이지 자동 새로고침
- [ ] 새로고침 후 변경된 설정 유지 확인

### 3.5 취소 기능
- [ ] "취소" 버튼 클릭
- [ ] 홈페이지 `/` 이동 확인
- [ ] 변경사항 저장 안 됨 확인

---

## 4. API 테스트

### 4.1 GET /api/notifications/settings
```bash
curl -X GET https://www.topmktx.com/api/notifications/settings \
  -H "Cookie: PHPSESSID=your_session_id"
```
- [ ] HTTP 200 OK 응답
- [ ] JSON 응답 포맷 확인
- [ ] `all_notifications`, `comments_enabled`, `likes_enabled`, `lectures_events_enabled`, `registration_enabled`, `notices_enabled` 필드 확인
- [ ] 로그인 안 한 경우 401 Unauthorized

### 4.2 PUT /api/notifications/settings
```bash
curl -X PUT https://www.topmktx.com/api/notifications/settings \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{
    "csrf_token": "your_csrf_token",
    "all_notifications": true,
    "comments_enabled": false,
    "likes_enabled": true,
    "lectures_events_enabled": true,
    "registration_enabled": true,
    "notices_enabled": false
  }'
```
- [ ] HTTP 200 OK 응답
- [ ] "알림 설정이 성공적으로 업데이트되었습니다." 메시지
- [ ] 변경된 설정 반영 확인
- [ ] CSRF 토큰 없으면 403 Forbidden
- [ ] 로그인 안 한 경우 401 Unauthorized

### 4.3 POST /api/notifications/toggle-all
```bash
curl -X POST https://www.topmktx.com/api/notifications/toggle-all \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{
    "csrf_token": "your_csrf_token",
    "enabled": false
  }'
```
- [ ] HTTP 200 OK 응답
- [ ] "모든 알림이 비활성화되었습니다." 메시지 (enabled=false 시)
- [ ] "모든 알림이 활성화되었습니다." 메시지 (enabled=true 시)

---

## 5. 에러 처리 테스트

### 5.1 네트워크 오류
- [ ] 개발자 도구에서 네트워크 차단 (Offline)
- [ ] 페이지 새로고침 시도
- [ ] Toast 에러 메시지 "설정을 불러오는데 실패했습니다." 표시
- [ ] 저장 버튼 클릭 시도
- [ ] Toast 에러 메시지 "저장 중 오류가 발생했습니다." 표시

### 5.2 세션 만료
- [ ] 세션 쿠키 삭제
- [ ] 페이지 새로고침
- [ ] 로그인 페이지로 리다이렉트 확인

### 5.3 CSRF 토큰 오류
- [ ] 개발자 도구 Console에서 csrfToken 변수 변조
- [ ] 저장 버튼 클릭
- [ ] Toast 에러 메시지 "유효하지 않은 요청입니다." 표시

---

## 6. 데이터베이스 검증

### 6.1 신규 사용자
```sql
SELECT * FROM notification_settings WHERE user_id = [신규_사용자_ID];
```
- [ ] 최초 접근 시 자동으로 레코드 생성
- [ ] 모든 설정 기본값 1 (ON)

### 6.2 설정 업데이트
```sql
SELECT * FROM notification_settings WHERE user_id = [테스트_사용자_ID];
```
- [ ] 설정 변경 후 DB 값 업데이트 확인
- [ ] `updated_at` 타임스탬프 변경 확인

### 6.3 회원 탈퇴
```sql
DELETE FROM users WHERE user_id = [탈퇴_테스트_계정_ID];
SELECT * FROM notification_settings WHERE user_id = [탈퇴_테스트_계정_ID];
```
- [ ] CASCADE DELETE로 알림 설정도 자동 삭제 확인

---

## 7. 보안 테스트

### 7.1 인증 확인
- [ ] 비로그인 상태로 `/notifications/settings` 직접 접근 차단
- [ ] API 엔드포인트 모두 인증 필요 확인

### 7.2 CSRF 보호
- [ ] 외부 도메인에서 API 호출 차단 확인
- [ ] CSRF 토큰 없는 요청 403 응답 확인

### 7.3 입력 검증
- [ ] Boolean 외 값 전송 시 정상 변환 확인
- [ ] 허용되지 않은 필드 전송 시 무시 확인

---

## 8. 성능 테스트

### 8.1 로딩 속도
- [ ] 페이지 초기 로딩 3초 이내
- [ ] API 응답 1초 이내
- [ ] 이미지/리소스 최적화 확인

### 8.2 반응 속도
- [ ] Toggle Switch 클릭 즉시 반응
- [ ] 저장 버튼 클릭 후 2초 이내 완료

---

## 9. 크로스 브라우저 테스트

### 9.1 Chrome (최신)
- [ ] 모든 기능 정상 동작
- [ ] UI 정상 렌더링

### 9.2 Safari (iOS/macOS)
- [ ] Toggle Switch 정상 동작
- [ ] API 호출 정상

### 9.3 Firefox (최신)
- [ ] 레이아웃 정상 표시
- [ ] JavaScript 정상 실행

### 9.4 Edge (최신)
- [ ] 모든 기능 정상 동작

---

## 10. 접근성 테스트

### 10.1 키보드 네비게이션
- [ ] Tab 키로 모든 Toggle Switch 포커스 가능
- [ ] Space/Enter 키로 토글 가능
- [ ] 저장/취소 버튼 키보드 접근 가능

### 10.2 스크린 리더
- [ ] Toggle Switch에 적절한 label 연결
- [ ] 버튼에 명확한 텍스트 레이블
- [ ] 알림 설명 텍스트 읽기 가능

---

## 버그 리포트 템플릿

```
**제목**: [버그] 알림 설정 - [간단한 설명]

**환경**:
- OS: [Windows 11 / macOS 14 / iOS 17 / Android 13]
- 브라우저: [Chrome 120 / Safari 17 / Firefox 121]
- 화면 크기: [1920x1080 / 375x667]

**재현 단계**:
1.
2.
3.

**예상 결과**:


**실제 결과**:


**스크린샷**:
[첨부]

**추가 정보**:
- 개발자 도구 Console 에러:
- Network 탭 상태:
```

---

## QA 완료 체크리스트

### 기능
- [ ] 페이지 접근 및 권한
- [ ] 전체 알림 토글
- [ ] 개별 알림 토글
- [ ] 저장/취소 기능
- [ ] API 엔드포인트

### UI/UX
- [ ] 데스크톱 레이아웃
- [ ] 태블릿 레이아웃
- [ ] 모바일 레이아웃
- [ ] Toggle Switch 애니메이션
- [ ] Toast/Loading 알림

### 보안
- [ ] 인증 및 권한
- [ ] CSRF 보호
- [ ] 입력 검증

### 성능
- [ ] 페이지 로딩 속도
- [ ] API 응답 속도
- [ ] 메모리 누수 없음

### 접근성
- [ ] 키보드 네비게이션
- [ ] 스크린 리더 호환

---

## 테스트 결과 기록

| 날짜 | 테스터 | 브라우저 | 통과/실패 | 비고 |
|------|--------|----------|-----------|------|
| 2025-10-16 |  |  |  |  |

---

**QA 승인**: ________________
**날짜**: ________________
