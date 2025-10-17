# Firebase 실시간 알림 시스템 설계

## 📊 Firebase Realtime Database 구조

```json
{
  "pendingRegistrations": {
    "userId123": {
      "count": 5,
      "message": "5개의 신청이 처리 대기 중입니다",
      "timestamp": 1704067200000,
      "lastUpdated": "2025-01-01 10:00:00",
      "details": {
        "lectures": 3,
        "events": 2
      }
    },
    "userId456": {
      "count": 1,
      "message": "1개의 신청이 처리 대기 중입니다",
      "timestamp": 1704067260000,
      "lastUpdated": "2025-01-01 10:01:00",
      "details": {
        "lectures": 1,
        "events": 0
      }
    }
  },
  "notificationSettings": {
    "userId123": {
      "enabled": true,
      "sound": true,
      "frequency": "realtime"
    }
  }
}
```

## 🔄 데이터 흐름

1. **신청 발생** → `RegistrationController.php`
2. **DB 업데이트** → MySQL 데이터베이스
3. **Firebase 업데이트** → `pendingRegistrations/{userId}` 노드
4. **실시간 감지** → 클라이언트의 Firebase 리스너
5. **즉시 알림** → UI 업데이트

## 🛠️ 구현 계획

### Phase 1: Firebase Helper 클래스 생성
- Firebase REST API 통신을 위한 헬퍼 클래스
- 기존 ChatController의 Firebase 설정 재사용

### Phase 2: 서버 사이드 업데이트
- RegistrationController에 Firebase 업데이트 로직 추가
- 신청 승인/거절/생성 시 실시간 알림 발송

### Phase 3: 클라이언트 사이드 교체
- registration-notifications.js 완전 재작성
- 기존 chat-notifications.js 패턴 활용

### Phase 4: 기존 시스템 제거
- 30초 폴링 로직 제거
- API 엔드포인트 유지 (하위 호환성)