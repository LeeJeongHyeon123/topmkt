# 프로필 페이지 성능 테스트 가이드 (v3.66.0)

## 🎯 목적
캐시 TTL 1시간 연장 효과를 실제 환경에서 측정합니다.

## 📊 테스트 방법

### 1️⃣ 캐시 초기화
```bash
# 프로필 캐시 삭제
rm /var/www/html/topmkt/cache/profile_*.json

# user_stats_cache 테이블 초기화 (옵션)
mysql -h 127.0.0.1 -u root -pDnlszkem1! TOPMKT -e "TRUNCATE TABLE user_stats_cache;"
```

### 2️⃣ 브라우저에서 테스트

**Chrome DevTools 사용**:
1. `F12` → Network 탭 열기
2. "Disable cache" 체크박스 **해제** (캐시 활성화)
3. `https://www.topmktx.com/profile` 접속 (로그인 상태)
4. **첫 번째 로드**: DOMContentLoaded 시간 확인 (캐시 MISS)
5. `Ctrl+R` 새로고침
6. **두 번째 로드**: DOMContentLoaded 시간 확인 (캐시 HIT)

**Console 탭 확인**:
```
🚀 프로필 페이지 성능 분석 (v3.66.0)
├─ 서버 응답: XXXms
```

### 3️⃣ 예상 결과

**최적화 전 (v3.65.0 이전)**:
- 캐시 MISS (10분 경과): ~6000ms (사용자 보고)
- 캐시 HIT: ~20ms

**최적화 후 (v3.66.0)**:
- 캐시 MISS (1시간 경과): ~6000ms (동일 - 쿼리 자체는 동일)
- 캐시 HIT: ~20ms (동일)
- **캐시 히트율**: 10분 TTL → 60분 TTL (6배 증가!)
- **사용자 체감 성능**: 6배 개선 (대부분 캐시 사용)

## 🚀 최적화 내용

### 1. JavaScript 성능 로깅 버그 수정
**파일**: `/src/views/user/profile.php` (라인 1352-1440)
**문제**: `loadEventEnd`가 0일 때 음수 타이밍 값 발생
**해결**: `safeCalc()` 함수로 안전한 계산, 100ms 지연 추가

### 2. 파일 캐시 TTL 연장
**파일**: `/src/models/UserOptimized.php` (라인 34-44)
**변경**: 600초 (10분) → 3600초 (1시간)
**효과**: 캐시 재계산 빈도 6분의 1로 감소

### 3. DB 캐시 TTL 연장
**파일**: `/src/models/UserOptimized.php` (라인 145-164)
**변경**: `10 MINUTE` → `1 HOUR`
**효과**: 통계 쿼리 실행 빈도 6분의 1로 감소

## 📈 성능 개선 예측

### 시나리오 1: 활발한 사용자 (10분마다 접속)
- **최적화 전**: 매번 캐시 만료 → 6초 로딩
- **최적화 후**: 1시간 내 캐시 유지 → 0.02초 로딩
- **개선율**: 99.7% 빠름 (300배)

### 시나리오 2: 보통 사용자 (30분마다 접속)
- **최적화 전**: 매번 캐시 만료 → 6초 로딩
- **최적화 후**: 1시간 내 캐시 유지 → 0.02초 로딩
- **개선율**: 99.7% 빠름 (300배)

### 시나리오 3: 가끔 사용자 (2시간마다 접속)
- **최적화 전**: 매번 캐시 만료 → 6초 로딩
- **최적화 후**: 매번 캐시 만료 → 6초 로딩
- **개선율**: 변화 없음 (캐시 만료)

## 🔍 캐시 파일 확인

```bash
# 캐시 파일 존재 확인
ls -lah /var/www/html/topmkt/cache/profile_*.json

# 캐시 파일 내용 확인 (user_id=4 예시)
cat /var/www/html/topmkt/cache/profile_4.json | jq .

# 캐시 나이 확인
stat /var/www/html/topmkt/cache/profile_4.json | grep Modify
```

## 💡 추가 최적화 제안 (향후)

### Phase 2: Redis 도입
- 파일 캐시 → Redis 메모리 캐시
- 예상 효과: 캐시 읽기 10배 빠름 (1ms → 0.1ms)

### Phase 3: 쿼리 병렬화
- 4개 순차 쿼리 → 병렬 실행
- 예상 효과: 캐시 미스 시 50% 빠름 (6초 → 3초)

### Phase 4: 비동기 캐시 워밍
- 캐시 만료 5분 전 백그라운드 갱신
- 예상 효과: 캐시 만료 시에도 빠른 응답

## 📝 로그 확인

**성능 로그 확인**:
```bash
# Apache 에러 로그에서 프로필 관련 로그 확인
tail -f /var/log/apache2/*error.log | grep -i "profile"
```

**예상 로그**:
```
✅ Profile cache HIT for user 4 (age: 123s / 3600s)
✅ Stats cache HIT for user 4
```

또는

```
Profile cache miss for user 4, calculating fresh data...
❌ Stats cache MISS for user 4, recalculating...
Profile calculation completed for user 4 in 150.23ms
```

## ✅ 테스트 완료 기준

1. **캐시 MISS**: 서버 응답 > 1000ms (정상 - 복잡한 쿼리)
2. **캐시 HIT**: 서버 응답 < 100ms (목표 달성)
3. **캐시 생성**: `/cache/profile_X.json` 파일 존재
4. **캐시 TTL**: 1시간 이내 재접속 시 캐시 사용

## 🎉 성공 조건

✅ 첫 로드 후 1시간 내 모든 재접속이 100ms 이내
✅ 음수 타이밍 값 없음
✅ Console에 "⚠️ 느림!" 경고 없음
