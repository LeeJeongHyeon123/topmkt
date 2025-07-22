# 🐳 도커 환경 완전 백업 가이드

**중요**: 도커 환경에서는 컨테이너 내부의 데이터베이스를 별도로 백업해야 합니다!

---

## 🚨 현재 백업 상태 확인

### ✅ 이미 백업된 것들:
- **소스 코드**: GitHub에 완전 백업 완료
- **데이터베이스 스키마**: `database/schema.sql` (테이블 구조)
- **환경 설정**: `.env.example` (설정 템플릿)

### ❌ 추가로 필요한 백업:
- **실제 데이터베이스 데이터** (도커 컨테이너 내부)
- **업로드된 파일들** (도커 볼륨 또는 바인드 마운트)
- **도커 설정** (docker-compose.yml, Dockerfile)

---

## 🔍 도커 환경 확인 및 백업

### 1단계: 도커 컨테이너 확인
```bash
# 실행 중인 컨테이너 확인
docker ps

# 모든 컨테이너 확인 (중지된 것 포함)
docker ps -a

# 컨테이너 상세 정보
docker inspect [컨테이너명]
```

### 2단계: 데이터베이스 컨테이너 백업

#### MySQL/MariaDB 컨테이너 백업:
```bash
# 자동 백업 스크립트 사용
./scripts/docker_db_backup.sh /backup/directory

# 또는 수동 백업
docker exec [DB컨테이너명] mysqldump -u root -p topmkt > backup_$(date +%Y%m%d).sql

# 압축
gzip backup_$(date +%Y%m%d).sql
```

#### PostgreSQL 컨테이너 백업:
```bash
docker exec [DB컨테이너명] pg_dump -U postgres topmkt > backup_$(date +%Y%m%d).sql
```

### 3단계: 볼륨 및 바인드 마운트 백업
```bash
# 볼륨 목록 확인
docker volume ls

# 특정 볼륨 백업
docker run --rm -v [볼륨명]:/backup-volume -v $(pwd):/backup alpine tar czf /backup/volume_backup.tar.gz -C /backup-volume .

# 업로드 파일 백업 (예시)
docker cp [컨테이너명]:/var/www/html/uploads ./uploads_backup
tar czf uploads_backup_$(date +%Y%m%d).tar.gz uploads_backup/
```

### 4단계: 도커 설정 파일 백업
```bash
# docker-compose.yml 복사
cp docker-compose.yml docker-compose.yml.backup

# Dockerfile들 백업
find . -name "Dockerfile*" -exec cp {} {}.backup \;

# .env 파일 백업 (중요!)
cp .env .env.backup
```

---

## 🚀 완전 복구 절차 (도커 환경)

### 1단계: 프로젝트 복원
```bash
# Git에서 복원
git clone https://github.com/LeeJeongHyeon123/topmkt.git
cd topmkt
```

### 2단계: 도커 환경 설정
```bash
# 환경 파일 설정
cp .env.example .env
# .env 파일을 실제 값으로 수정

# 도커 컴포즈 파일이 있다면
docker-compose up -d
```

### 3단계: 데이터베이스 복원
```bash
# 데이터베이스 백업 복원
gunzip -c backup_20250612.sql.gz | docker exec -i [DB컨테이너명] mysql -u root -p topmkt

# 또는 스키마만 복원 (데이터 없음)
docker exec -i [DB컨테이너명] mysql -u root -p topmkt < database/schema.sql
```

### 4단계: 파일 복원
```bash
# 업로드 파일 복원
docker cp uploads_backup/. [웹컨테이너명]:/var/www/html/uploads/

# 볼륨 복원
docker run --rm -v [볼륨명]:/restore-volume -v $(pwd):/backup alpine tar xzf /backup/volume_backup.tar.gz -C /restore-volume
```

---

## 🔧 현재 환경에서 즉시 해야 할 백업

### 1. 도커 컨테이너 확인
```bash
# 이 명령어들을 실행해서 현재 상태 파악
docker ps -a
docker volume ls
docker network ls
```

### 2. 실제 데이터 백업
```bash
# 데이터베이스 컨테이너가 있다면
docker exec [DB컨테이너] mysqldump -u root -p[비밀번호] topmkt > current_data_backup.sql

# 업로드 파일이 있다면
docker cp [컨테이너]:/var/www/html/uploads ./current_uploads_backup
```

### 3. 설정 파일 백업
```bash
# 현재 디렉토리의 도커 관련 파일들
ls -la docker-compose.yml .env Dockerfile*
```

---

## 📋 도커 환경 백업 체크리스트

### ✅ 코드 백업:
- [x] GitHub 저장소 백업 완료
- [x] 소스 코드 모든 파일 포함
- [x] Firebase 채팅 시스템 포함

### ❗ 데이터 백업 (도커 환경):
- [ ] **데이터베이스 실제 데이터 덤프**
- [ ] **업로드된 파일들**
- [ ] **도커 볼륨 데이터**
- [ ] **환경 변수 파일 (.env)**

### ❗ 설정 백업:
- [ ] **docker-compose.yml**
- [ ] **Dockerfile들**
- [ ] **nginx 설정 (컨테이너 내부)**
- [ ] **PHP 설정 (컨테이너 내부)**

---

## 🚨 긴급 대응 방안

### 현재 해야 할 일:

1. **도커 컨테이너 상태 확인**:
   ```bash
   docker ps -a
   docker images
   ```

2. **데이터베이스 백업 (컨테이너가 실행 중이라면)**:
   ```bash
   ./scripts/docker_db_backup.sh /tmp/emergency_backup
   ```

3. **중요 파일 백업**:
   ```bash
   # 현재 업로드 파일들
   tar czf uploads_emergency_backup.tar.gz public/assets/uploads/
   
   # 환경 설정
   cp .env .env.emergency_backup
   ```

4. **Git에 추가 커밋**:
   ```bash
   git add .
   git commit -m "긴급 도커 환경 백업 파일 추가"
   git push origin master
   ```

---

## 💡 권장사항

### 정기 백업 자동화:
```bash
# crontab에 추가
0 2 * * * /path/to/topmkt/scripts/docker_db_backup.sh /backup/daily
0 0 * * 0 /path/to/topmkt/scripts/weekly_full_backup.sh
```

### 백업 검증:
```bash
# 백업 파일 무결성 확인
gzip -t backup_file.sql.gz

# 백업에서 복원 테스트
docker run --rm mysql:latest mysql -e "source backup.sql" test_db
```

### 모니터링:
- 백업 파일 크기 추이 확인
- 백업 시간 기록
- 복원 테스트 정기 실행

---

**⚠️ 중요**: 도커 환경에서는 컨테이너가 삭제되면 내부 데이터도 함께 사라집니다. 반드시 실제 데이터를 별도로 백업하세요!