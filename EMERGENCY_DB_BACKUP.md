# 🚨 긴급 데이터베이스 백업 가이드

## ⚠️ 현재 상황

현재 Claude가 실행 중인 도커 컨테이너에는:
- ❌ MySQL 클라이언트 미설치
- ❌ PHP PDO MySQL 드라이버 미설치
- ❌ 직접적인 DB 접근 불가

**따라서 다음 방법으로 백업해야 합니다:**

---

## 🔥 즉시 실행해야 할 백업 방법

### 방법 1: 호스트 시스템에서 직접 백업

```bash
# 1. 호스트 시스템에 SSH 또는 터미널 접속

# 2. MySQL/MariaDB가 실행 중인지 확인
systemctl status mysql
# 또는
systemctl status mariadb

# 3. 데이터베이스 백업 실행
mysqldump -u root -p topmkt > topmkt_backup_$(date +%Y%m%d_%H%M%S).sql

# 4. 백업 파일 압축
gzip topmkt_backup_*.sql

# 5. 백업 파일을 안전한 곳에 복사
```

### 방법 2: 데이터베이스 서버에서 직접 백업

데이터베이스가 별도 서버/컨테이너에 있다면:

```bash
# 1. DB 서버/컨테이너 접속
ssh db-server
# 또는
docker exec -it [DB컨테이너명] bash

# 2. 백업 실행
mysqldump -u root -p topmkt > /tmp/topmkt_backup.sql

# 3. 백업 파일 가져오기
scp db-server:/tmp/topmkt_backup.sql ./
# 또는
docker cp [DB컨테이너]:/tmp/topmkt_backup.sql ./
```

### 방법 3: phpMyAdmin 사용 (설치되어 있다면)

1. 웹 브라우저에서 phpMyAdmin 접속
2. topmkt 데이터베이스 선택
3. 내보내기(Export) 탭 클릭
4. 빠른 내보내기 → SQL 선택 → 실행

### 방법 4: PHP 웹 백업 스크립트

웹을 통해 백업할 수 있는 스크립트를 생성해드렸습니다:

---

## 📋 백업 체크리스트

### 필수 백업 항목:
- [ ] **users** 테이블 (사용자 정보)
- [ ] **posts** 테이블 (게시글)
- [ ] **comments** 테이블 (댓글)
- [ ] **user_sessions** 테이블 (세션 정보)
- [ ] **verification_codes** 테이블 (인증 코드)
- [ ] **settings** 테이블 (시스템 설정)

### 백업 확인:
- [ ] 백업 파일 크기가 0이 아닌지 확인
- [ ] .sql 파일에 CREATE TABLE 문이 포함되어 있는지 확인
- [ ] 데이터(INSERT 문)가 포함되어 있는지 확인

---

## 🛠️ 웹 기반 백업 스크립트

다음 스크립트를 실행하면 웹을 통해 DB 구조를 확인할 수 있습니다: