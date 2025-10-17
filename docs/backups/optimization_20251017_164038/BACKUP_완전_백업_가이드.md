# 🛡️ 탑마케팅 완전 백업 가이드

**작성일**: 2025-06-25  
**작성자**: Claude Code  
**목적**: React.js + TypeScript 전환 전 완벽한 시스템 백업

---

## 📋 목차

1. [백업 개요](#1-백업-개요)
2. [데이터베이스 백업](#2-데이터베이스-백업)
3. [파일 시스템 백업](#3-파일-시스템-백업)
4. [설정 파일 백업](#4-설정-파일-백업)
5. [Git 저장소 백업](#5-git-저장소-백업)
6. [Firebase 데이터 백업](#6-firebase-데이터-백업)
7. [백업 검증](#7-백업-검증)
8. [복구 절차](#8-복구-절차)
9. [백업 체크리스트](#9-백업-체크리스트)

---

## 1. 백업 개요

### 🎯 백업 목적
- **React 전환 전 완벽한 상태 보존**
- **롤백 가능한 복구 포인트 생성**
- **데이터 손실 Zero 보장**
- **개발 환경 완전 복원 가능**

### 📊 백업 범위
```
전체 시스템 백업
├── 데이터베이스 (MariaDB)
│   ├── 구조 (스키마)
│   ├── 데이터 (모든 테이블)
│   └── 권한 설정
├── 파일 시스템
│   ├── 소스 코드 (/var/www/html/topmkt)
│   ├── 업로드 파일 (public/assets/uploads)
│   └── 로그 파일
├── 설정 파일
│   ├── Apache 설정
│   ├── PHP 설정
│   └── SSL 인증서
├── Git 저장소
│   ├── 전체 커밋 히스토리
│   └── 브랜치 상태
└── Firebase 데이터
    ├── Realtime Database
    └── 설정 정보
```

---

## 2. 데이터베이스 백업

### 🗄️ 완전 데이터베이스 덤프

#### 2.1 전체 데이터베이스 백업
```bash
# 전체 데이터베이스 구조 + 데이터 백업
mysqldump -u root -pDnlszkem1! \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --all-databases \
  --result-file=/var/www/html/backup/mysql_full_backup_$(date +%Y%m%d_%H%M%S).sql

# topmkt 데이터베이스만 백업 (권장)
mysqldump -u root -pDnlszkem1! \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --add-drop-database \
  --databases topmkt \
  --result-file=/var/www/html/backup/topmkt_db_backup_$(date +%Y%m%d_%H%M%S).sql
```

#### 2.2 테이블별 백업 (추가 보안)
```bash
# 중요 테이블 개별 백업
TABLES=("users" "lectures" "posts" "comments" "company_profiles" "lecture_images")

for table in "${TABLES[@]}"; do
    mysqldump -u root -pDnlszkem1! topmkt $table \
        --result-file="/var/www/html/backup/table_${table}_$(date +%Y%m%d_%H%M%S).sql"
done
```

#### 2.3 데이터베이스 구조만 백업
```bash
# 스키마만 백업 (테이블 구조, 인덱스, 제약조건)
mysqldump -u root -pDnlszkem1! \
  --no-data \
  --routines \
  --triggers \
  --events \
  topmkt \
  --result-file=/var/www/html/backup/topmkt_schema_$(date +%Y%m%d_%H%M%S).sql
```

### 📊 테이블 통계 백업
```bash
# 테이블별 레코드 수 확인
mysql -u root -pDnlszkem1! -e "
USE topmkt;
SELECT 
    table_name,
    table_rows,
    ROUND(data_length/1024/1024, 2) as 'Data_MB',
    ROUND(index_length/1024/1024, 2) as 'Index_MB'
FROM information_schema.tables 
WHERE table_schema = 'topmkt'
ORDER BY table_rows DESC;
" > /var/www/html/backup/table_statistics_$(date +%Y%m%d_%H%M%S).txt
```

---

## 3. 파일 시스템 백업

### 📁 전체 소스 코드 백업

#### 3.1 rsync를 이용한 완전 복사
```bash
# 전체 프로젝트 폴더 백업
rsync -avh --progress \
  /var/www/html/topmkt/ \
  /var/www/html/backup/topmkt_source_$(date +%Y%m%d_%H%M%S)/

# 업로드 파일만 별도 백업 (용량이 클 수 있음)
rsync -avh --progress \
  /var/www/html/topmkt/public/assets/uploads/ \
  /var/www/html/backup/uploads_$(date +%Y%m%d_%H%M%S)/
```

#### 3.2 tar 압축 백업
```bash
# 소스 코드 압축 백업
cd /var/www/html
tar -czf /workspace/backup/topmkt_source_$(date +%Y%m%d_%H%M%S).tar.gz \
  --exclude='topmkt/logs/*' \
  --exclude='topmkt/cache/*' \
  --exclude='topmkt/.git' \
  topmkt/

# 업로드 파일 압축 백업
tar -czf /workspace/backup/uploads_$(date +%Y%m%d_%H%M%S).tar.gz \
  -C /var/www/html/topmkt/public/assets uploads/
```

### 📝 로그 파일 백업
```bash
# 로그 파일 백업
mkdir -p /workspace/backup/logs_$(date +%Y%m%d_%H%M%S)
cp -r /var/www/html/topmkt/logs/* /workspace/backup/logs_$(date +%Y%m%d_%H%M%S)/ 2>/dev/null || true
cp /var/log/httpd/access_log /workspace/backup/logs_$(date +%Y%m%d_%H%M%S)/apache_access.log 2>/dev/null || true
cp /var/log/httpd/error_log /workspace/backup/logs_$(date +%Y%m%d_%H%M%S)/apache_error.log 2>/dev/null || true
```

---

## 4. 설정 파일 백업

### ⚙️ 시스템 설정 백업

#### 4.1 Apache 설정
```bash
# Apache 설정 파일 백업
mkdir -p /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/apache
cp /etc/httpd/conf/httpd.conf /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/apache/
cp -r /etc/httpd/conf.d/ /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/apache/
```

#### 4.2 PHP 설정
```bash
# PHP 설정 파일 백업
mkdir -p /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/php
cp /etc/php.ini /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/php/
cp -r /etc/php.d/ /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/php/ 2>/dev/null || true
```

#### 4.3 MariaDB 설정
```bash
# MariaDB 설정 파일 백업
mkdir -p /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/mysql
cp /etc/my.cnf /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/mysql/ 2>/dev/null || true
cp -r /etc/my.cnf.d/ /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/mysql/ 2>/dev/null || true
```

#### 4.4 SSL 인증서
```bash
# SSL 인증서 백업
mkdir -p /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/ssl
cp -r /etc/ssl/certs/topmktx.com* /workspace/backup/config_$(date +%Y%m%d_%H%M%S)/ssl/ 2>/dev/null || true
```

---

## 5. Git 저장소 백업

### 🔄 Git 완전 백업

#### 5.1 현재 상태 커밋
```bash
cd /var/www/html/topmkt

# 현재 모든 변경사항 커밋
git add .
git commit -m "React 전환 전 완전 백업 포인트

🎯 백업 시점: $(date '+%Y-%m-%d %H:%M:%S')
📋 백업 범위: 전체 소스 코드, 설정 파일
🚀 다음 단계: React.js + TypeScript 전환
📝 복구 방법: git checkout $(git rev-parse HEAD)

🧠 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

#### 5.2 태그 생성
```bash
# 백업 포인트 태그 생성
git tag -a "backup-before-react-$(date +%Y%m%d)" -m "완전 백업 포인트 - React 전환 전"

# 모든 브랜치 확인
git branch -a

# 원격 저장소에 푸시 (있다면)
git push origin --all
git push origin --tags
```

#### 5.3 로컬 백업
```bash
# Git 저장소 전체 복제
git clone --bare /var/www/html/topmkt /workspace/backup/git_repo_$(date +%Y%m%d_%H%M%S).git
```

---

## 6. Firebase 데이터 백업

### 🔥 Firebase 백업

#### 6.1 Realtime Database 백업
```bash
# Firebase CLI 설치 (필요시)
npm install -g firebase-tools

# Firebase 로그인 및 백업
firebase login
firebase use --add  # 프로젝트 선택

# 데이터베이스 내보내기
mkdir -p /workspace/backup/firebase_$(date +%Y%m%d_%H%M%S)
firebase database:get / --output /workspace/backup/firebase_$(date +%Y%m%d_%H%M%S)/realtime_db.json
```

#### 6.2 Firebase 설정 백업
```bash
# Firebase 설정 파일 백업
cp /var/www/html/topmkt/public/assets/js/firebase-config.js \
   /workspace/backup/firebase_$(date +%Y%m%d_%H%M%S)/firebase-config.js 2>/dev/null || true
```

---

## 7. 백업 검증

### ✅ 백업 무결성 확인

#### 7.1 파일 체크섬 생성
```bash
# 백업 파일들의 체크섬 생성
cd /workspace/backup
find . -type f -exec sha256sum {} + > backup_checksums_$(date +%Y%m%d_%H%M%S).txt
```

#### 7.2 데이터베이스 백업 검증
```bash
# SQL 파일 구문 검증
mysql -u root -pDnlszkem1! --execute="SET SESSION sql_mode = 'STRICT_TRANS_TABLES';" < /workspace/backup/topmkt_db_backup_*.sql
echo "Database backup verification: $?"
```

#### 7.3 백업 통계 생성
```bash
# 백업 상세 정보
cat > /workspace/backup/backup_info_$(date +%Y%m%d_%H%M%S).txt << EOF
=== 탑마케팅 완전 백업 정보 ===
백업 일시: $(date)
백업 위치: /workspace/backup
서버 정보: $(uname -a)

=== 백업 파일 목록 ===
$(ls -lah /workspace/backup/)

=== 디스크 사용량 ===
$(df -h /workspace/backup)

=== 데이터베이스 정보 ===
$(mysql -u root -pDnlszkem1! -e "SELECT VERSION(), NOW();")

=== Git 정보 ===
현재 브랜치: $(cd /var/www/html/topmkt && git branch --show-current)
마지막 커밋: $(cd /var/www/html/topmkt && git log -1 --oneline)
태그: $(cd /var/www/html/topmkt && git tag | tail -5)

=== PHP 버전 ===
$(php -v | head -1)

=== Apache 상태 ===
$(systemctl status httpd | head -5)
EOF
```

---

## 8. 복구 절차

### 🔄 완전 복구 가이드

#### 8.1 데이터베이스 복구
```bash
# 데이터베이스 완전 복구
mysql -u root -pDnlszkem1! < /workspace/backup/topmkt_db_backup_YYYYMMDD_HHMMSS.sql

# 특정 테이블만 복구
mysql -u root -pDnlszkem1! topmkt < /workspace/backup/table_users_YYYYMMDD_HHMMSS.sql
```

#### 8.2 파일 시스템 복구
```bash
# 소스 코드 복구
rsync -avh --delete /workspace/backup/topmkt_source_YYYYMMDD_HHMMSS/ /var/www/html/topmkt/

# 또는 압축 파일에서 복구
cd /var/www/html
tar -xzf /workspace/backup/topmkt_source_YYYYMMDD_HHMMSS.tar.gz
```

#### 8.3 Git 복구
```bash
# 특정 커밋으로 롤백
cd /var/www/html/topmkt
git checkout backup-before-react-YYYYMMDD

# 또는 저장소 완전 복구
git clone /workspace/backup/git_repo_YYYYMMDD_HHMMSS.git /var/www/html/topmkt_restored
```

---

## 9. 백업 체크리스트

### ✅ 실행 전 체크리스트

```
[ ] 1. 백업 디렉토리 생성 확인
    mkdir -p /workspace/backup

[ ] 2. 충분한 디스크 공간 확인 (최소 5GB)
    df -h /workspace

[ ] 3. 데이터베이스 연결 확인
    mysql -u root -pDnlszkem1! -e "SELECT 1;"

[ ] 4. Git 상태 확인
    cd /var/www/html/topmkt && git status

[ ] 5. 진행 중인 사용자 세션 확인
    # 사용자가 없는 시간대에 실행

[ ] 6. 서비스 일시 중지 고려
    # 필요시 유지보수 모드 활성화
```

### 📋 백업 실행 체크리스트

```
[ ] 1. 데이터베이스 전체 백업
[ ] 2. 테이블별 개별 백업
[ ] 3. 소스 코드 백업 (rsync + tar)
[ ] 4. 업로드 파일 백업
[ ] 5. 설정 파일 백업 (Apache, PHP, MySQL)
[ ] 6. SSL 인증서 백업
[ ] 7. Git 커밋 및 태그 생성
[ ] 8. Firebase 데이터 백업
[ ] 9. 백업 파일 체크섬 생성
[ ] 10. 백업 무결성 검증
```

### 🔍 백업 완료 후 체크리스트

```
[ ] 1. 모든 백업 파일 존재 확인
[ ] 2. SQL 파일 구문 검증
[ ] 3. 압축 파일 무결성 확인
[ ] 4. Git 태그 생성 확인
[ ] 5. 백업 정보 문서 생성
[ ] 6. 복구 테스트 실행 (별도 환경)
[ ] 7. 백업 위치 문서화
[ ] 8. 팀 공유 (백업 완료 알림)
```

---

## 🚀 백업 실행 스크립트

### 📝 원클릭 백업 스크립트

```bash
#!/bin/bash
# complete_backup.sh - 탑마케팅 완전 백업 스크립트

BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/workspace/backup"
PROJECT_DIR="/var/www/html/topmkt"

echo "🛡️ 탑마케팅 완전 백업 시작 - $BACKUP_DATE"

# 백업 디렉토리 생성
mkdir -p "$BACKUP_DIR"

# 1. 데이터베이스 백업
echo "📊 데이터베이스 백업 중..."
mysqldump -u root -pDnlszkem1! --single-transaction --routines --triggers --events --databases topmkt \
  --result-file="$BACKUP_DIR/topmkt_db_backup_$BACKUP_DATE.sql"

# 2. 소스 코드 백업
echo "📁 소스 코드 백업 중..."
rsync -avh --progress "$PROJECT_DIR/" "$BACKUP_DIR/topmkt_source_$BACKUP_DATE/"

# 3. Git 커밋 및 태그
echo "🔄 Git 백업 중..."
cd "$PROJECT_DIR"
git add .
git commit -m "React 전환 전 완전 백업 - $BACKUP_DATE"
git tag -a "backup-before-react-$(date +%Y%m%d)" -m "완전 백업 포인트"

# 4. 백업 검증
echo "✅ 백업 검증 중..."
cd "$BACKUP_DIR"
find . -type f -exec sha256sum {} + > "backup_checksums_$BACKUP_DATE.txt"

echo "🎉 백업 완료! 위치: $BACKUP_DIR"
echo "📝 복구 방법은 BACKUP_완전_백업_가이드.md 참조"
```

---

## 📞 문의 및 지원

**긴급 복구 시 참조:**
- 백업 문서: `/workspace/docs/BACKUP_완전_백업_가이드.md`
- 백업 위치: `/workspace/backup/`
- Git 태그: `backup-before-react-YYYYMMDD`

**주의사항:**
- 백업은 React 전환 직전에 실행
- 복구 시 반드시 테스트 환경에서 먼저 검증
- 프로덕션 복구는 신중히 진행

---

**📝 문서 관리자**: 개발팀  
**🔄 업데이트**: React 전환 완료 후 백업 전략 재검토  
**⚠️ 중요도**: 🔴 Critical - React 전환 전 필수 실행