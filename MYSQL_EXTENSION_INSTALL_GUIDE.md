# MySQL 확장 설치 가이드

## 문제 상황
강의 신청 시 "SyntaxError: Unexpected token '<', "<h1>시스템 오류"" 오류가 발생하는 근본 원인은 **MySQLi PHP 확장이 설치되어 있지 않기 때문**입니다.

## 확인 방법
```bash
# MySQLi 확장 설치 여부 확인
php -m | grep -i mysql

# PDO 드라이버 확인
php -r "print_r(PDO::getAvailableDrivers());"

# 웹에서 확인
https://www.topmktx.com/debug_registration_api.php
```

## 현재 상태 확인 완료 ✅
**디버깅 결과 (2025-07-07):**
- ❌ MySQLi 확장: 설치되지 않음 
- ❌ cURL 확장: 설치되지 않음
- ✅ JSON, Session, MBString: 정상 설치됨

## 해결 방법

### 1. CentOS/RHEL 계열 (🎯 권장 해결책)
```bash
# MySQL/MySQLi + cURL 확장 설치 (둘 다 필요)
sudo yum install php-mysqli php-mysqlnd php-curl

# 또는 dnf를 사용하는 경우
sudo dnf install php-mysqli php-mysqlnd php-curl

# Apache + PHP-FPM 재시작
sudo systemctl restart httpd
sudo systemctl restart php-fpm

# 설치 확인
php -m | grep -E "(mysqli|curl)"
```

### 2. Ubuntu/Debian 계열
```bash
# MySQL/MySQLi 확장 설치
sudo apt-get update
sudo apt-get install php-mysqli php-mysql

# Apache 재시작
sudo systemctl restart apache2
```

### 3. Docker 환경
```bash
# PHP Docker 이미지에서 확장 설치
docker-php-ext-install mysqli pdo_mysql

# 또는 Dockerfile에 추가
RUN docker-php-ext-install mysqli pdo_mysql
```

### 4. 컴파일 설치 (소스에서 빌드한 경우)
```bash
# PHP 재컴파일 시 옵션 추가
./configure --with-mysqli --with-pdo-mysql
make && make install
```

## 설치 확인
```bash
# 설치 후 확인
php -m | grep -i mysql

# 예상 출력:
# mysqli
# mysqlnd
# pdo_mysql
```

## 임시 해결책 (개발 환경)
만약 즉시 MySQL 확장을 설치할 수 없는 경우, SQLite를 사용한 임시 해결책:

```bash
# SQLite 확장 설치 (보통 기본 설치됨)
sudo yum install php-sqlite3  # CentOS/RHEL
sudo apt-get install php-sqlite3  # Ubuntu/Debian
```

그 후 Database 클래스를 SQLite용으로 임시 수정 가능합니다.

## 문제 해결 순서
1. **우선순위 1**: MySQLi 확장 설치
2. **우선순위 2**: PDO MySQL 드라이버 설치
3. **우선순위 3**: Database 클래스를 PDO로 변경

## 주의사항
- 확장 설치 후 반드시 웹서버(Apache/Nginx) 재시작 필요
- PHP-FPM 사용 시 `sudo systemctl restart php-fpm` 실행
- 확장 설치는 시스템 관리자 권한 필요

## 연락처
이 가이드로 해결되지 않을 경우:
- 시스템 관리자에게 문의
- 호스팅 업체 기술지원팀 연락
- PHP 설정 확인: `php -i | grep mysql`