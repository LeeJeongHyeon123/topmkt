# 🔥 탑마케팅 플랫폼 완전 백업 및 복구 가이드

**생성일**: 2025-06-12  
**최종 업데이트**: 2025-06-12  
**목적**: 서버가 통째로 삭제되어도 완전 복구 가능한 백업 시스템

**✅ 백업 시스템 완료 상태**: 
- Git 백업: GitHub에 완전 백업 완료
- DB 백업: 자동화 스크립트로 매일 백업 (`/backup/topmkt/`)
- 복구 스크립트: 원클릭 복구 시스템 구축 완료

---

## 📋 백업 완료 상태

### ✅ Git 저장소 백업 완료
- **원격 저장소**: https://github.com/LeeJeongHyeon123/topmkt.git
- **최신 커밋**: Firebase 채팅 시스템 구현 완료 및 전체 프로젝트 업데이트
- **백업 날짜**: 2025-06-12

### ✅ 포함된 백업 내용
1. **전체 소스 코드**
   - PHP MVC 구조 완전 백업
   - Firebase 채팅 시스템 코드
   - 모든 컨트롤러, 모델, 뷰 파일
   
2. **데이터베이스 스키마**
   - `database/schema.sql`: 완전한 DB 구조
   - 모든 테이블, 인덱스, 외래키 포함
   - 기본 설정 데이터 포함

3. **환경 설정 템플릿**
   - `.env.example`: 모든 필요한 환경 변수 예시
   - 데이터베이스, Firebase, SMS, 이메일 설정 포함

4. **완전한 문서**
   - API 설계서 (MVC 구조)
   - 데이터베이스 구조 문서
   - 개발 노트 (성능 테스트 결과 포함)
   - 디렉토리 구조 가이드

---

## 🚀 완전 복구 절차

### 🔄 자동 복구 (권장)

```bash
# 새 서버에서 원클릭 복구
wget https://raw.githubusercontent.com/LeeJeongHyeon123/topmkt/main/scripts/restore.sh
chmod +x restore.sh
./restore.sh www.topmktx.com
```

### 📋 수동 복구 절차

#### 1단계: 서버 환경 준비

```bash
# 1. 기본 패키지 설치 (Ubuntu/CentOS)
sudo apt update && sudo apt install -y git nginx php php-fpm php-mysql php-curl php-json php-mbstring mariadb-server

# 2. Composer 설치
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 3. Node.js 설치 (필요한 경우)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2단계: 프로젝트 복구

```bash
# 1. Git 저장소 클론
git clone https://github.com/LeeJeongHyeon123/topmkt.git
cd topmkt

# 2. 환경 설정 파일 생성
cp .env.example .env
# .env 파일을 실제 환경에 맞게 수정

# 3. 의존성 설치 (있는 경우)
# composer install
# npm install

# 4. 권한 설정
sudo chown -R www-data:www-data /var/www/topmkt
sudo chmod -R 755 /var/www/topmkt
sudo chmod -R 777 /var/www/topmkt/public/assets/uploads
sudo chmod -R 777 /var/www/topmkt/logs
```

### 3단계: 데이터베이스 복구

```bash
# 1. MariaDB 서비스 시작
sudo systemctl start mariadb
sudo systemctl enable mariadb

# 2. 데이터베이스 보안 설정
sudo mysql_secure_installation

# 3. 데이터베이스 생성 및 스키마 복구
mysql -u root -p < database/schema.sql

# 4. 실제 데이터 복원 (백업 파일이 있는 경우)
# 최신 백업에서 복원:
gunzip -c /backup/topmkt/topmkt_backup_YYYYMMDD_HHMMSS.sql.gz | mysql -u root -p topmkt

# 5. 사용자 생성 및 권한 부여
mysql -u root -p
```

```sql
-- 데이터베이스 사용자 생성
CREATE USER 'topmkt_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON topmkt.* TO 'topmkt_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4단계: 웹 서버 설정

#### Nginx 설정 예시
```nginx
# /etc/nginx/sites-available/topmkt
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/topmkt/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# Nginx 사이트 활성화
sudo ln -s /etc/nginx/sites-available/topmkt /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5단계: Firebase 설정

1. **Firebase 콘솔에서 새 프로젝트 생성**
   - https://console.firebase.google.com
   - Realtime Database 활성화

2. **Firebase 설정 복사**
   - 프로젝트 설정에서 웹 앱 추가
   - 설정 정보를 `.env` 파일에 업데이트

3. **Firebase 보안 규칙 설정**
```json
{
  "rules": {
    "chatRooms": {
      "$roomId": {
        ".read": "auth != null && (data.child('participants').child(auth.uid).exists() || newData.child('participants').child(auth.uid).exists())",
        ".write": "auth != null && (data.child('participants').child(auth.uid).exists() || newData.child('participants').child(auth.uid).exists())"
      }
    },
    "messages": {
      "$roomId": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    },
    "userRooms": {
      "$userId": {
        ".read": "auth != null && auth.uid == $userId",
        ".write": "auth != null && auth.uid == $userId"
      }
    }
  }
}
```

### 6단계: 최종 확인 및 테스트

```bash
# 1. 웹 서버 상태 확인
sudo systemctl status nginx php8.1-fpm mariadb

# 2. 로그 확인
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.1-fpm.log

# 3. 권한 재확인
ls -la /var/www/topmkt/public/assets/uploads/

# 4. 사이트 접속 테스트
curl -I http://your-domain.com
```

---

## 🔑 중요한 보안 정보 별도 백업

### 반드시 별도 저장할 정보:
1. **Firebase 설정 정보**
   - API 키, 프로젝트 ID
   - 서비스 계정 키 (JSON)

2. **데이터베이스 계정 정보**
   - 데이터베이스 사용자명/비밀번호
   - root 비밀번호

3. **SMS API 정보**
   - Cool SMS API 키/시크릿

4. **이메일 설정**
   - SMTP 서버 정보, 계정

5. **SSL 인증서**
   - Let's Encrypt 또는 유료 SSL 인증서

### 보안 정보 저장 방법:
- 암호화된 비밀번호 관리자 사용
- 안전한 클라우드 저장소에 암호화하여 보관
- 팀 내 안전한 공유 방법 확립

---

## 📊 백업 검증 체크리스트

### ✅ 코드 백업 확인:
- [ ] Git 저장소 최신 상태 확인
- [ ] 모든 소스 파일 포함 확인
- [ ] .gitignore 적절한 설정 확인

### ✅ 데이터베이스 백업 확인:
- [ ] schema.sql 파일 존재 확인
- [ ] 모든 테이블 구조 포함 확인
- [ ] 인덱스 및 외래키 포함 확인

### ✅ 설정 백업 확인:
- [ ] .env.example 파일 완성도 확인
- [ ] 모든 필요한 환경 변수 포함 확인
- [ ] 예시 값들이 적절히 설정되어 있는지 확인

### ✅ 문서 백업 확인:
- [ ] API 설계서 최신 상태 확인
- [ ] 데이터베이스 구조 문서 확인
- [ ] 개발 노트 업데이트 확인

---

## 🚨 정기 백업 권장사항

### 일일 백업:
```bash
# 자동 백업 스크립트 예시
#!/bin/bash
cd /var/www/topmkt
git add -A
git commit -m "일일 자동 백업: $(date '+%Y-%m-%d %H:%M:%S')"
git push origin master
```

### 주간 데이터베이스 백업:
```bash
# 데이터베이스 덤프
mysqldump -u topmkt_user -p topmkt > backup_$(date +%Y%m%d).sql
```

### 월간 전체 백업:
- 코드 + 데이터베이스 + 업로드 파일 전체 백업
- 다른 저장소에 추가 백업본 생성

---

## 📞 복구 시 도움이 필요한 경우

### 개발팀 연락처:
- **프로젝트 관리자**: jh@wincard.kr
- **기술 지원**: 070-4138-8899

### 주요 참고 자료:
- **GitHub 저장소**: https://github.com/LeeJeongHyeon123/topmkt.git
- **Firebase 콘솔**: https://console.firebase.google.com
- **도메인 관리**: (도메인 제공업체 정보 추가 필요)

---

**⚠️ 주의사항**: 이 문서는 완전한 복구를 위한 가이드입니다. 실제 복구 시에는 최신 환경에 맞게 설정을 조정해야 할 수 있습니다.

**🔒 보안**: 실제 운영 환경의 비밀번호나 API 키는 이 문서에 포함하지 마세요. 별도의 안전한 저장소에 보관하세요.