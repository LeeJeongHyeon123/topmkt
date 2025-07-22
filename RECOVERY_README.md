# 🚨 긴급 복구 가이드

**서버가 삭제되었나요? 걱정하지 마세요!** 이 가이드를 따라하면 완전히 복구할 수 있습니다.

---

## 🚀 빠른 복구 (15분 완료)

### 1단계: 서버 준비 (3분)
```bash
# Ubuntu/Debian
sudo apt update && sudo apt install -y git nginx php php-fpm php-mysql mariadb-server

# CentOS/RHEL
sudo yum update -y && sudo yum install -y git nginx php php-fpm php-mysql mariadb-server
```

### 2단계: 프로젝트 복구 (2분)
```bash
# 프로젝트 클론
sudo git clone https://github.com/LeeJeongHyeon123/topmkt.git /var/www/topmkt
cd /var/www/topmkt

# 자동 복구 스크립트 실행
sudo chmod +x scripts/restore.sh
sudo ./scripts/restore.sh your-domain.com
```

### 3단계: 환경 설정 (5분)
```bash
# 환경 파일 설정
sudo cp .env.example .env
sudo nano .env  # 실제 값으로 수정
```

### 4단계: 데이터베이스 설정 (3분)
```bash
# MariaDB 시작
sudo systemctl start mariadb
sudo mysql_secure_installation

# 데이터베이스 복구
mysql -u root -p < database/schema.sql
```

### 5단계: 최종 확인 (2분)
```bash
# 서비스 시작
sudo systemctl start nginx php-fpm mariadb
sudo systemctl enable nginx php-fpm mariadb

# 테스트
curl -I http://your-domain.com
```

---

## 📋 필수 정보 체크리스트

복구 전에 다음 정보를 준비하세요:

### ✅ 데이터베이스 정보
- [ ] 데이터베이스 이름: `topmkt`
- [ ] 사용자명: (새로 생성)
- [ ] 비밀번호: (새로 생성)

### ✅ Firebase 설정
- [ ] Firebase 프로젝트 ID
- [ ] API 키
- [ ] 데이터베이스 URL

### ✅ 도메인 설정
- [ ] 도메인명
- [ ] DNS 설정
- [ ] SSL 인증서

---

## 🔧 상세 복구 가이드

자세한 복구 절차는 다음 파일을 참조하세요:
- **완전 가이드**: [BACKUP_GUIDE.md](BACKUP_GUIDE.md)
- **자동 복구**: `scripts/restore.sh`
- **일일 백업**: `scripts/daily_backup.sh`

---

## 📞 긴급 연락처

복구 중 문제가 발생하면:
- **이메일**: jh@wincard.kr
- **전화**: 070-4138-8899
- **GitHub**: https://github.com/LeeJeongHyeon123/topmkt

---

## 🛡️ 백업 상태 확인

현재 백업 상태:
- ✅ **GitHub 저장소**: https://github.com/LeeJeongHyeon123/topmkt.git
- ✅ **완전한 소스코드** (Firebase 채팅 시스템 포함)
- ✅ **데이터베이스 스키마**: `database/schema.sql`
- ✅ **환경 설정 템플릿**: `.env.example`
- ✅ **자동 복구 스크립트**: `scripts/restore.sh`

**마지막 백업**: 2025-06-12 (Firebase 채팅 시스템 완료)

---

⚡ **팁**: 자동 복구 스크립트를 사용하면 대부분의 과정이 자동화됩니다!

```bash
sudo ./scripts/restore.sh your-domain.com
```